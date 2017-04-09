"use strict";

var nowPlayingModule = angular.module("app.now-playing", [
	"app.database"
]);

nowPlayingModule.controller("NowPlayingCtrl", ["$scope", "$interval", "db", function($scope, $interval, db) {
	$scope.track = {};

	var getNowPlaying = function() {
		db.Show.getNowPlaying()
			.then(function(track) {
				return db.getAlbumArt([track], 300);
			})
			.then(function(array) {
				$scope.track = array[0];
			});
	};

	// update now playing every 10 s
	getNowPlaying();

	var nowPlaying = $interval(getNowPlaying, 10000);

	$scope.$on("$destroy", function() {
		$interval.cancel(nowPlaying);
	});
}]);
