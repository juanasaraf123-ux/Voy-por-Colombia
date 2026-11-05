import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Icon from '../../../components/Icon';
import { can } from '../../../Layouts/AdminLayout';
import { fechaHora, Pager, STATUS, Thumb } from '../../../admin/ui';
import { u } from '../../../lib/url';

export default function Index({ section, items, pagination, counts, filters, categories, departments }) {
    const { auth } = usePage().props;
    const [q, setQ] = useState(filters.q);
    const base = '/admin/contenido/' + section.path;
    const go = (over) => router.get(u(base), Object.fromEntries(Object.entries({ ...filters, q, ...over }).filter(([, v]) => v !== '' && v !== null)), { preserveState: true });
    const setStatus = (id, status) => router.post(u(`/admin/publicaciones/${id}/estado`), { status }, { preserveScroll: true });
    const total = Object.values(counts).reduce((a, b) => a + Number(b), 0);
    return (
        <>
            <Head title={section.label} />
            <div className="flex between" style={{ marginBottom: 14 }}>
                <p className="muted" style={{ margin: 0 }}>{pagination.total} publicaciones en {section.label}</p>
                <Link className="btn btn-primary" href={u(base + '/nuevo')}><Icon name="plus" /> Nuevo {section.singular}</Link>
            </div>
            <nav className="tabs" aria-label="Filtrar por estado">
                {[['', `Todas (${total})`], ['published', `Publicadas (${counts.published || 0})`], ['scheduled', 'Programadas'], ['draft', `Borradores (${counts.draft || 0})`], ['archived', `Archivadas (${counts.archived || 0})`]].map(([k, l]) => (
                    <button key={k} type="button" aria-current={filters.estado === k ? 'true' : 'false'} style={filters.estado === k ? { background: 'var(--tinta)', color: '#fff' } : undefined} onClick={() => go({ estado: k, pagina: '' })}>{l}</button>
                ))}
            </nav>
            <form className="filters" onSubmit={(e) => { e.preventDefault(); go({ pagina: '' }); }}>
                <input className="input" type="search" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Buscar por título, lugar o texto…" aria-label="Buscar" />
                <select className="select" aria-label="Categoría" value={filters.categoria} onChange={(e) => go({ categoria: e.target.value, pagina: '' })}><option value="">Todas las categorías</option>{categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}</select>
                <select className="select" aria-label="Departamento" value={filters.departamento} onChange={(e) => go({ departamento: e.target.value, pagina: '' })}><option value="">Todos los departamentos</option>{departments.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}</select>
                <button className="btn" type="submit"><Icon name="filter" /> Filtrar</button>
            </form>
            <div className="card">
                {items.length ? (
                    <div className="table-wrap"><table className="table">
                        <thead><tr><th /><th>Título</th><th>Ubicación</th><th>Estado</th><th>Actualizado</th><th className="sr-only">Acciones</th></tr></thead>
                        <tbody>
                            {items.map((e) => (
                                <tr key={e.id}>
                                    <td><Thumb src={e.thumb} /></td>
                                    <td>
                                        <Link className="t-title" href={u(`/admin/publicaciones/${e.id}/editar`)}>{e.title}</Link>
                                        <div className="flex" style={{ marginTop: 4 }}>
                                            {e.featured && <span className="pill pill-scheduled"><Icon name="star" /> Destacado</span>}
                                            {e.isDemo && <span className="pill pill-demo">Demo</span>}
                                            {!e.hasCoords && section.map && <span className="pill pill-off">Sin coordenadas</span>}
                                        </div>
                                    </td>
                                    <td className="muted">{e.place || '—'}</td>
                                    <td><span className={`pill pill-${e.status}`}>{STATUS[e.status]}</span></td>
                                    <td className="muted">{fechaHora(e.updatedAt)}</td>
                                    <td><div className="actions">
                                        <Link className="btn btn-outline btn-sm" href={u(`/admin/publicaciones/${e.id}/editar`)}><Icon name="edit" /> Editar</Link>
                                        <a className="btn btn-outline btn-sm btn-icon" href={u(`/admin/publicaciones/${e.id}/vista-previa`)} target="_blank" rel="noopener" title="Vista previa" aria-label="Vista previa"><Icon name="eye" /></a>
                                        {can(auth, 'content.publish') && <>
                                            {e.status !== 'published' && e.status !== 'scheduled' ? <button className="btn btn-success btn-sm" type="button" onClick={() => setStatus(e.id, 'published')}><Icon name="check" /> Publicar</button>
                                                : <button className="btn btn-outline btn-sm" type="button" onClick={() => setStatus(e.id, 'draft')}>Despublicar</button>}
                                            {e.status !== 'archived' && <button className="btn btn-outline btn-sm" type="button" onClick={() => setStatus(e.id, 'archived')}>Archivar</button>}
                                        </>}
                                    </div></td>
                                </tr>
                            ))}
                        </tbody>
                    </table></div>
                ) : <div className="empty-state"><Icon name={section.icon} /><p>No hay publicaciones con estos filtros.</p></div>}
            </div>
            <Pager page={pagination.page} pages={pagination.pages} />
        </>
    );
}
