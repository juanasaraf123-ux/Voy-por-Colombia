import { Head, Link, usePage } from '@inertiajs/react';
import Icon from '../components/Icon';
import { FavButton, RouteButton } from '../components/Buttons';
import { Card, Html, Wave } from '../components/Content';
import { useViewer } from '../components/MediaViewer';
import { toast } from '../components/Toasts';
import { MiniMap } from '../map/SmallMaps';
import { fecha, place, u } from '../lib/url';

const FACT_ICONS = { best_time: 'calendar', altitude: 'mountain', dish_type: 'tag', origin: 'pin', varieties: 'leaf', process: 'cycle', tasting_notes: 'star', venue: 'columns', community: 'users', schedule: 'clock', contact_name: 'user', address: 'home', source: 'news' };
const BLOCKS = { activities: ['compass', 'Qué hacer'], ingredients: ['leaf', 'Ingredientes'], preparation: ['fire', 'Preparación'], products: ['store', 'Productos y servicios'], recommendations: ['info', 'Recomendaciones'] };

async function share(title) {
    const url = window.location.href;
    if (navigator.share) { try { await navigator.share({ title, url }); } catch { /* cancelado */ } return; }
    try { await navigator.clipboard.writeText(url); toast('Enlace copiado'); } catch { window.prompt('Copia este enlace:', url); }
}

