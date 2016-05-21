"use strict";

var registerModule = angular.module("app.register", []);

registerModule.controller("RegisterCtrl", ["$scope", "$http", function($scope, $http) {
	$scope.user = {};
	$scope.registered = false;

	$scope.register = function(user) {
		$http.post("/api/register.php", user)
			.then(function() {
				$scope.registered = true;
			}, function(res) {
				$scope.error = res.data;
			});
	};
}]);
