"use strict";

var usersModule = angular.module("wizbif.users", [
	"wizbif.alert",
	"wizbif.database"
]);

usersModule.controller("UsersCtrl", ["$scope", "db", function($scope, db) {
	$scope.teams = db.getDefs("teams");
	$scope.users = [];

	// initialize
	db.Users.getUsers(false).then(function(users) {
		$scope.users = users;
	});
}]);

usersModule.controller("UsersAdminCtrl", ["$scope", "$rootScope", "$uibModal", "db", function($scope, $rootScope, $uibModal, db) {
	$scope.statuses = db.getDefs("status");
	$scope.teams = db.getDefs("teams");
	$scope.users = [];
	$scope.statusID = "0";

	var getUsers = function() {
		db.Users.getUsers(true).then(function(users) {
			$scope.users = users;
		});
	};

	$scope.editUser = function(user) {
		$uibModal.open({
			templateUrl: "views/users_admin_edit.html",
			controller: "UsersAdminEditCtrl",
			scope: angular.extend($rootScope.$new(), {
				user: angular.copy(user)
			})
		}).result.then(getUsers);
	};

	// initialize
	getUsers();
}]);

usersModule.controller("UsersAdminEditCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.statuses = db.getDefs("status");
	$scope.teams = db.getDefs("teams");

	$scope.save = function(user) {
		db.Users.saveUser(user).then(function() {
			$scope.$close();
			alert.success("User successfully saved.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};
}]);

usersModule.controller("UsersReviewsCtrl", ["$scope", "db", function($scope, db) {
	$scope.currMonth = new Date(new Date().getUTCFullYear(), new Date().getUTCMonth());
	$scope.users = [];

	$scope.getChart = function(date1) {
		var date2 = angular.copy(date1);

		date2.setUTCMonth(date2.getUTCMonth() + 1);

		db.Users.getAlbumReviewChart(date1.getTime(), date2.getTime())
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
