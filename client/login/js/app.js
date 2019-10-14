"use strict";

var app = angular.module("app", [
	"ui.router",
	"app.login",
	"app.register",
	"app.request-password",
	"app.reset-password"
]);

app.config(["$compileProvider", function($compileProvider) {
	$compileProvider.debugInfoEnabled(false);
}]);

app.config(["$stateProvider", "$urlRouterProvider", function($stateProvider, $urlRouterProvider) {
	$stateProvider
		.state("login", {
			url: "/",
			templateUrl: "views/login.html",
			controller: "LoginCtrl"
		})
		.state("register", {
			url: "/register",
			templateUrl: "views/register.html",
			controller: "RegisterCtrl"
		})
		.state("request-password", {
			url: "/request-password",
			templateUrl: "views/request_password.html",
			controller: "RequestPasswordCtrl"
		})
		.state("reset-password", {
			url: "/reset-password/:transactionID",
			templateUrl: "views/reset_password.html",
			controller: "ResetPasswordCtrl"
		});

	$urlRouterProvider.otherwise("/");
}]);
