// npm i -g gulp
'use strict';

var gulp = require('gulp');
const zip = require('gulp-zip');
var gulpCopy = require('gulp-copy');
var clean = require('gulp-clean');
const autoprefixer = require('gulp-autoprefixer');
const cleanCSS = require('gulp-clean-css');

var sass = require('gulp-sass');
var live_reload = require('gulp-livereload');
var sourcemaps = require('gulp-sourcemaps');
const ghPages = require('gulp-gh-pages');
const shell = require('gulp-shell');

gulp.task('sass', function () {
    return gulp.src('admin/assets/scss/*.scss')
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(autoprefixer({
            browsers: ['last 3 versions'],
            cascade: false
        }))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest('admin/assets/css'))
        .pipe(live_reload());
});

gulp.task('watch', function () {
    live_reload.listen();
    gulp.watch(['admin/assets/scss/**/*.scss'], ['sass']);
});

gulp.task('default', ['sass', 'watch']);

/**
 * Build zip
 */
function clean_dist() {
    return gulp.src('dist', {read: false})
        .pipe(clean());
}

gulp.task('build', ['zip'], clean_dist);

gulp.task('clean', clean_dist);

gulp.task('copy', ['clean'], function () {
    return gulp
        .src([
            'admin/**/*',
            'templates/**/*',
            'assets/**/*',
            'inc/**/*',
            'providers/**/*',
            'helpers/**/*',
            '!inc/includes/kirki/node_modules/**/*',
            'languages/**/*',
            'thim-core.php',
            'readme.txt',
            'index.php',
            '!**/*.scss',
            '!**/*.log',
            '!**/Gruntfile.js',
            '!**/package.json'
        ])
        .pipe(gulpCopy('dist/thim-core', {}))
});

gulp.task('minify', ['copy'], function () {
    return gulp.src('./dist/**/*.css')
        .pipe(cleanCSS({
            rebase: false
        }))
        .pipe(gulp.dest('./dist'));
});

gulp.task('zip', ['minify'], function () {
    return gulp.src('dist/**/*')
        .pipe(zip('thim-core.zip'))
        .pipe(gulp.dest(''));
});

gulp.task('prepareDeploy', function () {
    return gulp
        .src([
            'thim-core.zip',
            'changelog.html'
        ])
        .pipe(gulpCopy('public-site', {}));
});

gulp.task('deploy', ['prepareDeploy'], shell.task([
    'node deploy.js'
]));