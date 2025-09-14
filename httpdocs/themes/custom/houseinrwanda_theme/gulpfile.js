let gulp = require('gulp'),
  sass = require('gulp-sass')(require('sass')),
  sourcemaps = require('gulp-sourcemaps'),
  $ = require('gulp-load-plugins')(),
  cleanCss = require('gulp-clean-css'),
  rename = require('gulp-rename'),
  postcss = require('gulp-postcss'),
  autoprefixer = require('autoprefixer'),
  postcssInlineSvg = require('postcss-inline-svg'),
  browserSync = require('browser-sync').create(),
  pxtorem = require('postcss-pxtorem'),
  postcssProcessors = [
    postcssInlineSvg({
      removeFill: true,
      paths: ['./node_modules/bootstrap-icons/icons']
    }),
    pxtorem({
      propList: ['font', 'font-size', 'line-height', 'letter-spacing', '*margin*', '*padding*'],
      mediaQuery: true
    })
  ];

const paths = {
  scss: {
    src: './scss/style.scss',
    dest: './css',
    watch: './scss/**/*.scss',
    bootstrap: './node_modules/bootstrap/scss/bootstrap.scss',
  },
  js: {
    bootstrap: './node_modules/bootstrap/dist/js/bootstrap.min.js',
    bootstrap_map: './node_modules/bootstrap/dist/js/bootstrap.min.js.map',
    popper: './node_modules/@popperjs/core/dist/umd/popper.min.js',
    barrio: '../../contrib/bootstrap_barrio/js/barrio.js',
    dest: './js'
  },
  // Add TomSelect paths
  tomselect: {
    js: './node_modules/tom-select/dist/js/*.js',
    css: './node_modules/tom-select/dist/css/*.css',
    dest: './libraries/tom-select'
  }
}

// Compile sass into CSS & auto-inject into browsers
function styles() {
  return gulp.src([paths.scss.bootstrap, paths.scss.src])
    .pipe(sourcemaps.init())
    .pipe(sass({
      includePaths: [
        './node_modules/bootstrap/scss',
        '../../contrib/bootstrap_barrio/scss'
      ],
      quietDeps: true,
      silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'abs-percent', 'legacy-js-api']
    }).on('error', sass.logError))
    .pipe($.postcss(postcssProcessors))
    .pipe(postcss([autoprefixer({
      overrideBrowserslist: [
        'Chrome >= 35',
        'Firefox >= 38',
        'Edge >= 12',
        'Explorer >= 10',
        'iOS >= 8',
        'Safari >= 8',
        'Android 2.3',
        'Android >= 4',
        'Opera >= 12']
    })]))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(cleanCss())
    .pipe(rename({suffix: '.min'}))
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(browserSync.stream())
}

// Move the javascript files into our js folder
function js() {
  return gulp.src([paths.js.bootstrap, paths.js.bootstrap_map, paths.js.popper, paths.js.barrio])
    .pipe(gulp.dest(paths.js.dest))
    .pipe(browserSync.stream())
}

// Copy TomSelect assets to libraries directory
function tomSelect() {
  return gulp.src([paths.tomselect.js, paths.tomselect.css])
    .pipe(gulp.dest(paths.tomselect.dest))
    .pipe(browserSync.stream())
}

// Static Server + watching scss/html files
function serve() {
  browserSync.init({
    proxy: 'http://hir-backend.ddev.site',
    open: false
  })

  gulp.watch([paths.scss.watch, paths.scss.bootstrap], styles).on('change', browserSync.reload)
}

// Update build tasks to include tomselect
const build = gulp.series(styles, gulp.parallel(js, tomSelect, serve))
const buildWithoutServe = gulp.series(styles, gulp.parallel(js, tomSelect))

exports.styles = styles
exports.js = js
exports.tomselect = tomSelect
exports.serve = serve
exports.build = buildWithoutServe

exports.default = build
