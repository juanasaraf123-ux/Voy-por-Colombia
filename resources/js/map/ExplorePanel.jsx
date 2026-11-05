import { Link } from '@inertiajs/react';
import Icon from '../components/Icon';
import { FavButton, RouteButton } from '../components/Buttons';
import { useViewer } from '../components/MediaViewer';
import { fecha, place as placeLabel, u } from '../lib/url';

/** Acciones multimedia del lugar: se abren en un visor encima del mapa. */
function MediaLinks({ m }) {
    const { openEntry } = useViewer();
    const item = (kind, ic, label, n) => (
        <button type="button" disabled={!n} onClick={() => openEntry(m.id, kind)}><Icon name={ic} />{label}</button>
    );
    return (
        <div className="xp-media">
            {item('image', 'camera', 'Fotos', 1)}
            {item('video', 'play', 'Video', m.media?.video)}
            {item('audio', 'volume', 'Audio', m.media?.audio)}
            <a href={m.lat !== null ? `https://www.openstreetmap.org/directions?to=${m.lat},${m.lng}` : u(m.url)} target="_blank" rel="noopener noreferrer"><Icon name="route" />Cómo llegar</a>
        </div>
    );
}

function Places({ items, onPlace, onHover, empty = 'Aún no hay lugares publicados aquí.' }) {
    if (!items.length) return <p className="xp-muted">{empty}</p>;
    return (
        <ul className="xp-list">
            {items.map((p) => (
                <li key={p.id}>
                    <button type="button" onClick={() => onPlace(p.id)} onMouseEnter={() => onHover(p.id, true)} onMouseLeave={() => onHover(p.id, false)}>
                        {p.thumb ? <img src={p.thumb} alt="" /> : <i style={{ background: p.catColor }} />}
                        <span><strong>{p.shortName}</strong><small>{p.extra ?? (p.pointType || p.catName)}</small></span>
                    </button>
                </li>
            ))}
        </ul>
    );
}

const Back = ({ onClick, children }) => <button type="button" className="xp-back" onClick={onClick}><Icon name="arrow-left" /> {children}</button>;

