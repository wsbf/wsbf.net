"use strict";

/**
 * Out module is called "app" and it depends on the
 * module "ngRoute" for routing views and "ui.bootstrap" for
 * directives that control Bootstrap components (Carousel,
 * Dropdown, etc.).
 *
 * Visit docs.angularjs.org for Angular documentation.
 */
var app = angular.module("app", [
	"ngAnimate",
	"ngResource",
	"ngRoute",
	"ngSanitize",
	"ui.bootstrap"
]);

/**
 * The first part of our module is a "config" block, which is a function
 * that will be run when the app is initiated (see the Angular docs for
 * more details about the differences between "run" and "config" blocks).
 *
 * This block maps routes to views and controllers; our app will
 * watch the current URL and load a particular HTML template into the <div>
 * in index.html with the "ng-view" directive. Now, instead of reloading
 * the entire page every time a particular page is viewed, each page is
 * reduced to a "view" that is loaded in place of the blog carousel.
 *
 * Some important things to notice about the syntax here:
 * - The arguments of the config function are dependencies that Angular
 *   will provide at runtime (see "Dependency Injection" in Angular docs)
 * - The argument names are also given as strings so that Angular can
 *   still inject the right dependencies when this code is minified
 * - For a config block, dependencies are always providers, but for other
 *   components they can be services, factories, constants, etc.
 *
 * @param $routeProvider  provider in module ngRoute
 */
app.config(["$routeProvider", function($routeProvider) {
	$routeProvider
		.when("/", { templateUrl: "views/slider_main.html", controller: "SliderCtrl" })
		.when("/philosophy", { templateUrl: "views/philosophy.html" })
		.when("/staff", { templateUrl: "views/staff.html" })
		.when("/history", { templateUrl: "views/history.html" })
		.when("/playlists", { templateUrl: "views/show_list.html" })
		.when("/playlists/:showID", { templateUrl: "views/show.html" })
		.when("/schedule", { templateUrl: "views/schedule.html" })
		.when("/charts", { templateUrl: "views/charts.html" })
		.when("/equipment", { templateUrl: "views/equipment.html" })
		.when("/recording", { templateUrl: "views/recording.html" })
		.when("/booking", { templateUrl: "views/booking.html" })
		.when("/join", { templateUrl: "views/join.html" })
		.when("/underwriting", { templateUrl: "views/underwriting.html" })
		.when("/psa", { templateUrl: "views/psa.html" })
		.when("/contact", { templateUrl: "views/contact.html" })
		.otherwise("/");
}]);

/**
 * Our first "object" in this module is a service, which is very similar
 * to a class. The service is called "db", and the function is the
 * constructor.
 *
 * The Database service provides a single interface to server-side data.
 * The entire server API is implemented here so that every controller
 * uses this service instead of $http, which is an Angular service
 * that provides low-level HTTP operations.
 *
 * This service uses Promises, which are an abstraction of callbacks
 * that make asynchronous programming a little better.
 *
 * @param $http      service in module ng
 * @param $q         service in module ng
 * @param $resource  service in module ngResource
 */
