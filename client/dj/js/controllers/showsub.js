"use strict";

var showsubModule = angular.module("wizbif.showsub", [
	"ui.bootstrap",
	"wizbif.alert",
	"wizbif.database"
]);

showsubModule.controller("ShowSubCtrl", ["$scope", "$uibModal", "db", "alert", function($scope, $uibModal, db, alert) {
	$scope.show_times = db.getDefs("show_times");
	$scope.requests = [];

	var getSubRequests = function() {
		db.ShowSub.getSubRequests().then(function(requests) {
			$scope.requests = requests;
		});
	};

	$scope.addRequest = function() {
		$uibModal.open({
			templateUrl: "views/showsub_request.html",
			controller: "ShowSubRequestCtrl"
		}).result.then(getSubRequests);
	};

	$scope.fill = function(request, user) {
		if ( confirm("Are you sure you want to sub this show on " + request.date + "? You will be held responsible if the show is missed.") ) {
			db.ShowSub.fillSubRequest(request.sub_requestID).then(function() {
				request.filled_by = user.preferred_name;
				alert.success("Sub request filled.");
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	$scope.remove = function(requests, index) {
		var request = requests[index];

		if ( confirm("Are you sure you want to remove your sub request? If you request another one less than 24 hours before your show, you will be held responsible for missing your show.") ) {
			db.ShowSub.removeSubRequest(request.sub_requestID).then(function() {
				requests.splice(index, 1);
				alert.success("Sub request removed.");
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	// initialize
	getSubRequests();
}]);

showsubModule.controller("ShowSubRequestCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.today = Date.now();
	$scope.days = db.getDefs("days");
	$scope.show_times = db.getDefs("show_times");
	$scope.user = {};
	$scope.request = {};

	/**
	 * Check whether the date of a request is the
	 * same day of week as the show of the request.
	 *
	 * @param request
	 * @return true if request date is valid, false otherwise
	 */
	$scope.isValidDay = function(request) {
		var show = _.find($scope.user.shows, { scheduleID: request.scheduleID });

		return (show || {}).dayID == request.date.getDay();
	};

	$scope.submit = function(request) {
		db.ShowSub.submitSubRequest(request).then(function() {
			alert.success("Sub request successfully added.");
			$scope.$close();
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	db.User.get().then(function(user) {
		$scope.user = user;
	});
}]);

libraryModule.controller("ShowSubAdminCtrl", ["$scope", "db", function($scope, db) {
	$scope.show_times = db.getDefs("show_times");
	$scope.requests = [];

	var getSubRequests = function(page) {
		db.ShowSub.getSubRequestsAdmin(page).then(function(requests) {
			$scope.requests = requests;
		});
	};

	$scope.getNewer = function() {
		$scope.page--;
		getSubRequests($scope.page);
	};

	$scope.getOlder = function() {
		$scope.page++;
		getSubRequests($scope.page);
	};

	$scope.search = function(term) {
		getSubRequests(null, term);
	};

	// initialize
	getSubRequests();
}]);
