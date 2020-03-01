const gulp = require('gulp');
const zip = require('gulp-zip');
const rename = require('gulp-rename');

const src = [
    './**/!(athena.zip)', 
    '!**/node_modules/**'
]

gulp.task('zip', () =>
    gulp.src(src)
        .pipe(zip('athena.zip'))
        .pipe(gulp.dest('./')));

gulp.task('watch', () => {
    gulp.watch(src, gulp.series('zip'));
})

gulp.task('default', gulp.parallel('zip', 'watch'));

