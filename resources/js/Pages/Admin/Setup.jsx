import { Head, useForm } from '@inertiajs/react';
import { AuthCard } from './Login';
import { u } from '../../lib/url';

export default function Setup({ token }) {
    const form = useForm({ name: '', email: '', username: '', password: '', password_confirmation: '', setup_token: token });
    const f = (name, label, type = 'text', auto) => (
        <>
            <label className="field-label" htmlFor={name}>{label}</label>
            <input className={`input ${form.errors[name] ? 'is-invalid' : ''}`} id={name} type={type} autoComplete={auto} required value={form.data[name]} onChange={(e) => form.setData(name, e.target.value)} />
            {form.errors[name] && <p className="field-error">{form.errors[name]}</p>}
        </>
    );
    return (
        <AuthCard>
            <Head title="Crear la primera cuenta" />
            <h1>Crear la primera cuenta</h1>
            <p className="muted">Te damos la bienvenida. Crea la cuenta de administración para gestionar el contenido. Esta página desaparece en cuanto exista la primera cuenta.</p>
            <form className="stack" noValidate onSubmit={(e) => { e.preventDefault(); form.post(u('/admin/instalar')); }}>
                {f('name', 'Nombre completo', 'text', 'name')}
                {f('email', 'Correo', 'email', 'email')}
                {f('username', 'Usuario', 'text', 'username')}
                {f('password', 'Contraseña (mínimo 10 caracteres, letras y números)', 'password', 'new-password')}
                {f('password_confirmation', 'Repite la contraseña', 'password', 'new-password')}
                <button className="btn btn-primary btn-block" type="submit" disabled={form.processing}>Crear administrador</button>
            </form>
        </AuthCard>
    );
}
Setup.layout = (p) => p;
