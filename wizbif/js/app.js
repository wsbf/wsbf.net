"use strict";
var app = angular.module("app", [
	"ngResource",
	"ngRoute",
	"ui.bootstrap",
	"ngFileUpload"
]);

app.config(["$routeProvider", function($routeProvider) {
	$routeProvider
		.when("/", { templateUrl: "views/home.html" })
		.when("/archives", { templateUrl: "views/archives.html", controller: "ArchivesCtrl" })
		.when("/charts", { templateUrl: "views/charts.html", controller: "ChartsCtrl" })
		.when("/fishbowl/admin", { templateUrl: "views/fishbowl_admin.html", controller: "FishbowlAdminCtrl" })
		.when("/fishbowl/admin/:id", { templateUrl: "views/fishbowl_review.html", controller: "FishbowlReviewCtrl" })
		.when("/fishbowl/app", { templateUrl: "views/fishbowl_app.html", controller: "FishbowlAppCtrl" })
		.when("/import", { templateUrl: "views/import.html", controller: "ImportCtrl" })
		.when("/import/album", { templateUrl: "views/import_album.html", controller: "ImportAlbumCtrl" })
		.when("/import/cart", { templateUrl: "views/import_cart.html", controller: "ImportCartCtrl" })
		.when("/library", { templateUrl: "views/library.html", controller: "LibraryCtrl" })
		.when("/library/:albumID", { templateUrl: "views/library_album.html", controller: "LibraryAlbumCtrl" })
		.when("/library/:albumID/edit", { templateUrl: "views/library_album_edit.html", controller: "LibraryAlbumCtrl" })
		.when("/review", { templateUrl: "views/review.html", controller: "ReviewListCtrl" })
		.when("/review/:albumID", { templateUrl: "views/review_album.html", controller: "ReviewAlbumCtrl" })
		.when("/schedule", { templateUrl: "views/schedule.html", controller: "ScheduleCtrl" })
		.when("/schedule/admin", { templateUrl: "views/schedule_admin.html", controller: "ScheduleCtrl" })
		.when("/schedule/admin/add/:dayID/:timeID", { templateUrl: "views/schedule_admin_add.html", controller: "ScheduleAddCtrl" })
		.when("/showsub", { templateUrl: "views/showsub.html", controller: "ShowSubCtrl" })
		.when("/showsub/request", { templateUrl: "views/showsub_request.html", controller: "ShowSubRequestCtrl" })
		.when("/user", { templateUrl: "views/user.html", controller: "UserCtrl" })
		.otherwise("/");
}]);

/**
 * The Database service provides an interface to server-side data.
 * The entire server API is implemented here so that every controller
 * uses this service instead of using HTTP requests directly.
 *
 * This service uses Promises, which are an abstraction of callbacks
 * that make asynchronous programming a little better.
 *
 * @param $http      service in module ng
 * @param $resource  service in module ngResource
 */
