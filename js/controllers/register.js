"use strict";

var registerModule = angular.module("app.register", []);

registerModule.controller("RegisterCtrl", ["$scope", "$http", function($scope, $http) {
	$scope.user = {};
	$scope.registered = false;

	$scope.register = function(user) {
		$http.post("/api/register.php", user)
			.success(function() {
				$scope.registered = true;
			})
			.error(function(message) {
				$scope.error = message;
			});
	};
}]);
