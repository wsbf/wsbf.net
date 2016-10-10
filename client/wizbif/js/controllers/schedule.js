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

	$scope.getShow = function(scheduleID) {
		$uibModal.open({
			templateUrl: "views/schedule_show.html",
			controller: "ScheduleShowCtrl",
			scope: angular.extend($rootScope.$new(), {
				scheduleID: scheduleID
			})
		});
	};

	$scope.editShow = function(scheduleID, dayID, timeID) {
		$uibModal.open({
			templateUrl: "views/schedule_admin_edit.html",
			controller: "ScheduleEditCtrl",
			scope: angular.extend($rootScope.$new(), {
				scheduleID: scheduleID,
				dayID: dayID,
				timeID: timeID
			})
		}).result.then(getSchedule);
	};

	$scope.removeShow = function(scheduleID) {
		if ( confirm("Remove show " + scheduleID + " from the schedule?") ) {
			db.Schedule.removeShow(scheduleID).then(function() {
				getSchedule();
				alert.success("Show successfully removed.");
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	// initialize
	$q.all([
		$scope.days.$promise,
		$scope.show_times.$promise
	]).then(getSchedule);
}]);

scheduleModule.controller("ScheduleShowCtrl", ["$scope", "db", function($scope, db) {
	$scope.days = db.getDefs("days");
	$scope.show_times = db.getDefs("show_times");
	$scope.show_types = db.getDefs("show_types");
	$scope.show = db.Schedule.getShow($scope.scheduleID);
}]);

scheduleModule.controller("ScheduleEditCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.days = db.getDefs("days");
	$scope.show_times = db.getDefs("show_times");
	$scope.show_types = db.getDefs("show_types");

	$scope.searchUsers = function(term) {
		return db.Users.search(term)
			.then(function(users) {
				users.forEach(function(u) {
					u.name = (u.preferred_name === u.first_name + " " + u.last_name)
						? u.preferred_name
						: u.first_name + " " + u.last_name + " (" + u.preferred_name + ")";
				});

				return users;
			});
	};

	$scope.addHost = function() {
		$scope.show.hosts.push($scope.newHost);
		$scope.newHost = null;
	};

	$scope.save = function(show) {
		db.Schedule.saveShow(show).then(function() {
			alert.success("Show successfully saved.");
			$scope.$close();
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	$scope.show_times.$promise.then(function() {
		var startID = Number.parseInt($scope.timeID);
		var endID = (startID + 1) % $scope.show_times.length;

		if ( $scope.scheduleID ) {
			$scope.show = db.Schedule.getShow($scope.scheduleID);
		}
		else {
			$scope.show = {
				dayID: $scope.dayID,
				start_time: $scope.show_times[startID].show_time,
				end_time: $scope.show_times[endID].show_time,
				hosts: []
			};
		}
	});
}]);
