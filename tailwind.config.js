import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', // 🌙 aktifkan dark mode berbasis class

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './resources/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // warna tambahan opsional agar dark mode lebih lembut
                darkBg: '#1e293b',
                darkCard: '#273549',
                darkText: '#e2e8f0',
            },
        },
    },

    plugins: [
        forms, // plugin default Laravel untuk form styling
    ],
}
