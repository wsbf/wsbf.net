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
		.state("dashboard", {
			url: "/",
			templateUrl: "views/dashboard.html"
		});
	$urlRouterProvider.otherwise("/");
}]);
