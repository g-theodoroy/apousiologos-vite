const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {

    //mode: 'jit',

    purge: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            minHeight: (theme) => ({
                ...theme('spacing'),
            }),
        },
    },

    variants: {
        extend: {
            opacity: ['disabled'],
            backgroundColor: ['active'],
            textColor: ['active'],
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
