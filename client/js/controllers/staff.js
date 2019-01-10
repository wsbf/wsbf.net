"use strict";

var staffModule = angular.module("app.staff", []);

staffModule.constant("staff", [
		{
			position: "General Manager",
			email: "gm@wsbf.net",
			name: "Roo Walker",
			imageUrl: "images/staff/chair.jpg"
		},
    {
			position: "Chief Announcer",
			email: "announcer@wsbf.net",
			name: "Jake Fladd",
			imageUrl: "images/staff/1819/Jake.jpg"
    },
    {
			position: "Chief Engineer",
			email: "chief@wsbf.net",
			name: "Christian Steinmetz",
			imageUrl: "images/staff/1819/Christian.jpg"
    },
    {
			position: "Computer Engineer",
			email: "computer@wsbf.net",
			name: "Harold Hyte",
			imageUrl: "images/staff/1819/Harold.jpg"
    },
    {
			position: "Events Coordinator",
			email: "events@wsbf.net",
			name: "Walker McDonald",
			imageUrl: "images/staff/chair.jpg"
    },
    {
			position: "Music Director",
			email: "music@wsbf.net",
			name: "Lizzy Parnell",
			imageUrl: "images/staff/1819/Lizzy.jpg"
    },
    {
			position: "Production Director",
			email: "production@wsbf.net",
			name: "Preston Dunnavant",
			imageUrl: "images/staff/1819/Preston.jpg"
    },
    {
			position: "Promotion Director",
			email: "promo@wsbf.net",
			name: "Sam Taylor",
			imageUrl: "images/staff/chair.jpg"
		},
		{
			position: "Member At Large",
			email: "member@wsbf.net",
			name: "Marissa Splendore",
			imageUrl: "images/staff/chair.jpg"
		}/*,
    {
			position: "Student Media Advisor",
			email: "jalexa5@g.clemson.edu",
			name: "Jackie Alexander",
			imageUrl: "images/wsbflady_100.png"
		}*/
]);

staffModule.controller("StaffCtrl", ["$scope", "staff", function($scope, staff) {
    $scope.staff = staff;
}]);
