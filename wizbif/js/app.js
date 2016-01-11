"use strict";
var app = angular.module("app", ["ngRoute", "ui.bootstrap", "ngFileUpload"]);

// TODO: implement all of those server scripts!
app.config(["$routeProvider", function($routeProvider) {
	$routeProvider
		.when("/", { templateUrl: "views/home.html" })
		.when("/user", { templateUrl: "views/user.html", controller: "UserCtrl" })
		.when("/schedule", { templateUrl: "views/schedule.html", controller: "ScheduleCtrl" })
		.when("/schedule/add", { templateUrl: "views/schedule_add.html", controller: "ScheduleAddShowCtrl" })
		.when("/charts", { templateUrl: "views/charts.html", controller: "ChartsCtrl" })
		.when("/archives", { templateUrl: "views/archives.html", controller: "ArchivesCtrl" })
		.when("/library", { templateUrl: "views/library.html", controller: "LibraryCtrl" })
		.when("/library/:albumID", { templateUrl: "views/library_album.html", controller: "LibraryAlbumCtrl" })
		.when("/library/:albumID/edit", { templateUrl: "views/library_album_edit.html", controller: "LibraryAlbumCtrl" })
		.when("/review", { templateUrl: "views/review.html", controller: "ReviewListCtrl" })
		.when("/review/:albumID", { templateUrl: "views/review_album.html", controller: "ReviewAlbumCtrl" })
		.when("/showsub", { templateUrl: "views/showsub.html", controller: "ShowSubCtrl" })
		.when("/showsub/request", { templateUrl: "views/showsub_request.html", controller: "ShowSubRequestCtrl" })
		.when("/fishbowl/app", { templateUrl: "views/fishbowl_app.html", controller: "FishbowlAppCtrl" })
		.when("/fishbowl/admin", { templateUrl: "views/fishbowl_admin.html", controller: "FishbowlAdminCtrl" })
		.when("/fishbowl/admin/:id", { templateUrl: "views/fishbowl_review.html", controller: "FishbowlReviewCtrl" })
		.otherwise("/");
}]);

