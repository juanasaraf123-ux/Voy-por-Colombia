import { Link } from '@inertiajs/react';
import Brand from './Brand';
import Icon from './Icon';
import { u, useSite } from '../lib/url';

export default function Footer() {
    const site = useSite();
    const socials = Object.entries(site.socials || {}).filter(([, v]) => v);
    return (
        <footer className="site-footer">
            <div className="container">
                <div className="footer-grid">
                    <div className="footer-brand">
                        <div style={{ color: '#fff' }}><Brand /></div>
                        <p>{site.footerText}</p>
                        {socials.length > 0 && (
                            <div className="socials">
                                {socials.map(([net, link]) => <a key={net} href={link} target="_blank" rel="noopener noreferrer" aria-label={net}><Icon name={net} /></a>)}
                            </div>
                        )}
                    </div>
                    <div>
                        <h4>Explora</h4>
                        <ul>
                            {[['/turismo', 'Turismo'], ['/gastronomia', 'Gastronomía'], ['/cafe', 'Café'], ['/emprendimiento', 'Emprendimiento'], ['/cultura', 'Cultura'], ['/noticias', 'Noticias'], ['/galeria', 'Galería de fotos']].map(([h, l]) => <li key={h}><Link href={u(h)}>{l}</Link></li>)}
                        </ul>
                    </div>
                    <div>
                        <h4>Planea</h4>
                        <ul>
                            <li><Link href={u('/')}>Mapa interactivo</Link></li>
                            <li><Link href={u('/rutas')}>Rutas sugeridas</Link></li>
                            <li><Link href={u('/mi-ruta')}>Mi ruta</Link></li>
                            <li><Link href={u('/favoritos')}>Favoritos</Link></li>
                            <li><Link href={u('/buscar')}>Buscar</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4>Regiones</h4>
                        <ul>{(site.regions || []).map((r) => <li key={r.slug}><Link href={u(`/?region=${r.slug}`)}>{r.name}</Link></li>)}</ul>
                        {site.contactEmail && <p className="mt-2"><a href={`mailto:${site.contactEmail}`}>{site.contactEmail}</a></p>}
                    </div>
                </div>
                <div className="footer-bottom">
                    <span>© {new Date().getFullYear()} {site.name} · {site.tagline}</span>
                    <span>Límites geográficos: <a href="https://www.geoboundaries.org" target="_blank" rel="noopener">geoBoundaries</a> (datos © colaboradores de OpenStreetMap, ODbL) · Las imágenes conservan los créditos de sus autores.</span>
                </div>
            </div>
        </footer>
    );
}
