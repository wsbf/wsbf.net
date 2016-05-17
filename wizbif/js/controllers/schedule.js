"use strict";

var scheduleModule = angular.module("app.schedule", [
    "ui.bootstrap",
    "app.alert",
    "app.database"
]);

scheduleModule.controller("ScheduleCtrl", ["$scope", "$q", "$uibModal", "$rootScope", "db", "alert", function($scope, $q, $uibModal, $rootScope, db, alert) {
	$scope.days = db.getDefs("days");
	$scope.show_times = db.getDefs("show_times");
	$scope.schedule = [];

	var getSchedule = function() {
		$q.all($scope.days.map(function(day) {
			return db.getSchedule(day.dayID);
		})).then(function(daySchedules) {
			$scope.schedule = $scope.show_times.map(function(t) {
				return daySchedules.map(function(day) {
					return _.find(day, { start_time: t.show_time });
				});
			});
		});
	};

	$scope.removeSchedule = function() {
		if ( confirm("Are you sure you want to remove the entire show schedule?")
		  && confirm("So you're absolutely sure? I don't want to have to fix everything if you mess up.")
		  && prompt("Type 'STATHGAR' to show me that you're for real.") === "STATHGAR" ) {
			db.removeSchedule().then(function() {
				getSchedule();
				alert.success("Schedule successfully cleared.");
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	$scope.getShow = function(scheduleID) {
		$uibModal.open({
			templateUrl: "views/schedule_show.html",
			scope: angular.extend($rootScope.$new(), {
				show: db.getShow(scheduleID)
			})
		});
	};

	$scope.removeShow = function(scheduleID) {
		db.removeShow(scheduleID).then(function() {
			getSchedule();
			alert.success("Show successfully removed.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	$q.all([
		$scope.days.$promise,
		$scope.show_times.$promise
	]).then(getSchedule);
}]);

// TODO: make this view a modal
scheduleModule.controller("ScheduleAddCtrl", ["$scope", "$routeParams", "$location", "db", "alert", function($scope, $routeParams, $location, db, alert) {
	$scope.days = db.getDefs("days");
	$scope.show_times = db.getDefs("show_times");
	$scope.show_types = db.getDefs("show_types");

	$scope.searchUsers = function(term) {
		return db.searchUsers(term);
	};

	$scope.addHost = function() {
		$scope.show.hosts.push($scope.newHost)
		$scope.newHost = null;
	};

	$scope.save = function() {
		// transform show object from view to server
		var show = angular.copy($scope.show);

		show.hosts = show.hosts.map(function(h) {
			return h.username;
		});

		db.addShow(show).then(function() {
			$location.url("/schedule/admin");
			alert.success("Show successfully added.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	$scope.show_times.$promise.then(function() {
		var startID = Number.parseInt($routeParams.timeID);
		var endID = (startID + 1) % $scope.show_times.length;

		$scope.show = {
			dayID: $routeParams.dayID,
			start_time: $scope.show_times[startID].show_time,
			end_time: $scope.show_times[endID].show_time,
			hosts: []
		};
	});
}]);
