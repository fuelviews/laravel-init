import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.js',
        './resources/**/*.vue',
        './resources/**/*.blade.php',
    ],
    theme: {
        extend: {},
    },
    plugins: [
        forms,
        typography
    ],
    corePlugins: {
        scale: true,
    },
};
