import { usePage } from '@inertiajs/react';
import Header from '../components/Header';
import Footer from '../components/Footer';
import Toasts from '../components/Toasts';
import { ViewerProvider } from '../components/MediaViewer';

/** Diseño del sitio público. La portada usa «fullscreen»: una sola pantalla, sin pie de página ni scroll. */
export default function PublicLayout({ children }) {
    const { component } = usePage();
    const fullscreen = component === 'Home';
    return (
        <ViewerProvider>
            <a className="skip-link" href="#contenido">Saltar al contenido</a>
            <Header transparent={fullscreen} />
            <main id="contenido" tabIndex={-1} className={fullscreen ? 'main-fullscreen' : ''}>{children}</main>
            {!fullscreen && <Footer />}
            <Toasts />
        </ViewerProvider>
    );
}
