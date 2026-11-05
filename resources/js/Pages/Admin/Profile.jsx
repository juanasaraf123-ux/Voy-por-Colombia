import { Head, useForm } from '@inertiajs/react';
import { Icon, Input, u } from '../../admin/ui';

export default function Profile({ user }) {
    const form = useForm({ name: user.name, email: user.email, current_password: '', password: '', password_confirmation: '' });
    return (
        <form className="card" style={{ maxWidth: 640 }} onSubmit={(e) => { e.preventDefault(); form.post(u('/admin/perfil'), { onSuccess: () => form.reset('current_password', 'password', 'password_confirmation') }); }}>
            <Head title="Mi perfil" />
            <p className="muted">Usuario: <strong>{user.username}</strong></p>
            <Input form={form} name="name" label="Nombre completo" maxLength={120} />
            <Input form={form} name="email" label="Correo" type="email" />
            <h2>Cambiar contraseña</h2>
            <Input form={form} name="current_password" label="Contraseña actual" type="password" autoComplete="current-password" />
            <Input form={form} name="password" label="Nueva contraseña" type="password" autoComplete="new-password" help="Mínimo 10 caracteres, con letras y números." />
            <Input form={form} name="password_confirmation" label="Repite la nueva contraseña" type="password" autoComplete="new-password" />
            <button className="btn btn-primary" type="submit" disabled={form.processing}><Icon name="check" /> Guardar</button>
        </form>
    );
}
