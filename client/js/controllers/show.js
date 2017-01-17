"use strict";

var showModule = angular.module("app.show", [
	"ui.router",
	"app.database"
]);

showModule.controller("ShowListCtrl", ["$scope", "$stateParams", "db", function($scope, $stateParams, db) {
	$scope.page = Number.parseInt($stateParams.page);
	$scope.shows = db.Show.getShows($stateParams.page, $stateParams.query);
}]);

showModule.controller("ShowCtrl", ["$scope", "$stateParams", "db", function($scope, $stateParams, db) {
	$scope.playlist = [];

	db.Show.getPlaylist($stateParams.showID)
		.then(function(playlist) {
			$scope.playlist = playlist;
		});
}]);
