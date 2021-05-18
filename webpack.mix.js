const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.setPublicPath('public');

mix.browserSync({
    host: '192.168.10.10',
    proxy: 'wizor.laravel',
    open: false,
    files: [
        'resources/views/**/*.php',
        'public/js/**/*.js'
    ],
    watchOptions: {
        usePolling: true,
        interval: 500
    }
});

mix
    .js('resources/js/app.js', 'public/js')
    .js('resources/js/app-admin.js', 'public/js')
    .sass('resources/sass/app-admin.scss', 'public/css').version()
    .sass('resources/sass/app.scss', 'public/css').version()
    .options({
        processCssUrls: false
    });




//mix.browserSync('wizor.company');

