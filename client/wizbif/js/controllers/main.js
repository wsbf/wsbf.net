"use strict";

var mainModule = angular.module("wizbif.main", [
	"wizbif.alert",
	"wizbif.database"
]);

mainModule.controller("MainCtrl", ["$scope", "alert", "db", function($scope, alert, db) {
	// temporary status/position sets
	var statusSets = {
		reviewer: ["0", "1", "2", "4", "5"],
		member: ["0", "1", "2", "4"]
	};

	var positionSets = {
		seniorStaff: [0, 1, 2, 3, 4, 5, 6, 7, 8],
		musicDirector: [0, 1, 2, 3, 8, 13, 14, 17, 18, 19, 20]
	};

	$scope.positions = db.getDefs("positions");
	$scope.user = {};
	$scope.check = {};
	$scope.alert = alert;

	var getUser = function() {
		db.User.get().then(function(user) {
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
