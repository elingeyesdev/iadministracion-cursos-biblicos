import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                serif: ['Cormorant Garamond', 'Georgia', 'serif'],
                sans: ['Jost', 'sans-serif'],
            },
            colors: {
                gold: { 50:'#fdfaf0', 100:'#faf3d6', 200:'#f4e49e', 300:'#ecd05a', 400:'#e2b92a', 500:'#c99a10', 600:'#a67c0c', 700:'#7d5c09', 800:'#54400a', 900:'#2e240a' },
                stone: { 50:'#faf9f7', 100:'#f2efe9', 200:'#e3ddd3', 300:'#cfc6b8', 400:'#b0a392', 500:'#8e7f6d', 600:'#6e6155', 700:'#564b41', 800:'#3a322b', 900:'#221e19' },
                cream: '#fdfaf3',
            },
            backgroundImage: {
                'cross-pattern': "url(\"data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M18 0h4v40h-4zM0 18h40v4H0z' fill='%23c99a10' fill-opacity='0.05'/%3E%3C/svg%3E\")",
            }
        },
    },

    plugins: [forms],
};