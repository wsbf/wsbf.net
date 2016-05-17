"use strict";

var chartModule = angular.module("app.chart", [
    "app.database"
]);

chartModule.controller("ChartCtrl", ["$scope", "db", function($scope, db) {
	var day = 24 * 3600 * 1000;
	var week = 7 * day;

	$scope.tracks = [];

	var getTracks = function() {
		var date1 = Date.now() - week - day;
		var date2 = Date.now() - day;

		db.getTopTracks(date1, date2).then(function(tracks) {
			$scope.tracks = tracks;
		});
	};

	// initialize
	getTracks();
}]);
