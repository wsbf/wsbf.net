"use strict";

var chartModule = angular.module("app.chart", [
	"app.database"
]);

chartModule.controller("AlbumChartCtrl", ["$scope", "db", function($scope, db) {
	var DAY = 24 * 3600 * 1000;
	var WEEK = 7 * DAY;

	$scope.today = Date.now();
	$scope.general_genres = db.getDefs("general_genres");
	$scope.count = 30;
	$scope.albums = [];

	$scope.getChart = function(date1, date2) {
		db.Charts.getTopAlbums(date1, date2, $scope.general_genreID)
			.then(function(albums) {
				$scope.albums = albums;
			});
	};

	$scope.getPrevWeek = function() {
		$scope.date1 -= WEEK;
		$scope.date2 -= WEEK;

		$scope.getChart($scope.date1, $scope.date2);
	};

	$scope.hasNextWeek = function() {
		return $scope.date2 + WEEK <= $scope.today;
	};

	$scope.getNextWeek = function() {
		$scope.date1 += WEEK;
		$scope.date2 += WEEK;

		$scope.getChart($scope.date1, $scope.date2);
	};

	$scope.getCurrWeek = function() {
		$scope.date1 = $scope.today - WEEK - DAY;
		$scope.date2 = $scope.today - DAY;

		$scope.getChart($scope.date1, $scope.date2);
	};

	// initialize
	$scope.getCurrWeek();
}]);

chartModule.controller("TrackChartCtrl", ["$scope", "db", function($scope, db) {
	var DAY = 24 * 3600 * 1000;
	var WEEK = 7 * DAY;

	$scope.today = Date.now();
	$scope.tracks = [];

	var getTracks = function(date1, date2) {
		db.Charts.getTopTracks(date1, date2).then(function(tracks) {
			$scope.tracks = tracks;
		});
	};

	var getCurrWeek = function() {
		$scope.date1 = $scope.today - WEEK - DAY;
		$scope.date2 = $scope.today - DAY;

		getTracks($scope.date1, $scope.date2);
	};

	// initialize
	getCurrWeek();
}]);

chartModule.controller("ChartWidgetCtrl", ["$scope", "db", function($scope, db) {
	var DAY = 24 * 3600 * 1000;
	var WEEK = 7 * DAY;

	var today = Date.now();
	var count = 10;
	$scope.albums = [];

	var getChart = function(date1, date2) {
		db.Charts.getTopAlbums(date1, date2)
			.then(function(albums) {
				albums = albums.slice(0, count);

				return db.getAlbumArt(albums, 64);
			})
			.then(function(albums) {
				$scope.albums = albums;
			});
	};

	var getCurrWeek = function() {
		var date1 = today - WEEK - DAY;
		var date2 = today - DAY;

		getChart(date1, date2);
	};

	// initialize
	getCurrWeek();
}]);
