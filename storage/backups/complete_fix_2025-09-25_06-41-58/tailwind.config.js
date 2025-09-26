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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],

    safelist: [
        'bg-gray-300', 'text-gray-800',
        'bg-yellow-300', 'text-yellow-800',
        'bg-green-300', 'text-green-800',
        'bg-red-300', 'text-red-800',
        'bg-blue-300', 'text-blue-800',
        'bg-purple-300', 'text-purple-800',
        'bg-orange-300', 'text-orange-800',
        'bg-indigo-300', 'text-indigo-800',
    ],
};
