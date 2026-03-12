import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

const devServerUrl = process.env.VITE_DEV_SERVER_URL || 'http://localhost:5173';
const devServerPort = Number(process.env.VITE_DEV_SERVER_PORT || 5173);
const devServerHost = process.env.VITE_DEV_SERVER_HOST || 'localhost';
const localAppOriginPattern = /^https?:\/\/(?:localhost|127\.0\.0\.1)(?::\d+)?$/;

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: devServerPort,
        strictPort: true,
        origin: devServerUrl,
        cors: {
            origin(origin, callback) {
                callback(null, origin === undefined || localAppOriginPattern.test(origin));
            },
        },
        hmr: {
            host: devServerHost,
            port: devServerPort,
            protocol: devServerUrl.startsWith('https://') ? 'wss' : 'ws',
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