app.service("db", ["$http", "$resource", function($http, $resource) {

	var Defs = $resource("/api/defs.php", {}, {
		get: { method: "GET", isArray: true, cache: true }
	});

	/**
	 * Get a definitions table.
	 *
	 * @param tableName
	 * @return table array
	 */
	this.getDefs = function(tableName) {
		return Defs.get({ table: tableName });
	};

	/**
	 * Get the list of users who can host shows.
	 *
	 * @param term  search term
	 * @return Promise of user array
	 */
	this.getUsers = function(term) {
		return $http.get("/api/users/users.php", {
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

	/**
	 * Get the show schedule for a day of the week.
	 *
	 * @param day  day of the week (0: Sunday, etc.)
	 * @return Promise of schedule array
	 */
	this.getSchedule = function(day) {
		return $http.get("/api/schedule/schedule.php", { params: { day: day } })
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Add a show to the schedule.
	 *
	 * @param show  show object
	 * @return Promise of http response
	 */
	this.addShow = function(show) {
		return $http.post("/api/schedule/add.php", show);
	};

	/**
	 * Get the top tracks over a period of time.
	 *
	 * @param date1  start timestamp
	 * @param date2  end timestamp
	 * @return Promise of tracks array
	 */
	this.getTopTracks = function(date1, date2) {
		return $http.get("/api/charts/tracks.php", {
			params: {
				date1: date1,
				date2: date2
			}
		}).then(function(res) {
			return res.data;
		});
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
	 * Get a rotation of the album library.
	 *
	 * @param rotation  index of rotation
	 * @return Promise of albums array
	 */
	this.getLibrary = function(rotation) {
		return $http.get("/api/library/library.php", {
			params: {
				rotation: rotation
			}
		}).then(function(res) {
			return res.data;
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

	var SimilarArtists = $resource("http://developer.echonest.com/api/v4/artist/similar", {
		api_key: "4VQZKSD99EUX9ON55",
		format: "json",
		start: 0
	}, {
		get: { method: "GET", cache: true }
	});

	/**
	 * Get a list of similar artists.
	 *
	 * @param artist_name  artist name
	 * @param count        number of similar artists
	 * @return Promise of similar artists array
	 */
	this.getSimilarArtists = function(artist_name, count) {
		return SimilarArtists.get({ name: artist_name, results: count })
			.$promise
			.then(function(data) {
				return (data.response.artists || []).map(function(a) {
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
	 * Get the list of albums available for review.
	 *
	 * TODO: try to merge with getLibrary()
	 *
	 * @return Promise of albums array
	 */
	this.getToBeReviewed = function() {
		return $http.get("/api/review/album_list.php")
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Get an album that is available for review.
	 *
	 * @param albumID  album ID
	 * @return Promise of album object
	 */
	this.getToBeReviewedAlbum = function(albumID) {
		return $http.get("/api/review/album.php", {
			params: {
				albumID: albumID
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Submit a new album review.
	 *
	 * @param album  album review
	 * @return Promise of http response
	 */
	this.reviewAlbum = function(album) {
		return $http.post("/api/review/album.php", album);
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

	/**
	 * Submit a sub request.
	 *
	 * @param request
	 * @return Promise of http response
	 */
	this.submitSubRequest = function(request) {
		return $http.post("/api/showsub/add.php", request);
	};

	/**
	 * Fill a sub request.
	 *
	 * @param requestID  sub request ID
	 * @return Promise of http response
	 */
	this.fillSubRequest = function(requestID) {
		return $http.post("/api/showsub/fill.php", null, {
			params: {
				requestID: requestID
			}
		});
	};

	/**
	 * Remove a sub request.
	 *
	 * @param requestID  sub request ID
	 * @return Promise of http response
	 */
	this.removeSubRequest = function(requestID) {
		return $http.post("/api/showsub/remove.php", null, {
			params: {
				requestID: requestID
			}
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
	 * Rate a fishbowl app.
	 *
	 * @param id      fishbowl app id
	 * @param rating  fishbowl app rating
	 * @return Promise of http response
	 */
	this.rateFishbowlApp = function(id, rating) {
		return $http.post("/api/fishbowl/review.php", null, {
			params: {
				id: id,
				rating: rating
			}
		});
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
}]);

app.controller("MainCtrl", ["$scope", "db", function($scope, db) {
	// temporary status/position sets
	var statusSets = {
		editProfile: ["0", "1", "2", "4"],
		reviewer: ["0", "1", "5"]
	};

	var positionSets = {
		seniorStaff: [0, 1, 2, 3, 4, 5, 6, 7, 8],
		musicDirector: [0, 1, 2, 3, 8, 13, 14, 17, 18, 19, 20],
		engineer: [1, 5, 6, 8, 10]
	};

	$scope.user = {};
	$scope.check = {};

	var getUser = function() {
		db.getUser().then(function(user) {
			$scope.user = user;

			_.assign($scope.check, _.mapValues(statusSets, function(set) {
				return set.indexOf(user.statusID) !== -1;
			}), _.mapValues(positionSets, function(set) {
				return set.indexOf(user.positionID) !== -1;
			}));
		}, function() {
			$scope.user = null;
		});
	};

	// initialize
	getUser();
}]);

app.controller("ArchivesCtrl", ["$scope", "db", function($scope, db) {
	$scope.show_types = db.getDefs("show_types");

	$scope.page = 0;
	$scope.archives = [];

	var getArchives = function(page, term) {
		db.getArchives(page, term).then(function(archives) {
			$scope.archives = archives;
		});
	};

	$scope.getNewer = function() {
		$scope.page--;
		getArchives($scope.page);
	};

	$scope.getOlder = function() {
		$scope.page++;
		getArchives($scope.page);
	};

	$scope.search = function(term) {
		getArchives(null, term);
	};

	// initialize
	getArchives($scope.page);
}]);

app.controller("ChartsCtrl", ["$scope", "db", function($scope, db) {
	var day = 24 * 3600 * 1000;
	var week = 7 * day;

	$scope.tracks = [];

	var getTracks = function() {
		var date1 = Date.now() - week - day;
		var date2 = Date.now() - day;

		db.getTopTracks(date1, date2).then(function(tracks) {
			$scope.tracks = tracks;
		});
	};

	// initialize
	getTracks();
}]);

app.controller("FishbowlAppCtrl", ["$scope", "$location", "db", function($scope, $location, db) {
	$scope.app = {};

	$scope.submit = function() {
		db.submitFishbowlApp($scope.app).then(function() {
			$location.url("/");
		});
	};
}]);

app.controller("FishbowlAdminCtrl", ["$scope", "db", function($scope, db) {
	$scope.fishbowl = [];
	$scope.bowls = [];

	var getFishbowl = function() {
		return db.getFishbowl().then(function(fishbowl) {
			$scope.fishbowl = fishbowl;
		});
	};

	$scope.archiveFishbowl = function() {
		if ( confirm("Are you sure you want to archive the fishbowl?") ) {
			db.archiveFishbowl().then(getFishbowl);
		}
	};

	/**
	 * Group the current list of fishbowl apps into bowls.
	 */
	$scope.getFishbowlResults = function() {
		var fishbowl = $scope.fishbowl
			.slice()
			.sort(function(app1, app2) {
				return app2.average - app1.average;
			});

		var NUM_BOWLS = 5;
		var bowlSize = Math.ceil(fishbowl.length / NUM_BOWLS);
		var bowls = [];

		for ( var i = 0; i < NUM_BOWLS; i++ ) {
			bowls[i] = _.shuffle(fishbowl.splice(0, bowlSize));
		}

		$scope.bowls = bowls;
	};

	// initialize
	getFishbowl();
}]);

app.controller("FishbowlReviewCtrl", ["$scope", "$routeParams", "$location", "db", function($scope, $routeParams, $location, db) {
	$scope.app = {};

	var getFishbowlApp = function() {
		db.getFishbowlApp($routeParams.id).then(function(app) {
			$scope.app = app;
		});
	};

	$scope.submit = function() {
		db.rateFishbowlApp($scope.app.id, $scope.rating).then(function() {
			$location.url("/fishbowl/admin");
		});
	};

	// initialize
	getFishbowlApp();
}]);

app.controller("ImportCtrl", ["$scope", "db", function($scope, db) {
	$scope.path = ["Root"];
	$scope.directories = [];
	$scope.carts = [];
	$scope.artists = [];

	/**
	 * Open an import directory.
	 *
	 * @param index  index of directory in current path
	 * @param dir    name of subdirectory
	 */
	$scope.openDirectory = function(index, dir) {
		var path;

		if ( dir ) {
			path = $scope.path.concat(dir);
		}
		else {
			path = $scope.path.slice(0, index + 1);
		}
		path.shift();

		db.getImportDirectory(path.join("/"))
			.then(function(info) {
				if ( dir ) {
					$scope.path.push(dir);
				}
				else {
					$scope.path.splice(index + 1);
				}

				$scope.directories = info.directories;
				$scope.carts = info.carts;
				$scope.artists = info.artists;
			});
	};

	// initialize
	$scope.openDirectory(0);
}]);

app.controller("ImportAlbumCtrl", ["$scope", "$location", "db", function($scope, $location, db) {
	$scope.general_genres = db.getDefs("general_genres");
	$scope.mediums = db.getDefs("mediums");
	$scope.album = {
		tracks: []
	};

	$scope.save = function() {
		db.importAlbum($scope.album).then(function() {
			$location.url("/import");
		});
	};
}]);

app.controller("ImportCartCtrl", ["$scope", "$location", "db", function($scope, $location, db) {
	$scope.cart_types = db.getDefs("cart_type");
	$scope.cart = {};

	$scope.save = function() {
		db.importCart($scope.cart).then(function() {
			$location.url("/import");
		});
	};
}]);

// TODO: add searching/sorting by DJs
app.controller("LibraryCtrl", ["$scope", "db", function($scope, db) {
	$scope.rotation = 7;
	$scope.albums = [];

	$scope.getLibrary = function(rotation) {
		db.getLibrary(rotation).then(function(albums) {
			$scope.rotation = rotation;
			$scope.albums = albums;
		});
	};

	// initialize
	$scope.getLibrary($scope.rotation);
}]);

app.controller("LibraryAlbumCtrl", ["$scope", "$routeParams", "$location", "db", function($scope, $routeParams, $location, db) {
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
			$location.url("/library");
		});
	};

	// initialize
	getAlbum();
}]);

app.controller("ReviewListCtrl", ["$scope", "db", function($scope, db) {
	$scope.albums = [];

	var getAlbums = function() {
		db.getToBeReviewed().then(function(albums) {
			$scope.albums = albums;
		});
	};

	// initialize
	getAlbums();
}]);

app.controller("ReviewAlbumCtrl", ["$scope", "$routeParams", "$location", "db", function($scope, $routeParams, $location, db) {
	$scope.general_genres = db.getDefs("general_genres");
	$scope.airability = db.getDefs("airability");

	$scope.album = {};

	var getAlbum = function() {
		db.getToBeReviewedAlbum($routeParams.albumID).then(function(album) {
			$scope.album = album;
		});
	};

	$scope.review = function() {
		db.reviewAlbum($scope.album).then(function(res) {
			$location.url("/review");
		});
	};

	// initialize
	getAlbum();
}]);

app.controller("ScheduleCtrl", ["$scope", "$q", "db", function($scope, $q, db) {
	$scope.days = db.getDefs("days");
	$scope.show_times = db.getDefs("show_times");
	$scope.schedule = [];

	var getSchedule = function() {
		$q.all($scope.days.map(function(day) {
			return db.getSchedule(day.dayID);
		})).then(function(daySchedules) {
			$scope.schedule = $scope.show_times.map(function(t) {
				return daySchedules.map(function(day) {
					return _.find(day, { start_time: t.show_time });
				});
			});
		});
	};

	// initialize
	$q.all([
		$scope.days.$promise,
		$scope.show_times.$promise
	]).then(getSchedule);
}]);

app.controller("ScheduleAddCtrl", ["$scope", "$routeParams", "$location", "db", function($scope, $routeParams, $location, db) {
	$scope.days = db.getDefs("days");
	$scope.show_times = db.getDefs("show_times");
	$scope.show_types = db.getDefs("show_types");

	$scope.getUsers = function(term) {
		return db.getUsers(term);
	};

	$scope.save = function() {
		// transform show object from view to server
		var show = angular.copy($scope.show);

		show.hosts = show.hosts.map(function(h) {
			return h.username;
		});

		db.addShow(show).then(function() {
			$location.url("/schedule/admin");
		});
	};

	// initialize
	$scope.show_times.$promise.then(function() {
		var startID = Number.parseInt($routeParams.timeID);
		var endID = (startID + 1) % $scope.show_times.length;

		$scope.show = {
			dayID: $routeParams.dayID,
			start_time: $scope.show_times[startID].show_time,
			end_time: $scope.show_times[endID].show_time,
			hosts: []
		};
	});
}]);

app.controller("ShowSubCtrl", ["$scope", "db", function($scope, db) {
	$scope.requests = [];

	var getSubRequests = function() {
		db.getSubRequests().then(function(requests) {
			$scope.requests = requests;
		});
	};

	$scope.fill = function(index) {
		var req = $scope.requests[index];

		if ( confirm("Are you sure you want to sub this show on " + req.date + "? You will be held responsible if the show is missed.") ) {
			db.fillSubRequest(req.sub_requestID)
				.then(function() {
					req.filled_by = $scope.$parent.user.preferred_name;
				});
		}
	};

	$scope.remove = function(index) {
		var req = $scope.requests[index];

		if ( confirm("Are you sure you want to remove your sub request? If you request another one less than 24 hours before your show, you will be held responsible for missing your show.") ) {
			db.removeSubRequest(req.sub_requestID)
				.then(function() {
					$scope.requests.splice(index, 1);
				});
		}
	};

	// initialize
	getSubRequests();
}]);

app.controller("ShowSubRequestCtrl", ["$scope", "$location", "db", function($scope, $location, db) {
	$scope.today = Date.now();
	$scope.days = db.getDefs("days");
	$scope.request = {};

	$scope.isValidDay = function(request) {
		return (_.find($scope.user.shows, { scheduleID: request.scheduleID }) || {}).dayID
			== request.date.getDay();
	};

	$scope.submit = function() {
		db.submitSubRequest($scope.request).then(function() {
			$location.url("/showsub");
		});
	};
}]);

app.controller("UserCtrl", ["$scope", "db", "$location", "Upload", function($scope, db, $location, Upload) {
	$scope.days = db.getDefs("days");
	$scope.general_genres = db.getDefs("general_genres");

	// TODO: implement image upload

	$scope.save = function() {
		db.saveUser($scope.user).then(function(res) {
			$location.url("/");
		});
	};
}]);
