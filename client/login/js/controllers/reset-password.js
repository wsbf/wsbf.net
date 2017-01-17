"use strict";

var resetPasswordModule = angular.module("app.reset-password", [
	"ui.router"
]);

resetPasswordModule.controller("ResetPasswordCtrl", ["$scope", "$stateParams", "$http", function($scope, $stateParams, $http) {
	$scope.submitted = false;

	$scope.submit = function(password) {
		$http.post("/api/auth/password_reset.php", {
			transactionID: $stateParams.transactionID,
			password: password
		}).then(function() {
			$scope.submitted = true;
		}, function(res) {
			$scope.error = res.data;
		});
	};
}]);
