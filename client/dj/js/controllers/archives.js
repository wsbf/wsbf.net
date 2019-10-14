"use strict";

var archivesModule = angular.module("wizbif.archives", [
	"wizbif.database"
]);

archivesModule.controller("ArchivesCtrl", ["$scope", "db", function($scope, db) {
	$scope.show_types = db.getDefs("show_types");
	$scope.page = 0;
	$scope.archives = [];

	var getArchives = function(page, term) {
		db.Show.getArchives(page, term).then(function(archives) {
			$scope.archives = archives;
		});
	};

	$scope.getNewer = function() {
		$scope.page--;
		getArchives($scope.page);
	};

	$scope.getOlder = function() {
		$scope.page++;
		getArchives($scope.page);
	};

	$scope.search = function(term) {
		getArchives(null, term);
	};

	// initialize
	getArchives($scope.page);
}]);