app.service("db", ["$http", function($http) {

	/**
	 * Get the list of users who can host shows.
	 *
	 * @param term  search term
	 * @return Promise of user array
	 */
	this.getUsers = function(term) {
		return $http.get("/api/users/hosts.php", {
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
	 * Get a list of show archives.
	 *
	 * TODO: align with playlists view
	 *
	 * @param page  page count from most recent
	 * @return Promise of archives array
	 */
	this.getArchives = function(page) {
		return $http.get("/api/shows/archives.php", {
			params: {
				page: page
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

	/**
	 * Get a list of similar artists.
	 *
	 * @param artist_name  artist name
	 * @param count        number of similar artists
	 * @param Promise of similar artists array
	 */
	this.getSimilarArtists = function(artist_name, count) {
		return $http.get("http://developer.echonest.com/api/v4/artist/similar", {
			params: {
				api_key: "4VQZKSD99EUX9ON55",
				format: "json",
				name: artist_name,
				results: count,
				start: 0
			}
		}).then(function(res) {
			return (res.data.response.artists || []).map(function(elem) {
				return elem.name;
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
	 * Get a fishbowl review.
	 *
	 * @return Promise of fishbowl review object
	 */
	this.getFishbowlReview = function() {
		return $http.get("/api/fishbowl/review.php")
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Submit a fishbowl review.
	 *
	 * @param app  fishbowl review object
	 * @return Promise of http response
	 */
	this.submitFishbowlReview = function(app) {
		return $http.post("/api/fishbowl/review.php", app);
	};
}]);

app.controller("MainCtrl", ["$scope", "db", function($scope, db) {
	// temporary position/status arrays
	var validEditProfile = ["0", "1", "2", "4"];
	var validReviewer = ["0", "1", "5"];
	var validSeniorStaff = [0, 1, 2, 3, 4, 5, 6, 7, 8];
	var validMusicDirector = [0, 1, 2, 3, 8, 13, 14, 17, 18, 19, 20];
	var validEngineer = [1, 5, 6, 8, 10];

	// temporary object for days
	$scope.days = [
		"Sunday",
		"Monday",
		"Tuesday",
		"Wednesday",
		"Thursday",
		"Friday",
		"Saturday"
	];
	$scope.user = {};
	$scope.check = {};

	var getUser = function() {
		db.getUser().then(function(user) {
			$scope.user = user;

			$scope.check = {
				editProfile: validEditProfile.indexOf(user.statusID) !== -1,
				reviewer: validReviewer.indexOf(user.statusID) !== -1,
				seniorStaff: validSeniorStaff.indexOf(user.positionID) !== -1,
				musicDirector: validMusicDirector.indexOf(user.positionID) !== -1,
				engineer: validEngineer.indexOf(user.positionID) !== -1
			};
		});
	};

	// initialize
	getUser();
}]);

app.controller("UserCtrl", ["$scope", "db", "$location", "Upload", function($scope, db, $location, Upload) {
	// TODO: implement image upload

	$scope.save = function() {
		db.saveUser($scope.user).then(function(res) {
			$location.url("/");
		});
	}
}]);

app.controller("ScheduleCtrl", ["$scope", "db", function($scope, db) {
	// temporary object for days
	$scope.days = [
		"Sunday",
		"Monday",
		"Tuesday",
		"Wednesday",
		"Thursday",
		"Friday",
		"Saturday"
	];

	// temporary code for show schedule times
	$scope.show_times = [
		"01:00:00",
		"03:00:00",
		"05:00:00",
		"07:00:00",
		"09:00:00",
		"11:00:00",
		"12:30:00",
		"14:00:00",
		"15:30:00",
		"17:00:00",
		"19:00:00",
		"21:00:00",
		"23:00:00",
	];
	$scope.schedule = [];

	var getSchedule = function(day) {
		db.getSchedule(day).then(function(schedule) {

			// temporary code to transform schedule from api to table
			$scope.schedule[day] = $scope.show_times.map(function(show_time) {
				return _.find(schedule, { start_time: show_time });
			});
		});
	};

	// initialize
	for ( var i = 0; i < 7; i++ ) {
		getSchedule(i);
	}
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

app.controller("ArchivesCtrl", ["$scope", "db", function($scope, db) {
	// temporary array of show types
	$scope.show_types = [
		"Rotation",
		"Specialty",
		"Jazz",
		"Sports/Talk",
		"Rotating Specialty",
		"Special Programming",
		"Live Sessions",
		"Free Form",
		"Automation"
	];

	$scope.page = 0;
	$scope.archives = [];

	var getArchives = function(page) {
		db.getArchives(page).then(function(archives) {
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

	// initialize
	getArchives($scope.page);
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
	// temporary code for general genres
	$scope.general_genres = [
		"Rock",
		"Loud Rock/Metal",
		"Hip-Hop/Rap",
		"Indie",
		"Electronic",
		"Folk/Americana/Bluegrass",
		"Punk",
		"Pop",
		"Jazz/Blues/Soul",
		"World",
		"R&B/Reggae",
		"Dance"
	];

	// temporary code for airability
	$scope.airability = [
		"FCC Clean",
		"Recommended",
		"No Air",
		"Silence After Track"
	];

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
	$scope.request = {};

	$scope.submit = function() {
		db.submitSubRequest($scope.request).then(function() {
			$location.url("/showsub");
		});
	};
}]);

app.controller("FishbowlAppCtrl", ["$scope", "$location", "db", function($scope, $location, db) {
	$scope.app = {};

	$scope.submit = function() {
		db.submitFishbowlApp($scope.app).then(function() {
			$location.url("/");
		});
	};
}]);

app.controller("ScheduleAddShowCtrl", ["$scope", "$location", "db", function($scope, $location, db) {
	// temporary code for days
	$scope.days = [
		{ dayID: 0, day: "Sunday" },
		{ dayID: 1, day: "Monday" },
		{ dayID: 2, day: "Tuesday" },
		{ dayID: 3, day: "Wednesday" },
		{ dayID: 4, day: "Thursday" },
		{ dayID: 5, day: "Friday" },
		{ dayID: 6, day: "Saturday" }
	];

	// temporary code for show times
	$scope.show_times = [
		"01:00:00",
		"03:00:00",
		"05:00:00",
		"07:00:00",
		"09:00:00",
		"11:00:00",
		"12:30:00",
		"14:00:00",
		"15:30:00",
		"17:00:00",
		"19:00:00",
		"21:00:00",
		"23:00:00",
	];

	// temporary code for show types
	$scope.show_types = [
		{ show_typeID: 0, type: "Rotation" },
		{ show_typeID: 1, type: "Specialty" },
		{ show_typeID: 2, type: "Jazz" },
		{ show_typeID: 3, type: "Talk / Sports" },
		{ show_typeID: 4, type: "Rotating Specialty" },
		{ show_typeID: 6, type: "Live Sessions" }
	];

	$scope.show = {
		hosts: []
	};

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
		db.archiveFishbowl().then(getFishbowl);
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

	getFishbowl();
}]);

app.controller("FishbowlReviewCtrl", ["$scope", "$routeParams", "$location", "db", function($scope, $routeParams, $location, db) {
	$scope.app = {};

	var getFishbowlReview = function() {
		db.getFishbowlReview().then(function(app) {
			$scope.app = app;
		});
	};

	$scope.submit = function() {
		db.submitFishbowlReview($scope.app).then(function() {
			$location.url("/fishbowl/admin");
		});
	};

	getFishbowlReview();
}]);
