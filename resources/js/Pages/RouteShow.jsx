import { Head, Link, usePage } from '@inertiajs/react';
import Icon from '../components/Icon';
import { Html, Wave } from '../components/Content';
import { toast } from '../components/Toasts';
import { RouteMap } from '../map/SmallMaps';
import { useMyRoute } from '../lib/store';
import { place, u } from '../lib/url';

export default function RouteShow({ route }) {
    const { site } = usePage().props;
    const [, , setIds] = useMyRoute();
    return (
        <>
            <Head title={route.title}><meta name="description" content={route.summary || ''} /></Head>
            <header className="entry-hero" style={{ '--c': 'var(--turquesa)', minHeight: '70svh' }}>
                {route.image && <img src={route.image} alt={route.title} />}
                <div className="container">
                    <nav className="breadcrumbs"><Link href={u('/')}>Inicio</Link><Icon name="chevron-right" /><Link href={u('/rutas')}>Rutas</Link></nav>
                    <h1>{route.title}</h1>
                    <p className="lead">{route.summary}</p>
                    <div className="flex gap-2 wrap mt-2">
                        {route.duration && <span className="badge"><Icon name="clock" /> {route.duration}</span>}
                        <span className="badge"><Icon name="pin" /> {route.stops.length} paradas</span>
                    </div>
                    <div className="flex gap-2 wrap mt-4">
                        <Link className="btn btn-primary" href={u('/?ruta=' + route.slug)}><Icon name="play" /> Recorrerla en el mapa</Link>
                        <button className="btn btn-glass" type="button" onClick={() => { setIds(route.stops.map((s) => s.id)); toast('Ahora es tu ruta: puedes editarla en «Mi ruta»'); }}><Icon name="route" /> Usar como mi ruta</button>
                    </div>
                </div>
                <Wave />
            </header>
            <section className="section" style={{ paddingTop: 20 }}>
                <div className="container">
                    <div className="my-route">
                        <div>
                            {route.body && <Html html={route.body} className="prose" />}
                            <div className="stop-list" style={{ marginTop: 24 }}>
                                {route.stops.map((s, i) => (
                                    <Link key={s.id} className="stop" href={u(s.url)}>
                                        {s.thumb && <img src={s.thumb} alt="" />}
                                        <span><strong>{i + 1}. {s.title}</strong><small>{place(s)}</small>{s.note && <small style={{ display: 'block' }}>{s.note}</small>}</span>
                                        <span className="stop-actions"><span className="btn btn-sm btn-outline btn-icon"><Icon name="arrow-right" /></span></span>
                                    </Link>
                                ))}
                            </div>
                        </div>
                        <RouteMap stops={route.stops} tiles={site.tiles} />
                    </div>
                </div>
            </section>
        </>
    );
}
