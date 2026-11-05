import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Confirm, Icon, u } from '../../admin/ui';

function Row({ c, icons }) {
    const [d, setD] = useState({ name: c.name, icon: c.icon || 'tag', color: c.color || '#0f9d77', sort_order: c.sort_order });
    return (
        <tr>
            <td><span className="cat-ico" style={{ background: d.color }}><Icon name={d.icon} /></span></td>
            <td><input className="input" value={d.name} maxLength={80} onChange={(e) => setD({ ...d, name: e.target.value })} aria-label="Nombre" /></td>
            <td><select className="select" value={d.icon} onChange={(e) => setD({ ...d, icon: e.target.value })} aria-label="Ícono">{icons.map((i) => <option key={i}>{i}</option>)}</select></td>
            <td><input type="color" value={d.color} onChange={(e) => setD({ ...d, color: e.target.value })} aria-label="Color" /></td>
            <td><input className="input" type="number" min={0} max={99} style={{ width: 70 }} value={d.sort_order} onChange={(e) => setD({ ...d, sort_order: e.target.value })} aria-label="Orden" /></td>
            <td className="muted">{c.entries_count}</td>
            <td className="actions">
                <button className="btn btn-outline btn-sm" type="button" onClick={() => router.post(u('/admin/categorias/' + c.id), d, { preserveScroll: true })}><Icon name="check" /> Guardar</button>
                <Confirm label="" onConfirm={() => router.post(u(`/admin/categorias/${c.id}/eliminar`), {}, { preserveScroll: true })} className="btn btn-outline btn-sm btn-icon" />
            </td>
        </tr>
    );
}

export default function Categories({ sections, categories, icons }) {
    const form = useForm({ section: 'tourism', name: '', icon: 'tag', color: '#0f9d77', sort_order: 0 });
    return (
        <>
            <Head title="Categorías" />
            <section className="card">
                <h2><Icon name="plus" /> Nueva categoría</h2>
                <form className="filters" onSubmit={(e) => { e.preventDefault(); form.post(u('/admin/categorias'), { preserveScroll: true, onSuccess: () => form.reset('name') }); }}>
                    <select className="select" value={form.data.section} onChange={(e) => form.setData('section', e.target.value)} aria-label="Sección">{sections.map((s) => <option key={s.key} value={s.key}>{s.label}</option>)}</select>
                    <input className="input" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} placeholder="Nombre" aria-label="Nombre" maxLength={80} />
                    <select className="select" value={form.data.icon} onChange={(e) => form.setData('icon', e.target.value)} aria-label="Ícono">{icons.map((i) => <option key={i}>{i}</option>)}</select>
                    <input type="color" value={form.data.color} onChange={(e) => form.setData('color', e.target.value)} aria-label="Color" />
                    <button className="btn btn-primary" type="submit"><Icon name="plus" /> Crear</button>
                </form>
                {form.errors.name && <p className="field-error">{form.errors.name}</p>}
            </section>
            {sections.map((s) => (
                <section key={s.key} className="card" id={'sec-' + s.key}>
                    <h2><Icon name={s.icon} /> {s.label}</h2>
                    <div className="table-wrap"><table className="table">
                        <thead><tr><th /><th>Nombre</th><th>Ícono</th><th>Color</th><th>Orden</th><th>Usos</th><th /></tr></thead>
                        <tbody>{categories.filter((c) => c.section === s.key).map((c) => <Row key={c.id + c.name} c={c} icons={icons} />)}</tbody>
                    </table></div>
                </section>
            ))}
        </>
    );
}
