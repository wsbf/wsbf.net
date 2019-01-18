"use strict";

var scheduleModule = angular.module("app.schedule", [
	"ui.router",
	"app.database"
]);

scheduleModule.controller("ScheduleCtrl", ["$scope", "$stateParams", "db", function($scope, $stateParams, db) {
	$scope.days = db.getDefs("days");
	$scope.show_times = db.getDefs("show_times");

	$scope.show_times.$promise.then(function(result) {
			result.forEach(function(element) {
					let converted = new Date("1 JAN 1970 " + element.show_time + " EST");
					element.show_time = converted.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + " ET";
			});
	});

	$scope.today = new Date();
	$scope.dayID = $stateParams.dayID || $scope.today.getDay();
	$scope.schedule = db.Schedule.get($scope.dayID);
}]);
