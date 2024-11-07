"use strict";

var mainModule = angular.module("wizbif.main", [
	"wizbif.alert",
	"wizbif.database"
]);

mainModule.constant("authSets", {
	reviewer: {
		key: "statusID",
		values: ["0", "1", "2", "4", "5"]
	},
	member: {
		key: "statusID",
		values: ["0", "1", "2", "4"]
	},
	seniorStaff: {
		key: "positionID",
		values: ["0", "1", "2", "3", "4", "5", "6", "7", "8", "13"]
	},
	musicDirector: {
		key: "positionID",
		values: ["0", "1", "2", "3", "8", "13", "14", "17", "18", "19", "20"]
	}
});

mainModule.controller("MainCtrl", ["$scope", "alert", "db", "authSets", function($scope, alert, db, authSets) {
	$scope.positions = db.getDefs("positions");
	$scope.user = {};
	$scope.auth = {};
	$scope.alert = alert;

	// const mockuser = {
	// 	positionID: "8",
	// 	statusID: "0"
	// }

	var getUser = function() {
		db.User.get().then(function(user) {
			$scope.user = user;
			// $scope.user = mockuser;

			$scope.auth = _.mapValues(authSets, function(set) {
				return set.values.indexOf(user[set.key]) !== -1;
				// return set.values.indexOf(mockuser[set.key]) !== -1;
			});
		}, function() {
			$scope.user = null;
		});
	};

	// initialize
	getUser();
}]);
