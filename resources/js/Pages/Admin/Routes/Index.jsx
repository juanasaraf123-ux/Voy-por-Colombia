import { Head, Link } from '@inertiajs/react';
import { Icon, STATUS, Thumb, u } from '../../../admin/ui';

export default function Index({ routes }) {
    return (
        <>
            <Head title="Rutas turísticas" />
            <div className="flex between" style={{ marginBottom: 14 }}>
                <p className="muted" style={{ margin: 0 }}>{routes.length} rutas</p>
                <Link className="btn btn-primary" href={u('/admin/rutas/nueva')}><Icon name="plus" /> Nueva ruta</Link>
            </div>
            <div className="card"><div className="table-wrap"><table className="table">
                <thead><tr><th /><th>Ruta</th><th>Paradas</th><th>Estado</th><th /></tr></thead>
                <tbody>
                    {routes.map((r) => (
                        <tr key={r.id}>
                            <td><Thumb src={r.thumb} /></td>
                            <td><Link className="t-title" href={u('/admin/rutas/' + r.id)}>{r.title}</Link><div className="flex">{r.featured && <span className="pill pill-scheduled">Destacada</span>}{r.isDemo && <span className="pill pill-demo">Demo</span>}{r.duration && <span className="muted">{r.duration}</span>}</div></td>
                            <td>{r.stops}</td>
                            <td><span className={`pill pill-${r.status}`}>{STATUS[r.status]}</span></td>
                            <td className="actions"><Link className="btn btn-outline btn-sm" href={u('/admin/rutas/' + r.id)}><Icon name="edit" /> Editar</Link>{r.status === 'published' && <a className="btn btn-outline btn-sm btn-icon" href={u('/rutas/' + r.slug)} target="_blank" rel="noopener" aria-label="Ver"><Icon name="eye" /></a>}</td>
                        </tr>
                    ))}
                </tbody>
            </table></div>{!routes.length && <div className="empty-state"><Icon name="route" /><p>Aún no hay rutas.</p></div>}</div>
        </>
    );
}
