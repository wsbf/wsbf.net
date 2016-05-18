"use strict";

var scheduleModule = angular.module("app.schedule", [
    "app.database"
]);

scheduleModule.controller("ScheduleCtrl", ["$scope", "db", function($scope, db) {
	$scope.today = new Date();
	$scope.day = $scope.today.getDay();
	$scope.schedule = [];

	$scope.getSchedule = function(day) {
		db.getSchedule(day)
			.then(function(schedule) {
				$scope.day = day;
				$scope.schedule = schedule;
			});
	};

    // initialize
	$scope.getSchedule($scope.day);
}]);
