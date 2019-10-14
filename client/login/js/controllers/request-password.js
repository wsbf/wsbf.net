"use strict";

var requestPasswordModule = angular.module("app.request-password", []);

requestPasswordModule.controller("RequestPasswordCtrl", ["$scope", "$http", function($scope, $http) {
	$scope.submitted = false;

	$scope.submit = function(username) {
		$http.post("/api/auth/password_request.php", null, { params: { username: username } })
			.then(function() {
				$scope.submitted = true;
			}, function(res) {
				$scope.error = res.data;
			});
	};
}]);
