"use strict";

var loginModule = angular.module("app.login", []);

loginModule.controller("LoginCtrl", ["$scope", "$http", "$window", function($scope, $http, $window) {
	$scope.user = {};

	$scope.login = function(user) {
		$http.post("/api/auth/login.php", user)
			.then(function(res) {
				$window.location.href = res.data;
			}, function(res) {
				$scope.error = res.data;
			});
	};
}]);
