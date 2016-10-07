"use strict";

var playlistModule = angular.module("app.playlist", [
	"app.database"
]);

playlistModule.controller("PlaylistCtrl", ["$scope", "$interval", "db", function($scope, $interval, db) {
	$scope.playlist = [];

	var getPlaylist = function() {
		db.Show.getPlaylist()
			.then(function(playlist) {
				return db.getAlbumArt(playlist, 64);
			})
			.then(function(playlist) {
				$scope.playlist = playlist;
			});
	};

	// update playlist every 60 s
	getPlaylist();
	$interval(getPlaylist, 60000);
}]);
