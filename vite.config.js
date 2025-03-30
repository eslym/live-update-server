import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        svelte(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true
        })
    ],
    resolve: {
        alias: {
            $lib: path.resolve('./resources/js/lib'),
            $assets: path.resolve('./resources/assets')
        }
    }
});
