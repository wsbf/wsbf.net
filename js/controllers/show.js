"use strict";

var showModule = angular.module("app.show", [
	"ngRoute",
	"app.database"
]);

showModule.controller("ShowCtrl", ["$scope", "$routeParams", "db", function($scope, $routeParams, db) {
	$scope.playlist = [];

	db.getPlaylist($routeParams.showID)
		.then(function(playlist) {
			$scope.playlist = playlist;
		});
}]);
