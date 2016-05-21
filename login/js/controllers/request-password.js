"use strict";

var requestPasswordModule = angular.module("app.request-password", []);

requestPasswordModule.controller("RequestPasswordCtrl", ["$scope", "$http", function($scope, $http) {
	$scope.submitted = false;

	$scope.submit = function(username) {
		$http.post("/api/password/request.php", null, { params: { username: username } })
			.then(function(res) {
				$scope.submitted = true;
			}, function(res) {
				$scope.error = res.data;
			});
	};
}]);
