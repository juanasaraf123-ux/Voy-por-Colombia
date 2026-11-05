import { Head, Link } from '@inertiajs/react';
import Icon from '../components/Icon';
import { Card, Empty, Wave } from '../components/Content';
import { useViewer } from '../components/MediaViewer';
import { u } from '../lib/url';

export default function Department({ dep, groups }) {
    const { open } = useViewer();
    return (
        <>
            <Head title={dep.name}><meta name="description" content={dep.summary || ''} /></Head>
            <header className="entry-hero dep-hero" style={{ '--c': dep.regionColor || '#2bb673' }}>
                {(dep.cover?.large || dep.photos[0]?.large) && <img src={dep.cover?.large || dep.photos[0]?.large} alt={dep.name} />}
                <div className="container">
                    <nav className="breadcrumbs"><Link href={u('/')}>Inicio</Link><Icon name="chevron-right" /><span>{dep.name}</span></nav>
                    <div className="flex gap-2 wrap mt-2">
                        {dep.region && <span className="badge" style={{ background: dep.regionColor, color: '#fff' }}>Región {dep.region}</span>}
                        {dep.capital && <span className="badge">Capital: {dep.capital}</span>}
                    </div>
                    <h1>{dep.name}</h1>
                    <p className="lead">{dep.summary}</p>
                    <Link className="btn btn-primary mt-2" href={u('/?departamento=' + dep.slug)}><Icon name="map" /> Explorar sus municipios en el mapa</Link>
                </div>
                <Wave />
            </header>
            <section className="section" style={{ paddingTop: 30 }}>
                <div className="container">
                    {!dep.total && <Empty icon="compass" title={`Aún no hay publicaciones en ${dep.name}`} text="Muy pronto agregaremos destinos, sabores e historias de este departamento." />}
                    {groups.map((g) => (
                        <div key={g.key} className="section-block" style={{ '--c': g.color }}>
                            <div className="section-block-head"><h2 className="title-lg" style={{ margin: 0 }}>{g.label}</h2></div>
                            <div className="grid grid-3">{g.items.map((e) => <Card key={e.id} e={e} />)}</div>
                        </div>
                    ))}
                    {dep.photos.length > 0 && (
                        <div className="section-block">
                            <div className="section-block-head" style={{ '--c': 'var(--amarillo)' }}><span className="ico"><Icon name="camera" /></span><h2 className="title-lg" style={{ margin: 0 }}>Galería</h2></div>
                            <div className="photo-strip">
                                {dep.photos.map((p, i) => <button key={p.id} type="button" onClick={() => open(dep.photos, i)}><img src={p.medium} alt={p.alt} loading="lazy" /><span>{p.title}</span></button>)}
                            </div>
                        </div>
                    )}
                </div>
            </section>
        </>
    );
}
