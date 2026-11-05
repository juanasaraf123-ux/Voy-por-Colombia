import { useEffect, useRef } from 'react';
import L from 'leaflet';
import { baseMap, esc, geoUrl, numberIcon, photoIcon } from './kit';
import { getJSONCached } from '../lib/url';

/** Mapa pequeño de la ficha: el departamento resaltado y el punto exacto. */
export function MiniMap({ lat, lng, dep, color = '#ff7a1a', thumb, tiles }) {
    const el = useRef(null);
    useEffect(() => {
        const map = baseMap(el.current, { tiles });
        let off = false;
        getJSONCached(geoUrl('departamentos.geojson')).then((deps) => {
            if (off) return;
            L.geoJSON(deps, { interactive: false, style: (f) => ({ color: '#fff', weight: 1, fillColor: f.properties.slug === dep ? color : '#2bb673', fillOpacity: f.properties.slug === dep ? 0.55 : 0.18 }) }).addTo(map);
            if (lat !== null && lat !== undefined) {
                L.marker([lat, lng], { icon: photoIcon(thumb, color) }).addTo(map);
                map.setView([lat, lng], 7);
            } else if (dep) {
                const layer = L.geoJSON(deps.features.filter((f) => f.properties.slug === dep));
                map.fitBounds(layer.getBounds(), { padding: [20, 20] });
            }
        });
        return () => { off = true; map.remove(); };
    }, [lat, lng, dep, color, thumb, tiles]);
    return <div ref={el} className="mini-map" aria-label="Mapa de ubicación" />;
}

/** Mapa de una ruta: paradas numeradas unidas por una línea (o solo puntos con fotos). */
export function RouteMap({ stops, mode = 'line', tiles, className = 'route-map', style }) {
    const el = useRef(null);
    const mapRef = useRef(null);
    const layerRef = useRef(null);
    useEffect(() => {
        const map = baseMap(el.current, { tiles, scrollWheelZoom: false });
        mapRef.current = map;
        layerRef.current = L.featureGroup().addTo(map);
        getJSONCached(geoUrl('departamentos.geojson')).then((deps) => {
            L.geoJSON(deps, { interactive: false, style: { color: '#fff', weight: 1, fillColor: '#2bb673', fillOpacity: 0.22 } }).addTo(map).bringToBack();
        });
        return () => map.remove();
    }, [tiles]);
    useEffect(() => {
        const layer = layerRef.current;
        const map = mapRef.current;
        if (!layer) return;
        layer.clearLayers();
        const pts = stops.filter((m) => m.lat !== null && m.lng !== null);
        if (mode === 'points') {
            pts.forEach((m) => L.marker([m.lat, m.lng], { icon: photoIcon(m.thumb, m.color || '#8a4b2a') }).bindTooltip(esc(m.title), { className: 'dep-tooltip' }).addTo(layer));
        } else {
            pts.forEach((m, i) => L.marker([m.lat, m.lng], { icon: numberIcon(i + 1) }).bindTooltip(esc(m.title), { className: 'dep-tooltip' }).addTo(layer));
            if (pts.length > 1) L.polyline(pts.map((m) => [m.lat, m.lng]), { color: '#ff3f8e', weight: 4, dashArray: '8 10', className: 'route-anim' }).addTo(layer);
        }
        if (pts.length) map.fitBounds(layer.getBounds(), { padding: [50, 50], maxZoom: 9 });
    }, [stops, mode]);
    return <div ref={el} className={className} style={style} />;
}