app.service("db", ["$http", "$q", "$resource", function($http, $q, $resource) {

	/**
	 * Get album info through the last.fm API.
	 *
	 * @param artist
	 * @param album
	 */
	var AlbumInfo = $resource("http://ws.audioscrobbler.com/2.0/", {
		api_key: "74e3ab782313ff6e306a5f52a0e043ab",
		format: "json",
		method: "album.getinfo"
	}, {
		get: { method: "GET", cache: true }
	});

	/**
	 * Get album art for an array of tracks or albums.
	 *
	 * size parameter is based on last.fm API
	 *  1: 64 x 64
	 *  2: 174 x 174
	 *
	 * @param items  array of tracks or albums
	 * @param size   size of album art
	 * @return Promise of updated items
	 */
	this.getAlbumArt = function(items, size) {
		return $q.all(items.map(function(item) {
			// TODO: request artist photo?
			if ( item.lb_album === "" ) {
				return $q.resolve(item);
			}

			/* add image URL to each item */
			return AlbumInfo.get({
				artist: item.lb_artist,
				album: item.lb_album
			}).$promise.then(function(info) {
				if ( !info.error ) {
					item.imageUrl = info.album.image[size]["#text"];
				}

				return item;
			});
		}));
	};

	/**
	 * Get album charting over a period of time.
	 *
	 * @param date1  start date timestamp
	 * @param date2  end date timestamp
	 * @return Promise of chart array
	 */
	this.getChart = function(date1, date2) {
		return $http.get("/api/charts/albums.php", {
			params: {
				date1: date1,
				date2: date2
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Get a list of shows by page or DJ name.
	 *
	 * @param page  page offset
	 * @param term  search term
	 * @return Promise of shows array
	 */
	this.getShows = function(page, term) {
		return $http.get("/api/shows/shows.php", {
			params: {
				page: page,
				term: term
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Get the playlist for a show, or the current
	 * show if no show ID is provided.
	 *
	 * @param showID  show ID
	 * @return Promise of playlist array
	 */
	this.getPlaylist = function(showID) {
		return $http.get("/api/shows/playlist.php", {
			params: {
				showID: showID
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Get the current track.
	 *
	 * @return Promise of track object
	 */
	this.getNowPlaying = function() {
		return $http.get("/api/shows/now.php")
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Get the schedule for a day of the week.
	 *
	 * @param day  day of the week (0 is Sunday, etc.)
	 * @return Promise of schedule array
	 */
	this.getSchedule = function(day) {
		return $http.get("/api/schedule/schedule.php", { params: { day: day } })
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Get previews of the most recent blog posts.
	 *
	 * @return Promise of blog preview array
	 */
	this.getBlogPreviews = function() {
		return $http.get("/api/blog/preview.php")
			.then(function(res) {
				return res.data;
			});
	};
}]);

app.controller("MainCtrl", ["$scope", "$uibModal", function($scope, $uibModal) {
	$scope.openMediaCenter = function() {
		$uibModal.open({
			templateUrl: "views/mediacenter.html",
			size: "lg"
		});
	};
}]);

app.controller("SliderCtrl", ["$scope", "db", function($scope, db) {
	$scope.previews = [];

	db.getBlogPreviews().then(function(previews) {
		$scope.previews = previews;
	});
}]);

app.controller("ShowListCtrl", ["$scope", "db", function($scope, db) {
	$scope.page = 0;
	$scope.shows = [];

	var getShows = function(page, term) {
		db.getShows(page, term).then(function(shows) {
			$scope.shows = shows;
		});
	};

	$scope.getNewer = function() {
		$scope.page--;
		getShows($scope.page);
	};

	$scope.getOlder = function() {
		$scope.page++;
		getShows($scope.page);
	};

	$scope.search = function(term) {
		getShows(null, term);
	};

	// initialize
	getShows($scope.page);
}]);

app.controller("ShowCtrl", ["$scope", "$routeParams", "db", function($scope, $routeParams, db) {
	$scope.playlist = [];

	db.getPlaylist($routeParams.showID)
		.then(function(playlist) {
			$scope.playlist = playlist;
		});
}]);

app.controller("PlaylistCtrl", ["$scope", "$interval", "db", function($scope, $interval, db) {
	$scope.playlist = [];

	var getPlaylist = function() {
		db.getPlaylist()
			.then(function(playlist) {
				return db.getAlbumArt(playlist, 1);
			})
			.then(function(playlist) {
				$scope.playlist = playlist;
			});
	};

	// update playlist every 60 s
	getPlaylist();
	$interval(getPlaylist, 60000);
}]);

app.controller("ScheduleCtrl", ["$scope", "db", function($scope, db) {
	$scope.today = new Date();
	$scope.day = $scope.today.getDay();
	$scope.schedule = [];

	$scope.getSchedule = function(day) {
		db.getSchedule(day)
			.then(function(schedule) {
				$scope.day = day;
				$scope.schedule = schedule;
			});
	};

	$scope.getSchedule($scope.day);
}]);

app.controller("ChartCtrl", ["$scope", "db", function($scope, db) {
	var DAY = 24 * 3600 * 1000;
	var WEEK = 7 * DAY;

	$scope.today = Date.now();
	$scope.count = 30;
	$scope.albums = [];

	var getChart = function(date1, date2) {
		db.getChart(date1, date2)
			.then(function(albums) {
				$scope.albums = albums;
			});
	};

	$scope.getPrevWeek = function() {
		$scope.date1 -= WEEK;
		$scope.date2 -= WEEK;

		getChart($scope.count, $scope.date1, $scope.date2);
	};

	$scope.hasNextWeek = function() {
		return $scope.date2 + WEEK <= $scope.today;
	};

	$scope.getNextWeek = function() {
		$scope.date1 += WEEK;
		$scope.date2 += WEEK;

		getChart($scope.date1, $scope.date2);
	};

	$scope.getCurrWeek = function() {
		$scope.date1 = $scope.today - WEEK - DAY;
		$scope.date2 = $scope.today - DAY;

		getChart($scope.date1, $scope.date2);
	};

	$scope.getCurrWeek();
}]);

app.controller("ChartWidgetCtrl", ["$scope", "db", function($scope, db) {
	var DAY = 24 * 3600 * 1000;
	var WEEK = 7 * DAY;

	var today = Date.now();
	var count = 10;
	$scope.albums = [];

	var getChart = function(date1, date2) {
		db.getChart(date1, date2)
			.then(function(albums) {
				albums = albums.slice(0, count);

				return db.getAlbumArt(albums, 1);
			})
			.then(function(albums) {
				$scope.albums = albums;
			});
	};

	var getCurrWeek = function() {
		var date1 = today - WEEK - DAY;
		var date2 = today - DAY;

		getChart(date1, date2);
	};

	getCurrWeek();
}]);

app.controller("NowPlayingCtrl", ["$scope", "$interval", "db", function($scope, $interval, db) {
	$scope.track = {};

	var getNowPlaying = function() {
		db.getNowPlaying()
			.then(function(track) {
				return db.getAlbumArt([track], 2);
			})
			.then(function(array) {
				$scope.track = array[0];
			});
	};

	// update now playing every 10 s
	getNowPlaying();

	$interval(getNowPlaying, 10000);
}]);

app.controller("WebcamCtrl", ["$scope", "$interval", function($scope, $interval) {
	var baseUrl = "/camera/studioa.jpg";

	$scope.webcamUrl = baseUrl;

	/* append time parameter to webcam url to force a reload */
	$interval(function() {
		$scope.webcamUrl = baseUrl + "?" + Date.now();
	}, 5000);
}]);
