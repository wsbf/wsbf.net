"use strict";

var fishbowlModule = angular.module("wizbif.fishbowl", [
	"wizbif.alert",
	"wizbif.database"
]);

fishbowlModule.controller("FishbowlAppCtrl", ["$scope", "$location", "db", "alert", function($scope, $location, db, alert) {
	$scope.info = {
		missed: true
	};
	$scope.app = {};

	$scope.submit = function(app) {
		db.submitFishbowlApp(app).then(function() {
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

fishbowlModule.controller("FishbowlLogCtrl", ["$scope", "$uibModal", "db", function($scope, $uibModal, db) {
	$scope.fishbowl_log_types = db.getDefs("fishbowl_log_types");
	$scope.fishbowlLog = [];

	var getFishbowlLog = function() {
		db.getFishbowlLog().then(function(fishbowlLog) {
			$scope.fishbowlLog = fishbowlLog;
		});
	};

	$scope.addItem = function() {
		$uibModal
			.open({
				templateUrl: "views/fishbowl_log_item.html",
				controller: "FishbowlLogItemCtrl"
			}).result
			.then(getFishbowlLog);
	};

	// initialize
	getFishbowlLog();
}]);

fishbowlModule.controller("FishbowlLogItemCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.fishbowl_log_types = db.getDefs("fishbowl_log_types");
	$scope.item = {};

	$scope.submit = function(item) {
		db.submitFishbowlLog(item).then(function() {
			alert.success("Fishbowl item submitted.");
			$scope.$close();
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};
}]);
