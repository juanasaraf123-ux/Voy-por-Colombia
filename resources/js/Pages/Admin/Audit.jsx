import { Head } from '@inertiajs/react';
import { fechaHora, Pager } from '../../admin/ui';

export default function Audit({ items, pagination }) {
    return (
        <>
            <Head title="Auditoría" />
            <p className="muted">Registro de las acciones administrativas ({pagination.total}).</p>
            <div className="card"><div className="table-wrap"><table className="table">
                <thead><tr><th>Fecha</th><th>Cuenta</th><th>Acción</th><th>Detalle</th><th>IP</th></tr></thead>
                <tbody>{items.map((a) => <tr key={a.id}><td className="muted">{fechaHora(a.at)}</td><td>{a.user}</td><td>{a.action}</td><td>{a.summary}</td><td className="muted">{a.ip}</td></tr>)}</tbody>
            </table></div></div>
            <Pager page={pagination.page} pages={pagination.pages} />
        </>
    );
}
