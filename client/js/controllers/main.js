"use strict";

var mainModule = angular.module("app.main", [
	"ui.bootstrap",
	"app.database"
]);

mainModule.controller("MainCtrl", ["$scope", "$uibModal", "db", function($scope, $uibModal, db) {
	$scope.show_times = db.getDefs("show_times");

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
