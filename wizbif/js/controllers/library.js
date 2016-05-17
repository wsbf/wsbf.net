"use strict";

var libraryModule = angular.module("app.library", [
    "ngRoute",
    "app.alert",
    "app.database"
]);

libraryModule.controller("LibraryCtrl", ["$scope", "db", function($scope, db) {
	$scope.rotations = db.getDefs("rotations");
	$scope.general_genres = db.getDefs("general_genres");
	$scope.rotationID = "0";
	$scope.page = 0;
	$scope.albums = [];

	$scope.getLibrary = function(rotationID, page, term) {
		db.getLibrary(rotationID, $scope.general_genreID, page, term)
			.then(function(albums) {
				$scope.rotationID = rotationID;
				$scope.page = page;
				$scope.albums = albums;
			});
	};

	// initialize
	$scope.getLibrary($scope.rotationID, $scope.page);
}]);

libraryModule.controller("LibraryAdminCtrl", ["$scope", "$window", "db", "alert", function($scope, $window, db, alert) {
	$scope.rotations = db.getDefs("rotations");
	$scope.general_genres = db.getDefs("general_genres");
	$scope.rotationID = "7";
	$scope.page = 0;
	$scope.albums = [];

	$scope.getLibrary = function(rotationID, page) {
		db.getLibrary(rotationID, $scope.general_genreID, page)
			.then(function(albums) {
				$scope.rotationID = rotationID;
				$scope.page = page;
				$scope.albums = albums;
			});
	};

	$scope.moveRotation = function() {
		var albums = $scope.albums
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

	$scope.printLabels = function() {
		// collect album IDs that are checked
		var albums = $scope.albums
			.filter(function(a) {
				return a.label;
			})
			.map(function(a) {
				return a.albumID;
			});

		// create url and open in new tab
		var param = albums.map(function(a) {
			return "albums[]=" + a;
		}).join("&");

		$window.open("/api/library/print_labels.php?" + param);

		// clear checkboxes
		$scope.albums.forEach(function(a) {
			a.label = false;
		});
	};

	// initialize
	$scope.getLibrary($scope.rotationID, $scope.page);
}]);

libraryModule.controller("LibraryAlbumCtrl", ["$scope", "$routeParams", "$location", "db", "alert", function($scope, $routeParams, $location, db, alert) {
	$scope.general_genres = db.getDefs("general_genres");
	$scope.airability = db.getDefs("airability");
	$scope.album = {};
	$scope.similar_artists = [];

	var getAlbum = function() {
		db.getLibraryAlbum($routeParams.albumID)
			.then(function(album) {
				$scope.album = album;

				return db.getSimilarArtists(album.artist_name, 10);
			})
			.then(function(similar_artists) {
				$scope.similar_artists = similar_artists;
			});
	};

	$scope.save = function() {
		db.saveAlbum($scope.album).then(function(res) {
			$location.url("/library/admin");
			alert.success("Album successfully saved.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	$scope.review = function() {
		db.reviewAlbum($scope.album).then(function(res) {
			$location.url("/library");
			alert.success("Album successfully reviewed!");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	getAlbum();
}]);
