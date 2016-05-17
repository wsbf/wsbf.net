"use strict";

var app = angular.module("app", [
	"app.register"
]);

app.config(["$compileProvider", function($compileProvider) {
	$compileProvider.debugInfoEnabled(false);
}]);
