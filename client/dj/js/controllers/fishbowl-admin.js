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
			console.log(apps)
			$scope.calculateRanks();
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
		console.log("apps: ", apps);  // Log the entire apps object
		console.log("username: ", username);  // Log the passed username
	
		if (!username) {
			alert.error("Username is missing or invalid.");
			return;
		}
		$uibModal.open({
			templateUrl: "views/fishbowl_review.html",
			controller: "FishbowlReviewCtrl",
			scope: angular.extend($rootScope.$new(), {
				apps: apps,
				username: username
			})
		});
	};

	// no longer using this
	// $scope.rateFishbowlApps = function(apps) {
	// 	if ( apps.some(function(a) { return !a.rating; }) ) {
	// 		return;
	// 	}

	// 	apps = apps.map(function(app) {
	// 		return {
	// 			fishbowlID: app.fishbowlID,
	// 			rating: app.rating
	// 		};
	// 	});

	// 	db.Fishbowl.rateApps(apps).then(function() {
	// 		getFishbowlApps();
	// 		alert.success("Fishbowl ratings updated.");
	// 	}, function(res) {
	// 		alert.error(res.data || res.statusText);
	// 	});
	// };

	// Function to calculate ranks
	$scope.calculateRanks = function() {
		// Sort users by points in descending order, considering disputes
		$scope.apps.sort(function(a, b) {
			// Calculate points minus disputes
			var aPoints = a.points - a.disputed;
			var bPoints = b.points - b.disputed;
			return bPoints - aPoints;
		});

		var currentRank = 0;
		var previousPoints = null;

		for (var i = 0; i < $scope.apps.length; i++) {
			// Calculate the adjusted points
			var adjustedPoints = $scope.apps[i].points - $scope.apps[i].disputed;

			if (previousPoints !== adjustedPoints) {
				currentRank++;
				previousPoints = adjustedPoints;
			}

			$scope.apps[i].rank = currentRank;
			$scope.apps[i].adjustedPoints = adjustedPoints;
		}
	};

	// not in use right now, might rework it soon (as of 10/17/2024)
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

fishbowlAdminModule.controller("FishbowlReviewCtrl", ["$scope", "db", function($scope, db, alert) {
	$scope.fishbowl_log_types = db.getDefs("fishbowl_log_types");
	$scope.index = -1;
	$scope.app = {};

	$scope.get = function(apps, index) {
		var username = apps[index].username;

		db.Fishbowl.getApp(username).then(function(app) {
			$scope.index = index;
			$scope.app = app;
		});
	};

	// as an admin, override a user's fishbowl log and mark an item as dispute
	$scope.disputeItem = function(fishbowl_logID) {
		var disputeDescription = prompt("Enter a description of the dispute:");

		if (disputeDescription !== null) {
			var disputeData = {
				fishbowl_logID: fishbowl_logID,
				dispute_description: disputeDescription
			};
			console.log(disputeData)
			db.Fishbowl.disputeLogItem(disputeData)
				.then(function() {
					alert.success("Fishbowl item marked as disputed.");
				}, function(res) {
					alert.error(res.data || res.statusText);
				});
		}
	};

	// initialize
	$scope.get($scope.apps, _.findIndex($scope.apps, { username: $scope.username }));
}]);
