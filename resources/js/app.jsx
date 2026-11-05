import 'leaflet/dist/leaflet.css';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import PublicLayout from './Layouts/PublicLayout';
import AdminLayout from './Layouts/AdminLayout';

const pages = import.meta.glob('./Pages/**/*.jsx');

createInertiaApp({
    title: (title) => (title ? `${title} · Voy por Colombia` : 'Voy por Colombia · Colombia, Tierra de Infinitos'),
    resolve: async (name) => {
        const page = (await pages[`./Pages/${name}.jsx`]()).default;
        // Diseño por defecto según el área (pública o panel). Las páginas pueden definir el suyo.
        if (page.layout === undefined) {
            page.layout = name.startsWith('Admin/')
                ? (p) => (name === 'Admin/Login' || name === 'Admin/Setup' ? p : <AdminLayout>{p}</AdminLayout>)
                : (p) => <PublicLayout>{p}</PublicLayout>;
        }
        return page;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: { color: '#ff3f8e', showSpinner: false },
});
