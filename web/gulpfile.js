var gulp = require('gulp'),
    autoprefixer = require('gulp-autoprefixer'),
    sass = require('gulp-sass'),
    sassImporter = require('sass-importer-npm'),
    argv = require('yargs').argv;

var paths = {
    css: 'public_html/dist/css',
    sass: 'app/Resources/assets/sass/**/',
    fonts: 'public_html/dist/fonts'
};
var autoprefixerOptions = {
    browsers: ['last 2 versions']
};
var sassOptions = {
    importer: sassImporter,
    outputStyle: argv.production ? 'compressed' : 'expanded'
};

gulp.task('sass', function () {
    return gulp.src(paths.sass + 'app.scss')
        .pipe(sass(sassOptions).on('error', sass.logError))
        .pipe(autoprefixer(autoprefixerOptions))
        .pipe(gulp.dest(paths.css));
});

gulp.task('fonts', function () {
    return gulp.src('node_modules/bootstrap-sass/assets/fonts/**/*')
        .pipe(gulp.dest(paths.fonts));
});

gulp.task('watch', function () {
    gulp.watch(paths.sass + '*.scss', ['sass']);
});

gulp.task('build', ['sass', 'fonts']);

gulp.task('default', ['build', 'watch']);
