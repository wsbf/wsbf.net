"use strict";

var app = angular.module("app", [
	"ui.router",
	"app.now-playing"
]);

app.config(["$compileProvider", function($compileProvider) {
	$compileProvider.debugInfoEnabled(true);
}]);

app.config(["$stateProvider", "$urlRouterProvider", function($stateProvider, $urlRouterProvider) {
	$stateProvider
		.state("listen", {
			url: "/",
			templateUrl: "views/play.html"
		});
	$urlRouterProvider.otherwise("/");
}]);
