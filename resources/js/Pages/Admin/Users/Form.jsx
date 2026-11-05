import { Head, useForm } from '@inertiajs/react';
import { Field, Icon, Input, u } from '../../../admin/ui';

export default function Form({ user, roles }) {
    const form = useForm({ name: user?.name ?? '', email: user?.email ?? '', username: user?.username ?? '', role: user?.role ?? 'editor', password: '', password_confirmation: '' });
    return (
        <form className="card" style={{ maxWidth: 640 }} onSubmit={(e) => { e.preventDefault(); form.post(u(user ? '/admin/usuarios/' + user.id : '/admin/usuarios/nuevo')); }}>
            <Head title={user ? 'Editar cuenta' : 'Nueva cuenta'} />
            <Input form={form} name="name" label="Nombre completo" maxLength={120} />
            <Input form={form} name="email" label="Correo" type="email" />
            <Input form={form} name="username" label="Usuario" maxLength={60} />
            <Field label="Rol" htmlFor="role" error={form.errors.role}><select className="select" id="role" value={form.data.role} onChange={(e) => form.setData('role', e.target.value)}>{Object.entries(roles).map(([k, l]) => <option key={k} value={k}>{l}</option>)}</select></Field>
            <Input form={form} name="password" label={user ? 'Nueva contraseña (opcional)' : 'Contraseña'} type="password" autoComplete="new-password" help="Mínimo 10 caracteres, con letras y números. Cambiarla cierra las sesiones abiertas de esa cuenta." />
            <Input form={form} name="password_confirmation" label="Repite la contraseña" type="password" autoComplete="new-password" />
            <button className="btn btn-primary" type="submit" disabled={form.processing}><Icon name="check" /> Guardar</button>
        </form>
    );
}
