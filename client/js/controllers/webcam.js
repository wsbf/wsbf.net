"use strict";

var webcamModule = angular.module("app.webcam", []);

webcamModule.controller("WebcamCtrl", ["$scope", "$interval", function($scope, $interval) {
	$scope.now = 0;

	// update time parameter to force refresh
	var now = $interval(function() {
		$scope.now = Date.now();
	}, 5000);

	$scope.$on("$destroy", function() {
		$interval.cancel(now);
	});
}]);
