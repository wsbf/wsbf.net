"use strict";
var app = angular.module("app", ["ngRoute", "ui.bootstrap"]);

// TODO: create 'db' service to abstract $http usage
// and implement all of those server scripts!
app.config(["$routeProvider", function($routeProvider) {
	$routeProvider
		.when("/", { templateUrl: "views/home.html" })
		.when("/user", { templateUrl: "views/user.html", controller: "UserCtrl" })
		.when("/schedule", { templateUrl: "views/schedule.html", controller: "ScheduleCtrl" })
		.when("/charts", { templateUrl: "views/charts.html", controller: "ChartsCtrl" })
		.when("/archives", { templateUrl: "views/archives.html", controller: "ArchivesCtrl" })
		.when("/library", { templateUrl: "views/library.html", controller: "LibraryCtrl" })
		.when("/library/:albumID", {
			templateUrl: "views/library_album.html",
			controller: "LibraryItemCtrl",
			resolve: {
				album: ["$route", "$http", function($route, $http) {
					var config = {
						params: { albumID: $route.current.params.albumID }
					};

					return $http.get("api/library/album.php", config)
						.then(function(res) {
							return res.data;
						});
				}]
			}
		})
		.when("/review", { templateUrl: "views/review.html", controller: "ReviewListCtrl" })
		.when("/review/:albumID", {
			templateUrl: "views/review_album.html",
			controller: "ReviewCtrl",
			resolve: {
				album: ["$route", "$http", function($route, $http) {
					var config = {
						params: { albumID: $route.current.params.albumID }
					};

					return $http.get("api/review/album.php", config)
						.then(function(res) {
							return res.data;
						});
				}]
			}
		})
		.when("/showsub", { templateUrl: "views/showsub.html", controller: "ShowSubCtrl" })
		.when("/showsub/request", { templateUrl: "views/showsub_request.html", controller: "ShowSubRequestCtrl" })
		.otherwise("/");
}]);

app.controller("MainCtrl", ["$scope", "$http", function($scope, $http) {
	// temporary position/status arrays
	var validEditProfile = [0, 1, 2, 4];
	var validReviewer = [0, 1, 5];
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

	var getUser = function() {
		$http.get("api/user.php")
			.then(function(res) {
				// temporary code to convert some properties
				res.data.statusID = parseInt(res.data.statusID);
				res.data.positionID = parseInt(res.data.positionID);

				$scope.user = res.data;
			});
	};

	/**
	 * Check whether the user can edit profile. Includes the following
	 * statuses:
	 *  0: Active
	 *  1: Semi-Active
	 *  2: Inactive
	 *  4: Alumni
	 */
	$scope.checkEditProfile = function() {
		return validEditProfile.indexOf($scope.user.statusID) !== -1;
	};

	$scope.checkReviewer = function() {
		return validReviewer.indexOf($scope.user.statusID) !== -1;
	};

	$scope.checkSeniorStaff = function() {
		return validSeniorStaff.indexOf($scope.user.positionID) !== -1;
	};

	$scope.checkMusicDirector = function() {
		return validMusicDirector.indexOf($scope.user.positionID) !== -1;
	};

	$scope.checkEngineer = function() {
		return validEngineer.indexOf($scope.user.positionID) !== -1;
	};

	getUser();
}]);

app.controller("UserCtrl", ["$scope", "$http", "$location", function($scope, $http, $location) {
	$scope.save = function() {
		$http.post("api/user.php", $scope.user)
			.then(function(res) {
				$location.url("/");
			});
	}
}]);

app.controller("ScheduleCtrl", ["$scope", "$http", function($scope, $http) {
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
	$scope.start_times = [
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
		$http.get("/api/schedule/schedule.php", { params: { day: day } })
			.then(function(res) {
				var schedule = res.data;

				// combine shows with multiple hosts (into array instead of string)
				schedule = schedule.reduce(function(array, s) {
					var i = _.findIndex(array, { start_time: s.start_time });

					if ( i === -1 ) {
						s.preferred_name = [s.preferred_name];
						array.push(s);
					}
					else {
						array[i].preferred_name.push(s.preferred_name);
					}

					return array;
				}, []);

				// temporary code to transform schedule from api to table
				$scope.schedule[day] = $scope.start_times.map(function(start_time) {
					return _.find(schedule, { start_time: start_time });
				});
			});
	};

	for ( var i = 0; i < 7; i++ ) {
		getSchedule(i);
	}
}]);

app.controller("ChartsCtrl", ["$scope", "$http", function($scope, $http) {
	$scope.songs = [];

	var getTracks = function() {
		$http.get("api/charts/tracks.php")
			.then(function(res) {
				$scope.songs = res.data;
			});
	};

	getTracks();
}]);

app.controller("ArchivesCtrl", ["$scope", "$http", function($scope, $http) {
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
		$http.get("api/archives/archives.php")
			.then(function(res) {
				$scope.archives = res.data;
			});
	};

	getArchives();
}]);

// TODO: add searching/sorting by DJs
app.controller("LibraryCtrl", ["$scope", "$http", function($scope, $http) {
	$scope.rotation = 7;
	$scope.albums = [];

	$scope.getLibrary = function(rotation) {
		$http.get("api/library/library.php", { params: { rotation: rotation } })
			.then(function(res) {
				$scope.rotation = rotation;
				$scope.albums = res.data;
			});
	};

	$scope.getLibrary($scope.rotation);
}]);

app.controller("LibraryItemCtrl", ["$scope", "album", function($scope, album) {
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

	$scope.album = album;

	// TODO: use echonest API to get similar artists (maybe do from backend)
	$scope.similar_artists = [];
}]);

app.controller("ReviewListCtrl", ["$scope", "$http", function($scope, $http) {
	$scope.albums = [];

	var getAlbums = function() {
		$http.get("api/review/album_list.php")
			.then(function(res) {
				$scope.albums = res.data;
			});
	};

	getAlbums();
}]);

app.controller("ReviewCtrl", ["$scope", "album", function($scope, album) {
	$scope.album = album;
}]);

app.controller("ShowSubCtrl", ["$scope", "$http", function($scope, $http) {
	$scope.requests = [];

	var getRequests = function() {
		$http.get("api/showsub/request_list.php")
			.then(function(res) {
				$scope.requests = res.data;
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

	getRequests();
}]);

app.controller("ShowSubRequestCtrl", ["$scope", "$http", function($scope, $http) {
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
