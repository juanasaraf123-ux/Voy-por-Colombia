import { Head, router, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { Check, Confirm, Field, fechaHora, Icon, Input, MapPicker, MarkdownEditor, MediaMulti, MediaSingle, STATUS, u } from '../../../admin/ui';
import { getJSON } from '../../../lib/url';

const metaText = (v) => (Array.isArray(v) ? v.join('\n') : v ?? '');

export default function Form({ section, fields, entry, cover, gallery, categories, departments, municipalities: initialMuns, canPublish, canDelete }) {
    const e = entry;
    const form = useForm({
        title: e?.title ?? '', slug: e?.slug ?? '', summary: e?.summary ?? '', body: e?.body ?? '', status: e?.status ?? 'draft',
        published_at: e?.published_at ?? '', department_id: e?.department_id ?? '', municipality_id: e?.municipality_id ?? '',
        lat: e?.lat ?? '', lng: e?.lng ?? '', show_on_map: e ? !!e.show_on_map : true, cover_media_id: e?.cover_media_id ?? '',
        is_featured: !!e?.is_featured, featured_order: e?.featured_order ?? 0, is_hidden_gem: !!e?.is_hidden_gem,
        event_start: e?.event_start ?? '', event_end: e?.event_end ?? '', author_name: e?.author_name ?? '',
        meta: Object.fromEntries(fields.map((f) => [f.key, metaText(e?.meta?.[f.key])])),
        categories: e?.categories ?? [], media_ids: e?.media_ids ?? [], keep_demo: !!e?.is_demo, _preview: false,
    });
    const [items, setItems] = useState(gallery);
    const [muns, setMuns] = useState(initialMuns);
    const [dirty, setDirty] = useState(false);
    useEffect(() => { if (form.isDirty) setDirty(true); }, [form.isDirty]);
    useEffect(() => {
        const warn = (ev) => { if (dirty && !form.processing) { ev.preventDefault(); ev.returnValue = ''; } };
        window.addEventListener('beforeunload', warn);
        return () => window.removeEventListener('beforeunload', warn);
    }, [dirty, form.processing]);

    const changeDep = async (id) => {
        form.setData((d) => ({ ...d, department_id: id, municipality_id: '' }));
        setMuns(id ? (await getJSON('/admin/api/municipios?departamento=' + id)).items : []);
    };
    const useMunicipality = () => {
        const m = muns.find((x) => String(x.id) === String(form.data.municipality_id));
        if (m?.lat) form.setData((d) => ({ ...d, lat: Number(m.lat).toFixed(6), lng: Number(m.lng).toFixed(6) }));
    };
    const submit = (preview = false) => {
        form.transform((d) => ({ ...d, _preview: preview, media_ids: items.map((m) => m.id) }));
        const url = e ? `/admin/publicaciones/${e.id}/editar` : `/admin/contenido/${section.path}/nuevo`;
        form.post(u(url), { preserveScroll: true, onSuccess: () => setDirty(false) });
    };
    const err = form.errors;
    const meta = (k, v) => form.setData('meta', { ...form.data.meta, [k]: v });

    return (
        <form className="grid-form" noValidate onSubmit={(ev) => { ev.preventDefault(); submit(false); }} style={{ '--c': section.color }}>
            <Head title={e ? 'Editar: ' + e.title : 'Nuevo ' + section.singular} />
            <div>
                {e && (
                    <div className="flex" style={{ marginBottom: 14 }}>
                        <span className={`pill pill-${e.effectiveStatus}`}>{STATUS[e.effectiveStatus]}</span>
                        {e.is_demo && <span className="pill pill-demo">Contenido de demostración</span>}
                        <a className="btn btn-outline btn-sm" style={{ marginLeft: 'auto' }} href={u(`/admin/publicaciones/${e.id}/vista-previa`)} target="_blank" rel="noopener"><Icon name="eye" /> Vista previa</a>
                        {e.effectiveStatus === 'published' && <a className="btn btn-outline btn-sm" href={u(e.url)} target="_blank" rel="noopener"><Icon name="globe" /> Ver publicado</a>}
                    </div>
                )}
                <section className="card">
                    <Field label="Título" htmlFor="title" error={err.title}>
                        <input className={`input input-title ${err.title ? 'is-invalid' : ''}`} id="title" maxLength={190} required value={form.data.title} onChange={(ev) => form.setData('title', ev.target.value)} />
                    </Field>
                    <Field label="Dirección web" htmlFor="slug" error={err.slug}>
                        <div className="flex" style={{ flexWrap: 'nowrap' }}><span className="hint" style={{ whiteSpace: 'nowrap' }}>/{section.path}/</span>
                            <input className="input" id="slug" value={form.data.slug} onChange={(ev) => form.setData('slug', ev.target.value)} placeholder="se genera a partir del título" /></div>
                    </Field>
                    <Field label={`Resumen (${form.data.summary.length}/400)`} htmlFor="summary" error={err.summary} help="Aparece en tarjetas, en el mapa y en buscadores. Obligatorio para publicar.">
                        <textarea className="textarea" id="summary" maxLength={400} rows={3} value={form.data.summary} onChange={(ev) => form.setData('summary', ev.target.value)} placeholder="Una o dos frases que despierten curiosidad." />
                    </Field>
                    <MarkdownEditor value={form.data.body} onChange={(v) => form.setData('body', v)} />
                </section>

                {fields.length > 0 && (
                    <section className="card">
                        <h2><Icon name={section.icon} /> Detalles de {section.label.toLowerCase()}</h2>
                        <div className="row">
                            {fields.map((f) => {
                                const name = 'meta.' + f.key;
                                const wide = ['lines', 'textarea'].includes(f.type);
                                return (
                                    <Field key={f.key} label={f.label} htmlFor={name} help={f.help} error={err[name]} wide={wide}>
                                        {f.type === 'select' ? (
                                            <select className="select" id={name} value={form.data.meta[f.key]} onChange={(ev) => meta(f.key, ev.target.value)}><option value="">—</option>{f.options.map((o) => <option key={o}>{o}</option>)}</select>
                                        ) : wide ? (
                                            <textarea className="textarea" id={name} rows={f.type === 'lines' ? 5 : 4} value={form.data.meta[f.key]} onChange={(ev) => meta(f.key, ev.target.value)} />
                                        ) : (
                                            <input className="input" id={name} type={['url', 'email', 'tel'].includes(f.type) ? f.type : 'text'} value={form.data.meta[f.key]} onChange={(ev) => meta(f.key, ev.target.value)} />
                                        )}
                                    </Field>
                                );
                            })}
                        </div>
                    </section>
                )}

                <section className="card">
                    <h2><Icon name="camera" /> Multimedia</h2>
                    <MediaMulti label="Galería, videos, audios y documentos" items={items} onChange={(list) => { setItems(list); setDirty(true); }} error={err.media_ids}
                        help="Arrastra para ordenar. Las imágenes se muestran en mosaico; los videos y audios con su reproductor (también dentro del mapa)." />
                </section>

                <section className="card">
                    <h2><Icon name="map" /> Ubicación</h2>
                    <div className="row">
                        <Field label="Departamento" htmlFor="department_id" error={err.department_id}>
                            <select className="select" id="department_id" value={form.data.department_id} onChange={(ev) => changeDep(ev.target.value)}>
                                <option value="">Sin departamento</option>{departments.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}
                            </select>
                        </Field>
                        <Field label="Municipio" htmlFor="municipality_id" error={err.municipality_id}>
                            <select className="select" id="municipality_id" value={form.data.municipality_id} onChange={(ev) => form.setData('municipality_id', ev.target.value)}>
                                <option value="">Sin municipio</option>{muns.map((m) => <option key={m.id} value={m.id}>{m.name}</option>)}
                            </select>
                        </Field>
                        <Input form={form} name="lat" label="Latitud" inputMode="decimal" placeholder="Ej. 4.6386" />
                        <Input form={form} name="lng" label="Longitud" inputMode="decimal" placeholder="Ej. -75.4875" />
                    </div>
                    <MapPicker lat={form.data.lat} lng={form.data.lng} onChange={(la, ln) => form.setData((d) => ({ ...d, lat: la, lng: ln }))} />
                    <div className="flex mt">
                        <button className="btn btn-outline btn-sm" type="button" onClick={useMunicipality} disabled={!form.data.municipality_id}><Icon name="pin" /> Usar el centro del municipio</button>
                        <button className="btn btn-outline btn-sm" type="button" onClick={() => form.setData((d) => ({ ...d, lat: '', lng: '' }))}><Icon name="close" /> Quitar coordenadas</button>
                    </div>
                    <p className="field-help">Haz clic en el mapa o arrastra el punto para ubicar el lugar.</p>
                    <Check form={form} name="show_on_map" label="Mostrar en el mapa interactivo" />
                </section>
            </div>

            <aside>
                <section className="card">
                    <h2><Icon name="check" /> Publicación</h2>
                    <Field label="Estado" htmlFor="status">
                        <select className="select" id="status" value={form.data.status} onChange={(ev) => form.setData('status', ev.target.value)}>
                            <option value="draft">Borrador</option>
                            {canPublish && <option value="published">Publicado</option>}
                            <option value="archived">Archivado</option>
                        </select>
                    </Field>
                    {!canPublish && <p className="field-help">Tu rol no permite publicar: se guardará como borrador.</p>}
                    <Input form={form} name="published_at" label="Fecha de publicación" type="datetime-local" help="Una fecha futura programa la publicación." />
                    <button className="btn btn-primary btn-block" type="submit" disabled={form.processing}><Icon name="check" /> {form.processing ? 'Guardando…' : 'Guardar'}</button>
                    {e && <button className="btn btn-outline btn-block mt" type="button" disabled={form.processing} onClick={() => submit(true)}><Icon name="eye" /> Guardar y ver vista previa</button>}
                    {dirty && <p className="field-help">Hay cambios sin guardar.</p>}
                </section>
                <section className="card">
                    <MediaSingle label="Imagen principal" value={form.data.cover_media_id} media={cover} onChange={(v) => form.setData('cover_media_id', v)} error={err.cover_media_id} help="Se usa en tarjetas, en el mapa y al compartir." />
                </section>
                {categories.length > 0 && (
                    <section className="card">
                        <h2><Icon name="tag" /> Categorías</h2>
                        {categories.map((c) => (
                            <label key={c.id} className="check">
                                <input type="checkbox" checked={form.data.categories.includes(c.id)} onChange={(ev) => form.setData('categories', ev.target.checked ? [...form.data.categories, c.id] : form.data.categories.filter((x) => x !== c.id))} />
                                <span>{c.name}</span>
                            </label>
                        ))}
                    </section>
                )}
                <section className="card">
                    <h2><Icon name="star" /> Destacar</h2>
                    <Check form={form} name="is_featured" label="Destacado en la portada" />
                    {form.data.is_featured && <Input form={form} name="featured_order" label="Orden" type="number" min={0} max={999} />}
                    {section.key !== 'news' && <Check form={form} name="is_hidden_gem" label="Joya poco conocida" />}
                </section>
                {section.hasEvents && (
                    <section className="card">
                        <h2><Icon name="calendar" /> Fechas del evento</h2>
                        <Input form={form} name="event_start" label="Inicio" type="datetime-local" />
                        <Input form={form} name="event_end" label="Fin" type="datetime-local" />
                    </section>
                )}
                {section.key === 'news' && <section className="card"><Input form={form} name="author_name" label="Autoría" maxLength={120} /></section>}
                {e?.is_demo && <section className="card"><Check form={form} name="keep_demo" label="Mantener como demostración" help="Desmárcalo cuando el contenido sea real y verificado." /></section>}
                {e && canDelete && (
                    <section className="card">
                        <h2><Icon name="trash" /> Eliminar</h2>
                        <p className="field-help">Los archivos multimedia se conservan en la biblioteca.</p>
                        <Confirm label="Eliminar publicación" onConfirm={(v) => router.post(u(`/admin/publicaciones/${e.id}/eliminar`), { confirm: v })} />
                    </section>
                )}
                {e && <p className="hint">Actualizado {fechaHora(e.updated_at)}</p>}
            </aside>
        </form>
    );
}
