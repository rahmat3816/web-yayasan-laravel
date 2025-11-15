import preset from './vendor/filament/support/tailwind.config.preset'
import daisyui from 'daisyui'
import themes from 'daisyui/src/theming/themes.js'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Inter var"', 'Inter', 'Nunito', 'sans-serif'],
                display: ['"Space Grotesk"', 'Inter', 'sans-serif'],
            },
            colors: {
                brand: {
                    50: '#f4f7ff',
                    100: '#dfe8ff',
                    200: '#bed1ff',
                    300: '#9cb7ff',
                    400: '#7496ff',
                    500: '#4d73ff',
                    600: '#3658db',
                    700: '#253eb7',
                    800: '#192a93',
                    900: '#101c7a',
                },
            },
            boxShadow: {
                glass: '0 20px 45px rgba(15, 23, 42, 0.12)',
            },
        },
    },
    plugins: [daisyui],
    daisyui: {
        themes: [
            {
                emerald: {
                    ...themes['emerald'],
                    'primary': '#059669',
                    'secondary': '#34d399',
                    'accent': '#38bdf8',
                    'neutral': '#1f2937',
                    'base-100': '#f5fdf7',
                    'base-200': '#e2f5ea',
                    'base-300': '#c8ebd9',
                    'info': '#0ea5e9',
                    'success': '#10b981',
                    'warning': '#facc15',
                    'error': '#f87171',
                },
            },
            {
                'emerald-dark': {
                    ...themes['emerald'],
                    'primary': '#34d399',
                    'secondary': '#2dd4bf',
                    'accent': '#38bdf8',
                    'neutral': '#0f172a',
                    'base-100': '#0f172a',
                    'base-200': '#1e293b',
                    'base-300': '#273548',
                    'base-content': '#f8fafc',
                    'info': '#38bdf8',
                    'success': '#4ade80',
                    'warning': '#facc15',
                    'error': '#fb7185',
                },
            },
        ],
        darkTheme: 'emerald-dark',
    },
}
