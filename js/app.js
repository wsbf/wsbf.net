"use strict";
var app = angular.module("app", ["ngRoute", "ui.bootstrap"]);

app.config(["$routeProvider", function($routeProvider) {
	$routeProvider
		.when("/", { templateUrl: "views/slider_main.html" })
		.when("/philosophy", { templateUrl: "views/philosophy.html" })
		.when("/staff", { templateUrl: "views/staff.html" })
		.when("/history", { templateUrl: "views/history.html" })
		.when("/schedule", { templateUrl: "views/schedule.html" })
		.when("/charts", { templateUrl: "views/charts.html" })
		.when("/equipment", { templateUrl: "views/equipment.html" })
		.when("/recording", { templateUrl: "views/recording.html" })
		.when("/booking", { templateUrl: "views/booking.html" })
		.when("/blog", { templateUrl: "views/blog.html" })
		.when("/join", { templateUrl: "views/join.html" })
		.when("/underwriting", { templateUrl: "views/underwriting.html" })
		.when("/psa", { templateUrl: "views/psa.html" })
		.when("/contact", { templateUrl: "views/contact.html" })
		.otherwise("/");
}]);

app.controller("PlaylistCtrl", ["$scope", "$http", function($scope, $http) {
	$scope.playlist = [];

	var getPlaylist = function() {
		$http.get("/api/playlist/current.php")
			.then(function(res) {
				$scope.playlist = res.data;
			});
	};

	getPlaylist();
}]);

app.controller("ScheduleCtrl", ["$scope", "$http", function($scope, $http) {
	$scope.today = Date.now();
	$scope.schedule = [];

	var getSchedule = function() {
		$http.get("/api/schedule/schedule.php")
			.then(function(res) {
				$scope.schedule = res.data;
			});
	};

	getSchedule();
}]);

app.controller("ChartCtrl", ["$scope", "$http", function($scope, $http) {
	$scope.charts = [];

	var getCharts = function() {
		$http.get("/api/charts/charts.php")
			.then(function(res) {
				$scope.charts = [];

				Object.keys(res.data).forEach(function(key) {
					$scope.charts.push(res.data[key]);
				});

				$scope.charts.sort(function(a, b) {
					return a.rank - b.rank;
				});
			});
	};

	getCharts();
}]);
