import L from 'leaflet';
import { u } from '../lib/url';

/** Utilidades compartidas de los mapas (Leaflet + GeoJSON de geoBoundaries / OpenStreetMap). */
export const BOUNDS_MAINLAND = [[-4.3, -79.1], [12.55, -66.8]];
export const SAN_ANDRES = [12.95, -81.55];
export const RELIEF_BOUNDS = [[12.7, -79.2], [-4.4, -66.7]]; // Debe coincidir con tools/geo/build_relief.py
export const ROUTE_COLORS = ['#ff3f8e', '#ffd447', '#12b5b0', '#ff7a1a', '#7b3fe4'];
export const ATTRIBUTION = 'Límites: <a href="https://www.geoboundaries.org" target="_blank" rel="noopener">geoBoundaries</a> · © OpenStreetMap';

export const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
export const iconHtml = (name, cls = 'icon') => `<svg class="${cls}" aria-hidden="true"><use href="${u('/assets/img/icons.svg')}#${name}"></use></svg>`;
export const geoUrl = (path) => '/data/geo/' + path;

/** Textura de relieve ilustrada como patrón SVG anclado a los límites geográficos. */
export function applyRelief(map) {
    const svg = map.getContainer().querySelector('.leaflet-overlay-pane svg');
    if (!svg || svg.querySelector('#vpc-relief')) return;
    const ns = 'http://www.w3.org/2000/svg';
    const defs = document.createElementNS(ns, 'defs');
    const pattern = document.createElementNS(ns, 'pattern');
    pattern.setAttribute('id', 'vpc-relief');
    pattern.setAttribute('patternUnits', 'userSpaceOnUse');
    const image = document.createElementNS(ns, 'image');
    image.setAttribute('href', u('/assets/img/relief/colombia-relief.webp'));
    image.setAttribute('preserveAspectRatio', 'none');
    pattern.appendChild(image);
    defs.appendChild(pattern);
    svg.insertBefore(defs, svg.firstChild);
    const place = () => {
        const nw = map.latLngToLayerPoint(RELIEF_BOUNDS[0]);
        const se = map.latLngToLayerPoint(RELIEF_BOUNDS[1]);
        for (const [k, v] of [['x', nw.x], ['y', nw.y], ['width', se.x - nw.x], ['height', se.y - nw.y]]) pattern.setAttribute(k, v);
        image.setAttribute('width', se.x - nw.x);
        image.setAttribute('height', se.y - nw.y);
    };
    place();
    map.on('zoomend viewreset', place);
}

/** Rótulos manuscritos de los mares (los países vecinos se omiten para no recargar el mapa). */
export function addMapNotes(map, { seasOnly = true } = {}) {
    const arrow = (d) => `<svg viewBox="0 0 34 20"><path d="${d}"/></svg>`;
    const group = L.layerGroup();
    [
        [[10.6, -77.7], 'Mar Caribe', 'sea', arrow('M4 10 q6 -6 12 0 t12 0')],
        [[5.4, -78.5], 'Océano<br>Pacífico', 'sea', arrow('M4 10 q6 -6 12 0 t12 0')],
        [[8.8, -68.3], 'Venezuela', '', arrow('M30 4 C 22 6, 12 10, 6 16 M6 16 l1 -7 M6 16 l7 -1')],
        [[-0.2, -69.0], 'Brasil', '', arrow('M24 2 C 22 8, 18 12, 12 17 M12 17 l1 -7 M12 17 l7 -2')],
        [[-0.9, -78.0], 'Ecuador', '', arrow('M28 4 C 20 8, 14 12, 6 16 M6 16 l2 -7 M6 16 l7 0')],
        [[-3.3, -73.6], 'Perú', '', arrow('M4 4 C 12 8, 20 12, 28 16 M28 16 l-7 0 M28 16 l-2 -7')],
    ].filter(([, , cls]) => !seasOnly || cls === 'sea').forEach(([ll, text, cls, svgArrow]) => {
        L.marker(ll, { interactive: false, keyboard: false, icon: L.divIcon({ className: '', iconSize: [120, 50], iconAnchor: [60, 25], html: `<div class="map-note ${cls}">${text}${svgArrow}</div>` }) }).addTo(group);
    });
    return group.addTo(map);
}

/**
 * Marcador según el nivel de acercamiento:
 * - 'bubble': burbuja con foto (pocos destinos destacados, para no recargar la vista del país).
 * - 'dot': punto de color discreto (vista del país).
 * - 'pin': pin con ícono según el tipo de lugar (al acercarse a un departamento).
 */
export function pinIcon(m, selected = false, mode = 'pin') {
    if (mode === 'bubble') {
        return L.divIcon({ className: '', iconSize: [48, 56], iconAnchor: [24, 56], html: `<div class="photo-bubble ${selected ? 'is-selected' : ''}"><img src="${esc(m.thumb)}" alt=""></div>` });
    }
    if (mode === 'dot' && !selected) {
        const c = m.catColor || m.color;
        return L.divIcon({ className: '', iconSize: [16, 16], iconAnchor: [8, 8], html: `<div class="map-dot" style="--pc:${esc(c)};--d:${(m.id % 7) * 0.35}s"></div>` });
    }
    const c = m.catColor || m.color;
    return L.divIcon({
        className: '', iconSize: [34, 44], iconAnchor: [17, 44],
        html: `<div class="pin-marker ${selected ? 'is-selected' : ''}" style="--pc:${esc(c)};--d:${(m.id % 7) * 0.35}s"><svg class="pin-bg" viewBox="0 0 34 44"><path d="M17 43 C 17 43, 2 27, 2 16 A 15 15 0 0 1 32 16 C 32 27, 17 43, 17 43 Z" fill="${esc(c)}" stroke="#fff" stroke-width="2"/></svg>${iconHtml(m.pointIcon || m.icon || 'pin')}</div>`,
    });
}

export function photoIcon(thumb, color = '#ff7a1a') {
    const html = thumb
        ? `<div class="photo-marker" style="--c:${esc(color)}"><img src="${esc(thumb)}" alt="" loading="lazy"></div>`
        : `<div class="photo-marker is-dot" style="background:${esc(color)}"></div>`;
    return L.divIcon({ html, className: '', iconSize: thumb ? [46, 46] : [22, 22], iconAnchor: thumb ? [23, 46] : [11, 11] });
}

export function numberIcon(n) {
    return L.divIcon({ className: '', iconSize: [34, 34], iconAnchor: [17, 17], html: `<div class="marker-cluster-custom" style="width:34px;height:34px;background:linear-gradient(135deg,#ff7a1a,#ff3f8e)">${n}</div>` });
}

/** Mapa base sencillo (fichas, rutas y panel): sin zoom con la rueda para no mover la página al desplazarse. */
export function baseMap(el, { tiles, scrollWheelZoom = false } = {}) {
    const map = L.map(el, { zoomControl: false, scrollWheelZoom, zoomSnap: 0.25, minZoom: 4, maxZoom: 17, maxBounds: [[-12, -90], [20, -58]], maxBoundsViscosity: 0.7 });
    L.control.zoom({ position: 'bottomright', zoomInTitle: 'Acercar', zoomOutTitle: 'Alejar' }).addTo(map);
    map.attributionControl.setPrefix(false);
    map.attributionControl.addAttribution(ATTRIBUTION);
    if (tiles?.url) L.tileLayer(tiles.url, { attribution: tiles.attribution || '', maxZoom: 18, opacity: 0.55 }).addTo(map);
    map.fitBounds(BOUNDS_MAINLAND, { padding: [10, 10] });
    return map;
}
