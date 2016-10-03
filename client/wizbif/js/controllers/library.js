"use strict";

var libraryModule = angular.module("wizbif.library", [
	"ngRoute",
	"wizbif.alert",
	"wizbif.database"
]);

libraryModule.controller("LibraryCtrl", ["$scope", "$routeParams", "$window", "$location", "alert", "db", function($scope, $routeParams, $window, $location, alert, db) {
	$scope.rotations = db.getDefs("rotations");
	$scope.general_genres = db.getDefs("general_genres");
	$scope.rotationID = $routeParams.rotationID;
	$scope.general_genreID = $routeParams.general_genreID;
	$scope.query = $routeParams.query;
	$scope.page = Number.parseInt($routeParams.page);
	$scope.albums = [];

	$scope.select = function(rotationID, general_genreID, query, page, admin) {
		var url_base = admin
			? "/library/admin"
			: "/library";
		var url;

		if ( general_genreID ) {
			url = url_base + "/r/" + rotationID + "/genre/" + general_genreID + "/page/" + page;
		}
		else if ( query ) {
			url = url_base + "/r/" + rotationID + "/search/" + query + "/page/" + page;
		}
		else {
			url = url_base + "/r/" + rotationID + "/page/" + page;
		}

		$location.url(url);
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

		db.Library.moveRotation(albums).then(function() {
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

	$scope.deleteAlbum = function(albums, index) {
		var album = albums[index];

		if ( confirm("Delete album " + album.albumID + "?") ) {
			db.Library.deleteAlbum(album.albumID)
				.then(function() {
					albums.splice(index, 1);
					alert.success("Album deleted.");
				}, function(res) {
					alert.error(res.data || res.statusText);
				});
		}
	};

	// initialize
	db.Library.getLibrary($scope.rotationID, $scope.general_genreID, $scope.query, $scope.page)
		.then(function(albums) {
			$scope.albums = albums;
		});
}]);

libraryModule.controller("LibraryAlbumCtrl", ["$scope", "$routeParams", "$location", "db", "alert", function($scope, $routeParams, $location, db, alert) {
	$scope.general_genres = db.getDefs("general_genres");
	$scope.airability = db.getDefs("airability");
	$scope.album = {};
	$scope.related_artists = [];

	var getAlbum = function() {
		db.Library.getAlbum($routeParams.albumID)
			.then(function(album) {
				$scope.album = album;

				return db.Library.getRelatedArtists(album.artist_name);
			})
			.then(function(related_artists) {
				$scope.related_artists = related_artists;
			});
	};

	$scope.save = function() {
		db.Library.saveAlbum($scope.album).then(function() {
			$location.url("/library/admin");
			alert.success("Album successfully saved.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	$scope.review = function() {
		db.Library.reviewAlbum($scope.album).then(function() {
			$location.url("/library");
			alert.success("Album successfully reviewed!");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	getAlbum();
}]);
