"use strict";

var userModule = angular.module("app.user", [
    "app.alert",
    "app.database"
]);

userModule.controller("UserCtrl", ["$scope", "$location", "db", "alert", function($scope, $location, db, alert) {
	$scope.days = db.getDefs("days");
	$scope.general_genres = db.getDefs("general_genres");

	$scope.save = function() {
		db.saveUser($scope.user).then(function(res) {
			$location.url("/");
			alert.success("Profile successfully saved.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};
}]);
