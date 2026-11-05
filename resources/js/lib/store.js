import { useEffect, useState, useCallback } from 'react';

/** Favoritos y «mi ruta» se guardan solo en el navegador del visitante (no requieren cuenta). */
function read(key) {
    try {
        const v = JSON.parse(localStorage.getItem(key) || '[]');
        return Array.isArray(v) ? v.filter((x) => Number.isInteger(x)) : [];
    } catch {
        return [];
    }
}

function write(key, list) {
    try { localStorage.setItem(key, JSON.stringify(list)); } catch { /* sin almacenamiento */ }
    window.dispatchEvent(new CustomEvent('vpc:store', { detail: key }));
}

function useList(key) {
    const [list, setList] = useState(() => read(key));
    useEffect(() => {
        const sync = (e) => { if (!e.detail || e.detail === key) setList(read(key)); };
        const storage = (e) => { if (e.key === key) setList(read(key)); };
        window.addEventListener('vpc:store', sync);
        window.addEventListener('storage', storage);
        return () => { window.removeEventListener('vpc:store', sync); window.removeEventListener('storage', storage); };
    }, [key]);
    const toggle = useCallback((id) => {
        const cur = read(key);
        write(key, cur.includes(id) ? cur.filter((x) => x !== id) : [...cur, id].slice(-60));
    }, [key]);
    const set = useCallback((next) => write(key, next), [key]);
    return [list, toggle, set];
}

export const useFavorites = () => useList('vpc:favoritos');
export const useMyRoute = () => useList('vpc:ruta');

export function useTheme() {
    const [theme, setTheme] = useState(() => document.documentElement.getAttribute('data-theme') || 'light');
    const change = (t) => {
        document.documentElement.setAttribute('data-theme', t);
        try { localStorage.setItem('vpc:tema', JSON.stringify(t)); } catch { /* sin almacenamiento */ }
        setTheme(t);
    };
    return [theme, change];
}
