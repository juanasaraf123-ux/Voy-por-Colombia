import { Head, Link, usePage } from '@inertiajs/react';
import Icon from '../components/Icon';
import { Card, Empty, Filters, PageHero, Pagination } from '../components/Content';
import { RouteMap } from '../map/SmallMaps';
import { fecha, place, u } from '../lib/url';

const COPY = {
    tourism: ['Selva, mar, montaña', 'y desierto', 'Busca un destino, parque o pueblo…', 'compass'],
    gastronomy: ['Un país que se cuenta', 'en la mesa', 'Busca un plato, bebida o ingrediente…', 'fork'],
    coffee: ['Del grano', 'a la taza', 'Busca una finca, variedad o método…', 'coffee'],
    entrepreneurship: ['Hecho con las manos', 'de su gente', 'Busca un emprendimiento o producto…', 'store'],
    culture: ['Fiestas, música y', 'saberes vivos', 'Busca una fiesta, ritmo, artesanía…', 'mask'],
    news: ['Lo que pasa en', 'los territorios', 'Busca noticias, eventos o historias…', 'news'],
};

/** Listado de cualquier sección (Turismo, Gastronomía, Café, Emprendimiento, Cultura y Noticias). */
export default function Section({ section, info, items, pagination, filters, categories, departments, municipalities, regions, process = [], upcoming = [] }) {
    const { site } = usePage().props;
    const [title, accent, placeholder, icon] = COPY[section.key];
    const path = '/' + section.path;
    const imgs = [info.cover?.large, ...items.map((i) => i.image)].filter(Boolean);
    return (
        <>
            <Head title={info.title}><meta name="description" content={info.description || info.tagline} /></Head>
            <PageHero color={section.color} icon={section.icon} eyebrow={info.title} title={title} accent={accent} lead={info.description || info.tagline}
                images={imgs.slice(0, 2)} sticker={[String(pagination.total), pagination.total === 1 ? 'publicación' : 'publicaciones']}>
                {section.key === 'tourism' && <Link className="btn btn-primary mt-4" href={u('/')}><Icon name="map" /> Explorar en el mapa</Link>}
            </PageHero>

            {process.length > 0 && (
                <section className="section coffee-process">
                    <div className="container">
                        <span className="eyebrow">El viaje del café</span>
                        <h2 className="title-xl" style={{ color: '#fff' }}>Seis pasos, <span className="italic-accent" style={{ color: 'var(--amarillo)' }}>mil manos</span></h2>
                        <div className="process-track">
                            {process.slice(0, 6).map((p, i) => (
                                <Link key={p.id} className="process-step" href={u(p.url)}>
                                    <div className="num">{p.thumb && <img src={p.thumb} alt="" loading="lazy" />}<b>{i + 1}</b></div>
                                    <h3>{p.title}</h3>
                                    <p>{p.summary}</p>
                                </Link>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {section.key === 'coffee' && items.some((i) => i.lat !== null) && (
                <section className="section-tight">
                    <div className="container">
                        <div className="section-head"><div><span className="eyebrow" style={{ '--c': 'var(--selva-2)' }}>Mapa cafetero</span><h2 className="title-lg">Fincas, rutas y experiencias</h2></div></div>
                        <RouteMap stops={items} mode="points" tiles={site.tiles} style={{ position: 'relative', top: 0, height: 420 }} />
                    </div>
                </section>
            )}

            {upcoming.length > 0 && section.key === 'culture' && (
                <section className="section-tight" style={{ '--c': section.color }}>
                    <div className="container">
                        <div className="section-head"><div><span className="eyebrow">Calendario cultural</span><h2 className="title-lg">Próximas celebraciones</h2><p className="muted">Fechas de referencia: confirma siempre la programación con los organizadores.</p></div></div>
                        <div className="grid grid-3">
                            {upcoming.map((ev) => {
                                const d = new Date(ev.eventStart);
                                return (
                                    <article key={ev.id} className="event-card" style={{ '--c': 'var(--rosa)' }}>
                                        <div className="date-tile"><b>{d.getDate()}</b><span>{fecha(ev.eventStart, false).split(' de ')[1]?.slice(0, 3)}</span></div>
                                        <div><h3><Link href={u(ev.url)}>{ev.title}</Link></h3><small>{place(ev) || 'Todo el país'}</small></div>
                                    </article>
                                );
                            })}
                        </div>
                    </div>
                </section>
            )}

            <section className="section" style={{ '--c': section.color, paddingTop: upcoming.length || process.length ? undefined : 0 }}>
                <div className="container">
                    <Filters path={path} filters={filters} categories={categories} regions={regions} departments={departments} municipalities={municipalities} placeholder={placeholder} />
                    <div className="results-info">
                        <span>{pagination.total} {pagination.total === 1 ? 'resultado' : 'resultados'}</span>
                        {(filters.q || filters.categoria || filters.region || filters.departamento) && <Link className="link-arrow" href={u(path)} preserveScroll>Limpiar filtros</Link>}
                    </div>
                    {items.length ? (
                        <>
                            <div className="grid grid-3">{items.map((e) => <Card key={e.id} e={e} />)}</div>
                            <Pagination path={path} filters={filters} page={pagination.page} pages={pagination.pages} />
                        </>
                    ) : <Empty icon={icon} />}
                </div>
            </section>
        </>
    );
}
