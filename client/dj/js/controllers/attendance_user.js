"use strict";

angular.module("wizbif.users").controller("AttendanceUserCtrl", ["$scope", "user", function($scope, user) {
	$scope.user = user;
}]);
