import { Link } from '@inertiajs/react';
import Icon from '../components/Icon';
import { FavButton, RouteButton } from '../components/Buttons';
import { useViewer } from '../components/MediaViewer';
import { place as placeLabel, u } from '../lib/url';

function MediaButtons({ m }) {
    const { openEntry } = useViewer();
    const btn = (kind, ic, label, n) => (
        <button type="button" className={n ? '' : 'is-off'} disabled={!n} onClick={() => openEntry(m.id, kind)}>
            <span><Icon name={ic} /></span>{label}
        </button>
    );
    return (
        <div className="spot-actions">
            {btn('image', 'camera', 'Galería', 1)}
            {btn('video', 'play', 'Video', m.media?.video)}
            {btn('audio', 'volume', 'Audio', m.media?.audio)}
            <a href={m.lat !== null ? `https://www.openstreetmap.org/directions?to=${m.lat},${m.lng}` : u(m.url)} target="_blank" rel="noopener noreferrer"><span><Icon name="route" /></span>Cómo llegar</a>
        </div>
    );
}

function PlaceList({ items, onPlace, onHover }) {
    if (!items.length) return <p className="panel-empty">Aún no hay lugares publicados aquí. ¡Pronto!</p>;
    return (
        <div className="spot-places">
            {items.map((p) => (
                <button key={p.id} type="button" onClick={() => onPlace(p.id)} onMouseEnter={() => onHover(p.id, true)} onMouseLeave={() => onHover(p.id, false)}>
                    {p.thumb ? <img src={p.thumb} alt="" /> : <span className="ico" style={{ background: p.catColor }}><Icon name={p.pointIcon || p.icon} /></span>}
                    <span><strong>{p.shortName}</strong><small>{p.pointType || p.catName}{p.municipality ? ' · ' + p.municipality : ''}</small></span>
                </button>
            ))}
        </div>
    );
}

function PlaceCard({ m, onBack, backLabel, markers, onPlace, onHover, touring }) {
    const { openEntry } = useViewer();
    const near = markers
        .filter((x) => x.id !== m.id && x.lat !== null)
        .map((x) => ({ ...x, d: Math.hypot(x.lat - m.lat, (x.lng - m.lng) * Math.cos((m.lat * Math.PI) / 180)) * 111 }))
        .sort((a, b) => a.d - b.d).slice(0, 3);
    return (
        <article className="spot-card" style={{ '--c': m.catColor }} key={m.id}>
            <div className="spot-media">
                {m.large && <img src={m.large} alt={m.alt || m.shortName} />}
                <span className="spot-chip"><Icon name={m.pointIcon || m.icon} /> {m.pointType || m.catName}</span>
                <FavButton id={m.id} className="spot-fav fav-btn" />
                {m.media.video > 0 && <button className="spot-play" type="button" onClick={() => openEntry(m.id, 'video')} aria-label="Ver video"><Icon name="play" /></button>}
                <div className="spot-title"><h2>{m.shortName}</h2><span><Icon name="pin" /> {placeLabel(m)}</span></div>
            </div>
            <div className="spot-body">
                {onBack && <button type="button" className="spot-back" onClick={onBack}><Icon name="arrow-left" /> {backLabel}</button>}
                {touring && <span className="tour-badge"><Icon name="route" /> Recorriendo la ruta…</span>}
                {m.tagline && <h3>{m.tagline}</h3>}
                <p>{m.summary}</p>
                <MediaButtons m={m} />
                {m.activities?.length > 0 && <div className="spot-tags">{m.activities.slice(0, 4).map((a) => <span key={a}>{a}</span>)}</div>}
                <div className="spot-cta">
                    <Link className="btn-yellow" href={u(m.url)}>Ver más <Icon name="chevron-right" /></Link>
                    <RouteButton id={m.id} className="btn-ghost-light" />
                </div>
                {near.length > 0 && (
                    <div className="spot-near">
                        <strong>Cerca de aquí</strong>
                        <PlaceList items={near.map((x) => ({ ...x, municipality: `${Math.round(x.d)} km` }))} onPlace={onPlace} onHover={onHover} />
                    </div>
                )}
                {m.isDemo && <small className="demo-tag">Contenido de demostración</small>}
            </div>
        </article>
    );
}

