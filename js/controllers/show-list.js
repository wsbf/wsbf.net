"use strict";

var showListModule = angular.module("app.show-list", [
    "app.database"
]);

showListModule.controller("ShowListCtrl", ["$scope", "db", function($scope, db) {
	$scope.page = 0;
	$scope.shows = [];

	var getShows = function(page, term) {
		db.getShows(page, term).then(function(shows) {
			$scope.shows = shows;
		});
	};

	$scope.getNewer = function() {
		$scope.page--;
		getShows($scope.page);
	};

	$scope.getOlder = function() {
		$scope.page++;
		getShows($scope.page);
	};

	$scope.search = function(term) {
		getShows(null, term);
	};

	// initialize
	getShows($scope.page);
}]);
