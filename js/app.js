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
	"app.show-list",
	"app.show",
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
		.when("/philosophy", {
			templateUrl: "views/philosophy.html"
		})
		.when("/staff", {
			templateUrl: "views/staff.html"
		})
		.when("/history", {
			templateUrl: "views/history.html"
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
		.when("/schedule/:dayID?", {
			templateUrl: "views/schedule.html"
		})
		.when("/charts", {
			templateUrl: "views/charts.html"
		})
		.when("/equipment", {
			templateUrl: "views/equipment.html"
		})
		.when("/recording", {
			templateUrl: "views/recording.html"
		})
		.when("/booking", {
			templateUrl: "views/booking.html"
		})
		.when("/join", {
			templateUrl: "views/join.html"
		})
		.when("/promotion", {
			templateUrl: "views/promotion.html"
		})
		.when("/contact", {
			templateUrl: "views/contact.html"
		})
		.otherwise("/");
}]);
