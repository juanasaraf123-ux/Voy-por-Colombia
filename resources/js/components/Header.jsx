import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import Brand from './Brand';
import Icon from './Icon';
import { getJSON, u } from '../lib/url';
import { useFavorites, useMyRoute, useTheme } from '../lib/store';

export const NAV = [
    ['/', 'Inicio', 'home', '#ff5a5f'],
    ['/turismo', 'Turismo', 'map', '#0f9d77'],
    ['/gastronomia', 'Gastronomía', 'fork', '#ff6b2c'],
    ['/cafe', 'Café', 'coffee', '#8a4b2a'],
    ['/emprendimiento', 'Emprendimiento', 'store', '#7b3fe4'],
    ['/cultura', 'Cultura', 'mask', '#ff3f8e'],
    ['/galeria', 'Galería', 'camera', '#f2a900'],
    ['/noticias', 'Noticias', 'news', '#1d6fe0'],
];

function useCurrentPath() {
    const { url } = usePage();
    const path = url.split('?')[0];
    return (href) => (href === '/' ? path === '/' : path === href || path.startsWith(href + '/'));
}

export function ThemeSwitch() {
    const [theme, setTheme] = useTheme();
    return (
        <div className="theme-switch" role="group" aria-label="Tema de color">
            <button type="button" aria-pressed={theme === 'dark'} onClick={() => setTheme('dark')} aria-label="Modo oscuro" title="Modo oscuro"><Icon name="moon" /></button>
            <button type="button" aria-pressed={theme !== 'dark'} onClick={() => setTheme('light')} aria-label="Modo claro" title="Modo claro"><Icon name="sun" /></button>
        </div>
    );
}

