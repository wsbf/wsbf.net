"use strict";
var app = angular.module("app", ["ngRoute", "ui.bootstrap"]);

app.config(["$routeProvider", function($routeProvider) {
	$routeProvider
		.when("/", { templateUrl: "views/slider_main.html", controller: "BlogPreviewCtrl" })
		.when("/philosophy", { templateUrl: "views/philosophy.html" })
		.when("/staff", { templateUrl: "views/staff.html" })
		.when("/history", { templateUrl: "views/history.html" })
		.when("/schedule", { templateUrl: "views/schedule.html" })
		.when("/charts", { templateUrl: "views/charts.html" })
		.when("/equipment", { templateUrl: "views/equipment.html" })
		.when("/recording", { templateUrl: "views/recording.html" })
		.when("/booking", { templateUrl: "views/booking.html" })
		.when("/blog", { templateUrl: "views/blog.html", controller: "BlogPreviewCtrl" })
		.when("/blogpost/:id", {
			templateUrl: "views/blogpost.html",
			controller: "BlogCtrl",
			resolve: {
				post: ["$route", "$http", function($route, $http) {
					return $http.get("api/blog_post.php?p=" + $route.current.params.id)
						.then(function(res) {
							return res.data;
						});
				}]
			}
		})
		.when("/join", { templateUrl: "views/join.html" })
		.when("/underwriting", { templateUrl: "views/underwriting.html" })
		.when("/psa", { templateUrl: "views/psa.html" })
		.when("/contact", { templateUrl: "views/contact.html" })
		.otherwise("/");
}]);

app.controller("MainCtrl", ["$scope", "$uibModal", function($scope, $uibModal) {
	$scope.openMediaCenter = function() {
		$uibModal.open({
			templateUrl: "views/mediacenter.html",
			size: "lg"
		});
	};
}]);

app.controller("BlogPreviewCtrl", ["$scope", "$http", function($scope, $http) {
	$scope.previews = [];

	var getBlogPreviews = function() {
		$http.get("api/blog_preview.php")
			.then(function(res) {
				$scope.previews = res.data;
			});
	};

	getBlogPreviews();
}]);

app.controller("BlogCtrl", ["$scope", "post", function($scope, post) {
	$scope.post = post;
}]);

// TODO: move some of these controllers into services to prevent duplication
app.controller("PlaylistCtrl", ["$scope", "$http", "$q", function($scope, $http, $q) {
	$scope.playlist = [];

	var getAlbumInfo = function(artist, album) {
		var url = "http://ws.audioscrobbler.com/2.0/?api_key=74e3ab782313ff6e306a5f52a0e043ab&format=json&method=album.getinfo"
				+ "&artist=" + artist
				+ "&album=" + album;

		return $http.get(url);
	};

	var getPlaylist = function() {
		var playlist;

		$http.get("api/playlist/current.php")
			.then(function(res) {
				var promises = [];

				playlist = res.data;

				for ( var i = 0; i < playlist.length; i++ ) {
					promises.push(getAlbumInfo(playlist[i].lb_artist, playlist[i].lb_album));
				}

				return $q.all(promises);
			})
			.then(function(array) {
				for ( var i = 0; i < playlist.length; i++ ) {
					if ( !array[i].data.error ) {
						playlist[i].imageUrl = array[i].data.album.image[1]["#text"];
					}
				}

				$scope.playlist = playlist;
			});
	};

	// playlist should update every 60 s
	getPlaylist();
}]);

app.controller("ScheduleCtrl", ["$scope", "$http", function($scope, $http) {
	$scope.today = Date.now();
	$scope.schedule = [];

	var getSchedule = function() {
		$http.get("api/schedule/schedule.php")
			.then(function(res) {
				$scope.schedule = res.data;
			});
	};

	getSchedule();
}]);

