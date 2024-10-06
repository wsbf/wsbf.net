"use strict";

var fishbowlAdminModule = angular.module("wizbif.fishbowl-admin", [
	"wizbif.alert",
	"wizbif.database"
]);

fishbowlAdminModule.controller("FishbowlAdminCtrl", ["$scope", "$rootScope", "$uibModal", "db", "alert", function($scope, $rootScope, $uibModal, db, alert) {
	$scope.apps = [];
	$scope.bowls = [];

	var getFishbowlApps = function() {
		return db.Fishbowl.get().then(function(apps) {
			$scope.apps = apps;
		});
	};

	$scope.archiveFishbowl = function() {
		if ( confirm("Are you sure you want to archive the fishbowl?") ) {
			db.Fishbowl.archive().then(function() {
				getFishbowlApps();
				alert.success("Fishbowl archived.");
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	$scope.review = function(apps, username) {
		$uibModal.open({
			templateUrl: "views/fishbowl_review.html",
			controller: "FishbowlReviewCtrl",
			scope: angular.extend($rootScope.$new(), {
				apps: apps,
				username: username
			})
		});
	};

	$scope.rateFishbowlApps = function(apps) {
		if ( apps.some(function(a) { return !a.rating; }) ) {
			return;
		}

		apps = apps.map(function(app) {
			return {
				fishbowlID: app.fishbowlID,
				rating: app.rating
			};
		});

		db.Fishbowl.rateApps(apps).then(function() {
			getFishbowlApps();
			alert.success("Fishbowl ratings updated.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	/**
	 * Group the current list of fishbowl apps into bowls.
	 */
	$scope.getFishbowlResults = function(apps) {
		apps = apps.slice()
			.sort(function(app1, app2) {
				return app2.average - app1.average;
			});

		var NUM_BOWLS = 5;
		var bowlSize = Math.ceil(apps.length / NUM_BOWLS);
		var bowls = [];

		for ( var i = 0; i < NUM_BOWLS; i++ ) {
			bowls[i] = _.shuffle(apps.splice(0, bowlSize));
		}

		$scope.bowls = bowls;
	};

	// initialize
	getFishbowlApps();
}]);

fishbowlAdminModule.controller("FishbowlReviewCtrl", ["$scope", "db", function($scope, db) {
	$scope.fishbowl_log_types = db.getDefs("fishbowl_log_types");
	$scope.index = -1;
	$scope.app = {};

	$scope.get = function(apps, index) {
		var fishbowlID = apps[index].fishbowlID;

		db.Fishbowl.getApp(fishbowlID).then(function(app) {
			$scope.index = index;
			$scope.app = app;
		});
	};

	// initialize
	$scope.get($scope.apps, _.findIndex($scope.apps, { fishbowlID: $scope.fishbowlID }));
}]);
