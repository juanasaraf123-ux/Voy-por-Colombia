import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import L from 'leaflet';
import { Icon, u } from '../../../admin/ui';
import { BOUNDS_MAINLAND, esc } from '../../../map/kit';
import { getJSON } from '../../../lib/url';

/** Mapa de gestión: arrastra un marcador para corregir la ubicación de una publicación. */
export default function Index({ markers, missing, departments, counts }) {
    const el = useRef(null);
    useEffect(() => {
        const map = L.map(el.current, { minZoom: 4, maxZoom: 16 }).fitBounds(BOUNDS_MAINLAND);
        getJSON('/data/geo/departamentos.geojson').then((g) => L.geoJSON(g, { interactive: false, style: { color: '#0f5c4a', weight: 1, fillColor: '#2bb673', fillOpacity: 0.15 } }).addTo(map));
        markers.forEach((m) => {
            const mk = L.marker([m.lat, m.lng], { draggable: true, title: m.title, icon: L.divIcon({ className: '', iconSize: [18, 18], iconAnchor: [9, 9], html: `<div style="width:18px;height:18px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.4);background:${m.status === 'published' ? '#1f8a5b' : '#f2a900'}"></div>` }) }).addTo(map);
            mk.bindTooltip(esc(m.title));
            mk.on('dragend', () => { const p = mk.getLatLng(); router.post(u(`/admin/mapa/publicaciones/${m.id}/ubicacion`), { lat: p.lat.toFixed(6), lng: p.lng.toFixed(6) }, { preserveScroll: true, preserveState: true }); });
            mk.on('dblclick', () => router.visit(u(`/admin/publicaciones/${m.id}/editar`)));
        });
        return () => map.remove();
    }, [markers]);
    return (
        <>
            <Head title="Gestión del mapa" />
            <section className="card">
                <p className="hint">Arrastra un punto para corregir su ubicación (se guarda al soltarlo). Doble clic para editar la publicación. Verde: publicado · Amarillo: borrador o archivado.</p>
                <div ref={el} className="map-admin" />
            </section>
            <div className="grid grid-2" style={{ marginTop: 18 }}>
                <section className="card">
                    <h2><Icon name="pin" /> Sin coordenadas ({missing.length})</h2>
                    <ul className="plain-list">{missing.map((e) => <li key={e.id}><Link href={u(`/admin/publicaciones/${e.id}/editar`)}>{e.title}</Link></li>)}{!missing.length && <li className="muted">Todas las publicaciones tienen ubicación.</li>}</ul>
                </section>
                <section className="card">
                    <h2><Icon name="map" /> Departamentos</h2>
                    <div className="table-wrap"><table className="table"><thead><tr><th>Departamento</th><th>Región</th><th>Publicaciones</th><th /></tr></thead>
                        <tbody>{departments.map((d) => <tr key={d.id}><td>{d.name}</td><td className="muted">{d.region?.name}</td><td>{counts[d.id] || 0}</td><td><Link className="btn btn-outline btn-sm" href={u('/admin/mapa/departamentos/' + d.id)}><Icon name="edit" /> Editar</Link></td></tr>)}</tbody>
                    </table></div>
                </section>
            </div>
        </>
    );
}
