import { Head, useForm } from '@inertiajs/react';
import { Field, Icon, Input, MediaSingle, u } from '../../../admin/ui';

export default function Department({ dep, cover, regions, municipalities }) {
    const form = useForm({ region_id: dep.region_id ?? '', capital: dep.capital ?? '', summary: dep.summary ?? '', cover_media_id: dep.cover_media_id ?? '' });
    return (
        <form className="grid-form" onSubmit={(e) => { e.preventDefault(); form.post(u('/admin/mapa/departamentos/' + dep.id), { preserveScroll: true }); }}>
            <Head title={dep.name} />
            <div>
                <section className="card">
                    <h2><Icon name="map" /> {dep.name}</h2>
                    <Field label="Región" htmlFor="r"><select className="select" id="r" value={form.data.region_id} onChange={(e) => form.setData('region_id', e.target.value)}><option value="">—</option>{regions.map((r) => <option key={r.id} value={r.id}>{r.name}</option>)}</select></Field>
                    <Input form={form} name="capital" label="Capital" maxLength={100} />
                    <Field label={`Presentación (${form.data.summary.length}/500)`} htmlFor="s" error={form.errors.summary} help="Aparece en el panel del mapa al tocar el departamento y en su página.">
                        <textarea className="textarea" id="s" rows={4} maxLength={500} value={form.data.summary} onChange={(e) => form.setData('summary', e.target.value)} />
                    </Field>
                    <button className="btn btn-primary" type="submit" disabled={form.processing}><Icon name="check" /> Guardar</button>
                </section>
                <section className="card"><h2>Municipios ({municipalities.length})</h2><p className="hint">{municipalities.join(' · ')}</p></section>
            </div>
            <aside><section className="card"><MediaSingle label="Imagen del departamento" value={form.data.cover_media_id} media={cover} onChange={(v) => form.setData('cover_media_id', v)} error={form.errors.cover_media_id} /></section></aside>
        </form>
    );
}
