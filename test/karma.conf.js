"use strict";

module.exports = function(config) {
	config.set({
		basePath: "../",
		files: [
			"bower_components/angular/angular.js",
			"bower_components/angular-animate/angular-animate.js",
			"bower_components/angular-resource/angular-resource.js",
			"bower_components/angular-route/angular-route.js",
			"bower_components/angular-sanitize/angular-sanitize.js",
			"bower_components/angular-mocks/angular-mocks.js",
			"bower_components/angular-bootstrap/ui-bootstrap-tpls.js",
			"js/*.js",
			"wizbif/js/*.js",
			"test/client/unit/*.js"
		],
		frameworks: ["jasmine"],
		browsers: ["Chrome"]
	});
};
