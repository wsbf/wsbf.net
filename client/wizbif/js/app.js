"use strict";
var app = angular.module("wizbif", [
	"ngRoute",
	"ui.bootstrap",
	"wizbif.main",
	"wizbif.archives",
	"wizbif.fishbowl-admin",
	"wizbif.fishbowl",
	"wizbif.import",
	"wizbif.library",
	"wizbif.logbook",
	"wizbif.schedule",
	"wizbif.showsub",
	"wizbif.staff",
	"wizbif.user",
	"wizbif.users"
]);

app.config(["$compileProvider", function($compileProvider) {
	$compileProvider.debugInfoEnabled(false);
}]);

app.config(["$routeProvider", function($routeProvider) {
	$routeProvider
		.when("/", {
			templateUrl: "views/home.html"
		})
		.when("/archives", {
			templateUrl: "views/archives.html",
			controller: "ArchivesCtrl"
		})
		.when("/fishbowl/admin", {
			templateUrl: "views/fishbowl_admin.html",
			controller: "FishbowlAdminCtrl"
		})
		.when("/fishbowl/app", {
			templateUrl: "views/fishbowl_app.html",
			controller: "FishbowlAppCtrl"
		})
		.when("/fishbowl/log", {
			templateUrl: "views/fishbowl_log.html",
			controller: "FishbowlLogCtrl"
		})
		.when("/import", {
			templateUrl: "views/import.html",
			controller: "ImportCtrl"
		})
		.when("/library", {
			redirectTo: "/library/r/0/page/0"
		})
		.when("/library/r/:rotationID/page/:page", {
			templateUrl: "views/library.html",
			controller: "LibraryCtrl"
		})
		.when("/library/r/:rotationID/genre/:general_genreID/page/:page", {
			templateUrl: "views/library.html",
			controller: "LibraryCtrl"
		})
		.when("/library/r/:rotationID/search/:query/page/:page", {
			templateUrl: "views/library.html",
			controller: "LibraryCtrl"
		})
		.when("/library/admin", {
			redirectTo: "/library/admin/r/7/page/0"
		})
		.when("/library/admin/r/:rotationID/page/:page", {
			templateUrl: "views/library_admin.html",
			controller: "LibraryCtrl"
		})
		.when("/library/admin/r/:rotationID/genre/:general_genreID/page/:page", {
			templateUrl: "views/library_admin.html",
			controller: "LibraryCtrl"
		})
		.when("/library/album/:albumID", {
			templateUrl: "views/library_album.html",
			controller: "LibraryAlbumCtrl"
		})
		.when("/library/album/:albumID/edit", {
			templateUrl: "views/library_album_edit.html",
			controller: "LibraryAlbumCtrl"
		})
		.when("/library/album/:albumID/review", {
			templateUrl: "views/library_album_review.html",
			controller: "LibraryAlbumCtrl"
		})
		.when("/logbook", {
			templateUrl: "views/logbook.html",
			controller: "LogbookCtrl"
		})
		.when("/schedule", {
			templateUrl: "views/schedule.html",
			controller: "ScheduleCtrl"
		})
		.when("/schedule/admin", {
			templateUrl: "views/schedule_admin.html",
			controller: "ScheduleCtrl"
		})
		.when("/showsub", {
			templateUrl: "views/showsub.html",
			controller: "ShowSubCtrl"
		})
		.when("/staff/admin", {
			templateUrl: "views/staff.html",
			controller: "StaffCtrl"
		})
		.when("/users", {
			templateUrl: "views/users.html",
			controller: "UsersCtrl"
		})
		.when("/users/:username/edit", {
			templateUrl: "views/user_edit.html",
			controller: "UserCtrl"
		})
		.when("/users/admin", {
			templateUrl: "views/users_admin.html",
			controller: "UsersAdminCtrl"
		})
		.when("/users/reviews", {
			templateUrl: "views/users_reviews.html",
			controller: "UsersReviewsCtrl"
		})
		.otherwise("/");
}]);
