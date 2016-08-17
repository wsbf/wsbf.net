"use strict";

var databaseModule = angular.module("wizbif.database", [
	"ngResource"
]);

/**
 * The Database service provides an interface to server-side data.
 * The entire server API is implemented here so that every controller
 * uses this service instead of using HTTP requests directly.
 *
 * This service uses Promises, which are an abstraction of callbacks
 * that make asynchronous programming a little better.
 *
 * @param $http	  service in module ng
 * @param $resource  service in module ngResource
 */
databaseModule.service("db", ["$http", "$q", "$resource", function($http, $q, $resource) {

	var Defs = $resource("/api/defs.php", {}, {
		get: { method: "GET", isArray: true, cache: true }
	});

	/**
	 * Get a definitions table.
	 *
	 * @param tableName  table name
	 * @return table array
	 */
	this.getDefs = function(tableName) {
		return Defs.get({ table: tableName });
	};

	/**
	 * Get the parameters for the current fishbowl application.
	 *
	 * @return Promise of fishbowl app info
	 */
	this.getFishbowlInfo = function() {
		return $http.get("/api/fishbowl/app.php")
			.then(function(res) {
				res.data.deadline *= 1000;

				return res.data;
			});
	};

	/**
	 * Submit a fishbowl application for the current user.
	 *
	 * @param app  fishbowl application object
	 * @return Promise of http response
	 */
	this.submitFishbowlApp = function(app) {
		return $http.post("/api/fishbowl/app.php", app);
	};

	/**
	 * Archive the previous fishbowl.
	 *
	 * @return Promise of http response
	 */
	this.archiveFishbowl = function() {
		return $http.post("/api/fishbowl/archive.php");
	};

	/**
	 * Get the fishbowl.
	 *
	 * @return Promise of fishbowl array
	 */
	this.getFishbowl = function() {
		return $http.get("/api/fishbowl/fishbowl.php")
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Get a fishbowl app.
	 *
	 * @param id  fishbowl app id
	 * @return Promise of fishbowl app object
	 */
	this.getFishbowlApp = function(id) {
		return $http.get("/api/fishbowl/review.php", { params: { id: id } })
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Rate all fishbowl apps.
	 *
	 * @param array of fishbowl app id's and ratings
	 * @return Promise of http response
	 */
	this.rateFishbowlApps = function(apps) {
		return $http.post("/api/fishbowl/review.php", apps);
	};

	/**
	 * Get the artists and other files in an import directory.
	 *
	 * @param path  path string
	 * @return Promise of directory info object
	 */
	this.getImportDirectory = function(path) {
		return $http.get("/api/import/directory.php", {
			params: {
				path: path
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Get an album that is staged for import.
	 *
	 * @param path	path string
	 * @param artist  artist name
	 * @param Promise of album object
	 */
	this.getImportAlbum = function(path, artist) {
		return $http.get("/api/import/album.php", {
			params: {
				path: path,
				artist: artist
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Get a cart that is staged for import.
	 *
	 * @param path  path string
	 * @param cart  cart name
	 * @param Promise of cart object
	 */
	this.getImportCart = function(path, cart) {
		return $http.get("/api/import/cart.php", {
			params: {
				path: path,
				cart: cart
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Import an album.
	 *
	 * @param album  album object
	 * @return Promise of http response
	 */
	this.importAlbum = function(album) {
		return $http.post("/api/import/album.php", album);
	};

	/**
	 * Import a cart.
	 *
	 * @param cart  cart object
	 * @return Promise of http response
	 */
	this.importCart = function(cart) {
		return $http.post("/api/import/cart.php", cart);
	};

	/**
	 * Get albums in the music library.
	 *
	 * @param rotationID	   rotation ID
	 * @param general_genreID  general genre ID
	 * @param page			 page offset
	 * @param term			 search term
	 * @return Promise of albums array
	 */
	this.getLibrary = function(rotationID, general_genreID, page, term) {
		return $http.get("/api/library/library.php", {
			params: {
				rotationID: rotationID,
				general_genreID: general_genreID,
				page: page,
				term: term
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Move albums through rotation.
	 *
	 * @param albums  array of albums
	 * @return Promise of http response
	 */
	this.moveRotation = function(albums) {
		return $http.post("/api/library/library.php", albums);
	};

	/**
	 * Print album labels to a PDF document.
	 *
	 * Currently this function is not used. Instead,
	 * the PDF is opened in a new tab with window.open().
	 *
	 * @param albums  array of album IDs
	 * @return Promise of http response
	 */
	this.printLabels = function(albums) {
		return $http.get("/api/library/print_labels.php", {
			params: {
				"albums[]": albums
			}
		});
	};

	/**
	 * Get an album in the library.
	 *
	 * @param albumID  album ID
	 * @return Promise of album object
	 */
	this.getLibraryAlbum = function(albumID) {
		return $http.get("/api/library/album.php", {
			params: {
				albumID: albumID
			}
		}).then(function(res) {
			return res.data;
		});
	};

	var Spotify = {};

	Spotify.SearchArtist = $resource("https://api.spotify.com/v1/search", {
		type: "artist",
		limit: 1
	}, {
		get: { method: "GET", cache: true }
	});

	Spotify.RelatedArtists = $resource("https://api.spotify.com/v1/artists/:id/related-artists", {}, {
		get: { method: "GET", cache: true }
	});

	/**
	 * Get a list of related artists.
	 *
	 * @param artist_name
	 * @return Promise of related artists array
	 */
	this.getRelatedArtists = function(artist_name) {
		return Spotify.SearchArtist
			.get({ q: artist_name }).$promise
			.then(function(data) {
				var artist = data.artists.items[0];

				return artist
					? Spotify.RelatedArtists.get({ id: artist.id }).$promise
					: $q.resolve({ artists: [] });
			})
			.then(function(data) {
				return data.artists.map(function(a) {
					return a.name;
				});
			});
	};

	/**
	 * Save an album.
	 *
	 * @param album  album
	 * @return Promise of http response
	 */
	this.saveAlbum = function(album) {
		return $http.post("/api/library/album.php", album);
	};

	/**
	 * Submit an album review.
	 *
	 * @param album  album review
	 * @return Promise of http response
	 */
	this.reviewAlbum = function(album) {
		return $http.post("/api/library/review.php", album);
	};

	/**
	 * Get the current listener count.
	 *
	 * @return Promise of listener count
	 */
	this.getLogbookListenerCount = function() {
		return $http.get("/api/logbook/listener_count.php")
			.then(function(res) {
				return res.data;
			});
	};

	var LogbookShow = $resource("/api/logbook/show.php");

	/**
	 * Get the current show.
	 *
	 * @return Promise of current show object
	 */
	this.getLogbookCurrentShow = function() {
		return LogbookShow.get().$promise;
	};

	/**
	 * Start a new show with the current user.
	 *
	 * @param scheduleID  schedule ID
	 * @return Promise of new show ID
	 */
	this.signOn = function(scheduleID) {
		return LogbookShow.save({ scheduleID: scheduleID }, null).$promise;
	};

	/**
	 * End the current show.
	 *
	 * @return Promise of http response
	 */
	this.signOff = function() {
		return LogbookShow.remove().$promise;
	};

	var LogbookTrack = $resource("/api/logbook/track.php");

	/**
	 * Get the information for an album.
	 *
	 * @param album_code  album code
	 * @return Promise of album object
	 */
	this.getLogbookAlbum = function(album_code) {
		return LogbookTrack.get({ album_code: album_code }).$promise;
	};

	/**
	 * Get the information for a track.
	 *
	 * @param album_code  album code
	 * @param disc_num	disc number
	 * @param track_num   track number
	 * @return Promise of track object
	 */
	this.getLogbookTrack = function(album_code, disc_num, track_num) {
		return LogbookTrack.get({
			album_code: album_code,
			disc_num: disc_num,
			track_num: track_num
		}).$promise;
	};

	/**
	 * Log a track in the current show.
	 *
	 * @param track
	 * @return Promise of http response
	 */
	this.logTrack = function(track) {
		return LogbookTrack.save({}, track).$promise;
	};

	/**
	 * Get the show schedule for a day of the week.
	 *
	 * @param day  day of the week (0: Sunday, etc.)
	 * @return Promise of schedule array
	 */
	this.getSchedule = function(day) {
		return $http.get("/api/schedule/schedule.php", {
			params: {
				day: day
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Remove the entire show schedule.
	 *
	 * @return Promise of http response
	 */
	this.removeSchedule = function() {
		return $http.delete("/api/schedule/schedule.php");
	};

	var Show = $resource("/api/schedule/show.php");

	/**
	 * Get a show in the schedule.
	 *
	 * @param scheduleID  schedule ID
	 * @return show object
	 */
	this.getShow = function(scheduleID) {
		return Show.get({ scheduleID: scheduleID });
	};

	/**
	 * Add a show to the schedule.
	 *
	 * @param show  show object
	 * @return Promise of http response
	 */
	this.addShow = function(show) {
		return Show.save({}, show).$promise;
	};

	/**
	 * Remove a show from the schedule.
	 *
	 * @param scheduleID  schedule ID
	 * @return Promise of http response
	 */
	this.removeShow = function(scheduleID) {
		return Show.remove({ scheduleID: scheduleID }).$promise;
	};

	/**
	 * Get a list of show archives by page or DJ name.
	 *
	 * @param page  page offset
	 * @param term  search term
	 * @return Promise of archives array
	 */
	this.getArchives = function(page, term) {
		return $http.get("/api/shows/archives.php", {
			params: {
				page: page,
				term: term
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Get the list of show sub requests.
	 *
	 * @return Promise of requests array
	 */
	this.getSubRequests = function() {
		return $http.get("/api/showsub/request_list.php")
			.then(function(res) {
				return res.data;
			});
	};

	var ShowSub = $resource("/api/showsub/request.php", {}, {
		fill: { method: "POST" }
	});

	/**
	 * Submit a sub request.
	 *
	 * @param request
	 * @return Promise of http response
	 */
	this.submitSubRequest = function(request) {
		return ShowSub.save({}, request).$promise;
	};

	/**
	 * Fill a sub request.
	 *
	 * @param requestID  sub request ID
	 * @return Promise of http response
	 */
	this.fillSubRequest = function(requestID) {
		return ShowSub.fill({ requestID: requestID }, null).$promise;
	};

	/**
	 * Remove a sub request.
	 *
	 * @param requestID  sub request ID
	 * @return Promise of http response
	 */
	this.removeSubRequest = function(requestID) {
		return ShowSub.remove({ requestID: requestID }).$promise;
	};

	/**
	 * Get a chart of album reviews for each user.
	 *
	 * @param date1  start date timestamp
	 * @param date2  end date timestamp
	 * @return Promise of user array
	 */
	this.getAlbumReviewChart = function(date1, date2) {
		return $http.get("/api/users/reviews.php", {
			params: {
				date1: date1 / 1000,
				date2: date2 / 1000
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Get a list of users.
	 *
	 * @param admin  whether to use admin API
	 * @return Promise of user array
	 */
	this.getUsers = function(admin) {
		var url = admin
			? "/api/users/users_admin.php"
			: "/api/users/users.php";

		return $http.get(url)
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Update users.
	 *
	 * @param users  array of users
	 * @return Promise of http response
	 */
	this.updateUsers = function(users) {
		return $http.post("/api/users/users_admin.php", users);
	};

	/**
	 * Search the list of users.
	 *
	 * @param term  search term
	 * @return Promise of users array
	 */
	this.searchUsers = function(term) {
		return $http.get("/api/users/search.php", {
			params: {
				term: term
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Get the current user.
	 *
	 * @return Promise of user object
	 */
	this.getUser = function() {
		return $http.get("/api/users/user.php")
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Save the current user.
	 *
	 * @param user
	 * @return Promise of http response
	 */
	this.saveUser = function(user) {
		return $http.post("/api/users/user.php", user);
	};
}]);
