import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import path from 'path'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],

    // 🧠 Untuk dukungan path alias seperti "@/components"
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },

    // ⚙️ Optimasi tambahan
    server: {
        host: '127.0.0.1', // pastikan cocok dengan laragon atau localhost kamu
        port: 5173,
        hmr: {
            host: '127.0.0.1',
        },
    },

    // 🚀 Output optimization untuk build production
    build: {
        chunkSizeWarningLimit: 600,
    },
})
