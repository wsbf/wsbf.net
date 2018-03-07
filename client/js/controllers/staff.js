"use strict";

var staffModule = angular.module("app.staff", []);

staffModule.constant("staff", [
    {
	position: "Chief Announcer",
	email: "announcer@wsbf.net",
	name: "Sean Brewer",
	imageUrl: "images/staff/Sean_Brewer.jpg"
    },
    {
	position: "Chief Engineer",
	email: "chief@wsbf.net",
	name: "Christian Steinmetz",
	imageUrl: "images/staff/Christian_Steinmetz.jpg"
    },
    {
	position: "Computer Engineer",
	email: "computer@wsbf.net",
	name: "Angelo Carrabba",
	imageUrl: "images/staff/Angelo_Carrabba.jpg"
    },
    {
	position: "Events Coordinator",
	email: "events@wsbf.net",
	name: "Jacob Lairson",
	imageUrl: "images/staff/Jacob_Lairson.jpg"
    },
    {
	position: "General Manager",
	email: "gm@wsbf.net",
	name: "Michael Murad",
	imageUrl: "images/staff/Michael_Murad.jpg"
    },
    {
	position: "Member At Large",
	email: "member@wsbf.net",
	name: "Luke Waldrop",
	imageUrl: "images/staff/Luke_Waldrop.jpg"
    },/*
    {
	position: "Music Director",
	email: "music@wsbf.net",
	name: "",
	imageUrl: "images/staff/chair.jpg"
    },*/
    {
	position: "Production Director",
	email: "production@wsbf.net",
	name: "Brady Sklar",
	imageUrl: "images/staff/Brady_Sklar.jpg"
    },
    {
	position: "Promotion Director",
	email: "promo@wsbf.net",
	name: "Christian Fultz",
	imageUrl: "images/staff/Christian_Fultz.jpg"
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
