import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    
    safelist: [
        'bg-rose-200',
        'bg-purple-200',
        'bg-rose-100', 
        'bg-purple-100',
        'bg-purple-50',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                yellow: {
                   500: '#fde047',
                },
                'light-steel-blue': {
                    50: '#f0f5fb',   // очень светлый
                    100: '#e6eff5',
                    200: '#d4e0ec',
                    300: '#C2D2E6',
                    400: '#B0C4DE',   // оригинальный LightSteelBlue
                    500: '#9db5d5',
                    600: '#8ba7cc',
                    // и так далее при необходимости
                  },
                'LightCyan': '#E0FFFF',
                'light-yellow': '#FFFFE0',
            }
            
        },
    },

    plugins: [forms],
};
