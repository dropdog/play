"use strict";
var gulp = require("gulp");
var sass = require("gulp-sass");
var sourcemaps = require("gulp-sourcemaps");
var autoprefixer = require("gulp-autoprefixer");
var importer = require("node-sass-globbing");
var plumber = require("gulp-plumber");
var browserSync = require("browser-sync").create();
var cssmin = require("gulp-cssmin");
var sassGlob = require("gulp-sass-glob");
// var postcss = require('gulp-postcss');
// var lost = require('lost');

var sass_config = {
  importer: importer,
  includePaths: [
    "/usr/lib/node_modules/breakpoint-sass/stylesheets/",
    "/usr/lib/node_modules/singularitygs/stylesheets/",
    "/usr/lib/node_modules/modularscale-sass/stylesheets/",
    "/usr/lib/node_modules/singularity-extras/stylesheets/",
    "/usr/lib/node_modules/compass-mixins/lib/"

    // "node_modules/breakpoint-sass/stylesheets/",
    // "node_modules/singularitygs/stylesheets/",
    // "node_modules/modularscale-sass/stylesheets/",
    // "node_modules/singularity-extras/stylesheets/",
    // "node_modules/compass-mixins/lib/",
    // "node_modules/susy/sass/susy/"
  ]
};

gulp.task("browser-sync", function() {
    browserSync.init({
        //injectChanges: true,
        proxy: "devel.backend.gr"
    });
    gulp.watch("sass/**/*.scss", ["sass"]);
    gulp.watch("css/*.css").on("change", browserSync.reload);
    gulp.watch("./js/rc_theme.js"/*, ["uglify"]*/).on("change", browserSync.reload);
});

gulp.task("sass", function () {
  return gulp
    .src("sass/rc_styles.scss")
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(sassGlob())
    .pipe(sass(sass_config).on("error", sass.logError))
    .pipe(autoprefixer({
      browsers: ["last 2 version"]
    }))
    .pipe(sourcemaps.write())
    // .pipe(cssmin())
    // .pipe(postcss([
    //   lost()
    // ]))
    .pipe(gulp.dest("css"));
});

gulp.task("default", [ "browser-sync"]);
