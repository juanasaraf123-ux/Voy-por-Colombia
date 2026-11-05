import { Head, router, useForm } from '@inertiajs/react';
import { Confirm, Icon, Input, u } from '../../admin/ui';

export default function Settings({ fields, demo }) {
    const form = useForm(Object.fromEntries(fields.map((f) => [f.key, f.value || ''])));
    return (
        <div className="grid-form">
            <form className="card" onSubmit={(e) => { e.preventDefault(); form.post(u('/admin/configuracion'), { preserveScroll: true }); }}>
                <Head title="Configuración" />
                <h2><Icon name="settings" /> Datos del sitio</h2>
                <div className="row">{fields.map((f) => <Input key={f.key} form={form} name={f.key} label={f.label + (f.required ? ' *' : '')} type={f.key.startsWith('social_') ? 'url' : f.key === 'contact_email' ? 'email' : 'text'} wide={f.key === 'footer_text'} />)}</div>
                <button className="btn btn-primary" type="submit" disabled={form.processing}><Icon name="check" /> Guardar</button>
            </form>
            <aside>
                <section className="card">
                    <h2><Icon name="trash" /> Contenido de demostración</h2>
                    <p className="hint">{demo.entries} publicaciones, {demo.routes} rutas y {demo.media} archivos están marcados como demostración.</p>
                    {(demo.entries > 0 || demo.media > 0 || demo.routes > 0)
                        ? <Confirm label="Eliminar demostración" onConfirm={(v) => router.post(u('/admin/configuracion/eliminar-demo'), { confirm: v })} />
                        : <p className="muted">No queda contenido de demostración.</p>}
                </section>
            </aside>
        </div>
    );
}
