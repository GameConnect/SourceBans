var gulp = require('gulp'),
    autoprefixer = require('gulp-autoprefixer'),
    concat = require('gulp-concat'),
    sass = require('gulp-sass'),
    uglify = require('gulp-uglify'),
    sassImporter = require('sass-importer-npm'),
    argv = require('yargs').argv;

var srcPaths = {
    sass: 'app/Resources/assets/sass/**/',
    fonts: 'node_modules/bootstrap-sass/assets/fonts/**/*',
    js: 'app/Resources/assets/js/**/'
};
var destPaths = {
    css: 'public_html/dist/css',
    fonts: 'public_html/dist/fonts',
    js: 'public_html/dist/js'
};
var autoprefixerOptions = {
    browsers: ['last 2 versions']
};
var sassOptions = {
    importer: sassImporter,
    outputStyle: argv.production ? 'compressed' : 'expanded'
};

gulp.task('sass', function () {
    return gulp.src(srcPaths.sass + 'app.scss')
        .pipe(sass(sassOptions).on('error', sass.logError))
        .pipe(autoprefixer(autoprefixerOptions))
        .pipe(gulp.dest(destPaths.css));
});

gulp.task('js', function() {
    gulp.src(srcPaths.js + '*.js')
        .pipe(concat('app.js'))
        .pipe(uglify())
        .pipe(gulp.dest(destPaths.js))
});

gulp.task('fonts', function () {
    return gulp.src(srcPaths.fonts)
        .pipe(gulp.dest(destPaths.fonts));
});

gulp.task('watch', function () {
    gulp.watch(srcPaths.sass + '*.scss', ['sass']);
    gulp.watch(srcPaths.js + '*.js', ['js']);
    gulp.watch(srcPaths.fonts, ['fonts']);
});

gulp.task('build', ['sass', 'js', 'fonts']);

gulp.task('default', ['build', 'watch']);
