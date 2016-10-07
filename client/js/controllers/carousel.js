"use strict";

var carouselModule = angular.module("app.carousel", [
	"app.database"
]);

carouselModule.controller("CarouselCtrl", ["$scope", "db", function($scope, db) {
	$scope.posts = [];

	db.Blog.getPreview().then(function(posts) {
		$scope.posts = posts;
	});
}]);
