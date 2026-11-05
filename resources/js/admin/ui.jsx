import { Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import L from 'leaflet';
import Icon from '../components/Icon';
import { getJSON, u } from '../lib/url';
import { BOUNDS_MAINLAND } from '../map/kit';

export const STATUS = { draft: 'Borrador', published: 'Publicado', archived: 'Archivado', scheduled: 'Programado' };
export const fechaHora = (iso) => (iso ? new Date(iso).toLocaleString('es-CO', { dateStyle: 'medium', timeStyle: 'short' }) : '');

/** Token CSRF actual (cookie XSRF-TOKEN de Laravel; se renueva al iniciar sesión). */
export function xsrf() {
    const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/);
    return m ? decodeURIComponent(m[1]) : '';
}

export async function postFiles(path, files, extra = {}, onProgress) {
    const fd = new FormData();
    [...files].forEach((f) => fd.append('files[]', f));
    Object.entries(extra).forEach(([k, v]) => fd.append(k, v));
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', u(path));
        xhr.setRequestHeader('X-XSRF-TOKEN', xsrf());
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.upload.onprogress = (e) => e.lengthComputable && onProgress?.(Math.round((100 * e.loaded) / e.total));
        xhr.onload = () => {
            let data = {};
            try { data = JSON.parse(xhr.responseText); } catch { /* respuesta vacía */ }
            if (xhr.status === 413 || (xhr.status === 422 && data.errors && !Array.isArray(data.errors))) {
                const msgs = data.errors ? Object.values(data.errors).flat() : ['El archivo supera el tamaño máximo que permite el servidor.'];
                resolve({ items: [], errors: msgs });
            } else if (xhr.status >= 200 && xhr.status < 300 || xhr.status === 422) resolve(data);
            else reject(new Error('HTTP ' + xhr.status));
        };
        xhr.onerror = () => reject(new Error('red'));
        xhr.send(fd);
    });
}

export function Field({ label, htmlFor, error, help, children, wide, style }) {
    return (
        <div className="field" style={{ ...(wide ? { gridColumn: '1/-1' } : {}), ...style }}>
            {label && <label className="field-label" htmlFor={htmlFor}>{label}</label>}
            {children}
            {help && <p className="field-help">{help}</p>}
            {error && <p className="field-error">{error}</p>}
        </div>
    );
}

export function Input({ form, name, label, help, type = 'text', wide, ...rest }) {
    const value = name.split('.').reduce((o, k) => o?.[k], form.data) ?? '';
    const err = form.errors[name];
    return (
        <Field label={label} htmlFor={name} error={err} help={help} wide={wide}>
            <input className={`input ${err ? 'is-invalid' : ''}`} id={name} name={name} type={type} value={value} onChange={(e) => form.setData(name, e.target.value)} {...rest} />
        </Field>
    );
}

export function Check({ form, name, label, help }) {
    return (
        <label className="check">
            <input type="checkbox" checked={!!form.data[name]} onChange={(e) => form.setData(name, e.target.checked)} />
            <span>{label}{help && <small className="field-help" style={{ display: 'block' }}>{help}</small>}</span>
        </label>
    );
}

export function Pager({ page, pages }) {
    if (pages < 2) return null;
    const go = (p) => {
        const params = new URLSearchParams(window.location.search);
        params.set('pagina', p);
        router.get(window.location.pathname + '?' + params, {}, { preserveState: true });
    };
    return (
        <nav className="pagination">
            {Array.from({ length: pages }, (_, i) => i + 1).map((p) => (p === page ? <span key={p} className="is-current">{p}</span> : <button key={p} type="button" className="btn btn-outline btn-sm" onClick={() => go(p)}>{p}</button>))}
        </nav>
    );
}

export const Thumb = ({ src }) => <img className="t-thumb" src={src || u('/assets/img/placeholder.svg')} alt="" />;

