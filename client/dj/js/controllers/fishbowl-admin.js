"use strict";

var fishbowlAdminModule = angular.module("wizbif.fishbowl-admin", [
	"wizbif.alert",
	"wizbif.database"
]);

fishbowlAdminModule.controller("FishbowlAdminCtrl", ["$scope", "$rootScope", "$uibModal", "db", "alert", function($scope, $rootScope, $uibModal, db, alert) {
	$scope.teams = db.getDefs("teams");

	$scope.houseTotals = {
		1: 0,
		2: 0,
		3: 0,
		4: 0
	};
	
	$scope.currentStartDate = '';
	$scope.currentEndDate = '';
	$scope.apps = [];
	$scope.bowls = [];

	var getFishbowlApps = function() {
		return db.Fishbowl.get().then(function(apps) {
			$scope.apps = apps;
			$scope.calculateRanks();
			$scope.calculateHousetotals();
		});
	};

	var getSemesterDates = function() {
		return db.Fishbowl.getDateRange().then(function(dates) {
			$scope.currentStartDate = dates['start_date']['date'].split(' ')[0];
			$scope.currentEndDate = dates['end_date']['date'].split(' ')[0];
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

	$scope.showDateInputs = function() {
		document.getElementById("dateInputs").classList.remove("hidden"); // unhide the date inputs
	}

	$scope.setDateRange = function(startDate_input, endDate_input) {
		if ( confirm("Are you sure you want to change the semester date range?") ) {
			var dates = {
				startDate: startDate_input, 
				endDate: endDate_input
			};
			db.Fishbowl.changeDates(dates).then(function() {
				getSemesterDates();
				getFishbowlApps();
				document.getElementById("dateInputs").classList.add("hidden");
				alert.success("Semester dates changed.");
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	// show a user's fishbowl summary 
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
		// initial sort function by points in descending order, considering disputes and reviews
		$scope.apps.sort(function(a, b) {
			var aReviewBonus = (Number(a.review_count) > 1 ? Number(a.review_count) : 0);
			var bReviewBonus = (Number(b.review_count) > 1 ? Number(b.review_count) : 0);
			var aPoints = Number(a.points) - Number(a.dispute_count) + Number(aReviewBonus);
			var bPoints = Number(b.points) - Number(b.dispute_count) + Number(bReviewBonus);
			return bPoints - aPoints;
		});

		var currentRank = 1;
		
		// Calculate adjusted points
		for (var i = 0; i < $scope.apps.length; i++) {
			// only count the reviews beyond the required 1
			var reviewBonus = Number($scope.apps[i].review_count) > 1 ? Number($scope.apps[i].review_count) - 1 : 0;
			var adjustedPoints = Number($scope.apps[i].points) - Number($scope.apps[i].dispute_count) + reviewBonus;
	
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

	// get a breakdown of points based on houses
	$scope.calculateHousetotals = function() {
		for (var i = 0; i < $scope.apps.length; i++) {
			$scope.houseTotals[$scope.apps[i].teamID] += $scope.apps[i].adjustedPoints;
		}
	}

	// not in use right now, might rework it soon (as of 10/17/2024)
	// $scope.getFishbowlResults = function(apps) {
	// 	apps = apps.slice()
	// 		.sort(function(app1, app2) {
	// 			return app2.average - app1.average;
	// 		});

	// 	var NUM_BOWLS = 5;
	// 	var bowlSize = Math.ceil(apps.length / NUM_BOWLS);
	// 	var bowls = [];

	// 	for ( var i = 0; i < NUM_BOWLS; i++ ) {
	// 		bowls[i] = _.shuffle(apps.splice(0, bowlSize));
	// 	}

	// 	$scope.bowls = bowls;
	// };

	// initialize
	getFishbowlApps();
	getSemesterDates();
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
				action: 'dispute', // set mode to dispute
				fishbowl_logID: fishbowl_logID,
				dispute_description: disputeDescription
			};
			db.Fishbowl.disputeLogItem(disputeData)
				.then(function() {
					alert.success("Fishbowl item disputed.");
					$scope.get($scope.apps, _.findIndex($scope.apps, { username: $scope.username }));
				}, function(res) {
					alert.error(res.data || res.statusText);
				});
		}
	};

	$scope.undisputeItem = function(fishbowl_logID) {
		var confirmUndispute = confirm("Are you sure you want to remove this dispute?");
	
		if (confirmUndispute) {
			var disputeData = {
				action: 'undispute',  // sets mode to undispute
				fishbowl_logID: fishbowl_logID
			};
	
			db.Fishbowl.disputeLogItem(disputeData)
				.then(function() {
					alert.success("Fishbowl item undisputed.");
					$scope.get($scope.apps, _.findIndex($scope.apps, { username: $scope.username }));
				}, function(res) {
					alert.error(res.data || res.statusText);
				});
		}
	};

	// initialize
	$scope.get($scope.apps, _.findIndex($scope.apps, { username: $scope.username }));
}]);
