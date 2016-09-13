var gulp = require('gulp'),
    autoprefixer = require('gulp-autoprefixer'),
    requirejs = require('gulp-requirejs-optimize'),
    sass = require('gulp-sass'),
    argv = require('yargs').argv;

var resolve = function (module) {
    return require.resolve(module).replace(/\.js$/, '');
};

var vendorPath = __dirname + '/node_modules';
var srcPaths = {
    sass: 'app/Resources/assets/sass/app.scss',
    fonts: vendorPath + '/bootstrap-sass/assets/fonts/**/*',
    js: 'app/Resources/assets/js/**/*.js'
};
var destPaths = {
    css: 'public_html/dist/css',
    fonts: 'public_html/dist/fonts',
    js: 'public_html/dist/js'
};
var watchPaths = {
    sass: 'app/Resources/assets/sass/**/*.scss',
    fonts: srcPaths.fonts,
    js: srcPaths.js
};
var autoprefixerOptions = {
    browsers: ['last 2 versions']
};
var requirejsOptions = {
    baseUrl: 'app/Resources/assets/js',
    include: 'app',
    name: 'almond',
    optimize: argv.production ? 'uglify2' : 'none',
    out: 'app.js',
    paths: {
        almond: resolve('almond'),
        axios: vendorPath + '/axios/dist/axios',
        bootstrap: resolve('bootstrap-sass'),
        jquery: resolve('jquery'),
        lodash: resolve('lodash'),
        matches: resolve('desandro-matches-selector'),
        raf: resolve('requestanimationframe'),
        ramda: resolve('ramda'),
        tpl: resolve('lodash-template-loader')
    },
    preserveLicenseComments: false,
    shim: {
        'bootstrap/affix':      { deps: ['jquery'] },
        'bootstrap/alert':      { deps: ['jquery'] },
        'bootstrap/button':     { deps: ['jquery'] },
        'bootstrap/carousel':   { deps: ['jquery'] },
        'bootstrap/collapse':   { deps: ['jquery'] },
        'bootstrap/dropdown':   { deps: ['jquery'] },
        'bootstrap/modal':      { deps: ['jquery'] },
        'bootstrap/popover':    { deps: ['jquery'] },
        'bootstrap/scrollspy':  { deps: ['jquery'] },
        'bootstrap/tab':        { deps: ['jquery'] },
        'bootstrap/tooltip':    { deps: ['jquery'] },
        'bootstrap/transition': { deps: ['jquery'] }
    },
    stubModules: ['lodash', 'tpl']
};
var sassOptions = {
    importer: function (url) {
        if (url[0] == '~') {
            url = vendorPath + '/' + url.substr(1);
        }

        return {file: url};
    },
    outputStyle: argv.production ? 'compressed' : 'expanded'
};

gulp.task('sass', function () {
    return gulp.src(srcPaths.sass)
        .pipe(sass(sassOptions).on('error', sass.logError))
        .pipe(autoprefixer(autoprefixerOptions))
        .pipe(gulp.dest(destPaths.css));
});

gulp.task('js', function() {
    gulp.src(srcPaths.js)
        .pipe(requirejs(requirejsOptions))
        .pipe(gulp.dest(destPaths.js))
});

gulp.task('fonts', function () {
    return gulp.src(srcPaths.fonts)
        .pipe(gulp.dest(destPaths.fonts));
});

gulp.task('watch', function () {
    gulp.watch(watchPaths.sass, ['sass']);
    gulp.watch(watchPaths.js, ['js']);
    gulp.watch(watchPaths.fonts, ['fonts']);
});

gulp.task('build', ['sass', 'js', 'fonts']);

gulp.task('default', ['build', 'watch']);
