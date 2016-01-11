"use strict";
var gulp = require("gulp");
var eslint = require("gulp-eslint");

gulp.task("default", []);

gulp.task("lint", function() {
	return gulp.src(["**/*.js", "!bower_components/**", "!node_modules/**"])
		.pipe(eslint({
			envs: ["browser", "node"],
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
