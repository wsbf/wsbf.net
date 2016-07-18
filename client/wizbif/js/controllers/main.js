"use strict";

var mainModule = angular.module("wizbif.main", [
	"wizbif.alert",
	"wizbif.database"
]);

mainModule.controller("MainCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	// temporary status/position sets
	var statusSets = {
		editProfile: ["0", "1", "2", "4"],
		reviewer: ["0", "1", "5"]
	};

	var positionSets = {
		seniorStaff: [0, 1, 2, 3, 4, 5, 6, 7, 8],
		musicDirector: [0, 1, 2, 3, 8, 13, 14, 17, 18, 19, 20],
		engineer: [1, 5, 6, 8, 10]
	};

	$scope.user = {};
	$scope.check = {};
	$scope.alert = alert;

	var getUser = function() {
		db.getUser().then(function(user) {
			$scope.user = user;

			_.assign($scope.check, _.mapValues(statusSets, function(set) {
				return set.indexOf(user.statusID) !== -1;
			}), _.mapValues(positionSets, function(set) {
				return set.indexOf(user.positionID) !== -1;
			}));
		}, function() {
			$scope.user = null;
		});
	};

	// initialize
	getUser();
}]);
