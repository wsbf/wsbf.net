"use strict";

var usersModule = angular.module("app.users", [
    "app.alert",
    "app.database"
]);

usersModule.controller("UsersCtrl", ["$scope", "db", function($scope, db) {
	$scope.teams = db.getDefs("teams");
	$scope.users = [];

	// initialize
	db.getUsers(false).then(function(users) {
		$scope.users = users;
	});
}]);

usersModule.controller("UsersAdminCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.statuses = db.getDefs("status");
	$scope.teams = db.getDefs("teams");
	$scope.users = [];
	$scope.statusID = "0";

	var getUsers = function() {
		db.getUsers(true).then(function(users) {
			$scope.users = users;
		});
	};

	$scope.save = function() {
		var users = $scope.users
			.filter(function(u) {
				return u.changed;
			})
			.map(function(u) {
				return {
					username: u.username,
					statusID: u.statusID,
					teamID: u.teamID
				};
			});

		db.updateUsers(users).then(function() {
			$scope.users.forEach(function(u) {
				u.changed = false;
			});
			alert.success("Users successfully updated.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	getUsers();
}]);

usersModule.controller("UsersReviewsCtrl", ["$scope", "db", function($scope, db) {
	$scope.currMonth = new Date(new Date().getUTCFullYear(), new Date().getUTCMonth());
	$scope.users = [];

	$scope.getChart = function(date1) {
		var date2 = angular.copy(date1);

		date2.setUTCMonth(date2.getUTCMonth() + 1);

		db.getAlbumReviewChart(date1.getTime(), date2.getTime())
			.then(function(users) {
				$scope.users = users;
			});
	};

	$scope.getPrevMonth = function() {
		$scope.date1.setUTCMonth($scope.date1.getUTCMonth() - 1);

		$scope.getChart($scope.date1);
	};

	$scope.hasNextMonth = function() {
		return $scope.date1 < $scope.currMonth;
	};

	$scope.getNextMonth = function() {
		$scope.date1.setUTCMonth($scope.date1.getUTCMonth() + 1);

		$scope.getChart($scope.date1);
	};

	$scope.getCurrMonth = function() {
		$scope.date1 = angular.copy($scope.currMonth);

		$scope.getChart($scope.date1);
	};

	// initialize
	$scope.getCurrMonth();
}]);
