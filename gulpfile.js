"use strict";
const path = require("path");
const gulp = require("gulp");
const csso = require("gulp-csso");
const eslint = require("gulp-eslint");
const htmlmin = require("gulp-htmlmin");
const newer = require("gulp-newer");
const symlink = require("gulp-symlink");
const uglify = require("gulp-uglify");

const SRC = "./client";
const DST = "./client_build";

gulp.task("lint", function() {
	return gulp
		.src([
			"**/*.js",
			"!client_build/**",
			"!client_build/node_modules/**",
			"!node_modules/**"
		])
		.pipe(eslint())
		.pipe(eslint.format());
});

gulp.task("api", function() {
	return gulp.src([
		path.join(SRC, "api/alumni/**"),
		path.join(SRC, "api/auth/**"),
		path.join(SRC, "api/blog/**"),
		path.join(SRC, "api/carts/**"),
		path.join(SRC, "api/charts/**"),
		path.join(SRC, "api/fishbowl/**"),
		path.join(SRC, "api/import/**"),
		path.join(SRC, "api/library/**"),
		path.join(SRC, "api/logbook/**"),
		path.join(SRC, "api/playlist/**"),
		path.join(SRC, "api/schedule/**"),
		path.join(SRC, "api/shows/**"),
		path.join(SRC, "api/showsub/**"),
		path.join(SRC, "api/staff/**"),
		path.join(SRC, "api/users/**"),
		path.join(SRC, "api/zautomate/**"),
		path.join(SRC, "api/connect.php"),
		path.join(SRC, "api/defs.php")
	], { base: SRC })
		.pipe(newer(DST))
		.pipe(gulp.dest(DST));
});

// could also just copy node_modules/
gulp.task("node_modules", function() {
	return gulp
		.src(path.join(SRC, "node_modules"), { base: SRC })
		.pipe(symlink(path.join(DST, "node_modules"), { force: true }));
});

gulp.task("public-html", function() {
	return gulp.src([
		path.join(SRC, "index.html"),
		path.join(SRC, "login/**/*.html"),
		path.join(SRC, "mobile/**/*.html"),
		path.join(SRC, "views/*.html")
	], { base: SRC })
		.pipe(newer(DST))
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
		.pipe(newer(DST))
		.pipe(csso())
		.pipe(gulp.dest(DST));
});

gulp.task("public-js", function() {
	return gulp.src([
		path.join(SRC, "js/**/*.js"),
		path.join(SRC, "mobile/**/*.js"),
		path.join(SRC, "login/**/*.js")
	], { base: SRC })
		.pipe(newer(DST))
		.pipe(uglify())
		.pipe(gulp.dest(DST));
});

gulp.task("public-images", function() {
	return gulp.src([
		path.join(SRC, "favicon.ico"),
		path.join(SRC, "images/**/*")
	], { base: SRC })
		.pipe(newer(DST))
		.pipe(gulp.dest(DST));
});

gulp.task("public-files", function() {
	return gulp.src([
		path.join(SRC, "files/**/*")
	], { base: SRC })
		.pipe(newer(DST))
		.pipe(gulp.dest(DST));
});

gulp.task("private-html", function() {
	return gulp.src([
		path.join(SRC, "*.html"),
		"!" + path.join(SRC, "index.html"),
		path.join(SRC, "dj/index.html"),
		path.join(SRC, "dj/views/*.html")
	], { base: SRC })
		.pipe(newer(DST))
		.pipe(htmlmin({
			removeComments: true,
			collapseWhitespace: true
		}))
		.pipe(gulp.dest(DST));
});

gulp.task("private-css", function() {
	return gulp.src([
		path.join(SRC, "dj/css/*.css")
	], { base: SRC })
		.pipe(newer(DST))
		.pipe(csso())
		.pipe(gulp.dest(DST));
});

gulp.task("private-js", function() {
	return gulp.src([
		path.join(SRC, "dj/js/**/*.js")
	], { base: SRC })
		.pipe(newer(DST))
		.pipe(uglify())
		.pipe(gulp.dest(DST));
});

const build = gulp.series([
	"api",
	"node_modules",
	"public-html",
	"public-css",
	"public-js",
	"public-images",
	"public-files",
	"private-html",
	"private-css",
	"private-js"
]);

gulp.task("build", build);

gulp.task("default", build);
