import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Check, Confirm, Field, Icon, Input, u } from '../../../admin/ui';
import { getJSON } from '../../../lib/url';

export default function Edit({ media: m, usage, usedIn, departments, municipalities, collections, sections }) {
    const form = useForm({
        title: m.title ?? '', alt_text: m.alt_text ?? '', caption: m.caption ?? '', credit: m.credit ?? '', license: m.license ?? '', source_url: m.source_url ?? '',
        department_id: m.department_id ?? '', municipality_id: m.municipality_id ?? '', collection_id: m.collection_id ?? '', gallery_section: m.gallery_section ?? '',
        in_gallery: !!m.in_gallery, gallery_featured: !!m.gallery_featured, keep_demo: !!m.is_demo,
    });
    const [muns, setMuns] = useState(municipalities);
    const dep = async (id) => { form.setData((d) => ({ ...d, department_id: id, municipality_id: '' })); setMuns(id ? (await getJSON('/admin/api/municipios?departamento=' + id)).items : []); };
    return (
        <form className="grid-form" onSubmit={(e) => { e.preventDefault(); form.post(u('/admin/multimedia/' + m.id), { preserveScroll: true }); }}>
            <Head title="Editar archivo" />
            <div>
                <section className="card">
                    <div className="media-preview">
                        {m.kind === 'image' ? <img src={m.large} alt={m.alt} /> : m.kind === 'video' ? <video src={m.src} controls preload="metadata" /> : m.kind === 'audio' ? <audio src={m.src} controls /> : m.kind === 'embed' ? <div className="video-frame"><iframe src={m.embed} title={m.title || 'Video'} allowFullScreen /></div> : <a href={m.src} target="_blank" rel="noopener noreferrer"><Icon name="file" /> Abrir documento</a>}
                    </div>
                    <p className="hint">{m.original_name} · {m.size}{m.width ? ` · ${m.width}×${m.height}` : ''}</p>
                </section>
                <section className="card">
                    <h2><Icon name="edit" /> Descripción y créditos</h2>
                    <div className="row">
                        <Input form={form} name="title" label="Título" maxLength={190} />
                        <Input form={form} name="credit" label="Crédito (autor o fuente)" maxLength={190} />
                        <Input form={form} name="alt_text" label="Texto alternativo" help="Describe la imagen para personas que usan lectores de pantalla." maxLength={255} wide />
                        <Input form={form} name="caption" label="Pie de foto" maxLength={500} wide />
                        <Input form={form} name="license" label="Licencia o permiso de uso" maxLength={120} />
                        <Input form={form} name="source_url" label="Enlace a la fuente" type="url" />
                    </div>
                </section>
            </div>
            <aside>
                <section className="card">
                    <h2><Icon name="camera" /> Galería pública</h2>
                    <Check form={form} name="in_gallery" label="Mostrar en la galería" />
                    <Check form={form} name="gallery_featured" label="Destacada en la galería" />
                    <Field label="Categoría" htmlFor="gs"><select className="select" id="gs" value={form.data.gallery_section} onChange={(e) => form.setData('gallery_section', e.target.value)}><option value="">—</option>{sections.map((s) => <option key={s.key} value={s.key}>{s.label}</option>)}</select></Field>
                    <Field label="Colección" htmlFor="col"><select className="select" id="col" value={form.data.collection_id} onChange={(e) => form.setData('collection_id', e.target.value)}><option value="">—</option>{collections.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}</select></Field>
                    <Field label="Departamento" htmlFor="dep" error={form.errors.department_id}><select className="select" id="dep" value={form.data.department_id} onChange={(e) => dep(e.target.value)}><option value="">—</option>{departments.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}</select></Field>
                    <Field label="Municipio" htmlFor="mun" error={form.errors.municipality_id}><select className="select" id="mun" value={form.data.municipality_id} onChange={(e) => form.setData('municipality_id', e.target.value)}><option value="">—</option>{muns.map((x) => <option key={x.id} value={x.id}>{x.name}</option>)}</select></Field>
                    {m.is_demo && <Check form={form} name="keep_demo" label="Mantener como demostración" />}
                    <button className="btn btn-primary btn-block" type="submit" disabled={form.processing}><Icon name="check" /> Guardar</button>
                </section>
                <section className="card">
                    <h2><Icon name="link" /> Uso</h2>
                    <p className="hint">Se usa en {usage} lugar(es).</p>
                    <ul className="plain-list">{usedIn.map((e) => <li key={e.id}><Link href={u(`/admin/publicaciones/${e.id}/editar`)}>{e.title}</Link></li>)}</ul>
                    <Confirm label="Eliminar archivo" onConfirm={() => router.post(u(`/admin/multimedia/${m.id}/eliminar`))} />
                    {usage > 0 && <p className="field-help">Al eliminarlo desaparecerá de esas publicaciones.</p>}
                </section>
            </aside>
        </form>
    );
}
