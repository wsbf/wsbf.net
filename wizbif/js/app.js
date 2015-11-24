"use strict";
var app = angular.module("app", ["ngRoute", "ui.bootstrap"]);

app.config(["$routeProvider", function($routeProvider) {
	$routeProvider
		.when("/", { templateUrl: "views/home.html" })
		.when("/user", { templateUrl: "views/user.html" })
		.otherwise("/");
}]);

app.controller("MainCtrl", ["$scope", function($scope) {
	// temporary object for user
	$scope.user = {
		preferred_name: "Ben Shealy"
	};
}]);
