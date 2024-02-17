import { defineConfig } from 'vite';
import laravel, {refreshPaths} from 'laravel-vite-plugin';
import dotenv from 'dotenv';

// Load environment variables
dotenv.config();

let appUrl = process.env.APP_URL;
const host = new URL(appUrl).hostname;

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: [
                ...refreshPaths,
                'app/Livewire/**',
                'resources/views/**/*.blade.php',
            ],
            detectTls: host ,
        }),
    ],
});
