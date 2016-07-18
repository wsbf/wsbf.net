"use strict";

var webcamModule = angular.module("app.webcam", []);

webcamModule.controller("WebcamCtrl", ["$scope", "$interval", function($scope, $interval) {
	$scope.now = 0;

	// update time parameter to force refresh
	$interval(function() {
		$scope.now = Date.now();
	}, 5000);
}]);
