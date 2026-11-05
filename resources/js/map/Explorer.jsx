import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import L from 'leaflet';
import Icon from '../components/Icon';
import { getJSONCached, norm } from '../lib/url';
import { addMapNotes, applyRelief, ATTRIBUTION, BOUNDS_MAINLAND, esc, geoUrl, pinIcon, ROUTE_COLORS, SAN_ANDRES } from './kit';
import ExplorePanel from './ExplorePanel';

const baseStyle = () => ({ color: 'rgba(255,255,255,0.75)', weight: 1, fillColor: 'url(#vpc-relief)', fillOpacity: 1, opacity: 1 });
const hoverStyle = { weight: 2.5, color: '#ffffff', fillOpacity: 0.86 };
const selectedStyle = { weight: 3, color: '#ffd447', fillOpacity: 1 };
const DETAIL_ZOOM = 6.4; // por debajo: vista del país con puntos discretos; por encima: pines con ícono
const MAX_BUBBLES = 6;   // fotos visibles en la vista del país

/**
 * Explorador de la portada: una sola pantalla con el mapa como protagonista.
 * Todo ocurre dentro del mapa y del panel lateral (departamento → municipio → lugar → fotos, video y audio)
 * sin cambiar de página ni desplazarla.
 */
export default function Explorer({ markers, counts, chips, routes, initial, texts, tiles, newest, news }) {
    const el = useRef(null);
    const mapRef = useRef(null);
    const L$ = useRef({}); // capas de Leaflet: deps, muns, pins, routes, selectedDep, selectedMun
    const pinsRef = useRef(new Map());

    const [ready, setReady] = useState(false);
    const [zoom, setZoom] = useState(5);
    const [dep, setDep] = useState(null); // { slug, name, info }
    const [mun, setMun] = useState(null); // { slug, name }
    const [placeId, setPlaceId] = useState(null);
    const [chip, setChip] = useState(initial.categoria || '');
    const [routesOn, setRoutesOn] = useState(false);
    const [routeId, setRouteId] = useState(null);
    const [touring, setTouring] = useState(false);
    const [query, setQuery] = useState('');
    const [sheet, setSheet] = useState(false); // panel abierto en móvil
    const tourTimer = useRef(null);

    const byId = useMemo(() => new Map(markers.map((m) => [m.id, m])), [markers]);
    const bubbles = useMemo(() => new Set(markers.filter((m) => m.featured && m.thumb && m.section === 'tourism').slice(0, MAX_BUBBLES).map((m) => m.id)), [markers]);
    const place = placeId ? byId.get(placeId) : null;
    const route = routeId ? routes.find((r) => r.id === routeId) : null;
    const visible = useCallback((m) => !chip || m.categories.some((c) => c.slug === chip), [chip]);
    const depSlug = dep?.slug;
    // Vista del país: puntos y pocas fotos. Al acercarse: pines con ícono, salvo los de otros departamentos.
    const modeFor = useCallback((m, z) => {
        if (z < DETAIL_ZOOM) return bubbles.has(m.id) ? 'bubble' : 'dot';
        return depSlug && m.departmentSlug !== depSlug ? 'dot' : 'pin';
    }, [bubbles, depSlug]);

    // Márgenes del encuadre: el país queda entre la presentación (izquierda) y el panel (derecha).
    const pad = () => {
        const w = window.innerWidth;
        if (w <= 900) return { paddingTopLeft: [12, 150], paddingBottomRight: [12, 110] };
        const side = w > 1280 ? 340 : 280;
        return { paddingTopLeft: [side, 24], paddingBottomRight: [side + 30, 64] };
    };
    const fitCountry = (animate = true) => {
        const map = mapRef.current;
        if (!map) return;
        animate ? map.flyToBounds(BOUNDS_MAINLAND, { ...pad(), duration: 0.9 }) : map.fitBounds(BOUNDS_MAINLAND, pad());
    };

    // ---------- Inicialización del mapa (una sola vez) ----------
    useEffect(() => {
        const map = L.map(el.current, {
            zoomControl: false, attributionControl: true, scrollWheelZoom: true, zoomSnap: 0.1, minZoom: 4.4, maxZoom: 11,
            maxBounds: [[-12, -92], [20, -58]], maxBoundsViscosity: 0.8, keyboard: true,
        });
        mapRef.current = map;
        map.attributionControl.setPrefix(false);
        map.attributionControl.addAttribution(ATTRIBUTION);
        L.control.zoom({ position: 'bottomright', zoomInTitle: 'Acercar', zoomOutTitle: 'Alejar' }).addTo(map);
        if (tiles?.url) L.tileLayer(tiles.url, { attribution: tiles.attribution || '', maxZoom: 18, opacity: 0.5 }).addTo(map);
        map.fitBounds(BOUNDS_MAINLAND, pad());
        setZoom(map.getZoom());
        map.on('zoomend', () => setZoom(map.getZoom()));
        map.on('focus', () => { if (window.scrollY) window.scrollTo(0, 0); });

        let cancelled = false;
        getJSONCached(geoUrl('departamentos.geojson')).then((deps) => {
            if (cancelled) return;
            const layer = L.geoJSON(deps, {
                style: baseStyle,
                onEachFeature: (f, lyr) => {
                    const n = counts[f.properties.slug] || 0;
                    lyr.bindTooltip(`${esc(f.properties.name)}<small>${n ? n + (n === 1 ? ' lugar' : ' lugares') : 'Por descubrir'}</small>`, { className: 'dep-tooltip', sticky: true, direction: 'top' });
                    lyr.on({
                        mouseover: () => { if (lyr !== L$.current.selectedDep) lyr.setStyle(hoverStyle); },
                        mouseout: () => { if (lyr !== L$.current.selectedDep) lyr.setStyle(baseStyle()); },
                        click: () => handlers.current.selectDep(f.properties.slug),
                    });
                },
            }).addTo(map);
            L$.current.deps = layer;
            applyRelief(map);
            addMapNotes(map);
            L$.current.pins = L.layerGroup().addTo(map);
            markers.forEach((m) => {
                const mk = L.marker([m.lat, m.lng], { icon: pinIcon(m, false, modeFor(m, map.getZoom())), title: m.shortName, riseOnHover: true, zIndexOffset: bubbles.has(m.id) ? 500 : 0, keyboard: true });
                mk.bindTooltip(`${esc(m.shortName)}<small>${esc(m.pointType || m.catName)}</small>`, { className: 'dep-tooltip', direction: 'top', offset: [0, -14] });
                mk.on('click', () => handlers.current.selectPlace(m.id, { fly: map.getZoom() < DETAIL_ZOOM }));
                pinsRef.current.set(m.id, mk);
            });
            setReady(true);
        });
        const resize = () => map.invalidateSize();
        window.addEventListener('resize', resize);
        return () => { cancelled = true; window.removeEventListener('resize', resize); map.remove(); };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Pines visibles según el filtro
    useEffect(() => {
        if (!ready) return;
        const pins = L$.current.pins;
        pins.clearLayers();
        markers.forEach((m) => { if (visible(m)) pins.addLayer(pinsRef.current.get(m.id)); });
    }, [ready, visible, markers]);

    // Íconos según el acercamiento y el lugar seleccionado
    useEffect(() => {
        if (!ready) return;
        markers.forEach((m) => pinsRef.current.get(m.id)?.setIcon(pinIcon(m, m.id === placeId, m.id === placeId && zoom < DETAIL_ZOOM && !bubbles.has(m.id) ? 'pin' : modeFor(m, zoom))));
    }, [ready, zoom, placeId, markers, modeFor, bubbles]);

    // ---------- Acciones ----------
    const clearMunLayer = () => { if (L$.current.muns) { mapRef.current.removeLayer(L$.current.muns); L$.current.muns = null; } };
    const clearDepSelection = () => { if (L$.current.selectedDep) L$.current.selectedDep.setStyle(baseStyle()); L$.current.selectedDep = null; };

    const selectDep = async (slug, { munSlug = null, fly = true } = {}) => {
        const map = mapRef.current;
        const layer = L$.current.deps?.getLayers().find((l) => l.feature.properties.slug === slug);
        if (!layer) return;
        stopTour();
        clearDepSelection();
        L$.current.selectedDep = layer;
        layer.setStyle(selectedStyle);
        setDep({ slug, name: layer.feature.properties.name, info: null });
        setMun(null);
        setPlaceId(null);
        setSheet(true);
        if (fly && !munSlug) map.flyToBounds(layer.getBounds(), { ...pad(), duration: 0.9, maxZoom: 7 });
        clearMunLayer();
        try {
            const [info, geo] = await Promise.all([getJSONCached(`/api/departamentos/${slug}`), getJSONCached(geoUrl(`municipios/${slug}.geojson`))]);
            if (L$.current.selectedDep !== layer) return;
            setDep({ slug, name: info.name, info });
            const mc = info.municipalityCounts || {};
            const muns = L.geoJSON(geo, {
                style: (f) => ({ color: 'rgba(255,255,255,0.85)', weight: 0.7, fillColor: '#ffd447', fillOpacity: mc[f.properties.slug] ? 0.25 : 0 }),
                onEachFeature: (f, ml) => {
                    const n = mc[f.properties.slug] || 0;
                    ml.bindTooltip(`${esc(f.properties.name)}${n ? `<small>${n} ${n === 1 ? 'lugar' : 'lugares'}</small>` : ''}`, { className: 'dep-tooltip', sticky: true });
                    ml.on('click', (ev) => { L.DomEvent.stopPropagation(ev); handlers.current.selectMun(f.properties.slug); });
                    ml.on('mouseover', () => { if (ml !== L$.current.selectedMun) ml.setStyle({ fillOpacity: 0.45 }); });
                    ml.on('mouseout', () => { if (ml !== L$.current.selectedMun) muns.resetStyle(ml); });
                },
            }).addTo(map);
            L$.current.muns = muns;
            if (munSlug) handlers.current.selectMun(munSlug);
        } catch { /* sin información adicional */ }
    };

    const selectMun = (slug) => {
        const muns = L$.current.muns;
        const layer = muns?.getLayers().find((l) => l.feature.properties.slug === slug);
        if (!layer) return;
        muns.eachLayer((x) => muns.resetStyle(x));
        L$.current.selectedMun = layer;
        layer.setStyle({ weight: 3, color: '#ff3f8e', fillOpacity: 0.12 });
        mapRef.current.flyToBounds(layer.getBounds(), { ...pad(), duration: 0.8, maxZoom: 8.6 });
        setMun({ slug, name: layer.feature.properties.name });
        setPlaceId(null);
        setSheet(true);
    };

    const selectPlace = (id, { fly = true, keepTour = false } = {}) => {
        const m = byId.get(id);
        if (!m) return;
        if (!keepTour) stopTour();
        setPlaceId(id);
        setSheet(true);
        if (fly) mapRef.current.flyTo([m.lat, m.lng], Math.max(mapRef.current.getZoom(), 6.8), { duration: 0.9 });
    };

    const backToCountry = () => {
        stopTour();
        clearDepSelection();
        clearMunLayer();
        if (L$.current.routes && routesOn) { mapRef.current.removeLayer(L$.current.routes); setRoutesOn(false); }
        setDep(null); setMun(null); setPlaceId(null); setRouteId(null);
        fitCountry();
    };

    /** Sube un nivel: lugar → municipio → departamento → país. */
    const back = () => {
        if (placeId) { setPlaceId(null); return; }
        if (routeId) { setRouteId(null); stopTour(); return; }
        if (mun) {
            setMun(null);
            L$.current.selectedMun = null;
            L$.current.muns?.eachLayer((x) => L$.current.muns.resetStyle(x));
            if (L$.current.selectedDep) mapRef.current.flyToBounds(L$.current.selectedDep.getBounds(), { ...pad(), duration: 0.8, maxZoom: 7 });
            return;
        }
        if (dep || routesOn) backToCountry();
    };

    // Rutas animadas
    const drawRoutes = () => {
        if (L$.current.routes) return L$.current.routes;
        const group = L.featureGroup();
        routes.forEach((r, i) => {
            const c = ROUTE_COLORS[i % ROUTE_COLORS.length];
            const pts = r.stops.filter((s) => s.lat !== null).map((s) => [s.lat, s.lng]);
            L.polyline(pts, { color: '#05202c', weight: 7, opacity: 0.3, interactive: false }).addTo(group);
            const line = L.polyline(pts, { color: c, weight: 4, opacity: 0.95, className: 'route-anim' }).addTo(group);
            line.bindTooltip(esc(r.title), { className: 'dep-tooltip', sticky: true });
            line.on('click', () => handlers.current.selectRoute(r.id));
        });
        L$.current.routes = group;
        return group;
    };
    const toggleRoutes = (on = !routesOn) => {
        const map = mapRef.current;
        const group = drawRoutes();
        setRoutesOn(on);
        if (on) {
            group.addTo(map);
            if (group.getLayers().length) map.flyToBounds(group.getBounds(), { ...pad(), duration: 0.9 });
            setPlaceId(null);
            setSheet(true);
        } else {
            map.removeLayer(group);
            setRouteId(null);
            stopTour();
        }
    };
    const selectRoute = (id) => {
        const r = routes.find((x) => x.id === id);
        if (!r) return;
        if (!routesOn) toggleRoutes(true);
        stopTour();
        setRouteId(id);
        setPlaceId(null);
        setSheet(true);
        const pts = r.stops.filter((s) => s.lat !== null).map((s) => [s.lat, s.lng]);
        if (pts.length) mapRef.current.flyToBounds(L.latLngBounds(pts), { ...pad(), duration: 0.9, maxZoom: 9 });
    };

    /** Recorrido guiado: el mapa vuela de parada en parada mostrando cada lugar. */
    function stopTour() {
        clearTimeout(tourTimer.current);
        tourTimer.current = null;
        setTouring(false);
    }
    const startTour = (r) => {
        const stops = r.stops.filter((s) => s.lat !== null);
        if (!stops.length) return;
        setTouring(true);
        let i = 0;
        const step = () => {
            const s = stops[i];
            mapRef.current.flyTo([s.lat, s.lng], 7, { duration: 1.6 });
            if (byId.has(s.id)) setPlaceId(s.id);
            i += 1;
            tourTimer.current = setTimeout(i < stops.length ? step : () => { setTouring(false); setPlaceId(null); }, 4200);
        };
        step();
    };

    const pickChip = (slug) => {
        const next = chip === slug ? '' : slug;
        setChip(next);
        if (next) {
            const pts = markers.filter((m) => m.categories.some((c) => c.slug === next)).map((m) => [m.lat, m.lng]);
            if (pts.length) mapRef.current.flyToBounds(L.latLngBounds(pts), { ...pad(), duration: 0.9, maxZoom: 7 });
        }
    };

    const goPlace = (m) => {
        if (m.departmentSlug && m.departmentSlug !== dep?.slug) selectDep(m.departmentSlug, { fly: false });
        setTimeout(() => selectPlace(m.id), 60);
    };

    const handlers = useRef({});
    handlers.current = { selectDep, selectMun, selectPlace, selectRoute };

    // ---------- Estado inicial desde la URL (enlaces del buscador y del pie de página) ----------
    useEffect(() => {
        if (!ready) return;
        if (initial.region === 'insular') mapRef.current.flyTo(SAN_ANDRES, 9, { duration: 1 });
        if (initial.departamento) selectDep(initial.departamento, { munSlug: initial.municipio || null });
        if (initial.lugar && byId.has(initial.lugar)) setTimeout(() => selectPlace(initial.lugar), 300);
        if (initial.ruta) { const r = routes.find((x) => x.slug === initial.ruta); if (r) selectRoute(r.id); }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [ready]);

    // URL compartible sin recargar ni mover la página.
    useEffect(() => {
        if (!ready) return;
        const p = new URLSearchParams();
        if (dep) p.set('departamento', dep.slug);
        if (mun) p.set('municipio', mun.slug);
        if (placeId) p.set('lugar', placeId);
        if (route) p.set('ruta', route.slug);
        if (chip) p.set('categoria', chip);
        const next = window.location.pathname + (p.toString() ? '?' + p : '');
        if (next !== window.location.pathname + window.location.search) window.history.replaceState(window.history.state, '', next);
    }, [ready, dep, mun, placeId, route, chip]);

    // Esc sube un nivel
    useEffect(() => {
        const key = (e) => { if (e.key === 'Escape' && !document.querySelector('.lightbox.is-open, .search-overlay.is-open')) back(); };
        window.addEventListener('keydown', key);
        return () => window.removeEventListener('keydown', key);
    });
    useEffect(() => () => clearTimeout(tourTimer.current), []);

    // ---------- Buscador dentro del mapa ----------
    const results = useMemo(() => {
        const q = norm(query.trim());
        if (q.length < 2) return [];
        const terms = q.split(/\s+/);
        return markers.filter((m) => {
            const hay = norm([m.title, m.shortName, m.municipality, m.department, m.pointType, m.categories.map((c) => c.name).join(' ')].join(' '));
            return terms.every((t) => hay.includes(t));
        }).slice(0, 6);
    }, [query, markers]);
    const [geoResults, setGeoResults] = useState([]);
    useEffect(() => {
        const q = query.trim();
        if (q.length < 2) { setGeoResults([]); return undefined; }
        const t = setTimeout(() => getJSONCached('/api/buscar?q=' + encodeURIComponent(q)).then((r) => setGeoResults(r.results.filter((x) => x.type !== 'entry').slice(0, 4))).catch(() => {}), 200);
        return () => clearTimeout(t);
    }, [query]);
    const pickGeo = (g) => {
        setQuery('');
        const params = new URLSearchParams(g.url.split('?')[1] || '');
        selectDep(params.get('departamento'), { munSlug: params.get('municipio') });
    };

    const level = place ? 'place' : route ? 'route' : routesOn ? 'routes' : mun ? 'municipality' : dep ? 'department' : 'country';
    const depPlaces = dep ? markers.filter((m) => m.departmentSlug === dep.slug && visible(m)) : [];
    const munPlaces = mun ? depPlaces.filter((m) => m.municipalitySlug === mun.slug) : [];
    const [first, ...rest] = texts.title.split(' ');

    return (
        <section className={`explore ${level !== 'country' ? 'is-zoomed' : ''}`} aria-label="Explora Colombia en el mapa">
            <div ref={el} className="explore-map" />

            <div className="explore-intro">
                <p className="explore-kicker">{texts.kicker}</p>
                <h1 className="explore-title"><span>{first}</span> <span className="t2">{rest.join(' ')}</span></h1>
                <p className="explore-sub">{texts.subtitle}</p>
                <div className="map-search" role="search">
                    <Icon name="search" />
                    <label className="sr-only" htmlFor="map-q">Buscar en el mapa</label>
                    <input id="map-q" type="search" autoComplete="off" placeholder="Busca un lugar o municipio" value={query} onChange={(e) => setQuery(e.target.value)} />
                    {(results.length > 0 || geoResults.length > 0) && (
                        <div className="map-search-results">
                            {results.map((m) => (
                                <button key={m.id} type="button" onClick={() => { setQuery(''); goPlace(m); }}>
                                    {m.thumb ? <img src={m.thumb} alt="" /> : <span className="ico"><Icon name="pin" /></span>}
                                    <span><strong>{m.shortName}</strong><small>{m.pointType || m.catName} · {m.department}</small></span>
                                </button>
                            ))}
                            {geoResults.map((g, i) => (
                                <button key={'g' + i} type="button" onClick={() => pickGeo(g)}>
                                    <span className="ico"><Icon name={g.icon} /></span>
                                    <span><strong>{g.title}</strong><small>{g.subtitle}</small></span>
                                </button>
                            ))}
                        </div>
                    )}
                </div>
            </div>

            {(dep || routesOn) && (
                <nav className="map-crumbs" aria-label="Nivel del mapa">
                    <button type="button" onClick={backToCountry}><Icon name="arrow-left" /> Colombia</button>
                    {dep && <><Icon name="chevron-right" /><button type="button" onClick={() => { setPlaceId(null); if (mun) back(); }} aria-current={!mun ? 'location' : undefined}>{dep.name}</button></>}
                    {mun && <><Icon name="chevron-right" /><span aria-current="location">{mun.name}</span></>}
                    {routesOn && !dep && <><Icon name="chevron-right" /><span aria-current="location">Rutas</span></>}
                </nav>
            )}

            <div className="explore-chips" role="group" aria-label="Filtrar por tipo de experiencia">
                <button type="button" className={!chip ? 'is-active' : ''} aria-pressed={!chip} onClick={() => setChip('')}>Todo</button>
                {chips.map((c) => (
                    <button key={c.slug} type="button" className={chip === c.slug ? 'is-active' : ''} aria-pressed={chip === c.slug} onClick={() => pickChip(c.slug)} style={{ '--c': c.color || '#fff' }}>
                        <i />{c.name === 'Experiencias comunitarias' ? 'Comunitario' : c.name}
                    </button>
                ))}
                {routes.length > 0 && <button type="button" className={`is-routes ${routesOn ? 'is-active' : ''}`} aria-pressed={routesOn} onClick={() => toggleRoutes()}><Icon name="route" /> Rutas</button>}
            </div>

            <aside className={`explore-panel ${sheet ? 'is-open' : ''}`} aria-label="Novedades y lugar seleccionado" aria-live="polite">
                <button type="button" className="sheet-handle" onClick={() => setSheet(!sheet)} aria-label={sheet ? 'Ocultar panel' : 'Mostrar panel'}><span /></button>
                <ExplorePanel
                    level={level} place={place} dep={dep} mun={mun} route={route} routes={routes} touring={touring}
                    depPlaces={depPlaces} munPlaces={munPlaces} markers={markers} newest={newest} news={news}
                    onBack={back} onCountry={backToCountry} onPlace={(id) => selectPlace(id)} onMun={selectMun} onRoute={selectRoute}
                    onTour={(r) => (touring ? stopTour() : startTour(r))} onRoutesOff={() => toggleRoutes(false)}
                    onHover={(id, on) => pinsRef.current.get(id)?.getElement()?.classList.toggle('is-pulse', on)}
                    onGoPlace={goPlace}
                />
            </aside>
        </section>
    );
}