function SearchOverlay({ onClose }) {
    const [q, setQ] = useState('');
    const [res, setRes] = useState(null);
    const input = useRef(null);
    useEffect(() => { input.current?.focus({ preventScroll: true }); }, []);
    useEffect(() => {
        const key = (e) => { if (e.key === 'Escape') onClose(); };
        window.addEventListener('keydown', key);
        return () => window.removeEventListener('keydown', key);
    }, [onClose]);
    useEffect(() => {
        if (q.trim().length < 2) { setRes(null); return undefined; }
        const t = setTimeout(() => getJSON('/api/buscar?q=' + encodeURIComponent(q.trim())).then(setRes).catch(() => setRes(null)), 180);
        return () => clearTimeout(t);
    }, [q]);
    const go = (href) => { onClose(); router.visit(u(href)); };
    return (
        <div className="search-overlay is-open" onClick={(e) => e.target === e.currentTarget && onClose()}>
            <div className="search-panel" role="dialog" aria-modal="true" aria-label="Buscar en la plataforma">
                <form className="search-field" role="search" onSubmit={(e) => { e.preventDefault(); if (q.trim()) go('/buscar?q=' + encodeURIComponent(q.trim())); }}>
                    <Icon name="search" />
                    <label className="sr-only" htmlFor="global-search">Buscar destinos, municipios, experiencias o noticias</label>
                    <input ref={input} id="global-search" type="search" autoComplete="off" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Busca un destino, municipio, plato, festival…" />
                    <button className="icon-btn" type="button" onClick={onClose} aria-label="Cerrar buscador"><Icon name="close" /></button>
                </form>
                <div className="search-results" aria-live="polite">
                    {!res ? (
                        <div className="search-hint">Prueba con:
                            <div className="chips">
                                {['Caribe', 'Café', 'Cartagena', 'Carnaval', 'Ajiaco', 'Amazonas'].map((s) => <button key={s} type="button" className="chip" onClick={() => setQ(s)}>{s}</button>)}
                            </div>
                        </div>
                    ) : !res.results.length ? (
                        <div className="search-hint">No encontramos resultados para «{q}». Prueba con un departamento, municipio o tipo de experiencia.</div>
                    ) : (
                        <>
                            {res.results.map((it, i) => (
                                <button key={i} type="button" className="search-item" onClick={() => go(it.url)} style={{ width: '100%', textAlign: 'left' }}>
                                    {it.thumb ? <img className="thumb" src={it.thumb} alt="" loading="lazy" /> : <span className="thumb"><Icon name={it.icon || 'pin'} /></span>}
                                    <span><strong>{it.title}</strong><small>{it.subtitle}</small></span>
                                </button>
                            ))}
                            <div className="search-all"><button type="button" className="link-arrow" onClick={() => go('/buscar?q=' + encodeURIComponent(q.trim()))}>Ver todos los resultados <Icon name="arrow-right" /></button></div>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}

export default function Header({ transparent = false }) {
    const isActive = useCurrentPath();
    const [menu, setMenu] = useState(false);
    const [search, setSearch] = useState(false);
    const [favs] = useFavorites();
    const [route] = useMyRoute();
    useEffect(() => {
        const key = (e) => {
            if (e.key === '/' && !/input|textarea|select/i.test(document.activeElement?.tagName || '')) { e.preventDefault(); setSearch(true); }
        };
        window.addEventListener('keydown', key);
        return () => window.removeEventListener('keydown', key);
    }, []);
    useEffect(() => router.on('navigate', () => { setMenu(false); setSearch(false); }), []);

    return (
        <>
            <header className={`site-header ${transparent ? 'is-transparent' : ''}`}>
                <div className="header-inner">
                    <Brand />
                    <nav className="main-nav" aria-label="Navegación principal">
                        <ul>
                            {NAV.map(([href, label, ic, color]) => (
                                <li key={href}>
                                    <Link href={u(href)} style={{ '--c': color }} aria-current={isActive(href) ? 'page' : undefined}>
                                        <Icon name={ic} /><span>{label}</span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </nav>
                    <div className="header-actions">
                        <button className="search-pill" type="button" onClick={() => setSearch(true)} aria-label="Buscar (atajo: /)"><Icon name="search" /><span>¿Qué lugar quieres descubrir?</span></button>
                        <Link className="icon-btn" href={u('/favoritos')} aria-label="Mis favoritos"><Icon name="heart" />{favs.length > 0 && <span className="count has-items">{favs.length}</span>}</Link>
                        <Link className="icon-btn" href={u('/mi-ruta')} aria-label="Mi ruta"><Icon name="route" />{route.length > 0 && <span className="count has-items">{route.length}</span>}</Link>
                        <a className="avatar-btn" href={u('/admin')} aria-label="Acceso para administradores" title="Acceso para administradores"><Icon name="user" /></a>
                        <button className="icon-btn menu-toggle" type="button" onClick={() => setMenu(true)} aria-label="Abrir menú" aria-expanded={menu}><Icon name="menu" /></button>
                    </div>
                </div>
            </header>
            <div className={`mobile-menu ${menu ? 'is-open' : ''}`} aria-hidden={!menu}>
                <div className="mobile-menu-top">
                    <Brand />
                    <button className="icon-btn" type="button" onClick={() => setMenu(false)} aria-label="Cerrar menú"><Icon name="close" /></button>
                </div>
                <nav aria-label="Navegación móvil">
                    <ol>
                        {NAV.map(([href, label, , color]) => (
                            <li key={href}><Link className="mm-link" style={{ '--c': color }} href={u(href)} aria-current={isActive(href) ? 'page' : undefined}>{label}<span className="dot" /></Link></li>
                        ))}
                    </ol>
                </nav>
                <div className="mobile-menu-foot">
                    <button className="btn btn-light" type="button" onClick={() => { setMenu(false); setSearch(true); }}><Icon name="search" /> Buscar</button>
                    <Link className="btn btn-glass" href={u('/mi-ruta')}><Icon name="route" /> Mi ruta</Link>
                    <Link className="btn btn-glass" href={u('/favoritos')}><Icon name="heart" /> Favoritos</Link>
                </div>
            </div>
            {search && <SearchOverlay onClose={() => setSearch(false)} />}
        </>
    );
}
