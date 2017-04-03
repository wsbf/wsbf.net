"use strict";

var alumniModule = angular.module("wizbif.alumni", [
	"ui.bootstrap",
	"wizbif.database"
]);

alumniModule.controller("AlumniCtrl", ["$scope", "$rootScope", "$uibModal", "db", function($scope, $rootScope, $uibModal, db) {
	$scope.alumni = [];

	$scope.openAlumni = function(alumni) {
		$uibModal.open({
			templateUrl: "views/alumni_modal.html",
			scope: angular.extend($rootScope.$new(), {
				alumni: alumni
			})
		});
	};

	// initialize
	db.Alumni.getAlumni().then(function(alumni) {
		$scope.alumni = alumni;
	});
}]);
