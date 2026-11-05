import { Head, Link, usePage } from '@inertiajs/react';
import Icon from '../../components/Icon';
import { fechaHora, STATUS, Thumb } from '../../admin/ui';
import { u } from '../../lib/url';

export default function Dashboard({ stats, published, drafts, scheduled, noCoords, recent, activity, demo }) {
    const { auth } = usePage().props;
    return (
        <>
            <Head title="Panel" />
            <div className="quick">
                <Link className="btn btn-primary" href={u('/admin/contenido/turismo/nuevo')}><Icon name="plus" /> Nuevo destino</Link>
                <Link className="btn btn-outline" href={u('/admin/contenido/noticias/nuevo')}><Icon name="news" /> Nueva noticia</Link>
                <Link className="btn btn-outline" href={u('/admin/multimedia')}><Icon name="upload" /> Subir fotos o videos</Link>
                <Link className="btn btn-outline" href={u('/admin/rutas/nueva')}><Icon name="route" /> Nueva ruta</Link>
                <Link className="btn btn-outline" href={u('/admin/inicio')}><Icon name="star" /> Editar portada</Link>
            </div>
            <div className="stats">
                {stats.map(([label, n, ic, color, href]) => (
                    <Link key={label} className="stat" href={u(href)} style={{ '--c': color }}><span className="ico"><Icon name={ic} /></span><span><b>{n.toLocaleString('es-CO')}</b><small>{label}</small></span></Link>
                ))}
            </div>
            {(demo.entries > 0 || demo.media > 0) && (
                <div className="alert alert-info"><Icon name="info" /><span>El sitio tiene {demo.entries} publicaciones y {demo.media} archivos de <strong>demostración</strong>. Cuando cargues contenido real puedes eliminarlos desde {auth.role === 'admin' ? <Link href={u('/admin/configuracion')}>Configuración</Link> : 'Configuración (administradores)'}.</span></div>
            )}
            {noCoords > 0 && <div className="alert alert-info"><Icon name="pin" /><span>{noCoords} destinos publicados no tienen coordenadas y no aparecen en el mapa. <Link href={u('/admin/mapa')}>Revisar en Gestión del mapa</Link>.</span></div>}
            <div className="grid grid-2">
                <section className="card">
                    <div className="card-head"><h2><Icon name="layers" /> Estado del contenido</h2></div>
                    <div className="stats" style={{ marginBottom: 0 }}>
                        <div className="stat" style={{ '--c': '#1f8a5b' }}><span className="ico"><Icon name="check" /></span><span><b>{published}</b><small>Publicadas</small></span></div>
                        <div className="stat" style={{ '--c': '#f2a900' }}><span className="ico"><Icon name="edit" /></span><span><b>{drafts}</b><small>Borradores</small></span></div>
                        <div className="stat" style={{ '--c': '#1d6fe0' }}><span className="ico"><Icon name="clock" /></span><span><b>{scheduled}</b><small>Programadas</small></span></div>
                    </div>
                </section>
                <section className="card">
                    <div className="card-head"><h2><Icon name="clock" /> Actividad reciente</h2>{auth.role === 'admin' && <Link className="hint" href={u('/admin/auditoria')}>Ver auditoría</Link>}</div>
                    <ul className="activity">
                        {activity.map((a) => <li key={a.id}><span className="a-ico"><Icon name="info" /></span><span><strong>{a.user}</strong> · {a.action}: {a.summary}<small>{fechaHora(a.at)}</small></span></li>)}
                        {!activity.length && <li className="muted">Sin actividad todavía.</li>}
                    </ul>
                </section>
            </div>
            <section className="card" style={{ marginTop: 18 }}>
                <div className="card-head"><h2><Icon name="edit" /> Editado recientemente</h2></div>
                <div className="table-wrap"><table className="table">
                    <thead><tr><th /><th>Título</th><th>Sección</th><th>Estado</th><th>Actualizado</th><th /></tr></thead>
                    <tbody>
                        {recent.map((e) => (
                            <tr key={e.id}>
                                <td><Thumb src={e.thumb} /></td>
                                <td><Link className="t-title" href={u(`/admin/publicaciones/${e.id}/editar`)}>{e.title}</Link></td>
                                <td>{e.section}</td>
                                <td><span className={`pill pill-${e.status}`}>{STATUS[e.status]}</span></td>
                                <td className="muted">{fechaHora(e.updatedAt)}</td>
                                <td className="actions"><Link className="btn btn-outline btn-sm" href={u(`/admin/publicaciones/${e.id}/editar`)}><Icon name="edit" /> Editar</Link></td>
                            </tr>
                        ))}
                    </tbody>
                </table></div>
            </section>
        </>
    );
}
