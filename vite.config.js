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
        host: '0.0.0.0', // pastikan cocok dengan laragon atau localhost kamu
        port: 5173,
        hmr: {
            host: '192.168.243.212',
        },
    },

    // 🚀 Output optimization untuk build production
    build: {
        chunkSizeWarningLimit: 600,
    },
})
