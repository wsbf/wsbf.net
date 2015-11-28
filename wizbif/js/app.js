"use strict";
var app = angular.module("app", ["ngRoute", "ui.bootstrap"]);

// TODO: move temp objects into dummy php files, add $http calls
app.config(["$routeProvider", function($routeProvider) {
	$routeProvider
		.when("/", { templateUrl: "views/home.html" })
		.when("/user", { templateUrl: "views/user.html" })
		.when("/schedule", { templateUrl: "views/schedule.html", controller: "ScheduleCtrl" })
		.when("/charts", { templateUrl: "views/charts.html", controller: "ChartsCtrl" })
		.when("/archives", { templateUrl: "views/archives.html", controller: "ArchivesCtrl" })
		.when("/library", { templateUrl: "views/library.html", controller: "LibraryCtrl" })
		.when("/library/:albumID", {
			templateUrl: "views/library_item.html",
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
		.when("/review", { templateUrl: "views/review_list.html", controller: "ReviewListCtrl" })
		.when("/review/:albumID", {
			templateUrl: "views/review.html",
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
		.otherwise("/");
}]);

/**
 * Currently, controllers and views are being developed by hard-coding
 * data that will eventually be provided by the server. Eventually a
 * "database" or "api" service should be developed to abstract the use
 * of $http in controllers.
 */
app.controller("MainCtrl", ["$scope", "$http", function($scope, $http) {
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
				$scope.user = res.data;
			});
	};

	getUser();
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
	// TODO: we should make a `def_show_times` table!
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
