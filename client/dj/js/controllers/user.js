"use strict";

var userModule = angular.module("wizbif.user", [
	"wizbif.alert",
	"wizbif.database"
]);

userModule.controller("UserCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.days = db.getDefs("days");
	$scope.general_genres = db.getDefs("general_genres");
	$scope.show_times = db.getDefs("show_times");
	$scope.statuses = db.getDefs("status");

	$scope.save = function() {
		db.User.save($scope.user).then(function() {
			alert.success("Profile successfully saved.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};
}]);
