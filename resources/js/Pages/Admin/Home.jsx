import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { EntrySearch, Field, Icon, MediaPicker, MediaSingle, Thumb, u } from '../../admin/ui';

export default function Home({ texts, heroMedia, heroVideo, spotlight, sections, featured }) {
    const [hero, setHero] = useState(heroMedia);
    const [spot, setSpot] = useState(spotlight);
    const [feat, setFeat] = useState(featured);
    const [picker, setPicker] = useState(false);
    const form = useForm({
        ...Object.fromEntries(texts.map((t) => [t.key, t.value || ''])),
        hero_video_id: heroVideo?.id ?? '',
        sections: sections.map((s) => ({ id: s.id, title: s.title, tagline: s.tagline ?? '', description: s.description ?? '', cover_media_id: s.cover_media_id ?? '', sort_order: s.sort_order, show_on_home: !!s.show_on_home })),
    });
    const setSection = (i, k, v) => form.setData('sections', form.data.sections.map((s, j) => (j === i ? { ...s, [k]: v } : s)));
    const submit = (e) => {
        e.preventDefault();
        form.transform((d) => ({ ...d, hero_media_ids: hero.map((m) => m.id), hero_spotlight_id: spot?.id ?? '', featured: feat.map((f) => ({ id: f.id, order: f.order })) }));
        form.post(u('/admin/inicio'), { preserveScroll: true });
    };
    return (
        <form className="grid-form" onSubmit={submit}>
            <Head title="Página de inicio" />
            <div>
                <section className="card">
                    <h2><Icon name="edit" /> Textos de la portada</h2>
                    {texts.map((t) => (
                        <Field key={t.key} label={t.label} htmlFor={t.key} error={form.errors[t.key]}>
                            {t.max > 150 ? <textarea className="textarea" id={t.key} rows={2} maxLength={t.max} value={form.data[t.key]} onChange={(e) => form.setData(t.key, e.target.value)} />
                                : <input className="input" id={t.key} maxLength={t.max} value={form.data[t.key]} onChange={(e) => form.setData(t.key, e.target.value)} />}
                        </Field>
                    ))}
                </section>
                <section className="card">
                    <h2><Icon name="star" /> Lugar protagonista</h2>
                    <p className="hint">Aparece en el panel del mapa al abrir la portada.</p>
                    {spot ? <div className="stop-row" style={{ gridTemplateColumns: '52px 1fr auto' }}><Thumb src={spot.thumb} /><span><strong>{spot.title}</strong><br /><small className="muted">{spot.place}</small></span><button type="button" className="btn btn-outline btn-sm" onClick={() => setSpot(null)}><Icon name="close" /> Quitar</button></div> : <p className="muted">Se usa el primer destino destacado.</p>}
                    <EntrySearch onPick={setSpot} placeholder="Buscar el lugar protagonista…" />
                    {form.errors.hero_spotlight_id && <p className="field-error">{form.errors.hero_spotlight_id}</p>}
                </section>
                <section className="card">
                    <h2><Icon name="sparkle" /> Destinos destacados</h2>
                    <p className="hint">Se muestran como «Destinos destacados» en la parte inferior del mapa. Ordena con el número (menor primero).</p>
                    {feat.map((f, i) => (
                        <div key={f.id} className="stop-row" style={{ gridTemplateColumns: '52px 1fr 90px auto' }}>
                            <Thumb src={f.thumb} /><span><strong>{f.title}</strong><br /><small className="muted">{f.section} · {f.place}</small></span>
                            <input className="input" type="number" min={0} max={999} value={f.order} aria-label="Orden" onChange={(e) => setFeat(feat.map((x, j) => (j === i ? { ...x, order: e.target.value } : x)))} />
                            <button type="button" className="btn btn-outline btn-sm btn-icon" onClick={() => setFeat(feat.filter((x) => x.id !== f.id))} aria-label="Quitar"><Icon name="close" /></button>
                        </div>
                    ))}
                    <EntrySearch onPick={(it) => !feat.some((x) => x.id === it.id) && setFeat([...feat, { ...it, order: feat.length + 1 }])} placeholder="Agregar un destacado…" />
                </section>
                <section className="card">
                    <h2><Icon name="layers" /> Presentación de las secciones</h2>
                    {form.data.sections.map((s, i) => (
                        <details key={s.id} className="sec-edit">
                            <summary><strong>{s.title}</strong> <span className="muted">({sections[i].section_key})</span></summary>
                            <div className="row">
                                <Field label="Título" htmlFor={'st' + i}><input className="input" id={'st' + i} maxLength={80} value={s.title} onChange={(e) => setSection(i, 'title', e.target.value)} /></Field>
                                <Field label="Orden" htmlFor={'so' + i}><input className="input" id={'so' + i} type="number" min={0} max={99} value={s.sort_order} onChange={(e) => setSection(i, 'sort_order', e.target.value)} /></Field>
                                <Field label="Lema" htmlFor={'sl' + i} wide><input className="input" id={'sl' + i} maxLength={160} value={s.tagline} onChange={(e) => setSection(i, 'tagline', e.target.value)} /></Field>
                                <Field label="Descripción" htmlFor={'sd' + i} wide><textarea className="textarea" id={'sd' + i} rows={2} maxLength={500} value={s.description} onChange={(e) => setSection(i, 'description', e.target.value)} /></Field>
                            </div>
                            <MediaSingle label="Imagen de la sección" value={s.cover_media_id} media={sections[i].cover} onChange={(v) => setSection(i, 'cover_media_id', v)} />
                        </details>
                    ))}
                </section>
            </div>
            <aside>
                <section className="card">
                    <h2><Icon name="image" /> Fondo de la portada</h2>
                    <div className="thumbs">{hero.map((m) => <div key={m.id} className="thumb"><img src={m.thumb} alt="" /><button type="button" onClick={() => setHero(hero.filter((x) => x.id !== m.id))} aria-label="Quitar"><Icon name="close" /></button></div>)}</div>
                    <button type="button" className="btn btn-outline btn-sm" onClick={() => setPicker(true)}><Icon name="plus" /> Elegir imagen</button>
                    <p className="field-help">Se usa la primera imagen como fondo, detrás del mapa.</p>
                    {form.errors.hero_media_ids && <p className="field-error">{form.errors.hero_media_ids}</p>}
                    {picker && <MediaPicker kind="image" multiple onClose={() => setPicker(false)} onPick={(list) => setHero([...hero, ...list.filter((m) => !hero.some((x) => x.id === m.id))])} />}
                </section>
                <section className="card">
                    <MediaSingle label="Video del botón «Ver video»" kind="video" value={form.data.hero_video_id} media={heroVideo} onChange={(v) => form.setData('hero_video_id', v)} error={form.errors.hero_video_id} />
                </section>
                <section className="card">
                    <button className="btn btn-primary btn-block" type="submit" disabled={form.processing}><Icon name="check" /> Guardar portada</button>
                    <a className="btn btn-outline btn-block mt" href={u('/')} target="_blank" rel="noopener"><Icon name="globe" /> Ver la portada</a>
                </section>
            </aside>
        </form>
    );
}
