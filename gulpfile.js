var gulp = require('gulp');
var minifyCSS = require('gulp-csso');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');

gulp.task('form-css', function(){
  return gulp.src(['assets/css/form.css', 'pro/assets/css/form.css'])
    .pipe(concat('form.css'))
    .pipe(minifyCSS())
    .pipe(gulp.dest('assets/dist/css'))
});

gulp.task('admin-css', function(){
  return gulp.src(['assets/css/admin.css', 'pro/assets/css/admin.css'])
    .pipe(concat('admin.css'))
    .pipe(minifyCSS())
    .pipe(gulp.dest('assets/dist/css'))
});

gulp.task('forms-js', function(){
  return gulp.src(['assets/js/forms.js', 'pro/assets/js/forms.js'])
    .pipe(concat('forms.js'))
    .pipe(uglify())
    .pipe(gulp.dest('assets/dist/js'))
});

gulp.task('admin-js', function(){
  return gulp.src(['assets/js/admin.js', 'pro/assets/js/admin.js'])
    .pipe(concat('admin.js'))
    .pipe(uglify())
    .pipe(gulp.dest('assets/dist/js'))
});

gulp.task('default', [ 'form-css', 'admin-css', 'forms-js', 'admin-js' ]);