/** Biblioteca multimedia en una ventana: buscar, elegir y subir sin salir del formulario. */
export function MediaPicker({ multiple, kind, onPick, onClose }) {
    const [items, setItems] = useState([]);
    const [page, setPage] = useState(1);
    const [pages, setPages] = useState(1);
    const [q, setQ] = useState('');
    const [tipo, setTipo] = useState(kind === 'image' ? 'image' : kind === 'video' ? 'video' : '');
    const [sel, setSel] = useState([]);
    const [msg, setMsg] = useState('Cargando…');
    const [progress, setProgress] = useState(null);
    const load = async (p, reset) => {
        setMsg('Cargando…');
        try {
            const params = new URLSearchParams({ pagina: p, q, tipo });
            const data = await getJSON('/admin/api/multimedia?' + params);
            setItems((prev) => (reset ? data.items : [...prev, ...data.items]));
            setPages(data.pages);
            setPage(p);
            setMsg(data.total ? '' : 'No hay archivos. Sube uno con el botón «Subir».');
        } catch { setMsg('No se pudo cargar la biblioteca.'); }
    };
    useEffect(() => { const t = setTimeout(() => load(1, true), 200); return () => clearTimeout(t); }, [q, tipo]); // eslint-disable-line react-hooks/exhaustive-deps
    useEffect(() => {
        const key = (e) => e.key === 'Escape' && onClose();
        window.addEventListener('keydown', key);
        document.body.style.overflow = 'hidden';
        return () => { window.removeEventListener('keydown', key); document.body.style.overflow = ''; };
    }, [onClose]);
    const toggle = (it) => setSel((s) => (s.some((x) => x.id === it.id) ? s.filter((x) => x.id !== it.id) : multiple ? [...s, it] : [it]));
    const upload = async (files) => {
        if (!files.length) return;
        setProgress(0);
        try {
            const data = await postFiles('/admin/multimedia/subir', files, {}, setProgress);
            if (data.errors?.length) setMsg(data.errors.join(' '));
            setItems((prev) => [...data.items, ...prev]);
            setSel((s) => (multiple ? [...s, ...data.items] : data.items.slice(0, 1)));
        } catch { setMsg('No se pudo subir el archivo.'); }
        setProgress(null);
    };
    const accept = kind === 'image' ? '.jpg,.jpeg,.png,.webp,.gif' : kind === 'video' ? '.mp4,.webm' : '.jpg,.jpeg,.png,.webp,.gif,.mp4,.webm,.mp3,.ogg,.wav,.m4a,.pdf';
    return (
        <div className="modal" onClick={(e) => e.target === e.currentTarget && onClose()}>
            <div className="modal-panel" role="dialog" aria-modal="true" aria-label="Biblioteca multimedia">
                <div className="modal-head">
                    <h2>Biblioteca multimedia</h2>
                    {!kind && (
                        <select className="select" style={{ width: 'auto' }} value={tipo} onChange={(e) => setTipo(e.target.value)} aria-label="Tipo">
                            <option value="">Todo</option><option value="image">Imágenes</option><option value="video">Videos</option><option value="audio">Audios</option><option value="document">Documentos</option>
                        </select>
                    )}
                    <input className="input" style={{ maxWidth: 260 }} type="search" placeholder="Buscar…" value={q} onChange={(e) => setQ(e.target.value)} aria-label="Buscar" autoFocus />
                    <label className="btn btn-outline btn-sm"><Icon name="upload" /> Subir<input className="sr-only" type="file" multiple={multiple} accept={accept} onChange={(e) => upload(e.target.files)} /></label>
                    <button className="icon-btn" type="button" onClick={onClose} aria-label="Cerrar"><Icon name="close" /></button>
                </div>
                <div className="modal-body">
                    {progress !== null && <div className="progress"><span style={{ width: progress + '%' }} /></div>}
                    {msg && <p className="hint">{msg}</p>}
                    <div className="picker-grid">
                        {items.map((it) => {
                            const i = sel.findIndex((x) => x.id === it.id);
                            return (
                                <button key={it.id} type="button" className={`pick ${i >= 0 ? 'is-selected' : ''}`} title={it.title || ''} onClick={() => toggle(it)} onDoubleClick={() => !multiple && (onPick([it]), onClose())}>
                                    <img src={it.thumb} alt="" loading="lazy" /><span className="badge-n">{i >= 0 ? i + 1 : ''}</span><span className="cap">{it.title || it.kind}</span>
                                </button>
                            );
                        })}
                    </div>
                    {page < pages && <div style={{ textAlign: 'center', marginTop: 14 }}><button className="btn btn-outline btn-sm" type="button" onClick={() => load(page + 1)}>Cargar más</button></div>}
                </div>
                <div className="modal-foot">
                    <span className="hint" style={{ marginRight: 'auto' }}>{sel.length ? `${sel.length} seleccionado(s)` : ''}</span>
                    <button className="btn btn-outline" type="button" onClick={onClose}>Cancelar</button>
                    <button className="btn btn-primary" type="button" disabled={!sel.length} onClick={() => { onPick(sel); onClose(); }}>{multiple ? 'Agregar seleccionados' : 'Usar este archivo'}</button>
                </div>
            </div>
        </div>
    );
}

