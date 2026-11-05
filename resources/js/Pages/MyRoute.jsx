import { Head, Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Icon from '../components/Icon';
import { Empty, PageHero } from '../components/Content';
import { toast } from '../components/Toasts';
import { RouteMap } from '../map/SmallMaps';
import { useMyRoute } from '../lib/store';
import { getJSON, place, u } from '../lib/url';

const km = (a, b) => {
    const R = 6371, dLat = ((b.lat - a.lat) * Math.PI) / 180, dLng = ((b.lng - a.lng) * Math.PI) / 180;
    const x = Math.sin(dLat / 2) ** 2 + Math.cos((a.lat * Math.PI) / 180) * Math.cos((b.lat * Math.PI) / 180) * Math.sin(dLng / 2) ** 2;
    return 2 * R * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x));
};

/** Planificador personal: se guarda en el navegador y se comparte con un enlace (?r=1,2,3). */
export default function MyRoute() {
    const { site } = usePage().props;
    const [ids, toggle, setIds] = useMyRoute();
    const [items, setItems] = useState(null);
    useEffect(() => {
        const shared = new URLSearchParams(window.location.search).get('r');
        if (shared) {
            const list = shared.split(',').map(Number).filter(Number.isInteger).slice(0, 30);
            if (list.length) { setIds(list); toast('Ruta compartida cargada'); }
            window.history.replaceState(window.history.state, '', window.location.pathname);
        }
    }, [setIds]);
    useEffect(() => {
        if (!ids.length) { setItems([]); return; }
        getJSON('/api/publicaciones?ids=' + ids.join(',')).then((r) => setItems(r.items)).catch(() => setItems([]));
    }, [ids]);
    const move = (i, d) => { const next = [...ids]; const j = i + d; if (j < 0 || j >= next.length) return; [next[i], next[j]] = [next[j], next[i]]; setIds(next); };
    const pts = (items || []).filter((m) => m.lat !== null);
    const dist = pts.slice(1).reduce((s, m, i) => s + km(pts[i], m), 0);
    const share = async () => {
        const url = window.location.origin + u('/mi-ruta') + '?r=' + ids.join(',');
        try { await navigator.clipboard.writeText(url); toast('Enlace de tu ruta copiado'); } catch { window.prompt('Copia este enlace:', url); }
    };
    return (
        <>
            <Head title="Mi ruta" />
            <PageHero color="var(--turquesa)" icon="route" eyebrow="Planificador" title="Arma" accent="tu ruta" compact
                lead="Agrega destinos desde el mapa o desde cada ficha, ordénalos a tu gusto y comparte el recorrido con un enlace." />
            <section className="section" style={{ paddingTop: 20 }}>
                <div className="container">
                    <div className="my-route">
                        <div>
                            {items && items.length > 0 && <div className="route-summary"><span><b>{items.length}</b> paradas</span>{pts.length > 1 && <span><b>{Math.round(dist)}</b> km en línea recta</span>}</div>}
                            <div className="stop-list">
                                {(items || []).map((m, i) => (
                                    <div key={m.id} className="stop">
                                        {m.thumb && <img src={m.thumb} alt="" />}
                                        <span><strong>{i + 1}. <Link href={u(m.url)}>{m.title}</Link></strong><small>{place(m)}</small></span>
                                        <span className="stop-actions">
                                            <button className="btn btn-sm btn-outline btn-icon" type="button" onClick={() => move(i, -1)} aria-label="Subir"><Icon name="chevron-left" style={{ transform: 'rotate(90deg)' }} /></button>
                                            <button className="btn btn-sm btn-outline btn-icon" type="button" onClick={() => move(i, 1)} aria-label="Bajar"><Icon name="chevron-down" /></button>
                                            <button className="btn btn-sm btn-outline btn-icon" type="button" onClick={() => toggle(m.id)} aria-label="Quitar"><Icon name="trash" /></button>
                                        </span>
                                    </div>
                                ))}
                            </div>
                            {items && !items.length && <Empty icon="route" title="Tu ruta está vacía" text="Abre el mapa y toca «+» en los destinos que quieras visitar." />}
                            <div className="flex gap-2 wrap mt-4">
                                <Link className="btn btn-primary" href={u('/')}><Icon name="plus" /> Agregar destinos</Link>
                                {ids.length > 0 && <button className="btn btn-outline" type="button" onClick={share}><Icon name="share" /> Compartir ruta</button>}
                                {ids.length > 0 && <button className="btn btn-outline" type="button" onClick={() => setIds([])}><Icon name="trash" /> Vaciar</button>}
                            </div>
                            <p className="muted mt-2" style={{ fontSize: '.85rem' }}>Las distancias son en línea recta y solo orientativas. Consulta el estado de las vías antes de viajar.</p>
                        </div>
                        <RouteMap stops={pts} tiles={site.tiles} />
                    </div>
                </div>
            </section>
        </>
    );
}
