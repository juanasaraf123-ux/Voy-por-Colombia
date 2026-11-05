import { usePage } from '@inertiajs/react';

/** Base del sitio (subcarpeta en XAMPP, por ejemplo «/voy-por-colombia»). La entrega el servidor. */
export function base() {
    return (document.querySelector('meta[name="vpc-base"]')?.content ?? '').replace(/\/$/, '');
}

/** Convierte una ruta del sitio («/turismo») en URL con la subcarpeta. Las URL absolutas no cambian. */
export function u(path = '/') {
    if (!path) return base() + '/';
    if (/^(https?:)?\/\//.test(path) || path.startsWith('#') || path.startsWith('mailto:')) return path;
    return base() + (path.startsWith('/') ? path : '/' + path);
}

export function useSite() {
    return usePage().props.site;
}

export async function getJSON(path, opts = {}) {
    const r = await fetch(u(path), { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, ...opts });
    if (!r.ok) throw new Error('HTTP ' + r.status);
    return r.json();
}

const cache = new Map();
/** Igual que getJSON pero recuerda la respuesta (GeoJSON y datos que no cambian durante la visita). */
export function getJSONCached(path) {
    if (!cache.has(path)) cache.set(path, getJSON(path).catch((e) => { cache.delete(path); throw e; }));
    return cache.get(path);
}

export const norm = (s) => String(s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

const MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
export function fecha(iso, withYear = true) {
    if (!iso) return '';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '';
    return `${d.getDate()} de ${MESES[d.getMonth()]}${withYear ? ' de ' + d.getFullYear() : ''}`;
}

export function place(m) {
    return [m.municipality && m.municipality !== m.department ? m.municipality : null, m.department].filter(Boolean).join(', ');
}
