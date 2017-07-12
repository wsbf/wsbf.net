"use strict";

module.exports = function(config) {
	config.set({
		basePath: "../",
		files: [
			"client/bower_components/angular/angular.js",
			"client/bower_components/angular-animate/angular-animate.js",
			"client/bower_components/angular-resource/angular-resource.js",
			"client/bower_components/angular-sanitize/angular-sanitize.js",
			"client/bower_components/angular-mocks/angular-mocks.js",
			"client/bower_components/angular-bootstrap/ui-bootstrap-tpls.js",
			"client/bower_components/angular-ui-router/release/angular-ui-router.js",
			"client/bower_components/videogular/videogular.js",
			"client/bower_components/videogular-controls/vg-controls.js",
			"client/js/**/*.js",
			"client/wizbif/js/**/*.js",
			"test/public/**/*.js",
			"test/wizbif/**/*.js"
		],
		frameworks: ["jasmine"],
		browsers: ["Chrome"]
	});
};
