"use strict";

var app = angular.module("app", [
	"ngAnimate",
	"ui.bootstrap",
	"ui.router",
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

app.config(["$stateProvider", "$urlRouterProvider", function($stateProvider, $urlRouterProvider) {
	$stateProvider
		.state("home", {
			url: "/",
			templateUrl: "views/slider_main.html",
			controller: "CarouselCtrl"
		})
		.state("artist-feature", {
			url: "/artistfeature",
			templateUrl: "views/artist_feature.html",
			// controller: "ArtistFeatureCtrl"
		})
		.state("booking", {
			url: "/booking",
			templateUrl: "views/booking.html"
		})
		.state("charts-albums", {
			url: "/charts/albums",
			templateUrl: "views/charts_albums.html",
			controller: "AlbumChartCtrl"
		})
		.state("charts-tracks", {
			url: "/charts/tracks",
			templateUrl: "views/charts_tracks.html",
			controller: "TrackChartCtrl"
		})
		.state("contact", {
			url: "/contact",
			templateUrl: "views/contact.html"
		})
		.state("about", {
			url: "/about",
			templateUrl: "views/about.html"
		})
		.state("join", {
			url: "/join",
			templateUrl: "views/join.html"
		})
		.state("playlists", {
			url: "/playlists/page/:page",
			params: {
				page: "0",
			},
			templateUrl: "views/show_list.html"
		})
		.state("playlists-search", {
			url: "/playlists/search/:query",
			templateUrl: "views/show_list.html"
		})
		.state("playlists-show", {
			url: "/playlists/show/:showID",
			templateUrl: "views/show.html"
		})
		.state("promotion", {
			url: "/promotion",
			templateUrl: "views/promotion.html"
		})
		.state("recording", {
			url: "/recording",
			templateUrl: "views/recording.html"
		})
		.state("schedule", {
			url: "/schedule/:dayID?",
			templateUrl: "views/schedule.html"
		})
		.state("staff", {
			url: "/staff",
			templateUrl: "views/staff.html",
			controller: "StaffCtrl"
		});

	$urlRouterProvider.otherwise("/");
}]);
