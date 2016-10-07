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

	var api = {};

	api.Defs = $resource("/api/defs.php", {}, {
		get: { method: "GET", isArray: true, cache: true }
	});

	api.LogbookShow = $resource("/api/logbook/show.php");

	api.LogbookTrack = $resource("/api/logbook/track.php");

	api.Show = $resource("/api/schedule/show.php");

	api.ShowSub = $resource("/api/showsub/request.php", {}, {
		fill: { method: "POST" }
	});

	api.Spotify = {};

	api.Spotify.SearchArtist = $resource("https://api.spotify.com/v1/search", {
		type: "artist",
		limit: 1
	}, {
		get: { method: "GET", cache: true }
	});

	api.Spotify.RelatedArtists = $resource("https://api.spotify.com/v1/artists/:id/related-artists", {}, {
		get: { method: "GET", cache: true }
	});

	/**
	 * Get a definitions table.
	 *
	 * @param tableName
	 * @return table array
	 */
	this.getDefs = function(tableName) {
		return api.Defs.get({ table: tableName });
	};

	this.Fishbowl = {};

	/**
	 * Get the current user's fishbowl log.
	 */
	this.Fishbowl.getLog = function() {
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
	this.Fishbowl.submitLog = function(item) {
		return $http.post("/api/fishbowl/fishbowl_log.php", item);
	};

	/**
	 * Get the parameters for the current fishbowl application.
	 *
	 * @return Promise of fishbowl app info
	 */
	this.Fishbowl.getInfo = function() {
		return $http.get("/api/fishbowl/app.php")
			.then(function(res) {
				res.data.deadline *= 1000;

				return res.data;
			});
	};

	/**
	 * Submit a fishbowl application for the current user.
	 *
	 * @param app
	 * @return Promise of http response
	 */
	this.Fishbowl.submitApp = function(app) {
		return $http.post("/api/fishbowl/app.php", app);
	};

	/**
	 * Archive the previous fishbowl.
	 *
	 * @return Promise of http response
	 */
	this.Fishbowl.archive = function() {
		return $http.post("/api/fishbowl/archive.php");
	};

	/**
	 * Get the fishbowl.
	 *
	 * @return Promise of fishbowl array
	 */
	this.Fishbowl.get = function() {
		return $http.get("/api/fishbowl/fishbowl.php")
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Get a fishbowl app.
	 *
	 * @param id
	 * @return Promise of fishbowl app object
	 */
	this.Fishbowl.getApp = function(id) {
		return $http.get("/api/fishbowl/review.php", { params: { id: id } })
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Rate all fishbowl apps.
	 *
	 * @param apps  array of fishbowl app id's and ratings
	 * @return Promise of http response
	 */
	this.Fishbowl.rateApps = function(apps) {
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

	/**
	 * Get a list of related artists.
	 *
	 * @param artist_name
	 * @return Promise of related artists array
	 */
	this.Library.getRelatedArtists = function(artist_name) {
		return api.Spotify.SearchArtist
			.get({ q: artist_name }).$promise
			.then(function(data) {
				var artist = data.artists.items[0];

				return artist
					? api.Spotify.RelatedArtists.get({ id: artist.id }).$promise
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

	this.Logbook = {};

	/**
	 * Get the current listener count.
	 *
	 * @return Promise of listener count
	 */
	this.Logbook.getListenerCount = function() {
		return $http.get("/api/logbook/listener_count.php")
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Get the current show.
	 *
	 * @return Promise of current show object
	 */
	this.Logbook.getCurrentShow = function() {
		return api.LogbookShow.get().$promise;
	};

	/**
	 * Start a new show with the current user.
	 *
	 * @param scheduleID
	 * @return Promise of new show ID
	 */
	this.Logbook.signOn = function(scheduleID) {
		return api.LogbookShow.save({ scheduleID: scheduleID }, null).$promise;
	};

	/**
	 * End the current show.
	 *
	 * @return Promise of http response
	 */
	this.Logbook.signOff = function() {
		return api.LogbookShow.remove().$promise;
	};

	/**
	 * Get the information for an album.
	 *
	 * @param album_code
	 * @return Promise of album object
	 */
	this.Logbook.getAlbum = function(album_code) {
		return api.LogbookTrack.get({ album_code: album_code }).$promise;
	};

	/**
	 * Get the information for a track.
	 *
	 * @param album_code
	 * @param disc_num
	 * @param track_num
	 * @return Promise of track object
	 */
	this.Logbook.getTrack = function(album_code, disc_num, track_num) {
		return api.LogbookTrack.get({
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
	this.Logbook.logTrack = function(track) {
		return api.LogbookTrack.save({}, track).$promise;
	};

	this.Schedule = {};

	/**
	 * Get the show schedule for a day of the week.
	 *
	 * @param day  day of the week (0: Sunday, etc.)
	 * @return Promise of schedule array
	 */
	this.Schedule.get = function(day) {
		return $http.get("/api/schedule/schedule.php", {
			params: {
				day: day
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Clear the entire show schedule.
	 *
	 * @return Promise of http response
	 */
	this.Schedule.clear = function() {
		return $http.delete("/api/schedule/schedule.php");
	};

	/**
	 * Get a show in the schedule.
	 *
	 * @param scheduleID
	 * @return show object
	 */
	this.Schedule.getShow = function(scheduleID) {
		return api.Show.get({ scheduleID: scheduleID });
	};

	/**
	 * Add a show to the schedule.
	 *
	 * @param show
	 * @return Promise of http response
	 */
	this.Schedule.addShow = function(show) {
		return api.Show.save({}, show).$promise;
	};

	/**
	 * Remove a show from the schedule.
	 *
	 * @param scheduleID
	 * @return Promise of http response
	 */
	this.Schedule.removeShow = function(scheduleID) {
		return api.Show.remove({ scheduleID: scheduleID }).$promise;
	};

	this.Show = {};

	/**
	 * Get a list of show archives by page or DJ name.
	 *
	 * @param page
	 * @param term
	 * @return Promise of archives array
	 */
	this.Show.getArchives = function(page, term) {
		return $http.get("/api/shows/archives.php", {
			params: {
				page: page,
				term: term
			}
		}).then(function(res) {
			return res.data;
		});
	};

	this.ShowSub = {};

	/**
	 * Get the list of show sub requests.
	 *
	 * @return Promise of requests array
	 */
	this.ShowSub.getSubRequests = function() {
		return $http.get("/api/showsub/request_list.php")
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Submit a sub request.
	 *
	 * @param request
	 * @return Promise of http response
	 */
	this.ShowSub.submitSubRequest = function(request) {
		return api.ShowSub.save({}, request).$promise;
	};

	/**
	 * Fill a sub request.
	 *
	 * @param requestID
	 * @return Promise of http response
	 */
	this.ShowSub.fillSubRequest = function(requestID) {
		return api.ShowSub.fill({ requestID: requestID }, null).$promise;
	};

	/**
	 * Remove a sub request.
	 *
	 * @param requestID
	 * @return Promise of http response
	 */
	this.ShowSub.removeSubRequest = function(requestID) {
		return api.ShowSub.remove({ requestID: requestID }).$promise;
	};

	this.Users = {};

	/**
	 * Get a chart of album reviews for each user.
	 *
	 * @param date1  start date timestamp
	 * @param date2  end date timestamp
	 * @return Promise of user array
	 */
	this.Users.getAlbumReviewChart = function(date1, date2) {
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
	this.Users.getUsers = function(admin) {
		var url = admin
			? "/api/users/users_admin.php"
			: "/api/users/users.php";

		return $http.get(url)
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Save a user.
	 *
	 * @param user
	 * @return Promise of http response
	 */
	this.Users.saveUser = function(user) {
		return $http.post("/api/users/users_admin.php", user);
	};

	/**
	 * Search the list of users.
	 *
	 * @param term
	 * @return Promise of users array
	 */
	this.Users.search = function(term) {
		return $http.get("/api/users/search.php", {
			params: {
				term: term
			}
		}).then(function(res) {
			return res.data;
		});
	};

	this.User = {};

	/**
	 * Get the current user.
	 *
	 * @return Promise of user object
	 */
	this.User.get = function() {
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
	this.User.save = function(user) {
		return $http.post("/api/users/user.php", user);
	};
}]);