export default function ExplorePanel(p) {
    const { level, place, dep, mun, route, routes, depPlaces, munPlaces, markers, newest, news, onBack, onPlace, onHover } = p;
    const { open, openEntry } = useViewer();

    if (level === 'place') {
        const m = place;
        const near = markers.filter((x) => x.id !== m.id).map((x) => ({ ...x, d: Math.hypot(x.lat - m.lat, (x.lng - m.lng) * Math.cos((m.lat * Math.PI) / 180)) * 111 }))
            .sort((a, b) => a.d - b.d).slice(0, 3).map((x) => ({ ...x, extra: `${x.pointType || x.catName} · ${Math.round(x.d)} km` }));
        return (
            <div className="xp" key={m.id} style={{ '--c': m.catColor }}>
                <figure className="xp-cover">
                    {m.large && <img src={m.large} alt={m.alt || m.shortName} />}
                    {m.media.video > 0 && <button className="xp-play" type="button" onClick={() => openEntry(m.id, 'video')} aria-label="Ver video"><Icon name="play" /></button>}
                    <FavButton id={m.id} className="xp-fav fav-btn" />
                </figure>
                <Back onClick={onBack}>{mun ? mun.name : dep ? dep.name : route ? route.title : 'Novedades'}</Back>
                {p.touring && <span className="xp-tour"><Icon name="route" /> Recorriendo la ruta…</span>}
                <span className="xp-kicker"><Icon name={m.pointIcon || m.icon} /> {m.pointType || m.catName}</span>
                <h2>{m.shortName}</h2>
                <p className="xp-loc"><Icon name="pin" /> {placeLabel(m)}</p>
                {m.tagline && <p className="xp-tagline">{m.tagline}</p>}
                <p>{m.summary}</p>
                <MediaLinks m={m} />
                <div className="xp-cta">
                    <Link className="btn-yellow" href={u(m.url)}>Ver más <Icon name="chevron-right" /></Link>
                    <RouteButton id={m.id} className="xp-icon-btn" compact />
                </div>
                {near.length > 0 && <><h3>Cerca de aquí</h3><Places items={near} onPlace={onPlace} onHover={onHover} /></>}
                {m.isDemo && <small className="xp-muted">Contenido de demostración</small>}
            </div>
        );
    }

    if (level === 'department') {
        const info = dep.info;
        return (
            <div className="xp" key={dep.slug} style={{ '--c': info?.regionColor || '#2bb673' }}>
                {info?.image && <figure className="xp-cover is-short"><img src={info.image} alt={dep.name} /></figure>}
                <Back onClick={p.onCountry}>Todo el país</Back>
                <span className="xp-kicker"><Icon name="map" /> {info?.region ? `Región ${info.region}` : 'Departamento'}</span>
                <h2>{dep.name}</h2>
                {info?.capital && <p className="xp-loc"><Icon name="pin" /> Capital: {info.capital}</p>}
                {!info ? <p className="xp-muted">Cargando…</p> : <>
                    {info.summary && <p>{info.summary}</p>}
                    {info.photos?.length > 0 && <button type="button" className="xp-link" onClick={() => open(info.photos, 0)}><Icon name="camera" /> Ver {info.photos.length} fotos</button>}
                    <h3>{depPlaces.length} {depPlaces.length === 1 ? 'lugar' : 'lugares'}</h3>
                    <Places items={depPlaces} onPlace={onPlace} onHover={onHover} />
                    {info.municipalities.length > 0 && (
                        <p className="xp-inline">
                            <strong>Municipios: </strong>
                            {info.municipalities.map((x, i) => <span key={x.slug}>{i > 0 && ' · '}<button type="button" onClick={() => p.onMun(x.slug)}>{x.name}</button></span>)}
                        </p>
                    )}
                    <Link className="xp-link" href={u(info.url)}>Todo sobre {dep.name} <Icon name="arrow-right" /></Link>
                </>}
            </div>
        );
    }

    if (level === 'municipality') {
        return (
            <div className="xp" key={mun.slug}>
                <Back onClick={onBack}>{dep?.name}</Back>
                <span className="xp-kicker"><Icon name="pin" /> Municipio</span>
                <h2>{mun.name}</h2>
                <Places items={munPlaces} onPlace={onPlace} onHover={onHover} />
            </div>
        );
    }

    if (level === 'route') {
        return (
            <div className="xp" key={route.id} style={{ '--c': '#ff3f8e' }}>
                {route.image && <figure className="xp-cover is-short"><img src={route.image} alt={route.title} /></figure>}
                <Back onClick={onBack}>Todas las rutas</Back>
                <span className="xp-kicker"><Icon name="route" /> Ruta{route.duration ? ' · ' + route.duration : ''}</span>
                <h2>{route.title}</h2>
                {route.summary && <p>{route.summary}</p>}
                <ol className="xp-stops">{route.stops.map((s) => <li key={s.id}><button type="button" onClick={() => onPlace(s.id)}>{s.shortName}</button></li>)}</ol>
                <button type="button" className="btn-yellow" onClick={() => p.onTour(route)}><Icon name={p.touring ? 'close' : 'play'} /> {p.touring ? 'Detener recorrido' : 'Recorrer en el mapa'}</button>
                <Link className="xp-link" href={u(route.url)}>Ver la ruta completa <Icon name="arrow-right" /></Link>
            </div>
        );
    }

    if (level === 'routes') {
        return (
            <div className="xp">
                <Back onClick={p.onRoutesOff}>Ocultar rutas</Back>
                <span className="xp-kicker"><Icon name="route" /> Rutas sugeridas</span>
                <h2>Elige una ruta</h2>
                <ul className="xp-list">
                    {routes.map((r) => (
                        <li key={r.id}><button type="button" onClick={() => p.onRoute(r.id)}>
                            {r.image && <img src={r.image} alt="" />}
                            <span><strong>{r.title}</strong><small>{r.stopsCount} paradas{r.duration ? ' · ' + r.duration : ''}</small></span>
                        </button></li>
                    ))}
                </ul>
            </div>
        );
    }

    // Vista inicial: lo más nuevo y las noticias destacadas.
    const onMap = newest && markers.some((x) => x.id === newest.id);
    return (
        <div className="xp">
            {newest && <>
                <span className="xp-kicker is-new"><Icon name="sparkle" /> Lo nuevo</span>
                {newest.large && <figure className="xp-cover"><img src={newest.large} alt={newest.alt || newest.title} /></figure>}
                <h2>{newest.shortName}</h2>
                <p className="xp-loc">{newest.sectionLabel}{placeLabel(newest) ? ' · ' + placeLabel(newest) : ''}</p>
                <p>{newest.summary}</p>
                <div className="xp-cta">
                    {onMap ? <button type="button" className="btn-yellow" onClick={() => p.onGoPlace(newest)}><Icon name="pin" /> Ver en el mapa</button>
                        : <Link className="btn-yellow" href={u(newest.url)}>Ver más <Icon name="chevron-right" /></Link>}
                </div>
            </>}
            {news.length > 0 && <>
                <h3>Noticias</h3>
                <ul className="xp-news">
                    {news.map((n) => (
                        <li key={n.id}><Link href={u(n.url)}><small>{fecha(n.publishedAt)}</small><strong>{n.title}</strong></Link></li>
                    ))}
                </ul>
                <Link className="xp-link" href={u('/noticias')}>Todas las noticias <Icon name="arrow-right" /></Link>
            </>}
        </div>
    );
}
