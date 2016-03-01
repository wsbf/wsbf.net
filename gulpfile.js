"use strict";
var path = require("path");
var gulp = require("gulp");
var changed = require("gulp-changed");
var csso = require("gulp-csso");
var eslint = require("gulp-eslint");
var htmlmin = require("gulp-htmlmin");
var imagemin = require("gulp-imagemin");
var symlink = require("gulp-symlink");
var uglify = require("gulp-uglify");

// TODO: add build tasks for wizbif/

var SRC = ".";
var DST = "../wsbf";

gulp.task("default", ["public"]);

gulp.task("lint", function() {
	return gulp.src(["**/*.js", "!bower_components/**", "!node_modules/**"])
		.pipe(eslint({
			envs: ["browser", "node", "jasmine"],
			globals: {
				"_": false,
				"angular": false
			},
			rules: {
				"camelcase": 0,
				"no-underscore-dangle": 0
			}
		}))
		.pipe(eslint.format());
});

gulp.task("public", [
	"bower-components",
	"public-api",
	"public-html",
	"public-css",
	"public-js",
	"public-images"
]);

// could also just copy bower_components/
gulp.task("bower-components", function() {
	return gulp.src("bower_components")
		.pipe(symlink(path.join(DST, "bower_components"), { force: true }));
});

gulp.task("public-api", function() {
	return gulp.src([
		path.join(SRC, "api/connect.php"),
		path.join(SRC, "api/defs.php"),
		path.join(SRC, "api/blog/preview.php"),
		path.join(SRC, "api/charts/albums.php"),
		path.join(SRC, "api/schedule/schedule.php"),
		path.join(SRC, "api/shows/functions.php"),
		path.join(SRC, "api/shows/now.php"),
		path.join(SRC, "api/shows/playlist.php"),
		path.join(SRC, "api/shows/shows.php")
	], { base: SRC })
		.pipe(changed(DST))
		.pipe(gulp.dest(DST));
});

gulp.task("public-html", function() {
	return gulp.src([
		path.join(SRC, "index.html"),
		path.join(SRC, "views/*.html")
	], { base: SRC })
		.pipe(changed(DST))
		.pipe(htmlmin({
			removeComments: true,
			collapseWhitespace: true
		}))
		.pipe(gulp.dest(DST));
});

gulp.task("public-css", function() {
	return gulp.src([
		path.join(SRC, "css/*.css")
	], { base: SRC })
		.pipe(changed(DST))
		.pipe(csso())
		.pipe(gulp.dest(DST));
});

gulp.task("public-js", function() {
	return gulp.src([
		path.join(SRC, "js/*.js")
	], { base: SRC })
		.pipe(changed(DST))
		.pipe(uglify())
		.pipe(gulp.dest(DST));
});

gulp.task("public-images", function() {
	return gulp.src([
		path.join(SRC, "favicon.ico"),
		path.join(SRC, "images/**/*")
	], { base: SRC })
		.pipe(changed(DST))
		.pipe(imagemin())
		.pipe(gulp.dest(DST));
});
