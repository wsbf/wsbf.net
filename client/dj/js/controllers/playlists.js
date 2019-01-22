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
	$scope.newTrack = { disc_num: 1 };

	$scope.getAlbum = function(album_code) {
		db.Playlist.getAlbum(album_code).then(function(album) {
			$scope.newTrack.albumID = album.albumID;
			$scope.newTrack.rotation = album.rotation;
			$scope.newTrack.artist_name = album.artist_name;
			$scope.newTrack.album_name = album.album_name;
			$scope.newTrack.label = album.label;
		});
	};

	$scope.getTrack = function(album_code, disc_num, track_num) {
		db.Playlist.getTrack(album_code, disc_num, track_num).then(function(track) {
			$scope.newTrack.track_name = track.track_name;
			$scope.newTrack.airabilityID = track.airabilityID;
		});
	};

	$scope.addTrack = function(playlist, track) {
		track.rotation = track.rotation || "O";
		playlist.unshift(track);

		$scope.newTrack = { disc_num: 1 };
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
