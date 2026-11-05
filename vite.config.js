import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],
    // Rutas relativas entre archivos compilados: funciona en la raíz del dominio o en una subcarpeta (XAMPP).
    experimental: {
        renderBuiltUrl: (filename, { hostType }) => (hostType === 'js' ? { relative: true } : undefined),
    },
    build: {
        assetsInlineLimit: 0,
        chunkSizeWarningLimit: 900,
    },
});
