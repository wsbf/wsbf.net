"use strict";

var showListModule = angular.module("app.show-list", [
	"app.database"
]);

showListModule.controller("ShowListCtrl", ["$scope", "$routeParams", "db", function($scope, $routeParams, db) {
	$scope.page = Number.parseInt($routeParams.page);
	$scope.shows = db.getShows($routeParams.page, $routeParams.query);
}]);
