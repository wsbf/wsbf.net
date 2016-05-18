"use strict";

var logbookModule = angular.module("wizbif.logbook", [
    "wizbif.database"
]);

logbookModule.controller("LogbookCtrl", ["$scope", "$interval", "db", function($scope, $interval, db) {
	$scope.days = db.getDefs("days");
	$scope.showID = null;
	$scope.show = null;
	$scope.listenerCount = 0;

	// TODO: maybe move to $scope.show.playlist
	$scope.playlist = [];

	var getCurrentShow = function() {
		db.getLogbookCurrentShow().then(function(show) {
			$scope.show = show;
		});
	};

	var getListenerCount = function() {
		db.getLogbookListenerCount().then(function(count) {
			$scope.listenerCount = count;
		});
	};

	$scope.signOn = function(scheduleID) {
		db.signOn(scheduleID).then(function(showID) {
			// TODO
			$scope.showID = showID;
		});
	};

	$scope.signOff = function(showID) {
		db.signOff(showID).then(function() {
			// TODO
			$scope.showID = null;
		});
	};

	$scope.getAlbum = function(album_code) {
		db.getLogbookAlbum(album_code).then(function(album) {
			$scope.newTrack.lb_rotation = album.rotation;
			$scope.newTrack.lb_artist = album.artist_name;
			$scope.newTrack.lb_album = album.album_name;
			$scope.newTrack.lb_label = album.label;
		});
	};

	$scope.getTrack = function(album_code, disc_num, track_num) {
		db.getLogbookTrack(album_code, disc_num, track_num).then(function(track) {
			$scope.newTrack.lb_track_name = track.track_name;
			$scope.newTrack.airabilityID = track.airabilityID;
		});
	};

	$scope.addTrack = function(track) {
		// TODO
	};

	// initialize
	$scope.$parent.navEnabled = false;

	getCurrentShow();
	getListenerCount();

	$interval(getListenerCount, 5000);
}]);