export default function ExplorePanel(p) {
    const { level, place, dep, mun, route, routes, depPlaces, munPlaces, markers, regions, counts, onBack, onPlace, onHover = p.onHoverPlace } = p;
    const { open } = useViewer();

    if (level === 'place') {
        const backLabel = mun ? mun.name : dep ? dep.name : route ? route.title : 'Todo el país';
        return <PlaceCard m={place} onBack={dep || route || mun ? onBack : null} backLabel={backLabel} markers={markers} onPlace={onPlace} onHover={onHover} touring={p.touring} />;
    }

    if (level === 'department') {
        const info = dep.info;
        return (
            <article className="spot-card" style={{ '--c': info?.regionColor || '#2bb673' }} key={dep.slug}>
                <div className="spot-media is-short">
                    {info?.image && <img src={info.image} alt={dep.name} />}
                    <span className="spot-chip"><Icon name="map" /> {info?.region ? `Región ${info.region}` : 'Departamento'}</span>
                    <div className="spot-title"><h2>{dep.name}</h2>{info?.capital && <span><Icon name="pin" /> Capital: {info.capital}</span>}</div>
                </div>
                <div className="spot-body">
                    <button type="button" className="spot-back" onClick={p.onCountry}><Icon name="arrow-left" /> Todo el país</button>
                    {!info ? <p className="panel-loading">Cargando…</p> : <>
                        {info.summary && <p>{info.summary}</p>}
                        <div className="panel-stats"><span><b>{depPlaces.length}</b> lugares</span><span><b>{info.municipalities.length}</b> municipios con contenido</span></div>
                        {info.photos?.length > 0 && (
                            <div className="spot-thumbs">
                                {info.photos.slice(0, 4).map((ph, i) => <button key={ph.id} type="button" onClick={() => open(info.photos, i)} aria-label={`Ampliar ${ph.title || ''}`}><img src={ph.thumb} alt="" /></button>)}
                            </div>
                        )}
                        <PlaceList items={depPlaces} onPlace={onPlace} onHover={onHover} />
                        {info.municipalities.length > 0 && (
                            <div className="mun-chips">
                                <strong>Municipios</strong>
                                <div>{info.municipalities.map((x) => <button key={x.slug} type="button" onClick={() => p.onMun(x.slug)}>{x.name} <em>{x.total}</em></button>)}</div>
                            </div>
                        )}
                        <p className="panel-tip"><Icon name="info" /> Toca un municipio en el mapa para acercarte.</p>
                        <Link className="btn-yellow" href={u(info.url)}>Todo sobre {dep.name} <Icon name="chevron-right" /></Link>
                    </>}
                </div>
            </article>
        );
    }

    if (level === 'municipality') {
        return (
            <article className="spot-card" style={{ '--c': '#ff7a1a' }} key={mun.slug}>
                <div className="spot-body">
                    <button type="button" className="spot-back" onClick={onBack}><Icon name="arrow-left" /> {dep?.name}</button>
                    <span className="spot-chip is-static"><Icon name="pin" /> Municipio</span>
                    <h2 className="panel-h">{mun.name}</h2>
                    <p>{munPlaces.length ? `${munPlaces.length} ${munPlaces.length === 1 ? 'lugar para descubrir' : 'lugares para descubrir'} en ${mun.name}.` : ''}</p>
                    <PlaceList items={munPlaces} onPlace={onPlace} onHover={onHover} />
                </div>
            </article>
        );
    }

    if (level === 'route') {
        return (
            <article className="spot-card" style={{ '--c': '#ff3f8e' }} key={route.id}>
                <div className="spot-media is-short">
                    {route.image && <img src={route.image} alt={route.title} />}
                    <span className="spot-chip"><Icon name="route" /> Ruta{route.duration ? ' · ' + route.duration : ''}</span>
                    <div className="spot-title"><h2>{route.title}</h2></div>
                </div>
                <div className="spot-body">
                    <button type="button" className="spot-back" onClick={onBack}><Icon name="arrow-left" /> Todas las rutas</button>
                    {route.summary && <p>{route.summary}</p>}
                    <ol className="spot-stops">{route.stops.map((s) => <li key={s.id}><button type="button" onClick={() => onPlace(s.id)}>{s.shortName}</button></li>)}</ol>
                    <button type="button" className="btn-tour" onClick={() => p.onTour(route)}><Icon name={p.touring ? 'close' : 'play'} /> {p.touring ? 'Detener recorrido' : 'Recorrer la ruta en el mapa'}</button>
                    <Link className="btn-yellow" href={u(route.url)}>Ver la ruta completa <Icon name="chevron-right" /></Link>
                </div>
            </article>
        );
    }

    if (level === 'routes') {
        return (
            <article className="spot-card" style={{ '--c': '#ff3f8e' }}>
                <div className="spot-body">
                    <button type="button" className="spot-back" onClick={p.onRoutesOff}><Icon name="arrow-left" /> Ocultar rutas</button>
                    <span className="spot-chip is-static"><Icon name="route" /> Rutas sugeridas</span>
                    <h2 className="panel-h">Elige una ruta</h2>
                    <p>Las líneas animadas unen las paradas de cada ruta. Toca una para verla y recorrerla.</p>
                    <div className="route-picks">
                        {routes.map((r) => (
                            <button key={r.id} type="button" onClick={() => p.onRoute(r.id)}>
                                {r.image && <img src={r.image} alt="" />}
                                <span><strong>{r.title}</strong><small>{r.stopsCount} paradas{r.duration ? ' · ' + r.duration : ''}</small></span>
                            </button>
                        ))}
                    </div>
                </div>
            </article>
        );
    }

    // País
    const total = markers.length;
    const deps = Object.keys(counts).length;
    return (
        <article className="spot-card spot-country" style={{ '--c': '#12b5b0' }}>
            <div className="spot-body">
                <span className="spot-chip is-static"><Icon name="compass" /> Colombia · modo exploración</span>
                <h2 className="panel-h">¿A dónde te lleva la curiosidad?</h2>
                <p>Toca un departamento para acercarte, elige un punto para ver sus fotos, videos y sonidos, o activa las rutas.</p>
                <div className="panel-stats"><span><b>{total}</b> lugares</span><span><b>{deps}</b> departamentos con contenido</span><span><b>1.122</b> municipios</span></div>
                <div className="region-chips">
                    <strong>Viaja por región</strong>
                    <div>{regions.map((r) => <button key={r.slug} type="button" onClick={() => p.onRegion(r.slug)} style={{ '--c': r.color }}><i />{r.name}</button>)}</div>
                </div>
                <button type="button" className="btn-yellow" onClick={p.onSurprise}><Icon name="sparkle" /> Sorpréndeme con un lugar</button>
                <p className="hand-note">{p.note} <Icon name="heart" /></p>
            </div>
        </article>
    );
}
