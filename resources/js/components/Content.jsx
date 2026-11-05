import { Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import Icon from './Icon';
import { FavButton } from './Buttons';
import { fecha, place, u } from '../lib/url';

export function Wave({ className = 'page-wave' }) {
    return (
        <svg className={className} viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
            <path fill="currentColor" d="M0 64c120-30 240-46 360-40s240 34 360 40 240-14 360-30 240-18 360 0v86H0z" />
        </svg>
    );
}

/** Encabezado de página con collage de imágenes. */
export function PageHero({ color, icon, eyebrow, title, accent, lead, images = [], sticker, children, compact = false }) {
    const imgs = images.filter(Boolean);
    return (
        <section className="page-hero" style={{ '--c': color, paddingBottom: compact ? 90 : undefined }}>
            <div className={`container ${imgs.length ? 'page-hero-grid' : ''}`}>
                <div>
                    <span className="eyebrow"><Icon name={icon} /> {eyebrow}</span>
                    <h1>{title}{accent && <> <em>{accent}</em></>}</h1>
                    {lead && <p className="lead">{lead}</p>}
                    {children}
                </div>
                {imgs.length > 0 && (
                    <div className="hero-collage">
                        <figure><img src={imgs[0]} alt="" /></figure>
                        {imgs[1] && <figure><img src={imgs[1]} alt="" loading="lazy" /></figure>}
                        {sticker && <div className="sticker"><span><b>{sticker[0]}</b>{sticker[1]}</span></div>}
                    </div>
                )}
            </div>
            <Wave />
        </section>
    );
}

/** Tarjeta estándar de publicación. */
export function Card({ e, showSection = false }) {
    const loc = place(e);
    return (
        <article className="card" style={{ '--c': e.color }}>
            <div className="card-media">
                {e.image ? <img src={e.image} alt={e.alt || e.title} loading="lazy" /> : <img src={u('/assets/img/placeholder.svg')} alt="" />}
                <div className="badges">
                    {showSection && <span className="badge badge-section"><Icon name={e.icon} /> {e.sectionLabel}</span>}
                    {e.hiddenGem && <span className="badge badge-gem"><Icon name="sparkle" /> Joya</span>}
                    {e.isDemo && ['entrepreneurship', 'news'].includes(e.section) && <span className="badge badge-demo">Demostración</span>}
                </div>
                {e.section !== 'news' && <FavButton id={e.id} className="fav-btn" label={`Guardar «${e.title}» en favoritos`} />}
            </div>
            <div className="card-body">
                <div className="card-meta">
                    {e.section === 'news' && e.publishedAt && <span><Icon name="calendar" /> {fecha(e.publishedAt)}</span>}
                    {e.section !== 'news' && e.eventStart && <span><Icon name="calendar" /> {fecha(e.eventStart, false)}</span>}
                    {loc && <span><Icon name="pin" /> {loc}</span>}
                </div>
                <h3><Link href={u(e.url)}>{e.title}</Link></h3>
                <p>{e.summary}</p>
                {e.categories?.length > 0 && (
                    <div className="card-cats">{e.categories.slice(0, 3).map((c) => <span key={c.slug} className="mini-tag" style={{ '--t': c.color || e.color }}>{c.name}</span>)}</div>
                )}
            </div>
        </article>
    );
}

export function Empty({ icon = 'compass', title = 'Aún no hay contenido aquí', text = 'Prueba con otros filtros o vuelve pronto: estamos agregando nuevos lugares.', children }) {
    return (
        <div className="empty">
            <Icon name={icon} />
            <h3>{title}</h3>
            <p className="muted">{text}</p>
            {children}
        </div>
    );
}

/** Navega conservando los filtros actuales (sin recargar ni subir la página al inicio). */
export function visitWith(path, params) {
    const clean = Object.fromEntries(Object.entries(params).filter(([, v]) => v !== '' && v !== null && v !== undefined));
    router.get(u(path), clean, { preserveState: true, preserveScroll: true, replace: true });
}

export function Filters({ path, filters, categories = [], regions = [], departments = [], municipalities = [], placeholder = 'Buscar…' }) {
    const [q, setQ] = useState(filters.q || '');
    const set = (k, v) => visitWith(path, { ...filters, [k]: v, ...(k === 'departamento' ? { municipio: '' } : {}), pagina: '' });
    return (
        <form className="filters" onSubmit={(ev) => { ev.preventDefault(); set('q', q); }}>
            <div className="filters-row">
                <div className="field grow">
                    <Icon name="search" />
                    <label className="sr-only" htmlFor="f-q">Buscar</label>
                    <input className="input" style={{ width: '100%' }} id="f-q" type="search" value={q} onChange={(e) => setQ(e.target.value)} placeholder={placeholder} />
                </div>
                {regions.length > 0 && (
                    <select className="select" aria-label="Región" value={filters.region || ''} onChange={(e) => set('region', e.target.value)}>
                        <option value="">Todas las regiones</option>
                        {regions.map((r) => <option key={r.slug} value={r.slug}>{r.name}</option>)}
                    </select>
                )}
                <select className="select" aria-label="Departamento" value={filters.departamento || ''} onChange={(e) => set('departamento', e.target.value)}>
                    <option value="">Todos los departamentos</option>
                    {departments.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}
                </select>
                {municipalities.length > 0 && (
                    <select className="select" aria-label="Municipio" value={filters.municipio || ''} onChange={(e) => set('municipio', e.target.value)}>
                        <option value="">Todos los municipios</option>
                        {municipalities.map((m) => <option key={m.id} value={m.id}>{m.name}</option>)}
                    </select>
                )}
                <button className="btn" type="submit"><Icon name="filter" /> Filtrar</button>
            </div>
            {categories.length > 0 && (
                <div className="chips chips-scroll" role="group" aria-label="Categorías">
                    <button className={`chip ${!filters.categoria ? 'is-active' : ''}`} type="button" onClick={() => set('categoria', '')}>Todo</button>
                    {categories.map((c) => (
                        <button key={c.slug} className={`chip ${filters.categoria === c.slug ? 'is-active' : ''}`} style={{ '--chip': c.color }} type="button" onClick={() => set('categoria', c.slug)}>
                            <Icon name={c.icon || 'tag'} /> {c.name}
                        </button>
                    ))}
                </div>
            )}
        </form>
    );
}

export function Pagination({ path, filters, page, pages }) {
    if (pages < 2) return null;
    const go = (p) => router.get(u(path), Object.fromEntries(Object.entries({ ...filters, pagina: p }).filter(([, v]) => v !== '' && v !== null)), { preserveState: true });
    const nums = [];
    for (let p = Math.max(1, page - 2); p <= Math.min(pages, page + 2); p++) nums.push(p);
    return (
        <nav className="pagination" aria-label="Paginación">
            {page > 1 && <button type="button" onClick={() => go(page - 1)} aria-label="Página anterior"><Icon name="chevron-left" /></button>}
            {nums.map((p) => (p === page ? <span key={p} className="is-current" aria-current="page">{p}</span> : <button key={p} type="button" onClick={() => go(p)}>{p}</button>))}
            {page < pages && <button type="button" onClick={() => go(page + 1)} aria-label="Página siguiente"><Icon name="chevron-right" /></button>}
        </nav>
    );
}

/** HTML ya saneado por el servidor (Markdown con el HTML del usuario escapado). */
export function Html({ html, className = 'prose' }) {
    return <div className={className} dangerouslySetInnerHTML={{ __html: html }} />;
}

/** Carga perezosa de un componente pesado (mapas) cuando entra en pantalla. */
export function useInView(options) {
    const ref = useRef(null);
    const [seen, setSeen] = useState(false);
    useEffect(() => {
        if (!ref.current || seen) return undefined;
        const io = new IntersectionObserver(([en]) => { if (en.isIntersecting) { setSeen(true); io.disconnect(); } }, options);
        io.observe(ref.current);
        return () => io.disconnect();
    }, [seen, options]);
    return [ref, seen];
}
