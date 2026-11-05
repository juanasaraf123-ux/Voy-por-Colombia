import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Icon from '../components/Icon';
import { Logo } from '../components/Brand';
import { u } from '../lib/url';

export const SECTIONS = [
    ['tourism', 'turismo', 'Turismo', 'compass', '#0f9d77'],
    ['gastronomy', 'gastronomia', 'Gastronomía', 'fork', '#ff6b2c'],
    ['coffee', 'cafe', 'Café', 'coffee', '#8a4b2a'],
    ['entrepreneurship', 'emprendimiento', 'Emprendimiento', 'store', '#7b3fe4'],
    ['culture', 'cultura', 'Cultura', 'mask', '#ff3f8e'],
    ['news', 'noticias', 'Noticias', 'news', '#1d6fe0'],
];

export function can(auth, permission) {
    const perms = auth?.permissions || [];
    return perms.includes('*') || perms.includes(permission);
}

export function Flash() {
    const { flash, errors } = usePage().props;
    const [hidden, setHidden] = useState({});
    const items = [];
    if (flash?.success) items.push(['success', flash.success]);
    if (flash?.error) items.push(['error', flash.error]);
    if (errors && Object.keys(errors).length && !flash?.error) items.push(['error', 'Revisa los campos marcados.']);
    useEffect(() => setHidden({}), [flash]);
    return items.filter(([, t]) => !hidden[t]).map(([type, text]) => (
        <div key={text} className={`alert alert-${type}`} role={type === 'error' ? 'alert' : 'status'}>
            <Icon name={type === 'error' ? 'alert' : 'check'} /><span>{text}</span>
            <button type="button" className="alert-close" aria-label="Cerrar" onClick={() => setHidden((h) => ({ ...h, [text]: true }))}><Icon name="close" /></button>
        </div>
    ));
}

export default function AdminLayout({ children }) {
    const { auth, site, url } = usePage().props;
    const path = usePage().url.split('?')[0];
    const [open, setOpen] = useState(false);
    useEffect(() => router.on('navigate', () => setOpen(false)), []);
    const active = (p, exact = false) => (exact ? path === p : path === p || path.startsWith(p + '/'));
    const nav = (href, icon, label, exact = false, color) => (
        <Link href={u(href)} aria-current={active(href, exact) ? 'page' : undefined} style={color ? { '--nav-c': color } : undefined}><Icon name={icon} /> {label}</Link>
    );
    const title = children?.props?.title || 'Panel';
    return (
        <>
            <a className="skip-link" href="#admin-main">Saltar al contenido</a>
            <aside className={`sidebar ${open ? 'is-open' : ''}`}>
                <Link className="brand" href={u('/admin')}><Logo /><span><strong>{site.name}</strong><small>Panel administrativo</small></span></Link>
                <nav aria-label="Menú del panel">
                    {nav('/admin', 'home', 'Panel', true)}
                    <p className="nav-label">Contenido</p>
                    {SECTIONS.map(([, p, label, ic, c]) => <span key={p}>{nav('/admin/contenido/' + p, ic, label, false, c)}</span>)}
                    {nav('/admin/rutas', 'route', 'Rutas')}
                    <p className="nav-label">Sitio</p>
                    {nav('/admin/multimedia', 'camera', 'Multimedia')}
                    {nav('/admin/mapa', 'map', 'Mapa')}
                    {nav('/admin/inicio', 'star', 'Página de inicio')}
                    {nav('/admin/categorias', 'tag', 'Categorías')}
                    {auth?.role === 'admin' && <>
                        <p className="nav-label">Administración</p>
                        {nav('/admin/usuarios', 'users', 'Usuarios')}
                        {nav('/admin/auditoria', 'shield', 'Auditoría')}
                        {nav('/admin/configuracion', 'settings', 'Configuración')}
                    </>}
                </nav>
                <div className="sidebar-foot">
                    <Link className="me" href={u('/admin/perfil')}><span className="avatar">{(auth?.name || '?').slice(0, 1).toUpperCase()}</span><span><strong>{auth?.name}</strong><small>{auth?.roleLabel}</small></span></Link>
                    <div className="flex">
                        <a className="btn btn-ghost btn-sm" href={u('/')} target="_blank" rel="noopener"><Icon name="globe" /> Ver sitio</a>
                        <button className="btn btn-ghost btn-sm" type="button" onClick={() => router.post(u('/admin/logout'))}><Icon name="logout" /> Salir</button>
                    </div>
                </div>
            </aside>
            <div className="admin-wrap">
                <header className="topbar">
                    <button className="icon-btn menu-btn" type="button" onClick={() => setOpen(!open)} aria-label="Abrir menú"><Icon name="menu" /></button>
                    <h1>{title}</h1>
                </header>
                <main id="admin-main" className="admin-main" tabIndex={-1}>
                    <Flash />
                    {children}
                </main>
            </div>
        </>
    );
}
