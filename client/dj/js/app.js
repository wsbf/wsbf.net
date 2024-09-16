"use strict";

var app = angular.module("wizbif", [
	"ui.bootstrap",
	"ui.router",
	"wizbif.main",
	"wizbif.alumni",
	"wizbif.archives",
	"wizbif.carts",
	"wizbif.fishbowl-admin",
	"wizbif.fishbowl",
	"wizbif.import",
	"wizbif.library",
	"wizbif.logbook",
	"wizbif.playlists",
	"wizbif.schedule",
	"wizbif.showsub",
	"wizbif.staff",
	"wizbif.user",
	"wizbif.users"
]);

app.config(["$compileProvider", function($compileProvider) {
	$compileProvider.debugInfoEnabled(false);
}]);

app.config(["$stateProvider", "$urlRouterProvider", function($stateProvider, $urlRouterProvider) {
	$stateProvider
		.state("home", {
			url: "/",
			templateUrl: "views/home.html"
		})
		.state("alumni", {
			url: "/alumni",
			templateUrl: "views/alumni.html",
			controller: "AlumniCtrl"
		})
		.state("faq", {
			url: "/faq",
			templateUrl: "views/faq.html"
		})
		.state("archives", {
			url: "/archives",
			templateUrl: "views/archives.html",
			controller: "ArchivesCtrl"
		})
		.state("carts-admin", {
			url: "/carts/admin/t/:cart_typeID",
			params: {
				cart_typeID: "0"
			},
			templateUrl: "views/carts_admin.html",
			controller: "CartsCtrl"
		})
		.state("documents", {
			url: "/documents",
			templateUrl: "views/documents.html"
		})
		.state("fishbowl-admin", {
			url: "/fishbowl/admin",
			templateUrl: "views/fishbowl_admin.html",
			controller: "FishbowlAdminCtrl"
		})
		.state("fishbowl-app", {
			url: "/fishbowl/app",
			templateUrl: "views/fishbowl_app.html",
			controller: "FishbowlAppCtrl"
		})
		.state("fishbowl-log", {
			url: "/fishbowl/log",
			templateUrl: "views/fishbowl_log.html",
			controller: "FishbowlLogCtrl"
		})
		.state("import", {
			url: "/import",
			templateUrl: "views/import.html",
			controller: "ImportCtrl"
		})
		.state("library", {
			url: "/library/r/:rotationID",
			params: {
				rotationID: "0",
				general_genreID: null,
				query: null,
				page: "0"
			},
			templateUrl: "views/library.html",
			controller: "LibraryCtrl"
		})
		.state("library-admin", {
			url: "/library/admin/r/:rotationID",
			params: {
				rotationID: "0",
				general_genreID: null,
				query: null,
				page: "0"
			},
			templateUrl: "views/library_admin.html",
			controller: "LibraryCtrl"
		})
		.state("library-album", {
			url: "/library/album/:albumID",
			templateUrl: "views/library_album.html",
			controller: "LibraryAlbumCtrl"
		})
		.state("library-album-edit", {
			url: "/library/album/:albumID/edit",
			templateUrl: "views/library_album_edit.html",
			controller: "LibraryAlbumCtrl"
		})
		.state("library-album-review", {
			url: "/library/album/:albumID/review",
			templateUrl: "views/library_album_review.html",
			controller: "LibraryAlbumCtrl"
		})
		.state("logbook", {
			url: "/logbook",
			templateUrl: "views/logbook.html",
			controller: "LogbookCtrl"
		})
		.state("playlists", {
			url: "/playlists",
			templateUrl: "views/playlists.html",
			controller: "PlaylistsCtrl"
		})
		.state("playlist-edit", {
			url: "/playlists/:playlistID",
			templateUrl: "views/playlist_edit.html",
			controller: "PlaylistEditCtrl"
		})
		.state("schedule", {
			url: "/schedule",
			templateUrl: "views/schedule.html",
			controller: "ScheduleCtrl"
		})
		.state("schedule-admin", {
			url: "/schedule/admin",
			templateUrl: "views/schedule_admin.html",
			controller: "ScheduleCtrl"
		})
		.state("schedule-interns", {
			url: "/schedule/interns",
			templateUrl: "views/schedule_interns.html",
			controller: "ScheduleInternsCtrl"
		})
		.state("showsub", {
			url: "/showsub",
			templateUrl: "views/showsub.html",
			controller: "ShowSubCtrl"
		})
		.state("staff-admin", {
			url: "/staff/admin",
			templateUrl: "views/staff.html",
			controller: "StaffCtrl"
		})
		.state("users", {
			url: "/users",
			templateUrl: "views/users.html",
			controller: "UsersCtrl"
		})
		.state("user", {
			url: "/users/:username/edit",
			templateUrl: "views/user_edit.html",
			controller: "UserCtrl"
		})
		.state("users-admin", {
			url: "/users/admin",
			templateUrl: "views/users_admin.html",
			controller: "UsersAdminCtrl"
		})
		.state("users-reviews", {
			url: "/users/reviews",
			templateUrl: "views/users_reviews.html",
			controller: "UsersReviewsCtrl"
		});

	$urlRouterProvider.otherwise("/");
}]);
