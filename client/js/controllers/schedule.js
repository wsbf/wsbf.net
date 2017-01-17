"use strict";

var scheduleModule = angular.module("app.schedule", [
	"ui.router",
	"app.database"
]);

scheduleModule.controller("ScheduleCtrl", ["$scope", "$stateParams", "db", function($scope, $stateParams, db) {
	$scope.days = db.getDefs("days");
	$scope.today = new Date();
	$scope.dayID = $stateParams.dayID || $scope.today.getDay();
	$scope.schedule = db.Schedule.get($scope.dayID);
}]);
