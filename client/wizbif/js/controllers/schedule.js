"use strict";

var scheduleModule = angular.module("wizbif.schedule", [
	"ui.bootstrap",
	"wizbif.alert",
	"wizbif.database"
]);

scheduleModule.controller("ScheduleCtrl", ["$scope", "$q", "$uibModal", "$rootScope", "db", "alert", function($scope, $q, $uibModal, $rootScope, db, alert) {
	$scope.days = db.getDefs("days");
	$scope.show_times = db.getDefs("show_times");
	$scope.schedule = [];

	var getSchedule = function() {
		$q.all($scope.days.map(function(day) {
			return db.Schedule.get(day.dayID);
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
			db.Schedule.clear().then(function() {
				getSchedule();
				alert.success("Schedule successfully cleared.");
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	$scope.addShow = function(dayID, timeID) {
		$uibModal.open({
			templateUrl: "views/schedule_admin_add.html",
			controller: "ScheduleAddCtrl",
			scope: angular.extend($rootScope.$new(), {
				dayID: dayID,
				timeID: timeID
			})
		}).result.then(getSchedule);
	};

	$scope.getShow = function(scheduleID) {
		$uibModal.open({
			templateUrl: "views/schedule_show.html",
			scope: angular.extend($rootScope.$new(), {
				show: db.Schedule.getShow(scheduleID)
			})
		});
	};

	$scope.removeShow = function(scheduleID) {
		db.Schedule.removeShow(scheduleID).then(function() {
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

scheduleModule.controller("ScheduleAddCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.days = db.getDefs("days");
	$scope.show_times = db.getDefs("show_times");
	$scope.show_types = db.getDefs("show_types");

	$scope.searchUsers = function(term) {
		return db.Users.search(term);
	};

	$scope.addHost = function() {
		$scope.show.hosts.push($scope.newHost);
		$scope.newHost = null;
	};

	$scope.save = function(show) {
		// transform show object from view to server
		show = angular.copy(show);

		show.hosts = show.hosts.map(function(h) {
			return h.username;
		});

		db.Schedule.addShow(show).then(function() {
			alert.success("Show successfully added.");
			$scope.$close();
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	$scope.show_times.$promise.then(function() {
		var startID = Number.parseInt($scope.timeID);
		var endID = (startID + 1) % $scope.show_times.length;

		$scope.show = {
			dayID: $scope.dayID,
			start_time: $scope.show_times[startID].show_time,
			end_time: $scope.show_times[endID].show_time,
			hosts: []
		};
	});
}]);
