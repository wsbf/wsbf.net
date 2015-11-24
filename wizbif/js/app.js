"use strict";
var app = angular.module("app", ["ngRoute", "ui.bootstrap"]);

app.config(["$routeProvider", function($routeProvider) {
	$routeProvider
		.when("/", { templateUrl: "views/home.html" })
		.when("/user", { templateUrl: "views/user.html" })
		.otherwise("/");
}]);

app.controller("MainCtrl", ["$scope", function($scope) {
	$scope.days = [
		"Sunday",
		"Monday",
		"Tuesday",
		"Wednesday",
		"Thursday",
		"Friday",
		"Saturday"
	];

	// temporary object for user
	$scope.user = {
		username: "Automation",
		first_name: "Otto",
		last_name: "Mation",
		preferred_name: "Otto Mation",
		statusID: 0,
		has_picture: 1,
		shows: [
			{
				scheduleID: -1,
				show_name: "The Best of WSBF",
				dayID: 1,
				start_time: "03:00:00",
				end_time: "05:00:00",
				show_typeID: 8,
				description: "It's really late."
			}
		]
	};
}]);
