import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Icon, Pager, postFiles, u } from '../../../admin/ui';

export default function Index({ items, pagination, filters, departments, limits, serverLimit }) {
    const [q, setQ] = useState(filters.q);
    const [progress, setProgress] = useState(null);
    const [msg, setMsg] = useState('');
    const [embed, setEmbed] = useState({ url: '', title: '' });
    const go = (over) => router.get(u('/admin/multimedia'), Object.fromEntries(Object.entries({ ...filters, q, ...over }).filter(([, v]) => v !== '')), { preserveState: true });
    const upload = async (files, inGallery) => {
        if (!files.length) return;
        setProgress(0); setMsg('');
        try {
            const r = await postFiles('/admin/multimedia/subir', files, { in_gallery: inGallery ? '1' : '0' }, setProgress);
            setMsg([r.items?.length ? `${r.items.length} archivo(s) cargado(s).` : '', ...(r.errors || [])].filter(Boolean).join(' '));
            router.reload({ only: ['items', 'pagination'] });
        } catch { setMsg('No se pudo subir. Revisa el tamaño del archivo y la conexión.'); }
        setProgress(null);
    };
    const [inGallery, setInGallery] = useState(true);
    return (
        <>
            <Head title="Centro multimedia" />
            <div className="grid grid-2">
                <section className="card">
                    <h2><Icon name="upload" /> Subir archivos</h2>
                    <label className="dropzone" onDragOver={(e) => e.preventDefault()} onDrop={(e) => { e.preventDefault(); upload(e.dataTransfer.files, inGallery); }}>
                        <Icon name="upload" />
                        <span><strong>Arrastra aquí tus fotos, videos, audios o PDF</strong><br />o haz clic para elegirlos (máximo 20 a la vez)</span>
                        <input className="sr-only" type="file" multiple accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.webm,.mp3,.ogg,.wav,.m4a,.pdf" onChange={(e) => upload(e.target.files, inGallery)} />
                    </label>
                    <label className="check"><input type="checkbox" checked={inGallery} onChange={(e) => setInGallery(e.target.checked)} /> Mostrar las imágenes en la galería pública</label>
                    {progress !== null && <div className="progress"><span style={{ width: progress + '%' }} /></div>}
                    {msg && <p className="hint">{msg}</p>}
                    <p className="field-help">Límites: imágenes {limits.image}, videos {limits.video}, audios {limits.audio}, documentos {limits.document} (el servidor admite hasta {serverLimit} por archivo). Las imágenes se optimizan en WebP y se eliminan sus metadatos. Usa solo material con derechos de uso y registra el crédito.</p>
                </section>
                <section className="card">
                    <h2><Icon name="video" /> Video de YouTube o Vimeo</h2>
                    <form className="stack" onSubmit={(e) => { e.preventDefault(); router.post(u('/admin/multimedia/video-en-linea'), { ...embed, in_gallery: true }); }}>
                        <label className="field-label" htmlFor="eu">Enlace del video</label>
                        <input className="input" id="eu" value={embed.url} onChange={(e) => setEmbed({ ...embed, url: e.target.value })} placeholder="https://www.youtube.com/watch?v=…" />
                        <label className="field-label" htmlFor="et">Título</label>
                        <input className="input" id="et" value={embed.title} onChange={(e) => setEmbed({ ...embed, title: e.target.value })} />
                        <button className="btn btn-primary" type="submit"><Icon name="plus" /> Agregar video</button>
                    </form>
                </section>
            </div>
            <nav className="tabs" style={{ marginTop: 18 }}>
                {[['', 'Todo'], ['image', 'Imágenes'], ['video', 'Videos'], ['audio', 'Audios'], ['document', 'Documentos']].map(([k, l]) => (
                    <button key={k} type="button" style={filters.tipo === k ? { background: 'var(--tinta)', color: '#fff' } : undefined} onClick={() => go({ tipo: k, pagina: '' })}>{l}</button>
                ))}
            </nav>
            <form className="filters" onSubmit={(e) => { e.preventDefault(); go({ pagina: '' }); }}>
                <input className="input" type="search" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Buscar por título, crédito o archivo…" aria-label="Buscar" />
                <select className="select" value={filters.departamento} onChange={(e) => go({ departamento: e.target.value, pagina: '' })} aria-label="Departamento"><option value="">Todos los departamentos</option>{departments.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}</select>
                <label className="check"><input type="checkbox" checked={filters.galeria === '1'} onChange={(e) => go({ galeria: e.target.checked ? '1' : '', pagina: '' })} /> Solo galería pública</label>
                <button className="btn" type="submit"><Icon name="filter" /> Filtrar</button>
            </form>
            <p className="muted">{pagination.total} archivos</p>
            <div className="media-grid">
                {items.map((m) => (
                    <Link key={m.id} className="media-tile" href={u('/admin/multimedia/' + m.id)}>
                        <img src={m.thumb} alt={m.alt || ''} loading="lazy" />
                        <span className="cap"><strong>{m.title || '(sin título)'}</strong><small>{({ image: 'Imagen', video: 'Video', embed: 'Video en línea', audio: 'Audio', document: 'Documento' })[m.kind]}{m.credit ? ' · ' + m.credit : ' · sin crédito'}{m.isDemo ? ' · demo' : ''}</small></span>
                    </Link>
                ))}
            </div>
            <Pager page={pagination.page} pages={pagination.pages} />
        </>
    );
}
