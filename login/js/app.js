"use strict";

var app = angular.module("app", [
	"ngRoute",
	"app.login",
	"app.register",
	"app.request-password",
	"app.reset-password"
]);

app.config(["$compileProvider", function($compileProvider) {
	$compileProvider.debugInfoEnabled(false);
}]);

app.config(["$routeProvider", function($routeProvider) {
	$routeProvider
		.when("/", {
			templateUrl: "views/login.html",
			controller: "LoginCtrl"
		})
		.when("/login", { redirectTo: "/" })
		.when("/register", {
			templateUrl: "views/register.html",
			controller: "RegisterCtrl"
		})
		.when("/request-password", {
			templateUrl: "views/request_password.html",
			controller: "RequestPasswordCtrl"
		})
		.when("/reset-password/:transactionID", {
			templateUrl: "views/reset_password.html",
			controller: "ResetPasswordCtrl"
		})
		.otherwise("/");
}]);
