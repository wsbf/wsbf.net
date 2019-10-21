"use strict";

module.exports = function(config) {
	config.set({
		basePath: "../",
		files: [
			"client/node_modules/angular/angular.js",
			"client/node_modules/angular-animate/angular-animate.js",
			"client/node_modules/angular-resource/angular-resource.js",
			"client/node_modules/angular-sanitize/angular-sanitize.js",
			"client/node_modules/angular-mocks/angular-mocks.js",
			"client/node_modules/angular-ui-bootstrap/ui-bootstrap-tpls.js",
			"client/node_modules/@uirouter/angularjs/release/angular-ui-router.js",
			"client/node_modules/jquery/dist/jquery.js",
			"client/node_modules/videogular/videogular.js",
			"client/node_modules/videogular-controls/vg-controls.js",
			"client/js/**/*.js",
			"client/dj/js/**/*.js",
			"test/public/**/*.js",
			"test/dj/**/*.js"
		],
		frameworks: ["jasmine"],
		browsers: ["ChromeHeadless"]
	});
};
