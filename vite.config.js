import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/cp.css',
                'resources/js/cp.js'
            ],
            refresh: true,
            publicDirectory: 'dist',
            hotFile: 'dist/hot',
        }),
    ],
});
