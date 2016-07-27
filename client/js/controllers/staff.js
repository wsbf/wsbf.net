"use strict";

var staffModule = angular.module("app.staff", []);

staffModule.constant("staff", [
	{
		position: "Chief Announcer",
		email: "announcer@wsbf.net",
		name: "Billy Moir",
		imageUrl: "images/wsbflady_100.png"
	},
	{
		position: "Chief Engineer",
		email: "chief@wsbf.net",
		name: "Christian Steinmetz",
		imageUrl: "images/wsbflady_100.png"
	},
	{
		position: "Computer Engineer",
		email: "computer@wsbf.net",
		name: "Ben Shealy",
		imageUrl: "images/wsbflady_100.png"
	},
	{
		position: "Events Coordinator",
		email: "events@wsbf.net",
		name: "Wesley Heaton",
		imageUrl: "images/wsbflady_100.png"
	},
	{
		position: "General Manager",
		email: "gm@wsbf.net",
		name: "Morgan Kern",
		imageUrl: "images/wsbflady_100.png"
	},
	{
		position: "Member At Large",
		email: "member@wsbf.net",
		name: "Ellie Lane",
		imageUrl: "images/wsbflady_100.png"
	},
	{
		position: "Music Director",
		email: "music@wsbf.net",
		name: "Ian Anderson",
		imageUrl: "images/wsbflady_100.png"
	},
	{
		position: "Production Director",
		email: "production@wsbf.net",
		name: "Brady Sklar",
		imageUrl: "images/wsbflady_100.png"
	},
	{
		position: "Promotion Director",
		email: "promo@wsbf.net",
		name: "Jessica Martin",
		imageUrl: "images/wsbflady_100.png"
	},
	{
		position: "Student Media Advisor",
		email: "jalexa5@g.clemson.edu",
		name: "Jackie Alexander",
		imageUrl: "images/wsbflady_100.png"
	}
]);

staffModule.controller("StaffCtrl", ["$scope", "staff", function($scope, staff) {
	$scope.staff = staff;
}]);
