"use strict";
var app = angular.module("app", ["ngRoute", "ui.bootstrap", "ngFileUpload"]);

// TODO: implement all of those server scripts!
app.config(["$routeProvider", function($routeProvider) {
	$routeProvider
		.when("/", { templateUrl: "views/home.html" })
		.when("/user", { templateUrl: "views/user.html", controller: "UserCtrl" })
		.when("/schedule", { templateUrl: "views/schedule.html", controller: "ScheduleCtrl" })
		.when("/charts", { templateUrl: "views/charts.html", controller: "ChartsCtrl" })
		.when("/archives", { templateUrl: "views/archives.html", controller: "ArchivesCtrl" })
		.when("/library", { templateUrl: "views/library.html", controller: "LibraryCtrl" })
		.when("/library/:albumID", { templateUrl: "views/library_album.html", controller: "LibraryAlbumCtrl" })
		.when("/library/:albumID/edit", { templateUrl: "views/library_album_edit.html", controller: "LibraryAlbumCtrl" })
		.when("/review", { templateUrl: "views/review.html", controller: "ReviewListCtrl" })
		.when("/review/:albumID", { templateUrl: "views/review_album.html", controller: "ReviewAlbumCtrl" })
		.when("/showsub", { templateUrl: "views/showsub.html", controller: "ShowSubCtrl" })
		.when("/showsub/request", { templateUrl: "views/showsub_request.html", controller: "ShowSubRequestCtrl" })
		.otherwise("/");
}]);

app.service("db", ["$http", function($http) {

	/**
	 * Get the current user.
	 *
	 * @return Promise of user object
	 */
	this.getUser = function() {
		return $http.get("api/user.php")
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
		return $http.post("api/user.php", user);
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
	 * Get the top tracks over a period of time.
	 *
	 * @param date1  start timestamp
	 * @param date2  end timestamp
	 * @return Promise of tracks array
	 */
	this.getTopTracks = function(date1, date2) {
		return $http.get("api/charts/tracks.php", {
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
	 * TODO: align with playlists view, add "page" param
	 *
	 * @return Promise of archives array
	 */
	this.getArchives = function() {
		return $http.get("api/archives/archives.php")
			.then(function(res) {
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
		return $http.get("api/library/library.php", {
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
		return $http.get("api/library/album.php", {
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
		return $http.post("api/library/album.php", album);
	};

	/**
	 * Get the list of albums available for review.
	 *
	 * TODO: try to merge with getLibrary()
	 *
	 * @return Promise of albums array
	 */
	this.getToBeReviewed = function() {
		return $http.get("api/review/album_list.php")
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
		return $http.get("api/review/album.php", {
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
		return $http.post("api/review/album.php", album);
	};

	/**
	 * Get the list of show sub requests.
	 *
	 * @return Promise of requests array
	 */
	this.getShowSubRequests = function() {
		return $http.get("api/showsub/request_list.php")
			.then(function(res) {
				return res.data;
			});
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

	$scope.archives = [];

	var getArchives = function() {
		db.getArchives().then(function(archives) {
			$scope.archives = archives;
		});
	};

	// initialize
	getArchives();
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

	var getRequests = function() {
		db.getShowSubRequests().then(function(requests) {
			$scope.requests = requests;
		});
	};

	$scope.fillRequest = function() {
		// confirm action
		// post to api/showsub/fill.php (see show_sub/show_sub_fill.php)
	};

	$scope.removeRequest = function() {
		// confirm action
		// post to api/showsub/remove.php (see show_sub/show_sub_remove.php)
	};

	// initialize
	getRequests();
}]);

app.controller("ShowSubRequestCtrl", ["$scope", "db", function($scope, db) {
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

	$scope.request = {
		username: $scope.$parent.user.username
	};
}]);
