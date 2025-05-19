import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/main.scss',
                'resources/sass/codebase/themes/corporate.scss',
                'resources/sass/codebase/themes/earth.scss',
                'resources/sass/codebase/themes/elegance.scss',
                'resources/sass/codebase/themes/flat.scss',
                'resources/sass/codebase/themes/pulse.scss',
                'resources/js/codebase/app.js',
                'resources/js/app.js',
                'resources/js/pages/datatables.js',
            ],
            refresh: true,
        }),
    ],
});