/** Un solo archivo de la biblioteca (portada, imagen de un departamento, video de la portada). */
export function MediaSingle({ label, value, media, onChange, kind = 'image', error, help }) {
    const [open, setOpen] = useState(false);
    const [current, setCurrent] = useState(media);
    useEffect(() => setCurrent(media), [media]);
    return (
        <div className="field media-field">
            <span className="field-label">{label}</span>
            <div className="cover-box">
                {value && current?.kind === 'image' ? <img src={current.medium} alt="" />
                    : value && current ? <span className="empty-label"><Icon name="video" /> {current.title}</span>
                        : <span className="empty-label"><Icon name={kind === 'video' ? 'video' : 'image'} /> Sin {kind === 'video' ? 'video' : 'imagen'}</span>}
            </div>
            <div className="flex">
                <button className="btn btn-outline btn-sm" type="button" onClick={() => setOpen(true)}><Icon name="image" /> Elegir de la biblioteca</button>
                {value && <button className="btn btn-outline btn-sm" type="button" onClick={() => { setCurrent(null); onChange(''); }}><Icon name="close" /> Quitar</button>}
            </div>
            {help && <p className="field-help">{help}</p>}
            {error && <p className="field-error">{error}</p>}
            {open && <MediaPicker kind={kind} onClose={() => setOpen(false)} onPick={([m]) => { setCurrent(m); onChange(m.id); }} />}
        </div>
    );
}

/** Varios archivos ordenables (galería de una publicación). */
export function MediaMulti({ label, items, onChange, help, error }) {
    const [open, setOpen] = useState(false);
    const drag = useRef(null);
    const move = (from, to) => {
        const next = [...items];
        const [it] = next.splice(from, 1);
        next.splice(to, 0, it);
        onChange(next);
    };
    return (
        <div className="field media-field">
            <div className="flex between"><span className="field-label">{label}</span>
                <button className="btn btn-outline btn-sm" type="button" onClick={() => setOpen(true)}><Icon name="plus" /> Agregar archivos</button></div>
            <div className="thumbs">
                {items.map((m, i) => (
                    <div key={m.id} className="thumb" draggable onDragStart={() => { drag.current = i; }} onDragOver={(e) => e.preventDefault()} onDrop={() => drag.current !== null && move(drag.current, i)}>
                        <img src={m.thumb} alt={m.title || ''} />
                        {m.kind !== 'image' && <span className="kind">{({ video: 'Video', embed: 'Video en línea', audio: 'Audio', document: 'Documento' })[m.kind]}</span>}
                        <button type="button" onClick={() => onChange(items.filter((x) => x.id !== m.id))} aria-label="Quitar"><Icon name="close" /></button>
                    </div>
                ))}
            </div>
            {help && <p className="field-help">{help}</p>}
            {error && <p className="field-error">{error}</p>}
            {open && <MediaPicker multiple onClose={() => setOpen(false)} onPick={(list) => onChange([...items, ...list.filter((m) => !items.some((x) => x.id === m.id))])} />}
        </div>
    );
}

