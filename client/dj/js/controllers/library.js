"use strict";

var libraryModule = angular.module("wizbif.library", [
	"ui.router",
	"wizbif.alert",
	"wizbif.database"
]);

libraryModule.controller("LibraryCtrl", ["$scope", "$q", "$state", "$window", "alert", "db", function($scope, $q, $state, $window, alert, db) {
	$scope.rotations = db.getDefs("rotations");
	$scope.general_genres = db.getDefs("general_genres");

	$scope.rotationID = $state.params.rotationID;
	$scope.general_genreID = $state.params.general_genreID;
	$scope.query = $state.params.query;
	$scope.page = Number.parseInt($state.params.page);

	$scope.albums = [];
	$scope.checkedOutAlbums = [];
	$scope.albumsPerPage = 25;
	
	$scope.selectedAll = false;

	$scope.go = function(rotationID, general_genreID, query, page, admin) {
		var state = admin
			? "library-admin"
			: "library";

		$state.go(state, {
			rotationID: rotationID,
			general_genreID: general_genreID,
			query: query,
			page: page
		});
	};

	/**
	 * Check out an album.
	 *
	 * @param albumID
	 */
	$scope.checkoutAlbum = function(albumID) {
		db.Library.checkoutAlbum(albumID).then(function() {
			$state.reload();
			alert.success("Album checked out.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};
	
	/**
	 * Return a checked-out album.
	 *
	 * @param albumID
	 */
	$scope.returnAlbum = function(albumID) {
		db.Library.returnAlbum(albumID).then(function() {
			$state.reload();
			alert.success("Album returned.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
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
				$state.reload();
				alert.success("Albums deleted.");
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	// load total albums per rotation page and calculate # of pages
	$scope.loadTotalPages = function() {
		db.Library.getTotalAlbums($scope.rotationID, $scope.general_genreID, $scope.query)
			.then(function(totalAlbums) {
				$scope.totalAlbums = totalAlbums;
				$scope.totalPages = Math.ceil(totalAlbums / $scope.albumsPerPage);

				// Create an array of page numbers for the dropdown
				$scope.pageNumbers = Array.from({ length: $scope.totalPages }, (_, i) => i);
			})
			.catch(function(error) {
				alert.error("Failed to load total albums: " + (error.data || error.statusText));
			});
	};

	// initialize array of checked out albums
	db.Library.getCheckedOutLibrary($scope.general_genreID, $scope.page)
		.then(function(checkedOutAlbums) {
			$scope.checkedOutAlbums = checkedOutAlbums;
		});

	// initialize array of library albums
	db.Library.getLibrary($scope.rotationID, $scope.general_genreID, $scope.query, $scope.page)
		.then(function(albums) {
			$scope.albums = albums;
		});
}]);

libraryModule.controller("LibraryAlbumCtrl", ["$scope", "$state", "db", "alert", function($scope, $state, db, alert) {
	$scope.rotations = db.getDefs("rotations");
	$scope.general_genres = db.getDefs("general_genres");
	$scope.airability = db.getDefs("airability");
	$scope.album = {};
	$scope.related_artists = [];

	var getAlbum = function(albumID) {
		db.Library.getAlbum(albumID)
			.then(function(album) {
				$scope.album = album;

				return db.Library.getRelatedArtists(album.artist_name);
			})
			.then(function(related_artists) {
				$scope.related_artists = related_artists;
			});
	};

	$scope.save = function(album) {
		db.Library.saveAlbum(album).then(function() {
			$state.go("library-admin");
			alert.success("Album successfully saved.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	$scope.review = function(album) {
		db.Library.reviewAlbum(album).then(function() {
			$state.go("library");
			alert.success("Album successfully reviewed!");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	getAlbum($state.params.albumID);
}]);
