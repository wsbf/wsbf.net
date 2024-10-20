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
			$scope.apps = [
				{username: 'tmerzla', preferred_name: 'Thomas 2', points: '22', disputes: '0', review_count: '1'},
				{username: 'mgreenz@clemson.edu', preferred_name: 'Maxwell Greenzweig', points: '5', disputes: '0', review_count: '0'},
				{username: 'bturne7', preferred_name: 'Brian Turner', points: '4', disputes: '0', review_count: '2'},
				{username: 'mjlowe', preferred_name: 'Madeline Lowe', points: '4', disputes: '0', review_count: '1'},
				{username: 'allierpb', preferred_name: 'Allie Burg', points: '4', disputes: '1', review_count: '4'},
				{username: 'matiarco', preferred_name: 'Matthew Porzio', points: '4', disputes: '0', review_count: '2'},
				{username: 'reedtanner03', preferred_name: 'Reed Tanner', points: '4', disputes: '0', review_count: '0'},
				{username: 'juno', preferred_name: 'Juno Ham', points: '4', disputes: '1', review_count: '2'}
			]
			console.log(apps)
			$scope.calculateRanks();
		});
	};

	// sorting variables
	$scope.sortColumn = 'rank';  // default sort column
	$scope.reverseSort = false;   // default sort direction

	// sort by a specfic column
	$scope.sortBy = function(column) {
		$scope.reverseSort = ($scope.sortColumn === column) ? !$scope.reverseSort : false; // toggle sort direction
		$scope.sortColumn = column; // new sort column
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

		var currentRank = 1;
		
		for (var i = 0; i < $scope.apps.length; i++) {
			// Calculate adjusted points
			var adjustedPoints = $scope.apps[i].points - $scope.apps[i].disputes;
	
			// Check if this user has the same points as the previous user
			if (i > 0 && adjustedPoints === $scope.apps[i - 1].adjustedPoints) {
				// If same points as previous user, assign the same rank
				$scope.apps[i].rank = $scope.apps[i - 1].rank;
			} else {
				// Otherwise, assign the current rank
				$scope.apps[i].rank = currentRank;
			}
	
			// Store adjusted points
			$scope.apps[i].adjustedPoints = adjustedPoints;
	
			// Increment rank for the next user
			currentRank++;
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

fishbowlAdminModule.controller("FishbowlReviewCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
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
					$scope.get($scope.apps, _.findIndex($scope.apps, { username: $scope.username }));
				}, function(res) {
					alert.error(res.data || res.statusText);
				});
		}
	};

	// initialize
	$scope.get($scope.apps, _.findIndex($scope.apps, { username: $scope.username }));
}]);
