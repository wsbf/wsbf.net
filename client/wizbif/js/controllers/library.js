"use strict";

var libraryModule = angular.module("wizbif.library", [
	"ngRoute",
	"wizbif.alert",
	"wizbif.database"
]);

libraryModule.controller("LibraryCtrl", ["$scope", "$routeParams", "$window", "alert", "db", function($scope, $routeParams, $window, alert, db) {
	$scope.rotations = db.getDefs("rotations");
	$scope.general_genres = db.getDefs("general_genres");
	$scope.rotationID = $routeParams.rotationID;
	$scope.page = 0;
	$scope.albums = [];

	$scope.getLibrary = function(page, term) {
		db.getLibrary($scope.rotationID, $scope.general_genreID, page, term)
			.then(function(albums) {
				$scope.page = page;
				$scope.albums = albums;
			});
	};

	$scope.moveRotation = function(albums) {
		albums = albums
			.filter(function(a) {
				return a.rotationID !== $scope.rotationID;
			})
			.map(function(a) {
				return {
					albumID: a.albumID,
					rotationID: a.rotationID
				};
			});

		db.moveRotation(albums).then(function() {
			$scope.getLibrary($scope.rotationID);
			alert.success("Rotation successfully moved.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	$scope.printLabels = function(albums) {
		// collect album IDs that are checked
		var albumIDs = albums
			.filter(function(a) {
				return a.label;
			})
			.map(function(a) {
				return a.albumID;
			});

		// create url and open in new tab
		var param = albumIDs.map(function(a) {
			return "albums[]=" + a;
		}).join("&");

		$window.open("/api/library/print_labels.php?" + param);

		// clear checkboxes
		albums.forEach(function(a) {
			a.label = false;
		});
	};

	// initialize
	$scope.getLibrary($scope.page);
}]);

libraryModule.controller("LibraryAlbumCtrl", ["$scope", "$routeParams", "$location", "db", "alert", function($scope, $routeParams, $location, db, alert) {
	$scope.general_genres = db.getDefs("general_genres");
	$scope.airability = db.getDefs("airability");
	$scope.album = {};
	$scope.related_artists = [];

	var getAlbum = function() {
		db.getLibraryAlbum($routeParams.albumID)
			.then(function(album) {
				$scope.album = album;

				return db.getRelatedArtists(album.artist_name);
			})
			.then(function(related_artists) {
				$scope.related_artists = related_artists;
			});
	};

	$scope.save = function() {
		db.saveAlbum($scope.album).then(function() {
			$location.url("/library/admin");
			alert.success("Album successfully saved.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	$scope.review = function() {
		db.reviewAlbum($scope.album).then(function() {
			$location.url("/library");
			alert.success("Album successfully reviewed!");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	getAlbum();
}]);
