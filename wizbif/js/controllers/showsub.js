"use strict";

var showsubModule = angular.module("app.showsub", [
    "app.alert",
    "app.database"
]);

showsubModule.controller("ShowSubCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.requests = [];

	var getSubRequests = function() {
		db.getSubRequests().then(function(requests) {
			$scope.requests = requests;
		});
	};

	$scope.fill = function(index) {
		var req = $scope.requests[index];

		if ( confirm("Are you sure you want to sub this show on " + req.date + "? You will be held responsible if the show is missed.") ) {
			db.fillSubRequest(req.sub_requestID).then(function() {
				req.filled_by = $scope.$parent.user.preferred_name;
				alert.success("Sub request filled.");
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	$scope.remove = function(index) {
		var req = $scope.requests[index];

		if ( confirm("Are you sure you want to remove your sub request? If you request another one less than 24 hours before your show, you will be held responsible for missing your show.") ) {
			db.removeSubRequest(req.sub_requestID).then(function() {
				$scope.requests.splice(index, 1);
				alert.success("Sub request removed.");
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	// initialize
	getSubRequests();
}]);

// TODO: make this view a modal
showsubModule.controller("ShowSubRequestCtrl", ["$scope", "$location", "db", "alert", function($scope, $location, db, alert) {
	$scope.today = Date.now();
	$scope.days = db.getDefs("days");
	$scope.request = {};

	$scope.isValidDay = function(request) {
		return (_.find($scope.user.shows, { scheduleID: request.scheduleID }) || {}).dayID
			== request.date.getDay();
	};

	$scope.submit = function() {
		db.submitSubRequest($scope.request).then(function() {
			$location.url("/showsub");
			alert.success("Sub request successfully added.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};
}]);
