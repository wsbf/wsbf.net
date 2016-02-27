"use strict";
var app = angular.module("register", []);

app.config(["$compileProvider", function($compileProvider) {
	$compileProvider.debugInfoEnabled(false);
}]);

app.controller("RegisterCtrl", ["$scope", "$http", "$window", function($scope, $http, $window) {
	$scope.user = {};

	$scope.register = function(user) {
		$http.post("/api/register.php", user)
			.success(function(url) {
				$window.location.href = url;
			})
			.error(function(message) {
				$scope.error = message;
			});
	};
}]);