// TODO: try to combine chart controllers
// TODO: what should be the date ranges?
app.controller("ChartCtrl", ["$scope", "$http", "$q", function($scope, $http, $q) {
	var day = 24 * 3600 * 1000;
	var week = 7 * day;

	$scope.today = Date.now();
	$scope.date1 = $scope.today - week - day;
	$scope.date2 = $scope.today - day;
	$scope.count = 30;
	$scope.charts = [];

	var getCharts = function(count, date1, date2) {
		var charts = [];

		$http.get("api/charts/charts.php")
			.then(function(res) {
				// temporary code to transform map into array
				Object.keys(res.data).forEach(function(key) {
					charts.push(res.data[key]);
				});

				// temporary code to sort array
				charts.sort(function(a, b) {
					return a.rank - b.rank;
				});

				$scope.charts = charts.slice(0, count);
			});
	};

	$scope.getPrevWeek = function() {
		$scope.date1 -= week;
		$scope.date2 -= week;

		getCharts($scope.count, $scope.date1, $scope.date2);
	};

	$scope.hasNextWeek = function() {
		return $scope.date2 + week <= $scope.today;
	};

	$scope.getNextWeek = function() {
		$scope.date1 += week;
		$scope.date2 += week;

		getCharts($scope.count, $scope.date1, $scope.date2);
	};

	$scope.getCurrWeek = function() {
		$scope.date1 = $scope.today - week - day;
		$scope.date2 = $scope.today - day;

		getCharts($scope.count, $scope.date1, $scope.date2);
	};

	getCharts($scope.count, $scope.date1, $scope.date2);
}]);

app.controller("ChartWidgetCtrl", ["$scope", "$http", "$q", function($scope, $http, $q) {
	$scope.charts = [];

	var getAlbumInfo = function(artist, album) {
		var url = "http://ws.audioscrobbler.com/2.0/?api_key=74e3ab782313ff6e306a5f52a0e043ab&format=json&method=album.getinfo"
				+ "&artist=" + artist
				+ "&album=" + album;

		return $http.get(url);
	};

	// should retrieve only the top 10 albums
	var getCharts = function() {
		var charts = [];

		$http.get("api/charts/charts.php")
			.then(function(res) {
				// temporary code to transform map into array
				Object.keys(res.data).forEach(function(key) {
					charts.push(res.data[key]);
				});

				// temporary code to sort array
				charts.sort(function(a, b) {
					return a.rank - b.rank;
				});

				// temporary code to retrieve top 10 albums
				charts = charts.slice(0, 10);

				var promises = [];
				for ( var i = 0; i < charts.length; i++ ) {
					promises.push(getAlbumInfo(charts[i].artist, charts[i].album));
				}

				return $q.all(promises);
			})
			.then(function(array) {
				for ( var i = 0; i < charts.length; i++ ) {
					if ( !array[i].data.error ) {
						charts[i].imageUrl = array[i].data.album.image[1]["#text"];
					}
				}

				$scope.charts = charts;
			});
	};

	getCharts();
}]);

app.controller("NowPlayingCtrl", ["$scope", "$http", function($scope, $http) {
	$scope.song = {};

	var getAlbumInfo = function(artist, album) {
		var url = "http://ws.audioscrobbler.com/2.0/?api_key=74e3ab782313ff6e306a5f52a0e043ab&format=json&method=album.getinfo"
				+ "&artist=" + artist
				+ "&album=" + album;

		return $http.get(url);
	};

	var getNowPlaying = function() {
		var song;

		$http.get("api/playlist/now.php")
			.then(function(res) {
				song = res.data;

				return getAlbumInfo(song.lb_artist, song.lb_album);
			})
			.then(function(res) {
				song.imageUrl = res.data.album.image[2]["#text"];
				$scope.song = song;
			});
	};

	// now playing should update every 10 s
	getNowPlaying();
}]);

app.controller("WebcamCtrl", ["$scope", "$interval", function($scope, $interval) {
	var baseUrl = "http://wsbf.net/camera/studioa.jpg";

	$scope.webcamUrl = baseUrl;

	/* append time parameter to webcam url to force a reload */
	$interval(function() {
		$scope.webcamUrl = baseUrl + "?" + Date.now();
	}, 5000);
}]);
