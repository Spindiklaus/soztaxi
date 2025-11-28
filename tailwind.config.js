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
            }
      }
            
        },
    },

    plugins: [forms],
};
