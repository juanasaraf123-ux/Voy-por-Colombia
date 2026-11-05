import { Head, Link, router } from '@inertiajs/react';
import { fechaHora, Icon, u } from '../../../admin/ui';

export default function Index({ users, roles, me }) {
    return (
        <>
            <Head title="Usuarios" />
            <div className="flex between" style={{ marginBottom: 14 }}>
                <p className="muted" style={{ margin: 0 }}>Administrador: acceso total. Editor: contenido, multimedia, mapa, portada, rutas y categorías.</p>
                <Link className="btn btn-primary" href={u('/admin/usuarios/nuevo')}><Icon name="plus" /> Nueva cuenta</Link>
            </div>
            <div className="card"><div className="table-wrap"><table className="table">
                <thead><tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Estado</th><th>Último ingreso</th><th /></tr></thead>
                <tbody>{users.map((x) => (
                    <tr key={x.id}>
                        <td><strong>{x.name}</strong><br /><small className="muted">{x.email}</small></td>
                        <td>{x.username}</td>
                        <td>{roles[x.role]}</td>
                        <td><span className={`pill ${x.is_active ? 'pill-published' : 'pill-off'}`}>{x.is_active ? 'Activa' : 'Desactivada'}</span></td>
                        <td className="muted">{fechaHora(x.last_login_at) || '—'}</td>
                        <td className="actions">
                            <Link className="btn btn-outline btn-sm" href={u('/admin/usuarios/' + x.id)}><Icon name="edit" /> Editar</Link>
                            {x.id !== me && <button className="btn btn-outline btn-sm" type="button" onClick={() => router.post(u(`/admin/usuarios/${x.id}/estado`), {}, { preserveScroll: true })}>{x.is_active ? 'Desactivar' : 'Activar'}</button>}
                        </td>
                    </tr>
                ))}</tbody>
            </table></div></div>
        </>
    );
}
