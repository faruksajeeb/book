import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
		vue(),
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: true, // Allow external connections
        port: 5173, // Optional: Explicitly set port
        cors: {
          origin: 'https://book.test', // Allow only your Laravel app
          credentials: true,          // Allow cookies or credentials
        },
        hmr: {
          protocol: 'ws',
          host: 'localhost', // Use localhost or your development host
        },
      },
});
