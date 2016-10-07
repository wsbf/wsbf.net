"use strict";

var showModule = angular.module("app.show", [
	"ngRoute",
	"app.database"
]);

showModule.controller("ShowListCtrl", ["$scope", "$routeParams", "db", function($scope, $routeParams, db) {
	$scope.page = Number.parseInt($routeParams.page);
	$scope.shows = db.Show.getShows($routeParams.page, $routeParams.query);
}]);

showModule.controller("ShowCtrl", ["$scope", "$routeParams", "db", function($scope, $routeParams, db) {
	$scope.playlist = [];

	db.Show.getPlaylist($routeParams.showID)
		.then(function(playlist) {
			$scope.playlist = playlist;
		});
}]);