/** Selector de coordenadas: clic en el mapa o arrastrar el marcador. */
export function MapPicker({ lat, lng, onChange }) {
    const el = useRef(null);
    const map = useRef(null);
    const marker = useRef(null);
    useEffect(() => {
        const m = L.map(el.current, { zoomControl: true, minZoom: 4, maxZoom: 16, scrollWheelZoom: false }).fitBounds(BOUNDS_MAINLAND);
        map.current = m;
        getJSON('/data/geo/departamentos.geojson').then((g) => L.geoJSON(g, { interactive: false, style: { color: '#0f5c4a', weight: 1, fillColor: '#2bb673', fillOpacity: 0.15 } }).addTo(m));
        m.on('click', (e) => onChange(e.latlng.lat.toFixed(6), e.latlng.lng.toFixed(6)));
        return () => m.remove();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);
    useEffect(() => {
        const m = map.current;
        const la = parseFloat(lat), ln = parseFloat(lng);
        if (Number.isNaN(la) || Number.isNaN(ln)) { if (marker.current) { m.removeLayer(marker.current); marker.current = null; } return; }
        if (!marker.current) {
            marker.current = L.marker([la, ln], { draggable: true, icon: L.divIcon({ className: '', iconSize: [22, 22], iconAnchor: [11, 11], html: '<div style="width:22px;height:22px;border-radius:50%;background:#ff3f8e;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.4)"></div>' }) }).addTo(m);
            marker.current.on('dragend', () => { const p = marker.current.getLatLng(); onChange(p.lat.toFixed(6), p.lng.toFixed(6)); });
            m.setView([la, ln], Math.max(m.getZoom(), 8));
        } else marker.current.setLatLng([la, ln]);
    }, [lat, lng]); // eslint-disable-line react-hooks/exhaustive-deps
    return <div ref={el} className="map-picker" aria-label="Haz clic en el mapa para ubicar el lugar" />;
}

/** Vista previa aproximada del Markdown (el servidor hace el render definitivo y escapa el HTML). */
export function mdPreview(src) {
    const esc = (s) => s.replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);
    const inline = (s) => esc(s).replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\*(.+?)\*/g, '<em>$1</em>')
        .replace(/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
    return (src || '').split(/\n{2,}/).map((block) => {
        const lines = block.split('\n');
        if (/^#{2,3} /.test(block)) return lines.map((l, i) => (i === 0 ? `<h${l.startsWith('###') ? 3 : 2}>${inline(l.replace(/^#+ /, ''))}</h${l.startsWith('###') ? 3 : 2}>` : `<p>${inline(l)}</p>`)).join('');
        if (lines.every((l) => /^[-*] /.test(l))) return `<ul>${lines.map((l) => `<li>${inline(l.slice(2))}</li>`).join('')}</ul>`;
        if (lines.every((l) => /^> ?/.test(l))) return `<blockquote>${inline(lines.map((l) => l.replace(/^> ?/, '')).join(' '))}</blockquote>`;
        return `<p>${inline(block).replace(/\n/g, '<br>')}</p>`;
    }).join('');
}

export function MarkdownEditor({ value, onChange, id = 'body' }) {
    const ta = useRef(null);
    const [preview, setPreview] = useState(false);
    const wrap = (before, after = before, placeholder = 'texto') => {
        const t = ta.current;
        const s = t.selectionStart, e = t.selectionEnd;
        const sel = value.slice(s, e) || placeholder;
        onChange(value.slice(0, s) + before + sel + after + value.slice(e));
        setTimeout(() => { t.focus(); t.setSelectionRange(s + before.length, s + before.length + sel.length); }, 0);
    };
    const line = (prefix) => {
        const t = ta.current;
        const s = value.lastIndexOf('\n', t.selectionStart - 1) + 1;
        onChange(value.slice(0, s) + prefix + value.slice(s));
    };
    return (
        <div className="field">
            <label className="field-label" htmlFor={id}>Contenido</label>
            <div className="md-toolbar" role="toolbar" aria-label="Formato">
                <button type="button" onClick={() => line('## ')}>Título</button>
                <button type="button" onClick={() => wrap('**')}><strong>N</strong></button>
                <button type="button" onClick={() => wrap('*')}><em>C</em></button>
                <button type="button" onClick={() => line('- ')}>• Lista</button>
                <button type="button" onClick={() => line('> ')}>❝ Cita</button>
                <button type="button" onClick={() => wrap('[', '](https://)', 'enlace')} aria-label="Enlace"><Icon name="link" /></button>
                <button type="button" style={{ marginLeft: 'auto' }} aria-pressed={preview} onClick={() => setPreview(!preview)}><Icon name="eye" /> Vista previa</button>
            </div>
            {preview ? <div className="md-preview prose" dangerouslySetInnerHTML={{ __html: mdPreview(value) }} />
                : <textarea ref={ta} className="textarea tall" id={id} value={value} onChange={(e) => onChange(e.target.value)} />}
            <p className="field-help">Formato sencillo: <code>## Subtítulo</code>, <code>**negrita**</code>, <code>*cursiva*</code>, listas con <code>-</code>, citas con <code>&gt;</code> y enlaces <code>[texto](https://…)</code>. El HTML no se interpreta por seguridad.</p>
        </div>
    );
}

/** Buscador de publicaciones (paradas de rutas, destacados, lugar protagonista). */
export function EntrySearch({ onPick, placeholder = 'Buscar una publicación…' }) {
    const [q, setQ] = useState('');
    const [items, setItems] = useState([]);
    useEffect(() => {
        if (q.trim().length < 2) { setItems([]); return undefined; }
        const t = setTimeout(() => getJSON('/admin/api/publicaciones?q=' + encodeURIComponent(q)).then((r) => setItems(r.items)).catch(() => {}), 220);
        return () => clearTimeout(t);
    }, [q]);
    return (
        <div className="field">
            <input className="input" type="search" value={q} onChange={(e) => setQ(e.target.value)} placeholder={placeholder} aria-label={placeholder} />
            {items.length > 0 && (
                <div className="search-results">
                    {items.map((it) => (
                        <button key={it.id} type="button" onClick={() => { onPick(it); setQ(''); setItems([]); }}>
                            <Thumb src={it.thumb} /><span><strong>{it.title}</strong><small>{it.section} · {it.place || 'sin ubicación'} · {it.status}{!it.hasCoords ? ' · sin coordenadas' : ''}</small></span>
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}

export function Confirm({ label = 'Eliminar', word = 'ELIMINAR', onConfirm, className = 'btn btn-danger btn-sm' }) {
    const [open, setOpen] = useState(false);
    const [v, setV] = useState('');
    if (!open) return <button type="button" className={className} onClick={() => setOpen(true)}><Icon name="trash" /> {label}</button>;
    return (
        <span className="confirm-inline">
            <input className="input" style={{ width: 130 }} value={v} onChange={(e) => setV(e.target.value)} placeholder={`Escribe ${word}`} aria-label={`Escribe ${word} para confirmar`} autoFocus />
            <button type="button" className="btn btn-danger btn-sm" disabled={v !== word} onClick={() => onConfirm(v)}>Confirmar</button>
            <button type="button" className="btn btn-outline btn-sm" onClick={() => { setOpen(false); setV(''); }}>Cancelar</button>
        </span>
    );
}

export { Link, u, Icon };
