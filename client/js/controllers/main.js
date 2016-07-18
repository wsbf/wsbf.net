"use strict";

var mainModule = angular.module("app.main", [
	"ui.bootstrap"
]);

mainModule.controller("MainCtrl", ["$scope", "$uibModal", function($scope, $uibModal) {
	$scope.$on("$routeChangeSuccess", function() {
		$scope.navCollapsed = false;
	});

	$scope.openMediaCenter = function() {
		$uibModal.open({
			templateUrl: "views/mediacenter.html",
			size: "lg"
		});
	};
}]);
