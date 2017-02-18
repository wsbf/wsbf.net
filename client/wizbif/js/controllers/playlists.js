"use strict";

var playlistsModule = angular.module("wizbif.playlists", [
	"ui.router",
	"wizbif.alert",
	"wizbif.database"
]);

playlistsModule.controller("PlaylistsCtrl", ["$scope", "$state", "alert", "db", function($scope, $state, alert, db) {
	$scope.playlists = [];

	$scope.deletePlaylist = function(playlist) {
		if ( confirm("Delete playlist \"" + playlist.name + "\"?") ) {
			db.Playlist.delete(playlist.playlistID).then(function() {
				$state.reload();
				alert.success("Playlist deleted.");
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	// initialize
	db.Playlist.getPlaylists()
		.then(function(playlists) {
			$scope.playlists = playlists;
		});
}]);

playlistsModule.controller("PlaylistEditCtrl", ["$scope", "$state", "alert", "db", function($scope, $state, alert, db) {
	$scope.playlist = $state.params.playlistID
		? db.Playlist.get($state.params.playlistID)
		: { tracks: [] };

	$scope.getAlbum = function(album_code) {
		// TODO: stub
	};

	$scope.getTrack = function(album_code, disc_num, track_num) {
		// TODO: stub
	};

	$scope.addTrack = function(playlist, track) {
		track.rotation = track.rotation || "O";
		playlist.unshift(track);

		$scope.newTrack = {};
	};

	$scope.save = function(playlist) {
		db.Playlist.save(playlist).then(function() {
			$state.go("playlists");
			alert.success("Playlist saved.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};
}]);
