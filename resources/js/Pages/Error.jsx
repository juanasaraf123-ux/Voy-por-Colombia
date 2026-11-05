import { Head, Link } from '@inertiajs/react';
import Icon from '../components/Icon';
import { u } from '../lib/url';

const MESSAGES = {
    403: ['Acceso no permitido', 'No tienes permiso para ver esta página o realizar esta acción.'],
    404: ['No encontramos esta página', 'Puede que el enlace haya cambiado o que el contenido ya no esté publicado.'],
    419: ['La sesión expiró', 'Recarga la página e inténtalo de nuevo.'],
    429: ['Demasiados intentos', 'Espera unos minutos antes de intentarlo de nuevo.'],
    500: ['Algo salió mal', 'Tuvimos un problema al procesar tu solicitud. Inténtalo de nuevo en unos minutos.'],
    503: ['Volvemos enseguida', 'El sitio está en mantenimiento.'],
};

export default function Error({ status }) {
    const [title, text] = MESSAGES[status] || MESSAGES[500];
    return (
        <>
            <Head title={title} />
            <section className="page-hero" style={{ '--c': '#ff7a1a' }}>
                <div className="container narrow" style={{ textAlign: 'center' }}>
                    <span className="eyebrow">Error {status}</span>
                    <h1>{title}</h1>
                    <p className="lead">{text}</p>
                    <p><Link className="btn btn-primary" href={u('/')}><Icon name="map" /> Volver al mapa</Link></p>
                </div>
            </section>
        </>
    );
}
