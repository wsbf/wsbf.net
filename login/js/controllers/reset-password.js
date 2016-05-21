"use strict";

var resetPasswordModule = angular.module("app.reset-password", []);

resetPasswordModule.controller("ResetPasswordCtrl", ["$scope", "$routeParams", "$http", function($scope, $routeParams, $http) {
	$scope.submitted = false;

	$scope.submit = function(password) {
		$http.post("/api/password/reset.php", {
			transactionID: $routeParams.transactionID,
			password: password
		}).then(function(res) {
			$scope.submitted = true;
		}, function(res) {
			$scope.error = res.data;
		});
	};
}]);
