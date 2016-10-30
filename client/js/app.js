"use strict";

var app = angular.module("app", [
	"ngAnimate",
	"ngRoute",
	"ui.bootstrap",
	"app.main",
	"app.carousel",
	"app.chart",
	"app.now-playing",
	"app.playlist",
	"app.schedule",
	"app.show",
	"app.staff",
	"app.webcam"
]);

app.config(["$compileProvider", function($compileProvider) {
	$compileProvider.debugInfoEnabled(false);
}]);

app.config(["$routeProvider", function($routeProvider) {
	$routeProvider
		.when("/", {
			templateUrl: "views/slider_main.html",
			controller: "CarouselCtrl"
		})
		.when("/booking", {
			templateUrl: "views/booking.html"
		})
		.when("/charts", {
			redirectTo: "/charts/albums"
		})
		.when("/charts/albums", {
			templateUrl: "views/charts_albums.html",
			controller: "AlbumChartCtrl"
		})
		.when("/charts/tracks", {
			templateUrl: "views/charts_tracks.html",
			controller: "TrackChartCtrl"
		})
		.when("/contact", {
			templateUrl: "views/contact.html"
		})
		.when("/history", {
			templateUrl: "views/history.html"
		})
		.when("/join", {
			templateUrl: "views/join.html"
		})
		.when("/philosophy", {
			templateUrl: "views/philosophy.html"
		})
		.when("/playlists", {
			redirectTo: "/playlists/page/0"
		})
		.when("/playlists/page/:page", {
			templateUrl: "views/show_list.html"
		})
		.when("/playlists/search/:query", {
			templateUrl: "views/show_list.html"
		})
		.when("/playlists/show/:showID", {
			templateUrl: "views/show.html"
		})
		.when("/promotion", {
			templateUrl: "views/promotion.html"
		})
		.when("/recording", {
			templateUrl: "views/recording.html"
		})
		.when("/schedule/:dayID?", {
			templateUrl: "views/schedule.html"
		})
		.when("/staff", {
			templateUrl: "views/staff.html",
			controller: "StaffCtrl"
		})
		.otherwise("/");
}]);
