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
	 * Get the current user's fishbowl log.
	 */
	this.getFishbowlLog = function() {
		return $http.get("/api/fishbowl/fishbowl_log.php")
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Submit a fishbowl log item.
	 *
	 * @param item
	 */
	this.submitFishbowlLog = function(item) {
		return $http.post("/api/fishbowl/fishbowl_log.php", item);
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

	this.Import = {};

	/**
	 * Get the albums and carts in the import directory.
	 *
	 * @return Promise of directory info object
	 */
	this.Import.getDirectory = function() {
		return $http.get("/api/import/directory.php")
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Get an album in the import directory.
	 *
	 * @param artist_name
	 * @param album_name
	 * @param Promise of album object
	 */
	this.Import.getAlbum = function(artist_name, album_name) {
		return $http.get("/api/import/album.php", {
			params: {
				artist_name: artist_name,
				album_name: album_name
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Get a cart in the import directory.
	 *
	 * @param filename
	 * @param Promise of cart object
	 */
	this.Import.getCart = function(filename) {
		return $http.get("/api/import/cart.php", {
			params: {
				filename: filename
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Import an album.
	 *
	 * @param album
	 * @return Promise of http response
	 */
	this.Import.importAlbum = function(album) {
		return $http.post("/api/import/album.php", album);
	};

	/**
	 * Import a cart.
	 *
	 * @param cart
	 * @return Promise of http response
	 */
	this.Import.importCart = function(cart) {
		return $http.post("/api/import/cart.php", cart);
	};

	this.Library = {};

	/**
	 * Get albums in the music library.
	 *
	 * @param rotationID
	 * @param general_genreID
	 * @param query
	 * @param page
	 * @return Promise of albums array
	 */
	this.Library.getLibrary = function(rotationID, general_genreID, query, page) {
		return $http.get("/api/library/library.php", {
			params: {
				rotationID: rotationID,
				general_genreID: general_genreID,
				query: query,
				page: page
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Move albums through rotation.
	 *
	 * @param albums
	 * @return Promise of http response
	 */
	this.Library.moveRotation = function(albums) {
		return $http.post("/api/library/library.php", albums);
	};

	/**
	 * Print album labels to a PDF document.
	 *
	 * Currently this function is not used. Instead,
	 * the PDF is opened in a new tab with window.open().
	 *
	 * @param albumIDs
	 * @return Promise of http response
	 */
	this.Library.printLabels = function(albumIDs) {
		return $http.get("/api/library/print_labels.php", {
			params: {
				"albums[]": albumIDs
			}
		});
	};

	/**
	 * Get an album in the library.
	 *
	 * @param albumID
	 * @return Promise of album object
	 */
	this.Library.getAlbum = function(albumID) {
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
	this.Library.getRelatedArtists = function(artist_name) {
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
	 * @param album
	 * @return Promise of http response
	 */
	this.Library.saveAlbum = function(album) {
		return $http.post("/api/library/album.php", album);
	};

	/**
	 * Submit an album review.
	 *
	 * @param album
	 * @return Promise of http response
	 */
	this.Library.reviewAlbum = function(album) {
		return $http.post("/api/library/review.php", album);
	};

	/**
	 * Delete an album.
	 *
	 * @param albumID
	 */
	this.Library.deleteAlbum = function(albumID) {
		return $http.delete("/api/library/album.php", {
			params: {
				albumID: albumID
			}
		});
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
