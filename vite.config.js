import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

const devServerHost = process.env.VITE_DEV_SERVER_HOST ?? '0.0.0.0';
const hmrHost = process.env.VITE_HMR_HOST ?? 'localhost';
const hmrPort = Number(process.env.VITE_HMR_PORT ?? 5173);

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: devServerHost,
        port: hmrPort,
        strictPort: true,
        hmr: {
            host: hmrHost,
            port: hmrPort,
        },
        watch: {
            usePolling: true,
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