export default function Entry({ entry: e, section, fields, images, videos, audios, documents, nearby, related, preview }) {
    const { site } = usePage().props;
    const { open } = useViewer();
    const f = Object.fromEntries(fields.map((x) => [x.key, x]));
    const val = (k) => f[k]?.value;
    const lines = (k) => (Array.isArray(val(k)) ? val(k).filter(Boolean) : []);
    const loc = place(e);
    const credits = [...new Set(images.map((m) => m.credit).filter(Boolean))];
    return (
        <>
            <Head title={e.title}><meta name="description" content={e.summaryFull || ''} /></Head>
            {preview && (
                <div className="preview-bar" role="status"><Icon name="eye" /> Vista previa · {({ draft: 'Borrador', published: 'Publicado', archived: 'Archivado', scheduled: 'Programado' })[e.effectiveStatus]}
                    <a className="btn btn-sm btn-light" href={u(`/admin/publicaciones/${e.id}/editar`)}><Icon name="edit" /> Volver a editar</a></div>
            )}
            <header className="entry-hero" style={{ '--c': section.color }}>
                {e.large && <img src={e.large} alt={e.alt || e.title} fetchPriority="high" />}
                <div className="container">
                    <nav className="breadcrumbs" aria-label="Ruta de navegación">
                        <Link href={u('/')}>Inicio</Link><Icon name="chevron-right" />
                        <Link href={u('/' + section.path)}>{section.label}</Link>
                        {e.departmentSlug && <><Icon name="chevron-right" /><Link href={u('/departamentos/' + e.departmentSlug)}>{e.department}</Link></>}
                        {e.municipality && <><Icon name="chevron-right" /><span>{e.municipality}</span></>}
                    </nav>
                    <div className="flex gap-2 wrap mt-2">
                        <span className="badge badge-section"><Icon name={section.icon} /> {section.label}</span>
                        {e.categories.slice(0, 3).map((c) => <span key={c.slug} className="badge">{c.name}</span>)}
                        {e.hiddenGem && <span className="badge badge-gem"><Icon name="sparkle" /> Joya escondida</span>}
                        {e.isDemo && <span className="badge badge-demo">Contenido de demostración</span>}
                    </div>
                    <h1>{e.title}</h1>
                    <p className="lead">{e.summaryFull}</p>
                    <div className="flex gap-2 wrap mt-4">
                        {e.section !== 'news' && <FavButton id={e.id} className="btn btn-light btn-icon fav-btn" />}
                        {e.lat !== null && <RouteButton id={e.id} className="btn btn-glass" />}
                        {e.lat !== null && <Link className="btn btn-glass" href={u(`/?departamento=${e.departmentSlug}&lugar=${e.id}`)}><Icon name="map" /> Ver en el mapa</Link>}
                        <button className="btn btn-glass" type="button" onClick={() => share(e.title)}><Icon name="share" /> Compartir</button>
                    </div>
                </div>
                <Wave />
            </header>

            <div className="container" style={{ '--c': section.color }}>
                <div className="entry-layout">
                    <article className="entry-main">
                        {e.section === 'news' && <p className="card-meta" style={{ marginBottom: 20 }}><Icon name="calendar" /> {fecha(e.publishedAt)}{e.authorName ? ' · ' + e.authorName : ''}</p>}
                        {e.isDemo && <p className="demo-note"><Icon name="info" /><span>Esta ficha es de <strong>demostración</strong>: verifica horarios, tarifas y datos con fuentes oficiales. Se reemplazará desde el panel administrativo.</span></p>}
                        {e.body && <Html html={e.body} className="prose dropcap" />}
                        {Object.entries(BLOCKS).map(([k, [ic, label]]) => lines(k).length > 0 && (
                            <section key={k} className="block"><h2><Icon name={ic} /> {label}</h2>
                                {k === 'preparation' ? <ol className="steps">{lines(k).map((a) => <li key={a}><span>{a}</span></li>)}</ol>
                                    : <ul className={k === 'ingredients' ? 'ingredients-list' : 'pill-list'}>{lines(k).map((a) => <li key={a}>{a}</li>)}</ul>}
                            </section>
                        ))}
                        {f.history && <section className="block"><h2><Icon name="book" /> Historia</h2><Html html={f.history.html} /></section>}
                        {f.how_to_get && <section className="block" id="como-llegar"><h2><Icon name="route" /> Cómo llegar</h2><Html html={f.how_to_get.html} /></section>}
                        {f.respect_note && <section className="block"><div className="demo-note" style={{ background: 'color-mix(in srgb, var(--verde) 14%, white)' }}><Icon name="hand" /><div><strong>Visita con respeto.</strong> <Html html={f.respect_note.html} className="" /></div></div></section>}

                        {images.length > 0 && (
                            <section className="block" id="galeria">
                                <h2><Icon name="camera" /> Galería</h2>
                                <div className={`entry-gallery count-${Math.min(4, images.length)}`}>
                                    {images.map((m, i) => <button key={m.id} type="button" onClick={() => open(images, i)} aria-label={`Ampliar imagen: ${m.title || e.title}`}><img src={m.medium} srcSet={m.srcset || undefined} sizes="(max-width: 620px) 50vw, 300px" alt={m.alt || e.title} loading="lazy" /></button>)}
                                </div>
                                {credits.length > 0 && <p className="credit">Créditos: {credits.join(' · ')}</p>}
                            </section>
                        )}
                        {videos.length > 0 && (
                            <section className="block" id="videos">
                                <h2><Icon name="video" /> Videos</h2>
                                <div className="grid" style={{ gap: 18 }}>
                                    {videos.map((v) => (
                                        <figure key={v.id} style={{ margin: 0 }}>
                                            <div className="video-frame">{v.kind === 'embed' ? <iframe src={v.embed} title={v.title || 'Video'} loading="lazy" allow="encrypted-media; picture-in-picture" allowFullScreen /> : <video src={v.src} controls preload="metadata" playsInline />}</div>
                                            {(v.title || v.credit) && <figcaption className="credit">{[v.title, v.credit].filter(Boolean).join(' · ')}</figcaption>}
                                        </figure>
                                    ))}
                                </div>
                            </section>
                        )}
                        {audios.length > 0 && (
                            <section className="block" id="audio">
                                <h2><Icon name="volume" /> Escucha</h2>
                                <div className="grid" style={{ gap: 12 }}>
                                    {audios.map((a) => <div key={a.id} className="audio-card"><strong>{a.title || 'Audio'}</strong><audio src={a.src} controls preload="none" />{a.credit && <span className="credit">{a.credit}</span>}</div>)}
                                </div>
                            </section>
                        )}
                        {documents.length > 0 && (
                            <section className="block"><h2><Icon name="file" /> Documentos</h2>
                                <ul className="pill-list">{documents.map((d) => <li key={d.id}><a href={d.src} target="_blank" rel="noopener noreferrer">{d.title || 'Documento'} (PDF)</a></li>)}</ul>
                            </section>
                        )}
                        {e.coverMedia?.credit && <p className="credit mt-4">Imagen principal: {e.coverMedia.credit}</p>}
                    </article>

                    <aside className="entry-aside">
                        <div className="facts">
                            {loc && <div className="fact"><span className="ico"><Icon name="pin" /></span><div><small>Ubicación</small><span>{loc}</span></div></div>}
                            {e.regionName && <div className="fact"><span className="ico"><Icon name="map" /></span><div><small>Región</small><span>{e.regionName}</span></div></div>}
                            {e.eventStart && <div className="fact"><span className="ico"><Icon name="calendar" /></span><div><small>Fecha</small><span>{fecha(e.eventStart)}{e.eventEnd && e.eventEnd.slice(0, 10) !== e.eventStart.slice(0, 10) ? ' al ' + fecha(e.eventEnd) : ''}</span></div></div>}
                            {Object.entries(FACT_ICONS).map(([k, ic]) => f[k] && typeof val(k) === 'string' && <div key={k} className="fact"><span className="ico"><Icon name={ic} /></span><div><small>{f[k].label}</small><span>{val(k)}</span></div></div>)}
                            {val('contact_phone') && <div className="fact"><span className="ico"><Icon name="phone" /></span><div><small>Teléfono</small><a href={'tel:' + String(val('contact_phone')).replace(/[^\d+]/g, '')}>{val('contact_phone')}</a></div></div>}
                            {val('contact_email') && <div className="fact"><span className="ico"><Icon name="mail" /></span><div><small>Correo</small><a href={'mailto:' + val('contact_email')}>{val('contact_email')}</a></div></div>}
                            {val('website') && /^https?:\/\//.test(val('website')) && <div className="fact"><span className="ico"><Icon name="globe" /></span><div><small>Sitio web</small><a href={val('website')} target="_blank" rel="noopener nofollow noreferrer">{val('website').replace(/^https?:\/\//, '')}</a></div></div>}
                            {val('instagram') && <div className="fact"><span className="ico"><Icon name="instagram" /></span><div><small>Instagram</small><a href={'https://instagram.com/' + encodeURIComponent(String(val('instagram')).replace(/^@/, ''))} target="_blank" rel="noopener nofollow noreferrer">@{String(val('instagram')).replace(/^@/, '')}</a></div></div>}
                            {val('source_url') && /^https?:\/\//.test(val('source_url')) && <div className="fact"><span className="ico"><Icon name="link" /></span><div><small>Fuente</small><a href={val('source_url')} target="_blank" rel="noopener nofollow noreferrer">Ver fuente original</a></div></div>}
                        </div>
                        {(e.lat !== null || e.departmentSlug) && <MiniMap lat={e.lat} lng={e.lng} dep={e.departmentSlug} color={section.color} thumb={e.thumb} tiles={site.tiles} />}
                    </aside>
                </div>
            </div>

            {(nearby.length > 0 || related.length > 0) && (
                <section className="section">
                    <div className="container">
                        <div className="section-head"><div><span className="eyebrow" style={{ '--c': 'var(--naranja)' }}>{nearby.length ? 'Muy cerca' : 'Sigue explorando'}</span><h2 className="title-lg">{nearby.length ? 'Lugares cercanos' : 'También te puede gustar'}</h2></div></div>
                        <div className="grid grid-3">{(nearby.length ? nearby.slice(0, 3) : related).map((n) => <Card key={n.id} e={n} showSection />)}</div>
                    </div>
                </section>
            )}
        </>
    );
}
