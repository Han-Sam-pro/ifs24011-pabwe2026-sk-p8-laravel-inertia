import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

createInertiaApp({
    title: (title) => (title ? `${title} · Blog Pabwe` : 'Blog Pabwe'),
    // Progress bar dan status pill dibuat sendiri (lihat Components/ProgressIndicators.jsx).
    progress: false,
    resolve: (name) => {
        // Lazy: setiap halaman menjadi chunk sendiri, jadi halaman publik tidak memuat kode admin/editor.
        const pages = import.meta.glob('./Pages/**/*.jsx');
        const loader = pages[`./Pages/${name}.jsx`];

        if (!loader) {
            throw new Error(`Halaman "${name}" tidak ditemukan.`);
        }

        return loader().then((module) => module.default);
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});
