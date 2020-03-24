var gulp = require('gulp');
var minifyCSS = require('gulp-csso');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');

var formCSS = ['assets/css/form.css', 'pro/assets/css/form.css'];
var adminCSS = ['assets/css/admin.css', 'pro/assets/css/admin.css'];
var formJS = ['assets/js/forms.js', 'pro/assets/js/forms.js'];
var adminJS = ['assets/js/admin.js', 'pro/assets/js/admin.js'];
var all = [].concat(formCSS, adminCSS, formJS, adminJS);

gulp.task('form-css', function(){
  return gulp.src(formCSS, { allowEmpty: true })
    .pipe(concat('form.css'))
    .pipe(minifyCSS())
    .pipe(gulp.dest('assets/dist/css'))
});

gulp.task('admin-css', function(){
  return gulp.src(adminCSS, { allowEmpty: true })
    .pipe(concat('admin.css'))
    .pipe(minifyCSS())
    .pipe(gulp.dest('assets/dist/css'))
});

gulp.task('forms-js', function(){
  return gulp.src(formJS, { allowEmpty: true })
    .pipe(concat('forms.js'))
    .pipe(uglify())
    .pipe(gulp.dest('assets/dist/js'))
});

gulp.task('admin-js', function(){
  return gulp.src(adminJS, { allowEmpty: true })
    .pipe(concat('admin.js'))
    .pipe(uglify())
    .pipe(gulp.dest('assets/dist/js'))
});

gulp.task('watch', function () {
    // Endless stream mode
    return gulp.watch(all, gulp.series('build'));
});

gulp.task('build', gulp.series('form-css', 'admin-css', 'forms-js', 'admin-js'));