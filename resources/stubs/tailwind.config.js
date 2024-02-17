import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './resources/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Lexend"', ...defaultTheme.fontFamily.sans],
                serif: ['"Lexend"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'prime': {
                    DEFAULT: '#E60C05',
                    50: '#ff1308',
                    100: '#ff1007',
                    200: '#ff0e06',
                    300: '#fd0d05',
                    400: '#f10c05',
                    500: '#E60C05',
                    600: '#cf0a04',
                    700: '#ac0903',
                    800: '#8a0703',
                    900: '#670502',
                    950: '#450301',
                },
                'alt': {
                    DEFAULT: '#4DE9F0',
                    50: '#7bffff',
                    100: '#6bffff',
                    200: '#5cffff',
                    300: '#54ffff',
                    400: '#50f4fc',
                    500: '#4DE9F0',
                    600: '#45d1d8',
                    700: '#39aeb4',
                    800: '#2e8b90',
                    900: '#22686c',
                    950: '#174548'
                },
                'cta': {
                    DEFAULT: '#F0704D',
                    50: '#ffb37b',
                    100: '#ff9c6b',
                    200: '#ff865c',
                    300: '#ff7b54',
                    400: '#fc7550',
                    500: '#F0704D',
                    600: '#d86445',
                    700: '#b45439',
                    800: '#90432e',
                    900: '#6c3222',
                    950: '#482117',
                },
                'background': {
                    DEFAULT: '#d4d4d4',
                    50: '#fafafa',
                    150: '#f2f2f2',
                    250: '#e9e9e9',
                    350: '#e0e0e0',
                    450: '#d8d8d8',
                    500: '#d4d4d4',
                    600: '#a9a9a9',
                    700: '#7f7f7f',
                    800: '#545454',
                    900: '#2a2a2a',
                    950: '#0d0d0d',
                },
                'bodycolor': {
                    DEFAULT: '#1c1917',
                    50: '#e8e8e7',
                    150: '#babab9',
                    250: '#8d8c8b',
                    350: '#605e5c',
                    450: '#32302e',
                    500: '#1c1917',
                    600: '#161412',
                    700: '#100f0d',
                    800: '#0b0a09',
                    900: '#050404',
                    950: '#020202',
                },
                'darkheader': {
                    DEFAULT: '#27272a',
                    50: '#e9e9e9',
                    150: '#bebebf',
                    250: '#939394',
                    350: '#676769',
                    450: '#3c3c3f',
                    500: '#27272a',
                    600: '#1f1f21',
                    700: '#171719',
                    800: '#0f0f10',
                    900: '#070708',
                },
            },
            'textColor': {
                'link': {
                    DEFAULT: '#3490DC',
                    50: '#D1E6F7',
                    100: '#BFDCF4',
                    200: '#9CC9EE',
                    300: '#7AB6E8',
                    400: '#57A3E2',
                    500: '#3490DC',
                    600: '#2073B8',
                    700: '#185588',
                    800: '#0F3758',
                    900: '#071929',
                    950: '#030A11',
                },
              }
        },
    },
    plugins: [
        forms,
        typography
    ],
    corePlugins: {
        scale: true,
    },
};
