import { Head, Link } from '@inertiajs/react';
import Icon from '../components/Icon';
import { Empty, PageHero } from '../components/Content';
import { u } from '../lib/url';

export function RouteCard({ r }) {
    return (
        <article className="route-card">
            <div className="card-media">{r.image && <img src={r.image} alt={r.title} loading="lazy" />}<div className="route-line">{Array.from({ length: r.stopsCount || 0 }).map((_, k) => <span key={k}>{k > 0 && <b />}<i /></span>)}</div></div>
            <div className="card-body">
                <div className="card-meta">{r.duration && <span><Icon name="clock" /> {r.duration}</span>}<span><Icon name="pin" /> {r.stopsCount} paradas</span></div>
                <h3><Link href={u(r.url)}>{r.title}</Link></h3>
                <p>{r.summary}</p>
            </div>
        </article>
    );
}

export default function Routes({ routes }) {
    return (
        <>
            <Head title="Rutas turísticas" />
            <PageHero color="var(--turquesa)" icon="route" eyebrow="Rutas turísticas" title="Itinerarios para" accent="recorrer Colombia" compact
                lead="Recorridos sugeridos con varias paradas. Úsalos como inspiración o arma el tuyo.">
                <div className="flex gap-2 wrap mt-4">
                    <Link className="btn btn-primary" href={u('/mi-ruta')}><Icon name="route" /> Arma tu propia ruta</Link>
                    <Link className="btn btn-outline" href={u('/')}><Icon name="map" /> Verlas en el mapa</Link>
                </div>
            </PageHero>
            <section className="section" style={{ paddingTop: 20 }}>
                <div className="container">
                    {routes.length ? <div className="grid grid-3">{routes.map((r) => <RouteCard key={r.id} r={r} />)}</div> : <Empty icon="route" title="Aún no hay rutas publicadas" />}
                </div>
            </section>
        </>
    );
}
