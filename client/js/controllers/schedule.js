"use strict";

var scheduleModule = angular.module("app.schedule", [
	"ngRoute",
	"app.database"
]);

scheduleModule.controller("ScheduleCtrl", ["$scope", "$routeParams", "db", function($scope, $routeParams, db) {
	$scope.today = new Date();
	$scope.dayID = $routeParams.dayID || $scope.today.getDay();
	$scope.schedule = db.Schedule.get($scope.dayID);
}]);
