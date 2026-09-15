"use strict";

var attendanceModule = angular.module("wizbif.attendance", [
	"wizbif.alert",
	"wizbif.database"
]);

attendanceModule.controller("AttendanceCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.events = [];
	$scope.passwords = {};

	var load = function() {
		db.Attendance.getEvents(false).then(function(events) {
			$scope.events = events;
		});
	};

	$scope.mark = function(event) {
		db.Attendance.mark(event.eventID, $scope.passwords[event.eventID]).then(function() {
			event.attending = true;
			$scope.passwords[event.eventID] = "";
			alert.success("Attendance recorded.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	load();
}]);

attendanceModule.controller("AttendanceAdminCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.events = [];
	$scope.event = { eventType: "full_staff", eventDate: new Date() };
	$scope.eventTypes = [
		{ value: "full_staff", label: "Full staff meeting" },
		{ value: "virtual_full_staff", label: "Virtual full staff meeting" },
		{ value: "committee", label: "Committee meeting" },
		{ value: "other", label: "Special/other" }
	];

	var load = function() {
		db.Attendance.getEvents(true).then(function(events) {
			events.forEach(function(event) {
				event.attendeesExpanded = false;
			});
			$scope.events = events;
		});
	};

	$scope.toggleAttendees = function(event) {
		event.attendeesExpanded = !event.attendeesExpanded;
	};

	$scope.deleteEvent = function(event) {
		if (!confirm("Are you sure you want to delete '" + event.name + "'?")) {
			return;
		}
		db.Attendance.deleteEvent(event.eventID).then(function() {
			alert.success("Event deleted.");
			load();
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	$scope.create = function() {
		db.Attendance.create($scope.event).then(function() {
			alert.success("Event created.");
			$scope.event = { eventType: "full_staff", eventDate: new Date() };
			load();
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	$scope.removeAttendance = function(attendee) {
		if (!confirm("Remove " + attendee.preferred_name + " from this event?")) {
			return;
		}
		db.Attendance.remove(attendee.attendanceID).then(function() {
			alert.success("Attendance removed.");
			load();
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	load();
}]);
