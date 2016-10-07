"use strict";

var staffModule = angular.module("wizbif.staff", [
	"wizbif.database"
]);

staffModule.controller("StaffCtrl", ["$scope", "db", function($scope, db) {
	$scope.positions = db.getDefs("positions");
	$scope.staff = [];

	var getStaff = function() {
		db.Staff.get().then(function(staff) {
			$scope.staff = staff;
		});
	};

	// initialize
	getStaff();
}]);
