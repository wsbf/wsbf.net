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
		db.Fishbowl.submitApp(app).then(function() {
			$location.url("/");
			alert.success("Fishbowl app submitted.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	db.Fishbowl.getInfo().then(function(info) {
		$scope.info = info;
		$scope.info.missed = info.deadline < Date.now();
	});
}]);

fishbowlModule.controller("FishbowlLogCtrl", ["$scope", "$uibModal", "alert", "db", function($scope, $uibModal, alert, db) {
	$scope.fishbowl_log_types = db.getDefs("fishbowl_log_types");
	$scope.fishbowlLog = [];

	var getFishbowlLog = function() {
		db.Fishbowl.getLog().then(function(fishbowlLog) {
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

	$scope.deleteItem = function(fishbowlLogID) {
		console.log(fishbowlLogID);

		db.Fishbowl.deleteLogItem(fishbowlLogID)
			.then(function() {
				alert.success("Fishbowl item deleted.");
				getFishbowlLog();
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
	};

	// initialize
	getFishbowlLog();
}]);

fishbowlModule.controller("FishbowlLogItemCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.fishbowl_log_types = db.getDefs("fishbowl_log_types");
	$scope.item = {};

	$scope.submit = function(item) {
		db.Fishbowl.submitLog(item).then(function() {
			alert.success("Fishbowl item submitted.");
			$scope.$close();
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};
}]);
