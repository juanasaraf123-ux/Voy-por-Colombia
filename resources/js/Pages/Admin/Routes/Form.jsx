import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Check, Confirm, EntrySearch, Field, Icon, Input, MediaSingle, Thumb, u } from '../../../admin/ui';

export default function Form({ route: r, cover, stops: initial }) {
    const [stops, setStops] = useState(initial);
    const form = useForm({ title: r?.title ?? '', summary: r?.summary ?? '', body: r?.body ?? '', duration: r?.duration ?? '', cover_media_id: r?.cover_media_id ?? '', status: r?.status ?? 'draft', is_featured: !!r?.is_featured, keep_demo: !!r?.is_demo });
    const move = (i, d) => { const n = [...stops]; const j = i + d; if (j < 0 || j >= n.length) return; [n[i], n[j]] = [n[j], n[i]]; setStops(n); };
    const submit = (e) => {
        e.preventDefault();
        form.transform((d) => ({ ...d, stops: stops.map((s) => ({ id: s.id, note: s.note || '' })) }));
        form.post(u(r ? '/admin/rutas/' + r.id : '/admin/rutas/nueva'), { preserveScroll: true });
    };
    return (
        <form className="grid-form" onSubmit={submit}>
            <Head title={r ? 'Editar ruta' : 'Nueva ruta'} />
            <div>
                <section className="card">
                    <Input form={form} name="title" label="Nombre de la ruta" maxLength={190} />
                    <Field label="Resumen" htmlFor="summary" error={form.errors.summary}><textarea className="textarea" id="summary" rows={2} maxLength={400} value={form.data.summary} onChange={(e) => form.setData('summary', e.target.value)} /></Field>
                    <Field label="Descripción y recomendaciones" htmlFor="body"><textarea className="textarea" id="body" rows={6} value={form.data.body} onChange={(e) => form.setData('body', e.target.value)} /></Field>
                    <Input form={form} name="duration" label="Duración sugerida" maxLength={60} placeholder="Ej. 4 a 6 días" />
                </section>
                <section className="card">
                    <h2><Icon name="route" /> Paradas (en orden)</h2>
                    {stops.map((s, i) => (
                        <div key={s.id} className="stop-row">
                            <span className="n">{i + 1}</span><Thumb src={s.thumb} />
                            <span><strong>{s.title}</strong><br /><small className="muted">{s.place}{!s.hasCoords ? ' · sin coordenadas (no se dibuja en el mapa)' : ''}</small></span>
                            <input className="input" maxLength={255} placeholder="Nota (opcional)" value={s.note || ''} onChange={(e) => setStops(stops.map((x, j) => (j === i ? { ...x, note: e.target.value } : x)))} />
                            <span className="actions">
                                <button className="btn btn-outline btn-sm btn-icon" type="button" onClick={() => move(i, -1)} aria-label="Subir">↑</button>
                                <button className="btn btn-outline btn-sm btn-icon" type="button" onClick={() => move(i, 1)} aria-label="Bajar">↓</button>
                                <button className="btn btn-outline btn-sm btn-icon" type="button" onClick={() => setStops(stops.filter((x) => x.id !== s.id))} aria-label="Quitar"><Icon name="close" /></button>
                            </span>
                        </div>
                    ))}
                    {form.errors.stops && <p className="field-error">{form.errors.stops}</p>}
                    <EntrySearch onPick={(it) => !stops.some((x) => x.id === it.id) && setStops([...stops, { ...it, note: '' }])} placeholder="Agregar parada: busca un destino, finca, evento…" />
                </section>
            </div>
            <aside>
                <section className="card">
                    <Field label="Estado" htmlFor="status"><select className="select" id="status" value={form.data.status} onChange={(e) => form.setData('status', e.target.value)}><option value="draft">Borrador</option><option value="published">Publicada</option><option value="archived">Archivada</option></select></Field>
                    <Check form={form} name="is_featured" label="Destacada" />
                    {r?.is_demo && <Check form={form} name="keep_demo" label="Mantener como demostración" />}
                    <button className="btn btn-primary btn-block" type="submit" disabled={form.processing}><Icon name="check" /> Guardar ruta</button>
                </section>
                <section className="card"><MediaSingle label="Imagen de la ruta" value={form.data.cover_media_id} media={cover} onChange={(v) => form.setData('cover_media_id', v)} error={form.errors.cover_media_id} /></section>
                {r && <section className="card"><Confirm label="Eliminar ruta" onConfirm={() => router.post(u(`/admin/rutas/${r.id}/eliminar`))} /></section>}
            </aside>
        </form>
    );
}
