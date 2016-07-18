"use strict";

var fishbowlAppModule = angular.module("wizbif.fishbowl-app", [
	"wizbif.alert",
	"wizbif.database"
]);

fishbowlAppModule.controller("FishbowlAppCtrl", ["$scope", "$location", "db", "alert", function($scope, $location, db, alert) {
	$scope.info = {
		missed: true
	};
	$scope.app = {};

	$scope.submit = function() {
		db.submitFishbowlApp($scope.app).then(function() {
			$location.url("/");
			alert.success("Fishbowl app submitted.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	db.getFishbowlInfo().then(function(info) {
		$scope.info = info;
		$scope.info.missed = info.deadline < Date.now();
	});
}]);
