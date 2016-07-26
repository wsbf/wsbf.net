"use strict";

var staffModule = angular.module("app.staff", []);

staffModule.constant("staff", [
	{
		position: "Chief Announcer",
		email: "announcer@wsbf.net",
		name: "Billy Moir",
		imageUrl: "images/logo.jpg"
	},
	{
		position: "Chief Engineer",
		email: "chief@wsbf.net",
		name: "Christian Steinmetz",
		imageUrl: "images/logo.jpg"
	},
	{
		position: "Computer Engineer",
		email: "computer@wsbf.net",
		name: "Ben Shealy",
		imageUrl: "images/logo.jpg"
	},
	{
		position: "Events Coordinator",
		email: "events@wsbf.net",
		name: "Wesley Heaton",
		imageUrl: "images/logo.jpg"
	},
	{
		position: "General Manager",
		email: "gm@wsbf.net",
		name: "Morgan Kern",
		imageUrl: "images/logo.jpg"
	},
	{
		position: "Member At Large",
		email: "member@wsbf.net",
		name: "Ellie Lane",
		imageUrl: "images/logo.jpg"
	},
	{
		position: "Music Director",
		email: "music@wsbf.net",
		name: "Ian Anderson",
		imageUrl: "images/logo.jpg"
	},
	{
		position: "Production Director",
		email: "production@wsbf.net",
		name: "Brady Sklar",
		imageUrl: "images/logo.jpg"
	},
	{
		position: "Promotion Director",
		email: "promo@wsbf.net",
		name: "Jessica Martin",
		imageUrl: "images/logo.jpg"
	},
	{
		position: "Student Media Advisor",
		email: "jalexa5@g.clemson.edu",
		name: "Jackie Alexander",
		imageUrl: "images/logo.jpg"
	}
]);

staffModule.controller("StaffCtrl", ["$scope", "staff", function($scope, staff) {
	$scope.staff = staff;
}]);
