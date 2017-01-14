"use strict";

var libraryModule = angular.module("wizbif.library", [
	"ngRoute",
	"wizbif.alert",
	"wizbif.database"
]);

libraryModule.controller("LibraryCtrl", ["$scope", "$routeParams", "$window", "$location", "$q", "alert", "db", function($scope, $routeParams, $window, $location, $q, alert, db) {
	$scope.rotations = db.getDefs("rotations");
	$scope.general_genres = db.getDefs("general_genres");

	$scope.rotationID = $routeParams.rotationID;
	$scope.general_genreID = $routeParams.general_genreID;
	$scope.query = $routeParams.query;
	$scope.page = Number.parseInt($routeParams.page);

	$scope.albums = [];
	$scope.selectedAll = false;

	$scope.go = function(rotationID, general_genreID, query, page, admin) {
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

	/**
	 * Select or unselect all albums.
	 *
	 * @param selectedAll
	 */
	$scope.setSelectedAll = function(selectedAll) {
		$scope.selectedAll = selectedAll;

		$scope.albums.forEach(function(album) {
			album.selected = selectedAll;
		});
	};

	/**
	 * Move all selected albums down one rotation.
	 *
	 * @param albums
	 */
	$scope.moveRotation = function(albums) {
		albums = albums
			.filter(function(a) {
				return a.selected;
			})
			.map(function(a) {
				return {
					albumID: a.albumID
				};
			});

		db.Library.moveRotation(albums).then(function() {
			$scope.albums = $scope.albums.filter(function(a) {
				return !a.selected;
			});

			alert.success("Rotation successfully moved.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	/**
	 * Print labels for all selected albums.
	 *
	 * @param albums
	 */
	$scope.printLabels = function(albums) {
		albums = albums
			.filter(function(a) {
				return a.selected;
			})
			.map(function(a) {
				return {
					albumID: a.albumID
				};
			});

		// create url and open in new tab
		var param = albums.map(function(a) {
			return "albums[]=" + a.albumID;
		}).join("&");

		$window.open("/api/library/print_labels.php?" + param);

		$scope.setSelectedAll(false);
	};

	/**
	 * Delete all selected albums.
	 *
	 * @param albums
	 */
	$scope.deleteAlbums = function(albums) {
		albums = albums.filter(function(album) {
			return album.selected;
		});

		if ( confirm("Delete " + albums.length + " albums?") ) {
			var promises = albums.map(function(album) {
				return db.Library.deleteAlbum(album.albumID);
			});

			$q.all(promises).then(function() {
				alert.success("Albums deleted.");
				$scope.go($scope.rotationID, $scope.general_genreID, $scope.query, $scope.page, true);
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
	$scope.rotations = db.getDefs("rotations");
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
