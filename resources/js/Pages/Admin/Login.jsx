import { Head, Link, useForm, usePage } from '@inertiajs/react';
import Icon from '../../components/Icon';
import { Logo } from '../../components/Brand';
import { Flash } from '../../Layouts/AdminLayout';
import { u } from '../../lib/url';

export function AuthCard({ children }) {
    const { site } = usePage().props;
    return (
        <main className="auth-card">
            <Link className="brand" href={u('/')}><Logo /><span><strong>{site.name}</strong><small>Panel administrativo</small></span></Link>
            <Flash />
            {children}
        </main>
    );
}

export default function Login({ noUsers }) {
    const form = useForm({ login: '', password: '' });
    return (
        <AuthCard>
            <Head title="Ingresar" />
            <h1>Te damos la bienvenida</h1>
            <p className="muted">Ingresa con tu correo o nombre de usuario.</p>
            <form className="stack" noValidate onSubmit={(e) => { e.preventDefault(); form.post(u('/admin/login'), { onFinish: () => form.reset('password') }); }}>
                <label className="field-label" htmlFor="login">Correo o usuario</label>
                <input className={`input ${form.errors.login ? 'is-invalid' : ''}`} id="login" autoComplete="username" required autoFocus value={form.data.login} onChange={(e) => form.setData('login', e.target.value)} />
                {form.errors.login && <p className="field-error">{form.errors.login}</p>}
                <label className="field-label" htmlFor="password">Contraseña</label>
                <input className="input" id="password" type="password" autoComplete="current-password" required value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} />
                <button className="btn btn-primary btn-block" type="submit" disabled={form.processing}><Icon name="arrow-right" /> Ingresar</button>
            </form>
            {noUsers && <p className="hint mt">Aún no hay cuentas. Abre esta página desde el computador donde está instalado el sitio para crear la primera, o usa <code>php artisan vpc:admin</code>.</p>}
            <p className="hint mt"><Link href={u('/')}>← Volver al sitio</Link></p>
        </AuthCard>
    );
}
Login.layout = (p) => p;
