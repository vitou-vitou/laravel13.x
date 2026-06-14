import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Instrument Sans', 'Figtree', ...defaultTheme.fontFamily.sans],
                mono: ['ui-monospace', 'SFMono-Regular', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                ops: {
                    ink: '#1c1917',
                    muted: '#78716c',
                    surface: '#fafaf9',
                    border: '#e7e5e4',
                    accent: '#4338ca',
                    'accent-hover': '#3730a3',
                },
            },
            boxShadow: {
                panel: '0 1px 2px 0 rgb(28 25 23 / 0.04), 0 1px 3px 0 rgb(28 25 23 / 0.06)',
            },
        },
    },

    plugins: [forms],
};
