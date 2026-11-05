import { Head } from '@inertiajs/react';
import { useState } from 'react';
import Icon from '../components/Icon';
import { Empty, PageHero, visitWith } from '../components/Content';
import { useViewer } from '../components/MediaViewer';
import { getJSON } from '../lib/url';

const SECTIONS = [['tourism', 'Turismo', 'compass', '#0f9d77'], ['gastronomy', 'Gastronomía', 'fork', '#ff6b2c'], ['coffee', 'Café', 'coffee', '#8a4b2a'], ['entrepreneurship', 'Emprendimiento', 'store', '#7b3fe4'], ['culture', 'Cultura', 'mask', '#ff3f8e']];

export default function Gallery({ info, items: first, total, collections, departments, municipalities, filters }) {
    const { open } = useViewer();
    const [extra, setExtra] = useState({ key: JSON.stringify(filters), items: [] });
    const more = extra.key === JSON.stringify(filters) ? extra.items : [];
    const items = [...first, ...more];
    const set = (k, v) => visitWith('/galeria', { ...filters, [k]: v, ...(k === 'departamento' ? { municipio: '' } : {}) });
    const loadMore = async () => {
        const p = new URLSearchParams(Object.entries({ ...filters, desde: items.length }).filter(([, v]) => v !== ''));
        const r = await getJSON('/api/galeria?' + p);
        setExtra({ key: JSON.stringify(filters), items: [...more, ...r.items] });
    };
    return (
        <>
            <Head title={info.title} />
            <PageHero color="#f2a900" icon="camera" eyebrow={info.title} title="Colombia en" accent="imágenes" lead={info.description}
                images={[first[0]?.large || info.cover?.large, first[1]?.medium]} sticker={[String(total), 'imágenes']} />
            <section className="section" style={{ '--c': '#f2a900', paddingTop: 0 }}>
                <div className="container container-wide">
                    <div className="filters">
                        <div className="chips chips-scroll" role="group" aria-label="Categoría">
                            <button type="button" className={`chip ${!filters.categoria ? 'is-active' : ''}`} onClick={() => set('categoria', '')}>Todo</button>
                            {SECTIONS.map(([k, l, ic, c]) => <button key={k} type="button" className={`chip ${filters.categoria === k ? 'is-active' : ''}`} style={{ '--chip': c }} onClick={() => set('categoria', k)}><Icon name={ic} /> {l}</button>)}
                        </div>
                        <div className="filters-row">
                            <select className="select" aria-label="Tipo de contenido" value={filters.tipo} onChange={(e) => set('tipo', e.target.value)}>
                                <option value="">Fotos y videos</option><option value="foto">Solo fotos</option><option value="video">Solo videos</option>
                            </select>
                            <select className="select" aria-label="Colección" value={filters.coleccion} onChange={(e) => set('coleccion', e.target.value)}>
                                <option value="">Todas las colecciones</option>{collections.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                            </select>
                            <select className="select" aria-label="Departamento" value={filters.departamento} onChange={(e) => set('departamento', e.target.value)}>
                                <option value="">Todos los departamentos</option>{departments.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}
                            </select>
                            {municipalities.length > 0 && (
                                <select className="select" aria-label="Municipio" value={filters.municipio} onChange={(e) => set('municipio', e.target.value)}>
                                    <option value="">Todos los municipios</option>{municipalities.map((m) => <option key={m.id} value={m.id}>{m.name}</option>)}
                                </select>
                            )}
                            <span className="muted" style={{ marginLeft: 'auto', fontWeight: 600 }}>{total} elementos</span>
                        </div>
                    </div>
                    {items.length ? (
                        <>
                            <div className="masonry">
                                {items.map((m, i) => (
                                    <button key={m.id} className="masonry-item" type="button" onClick={() => open(items, i)} aria-label={`Ampliar: ${m.title || 'imagen'}`}>
                                        <img src={m.medium} alt={m.alt || ''} loading="lazy" width={m.width || undefined} height={m.height || undefined} />
                                        {m.kind !== 'image' && <span className="play"><Icon name="play" /></span>}
                                        <span className="cap"><strong>{m.title}</strong><small>{[m.municipality || m.department, m.credit].filter(Boolean).join(' · ')}</small></span>
                                    </button>
                                ))}
                            </div>
                            {total > items.length && <div className="load-more"><button className="btn btn-outline" type="button" onClick={loadMore}><Icon name="plus" /> Cargar más</button></div>}
                        </>
                    ) : <Empty icon="camera" title="No hay imágenes con esos filtros" />}
                </div>
            </section>
        </>
    );
}
