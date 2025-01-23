"use strict";

var staffModule = angular.module("app.staff", []);

staffModule.constant("staff", [
		{
			position: "General Manager",
			email: "gm@wsbf.net",
			name: " Dillon Finley",
			imageUrl: "images/staff/2425/dillon-finley.png"
		},
    {
			position: "Chief Announcer",
			email: "announcer@wsbf.net",
			name: "Thomas Merzlak",
			imageUrl: "images/staff/2425/thomas-merzlak.png"
    },
    {
			position: "Chief Engineer",
			email: "chief@wsbf.net",
			name: "Bernardo Vargas",
			imageUrl: "images/staff/2425/bernardo-vargas.png"
    },
    /*{
			position: "Equipment Engineer",
			email: "equipment@wsbf.net",
			name: "McCormick Emge",
			imageUrl: "images/staff/chair.jpg"
    },*/
    {
			position: "Computer Engineer",
			email: "computer@wsbf.net",
			name: "Jai Agarwal",
			imageUrl: "images/staff/2425/jai-agarwal.png"
    },
    {
			position: "Events Coordinator",
			email: "events@wsbf.net",
			name: "Jack Waldrop",
			imageUrl: "images/staff/2425/jack-waldrop.png"
    },
    {
			position: "Music Director",
			email: "music@wsbf.net",
			name: "Nick Brenkert",
			imageUrl: "images/staff/2425/nick-brenkert.png"
    },
    {
			position: "Production Director",
			email: "production@wsbf.net",
			name: "Sean Adams",
			imageUrl: "images/staff/2425/sean-adams.png"
    },
    {
			position: "Promotion Director",
			email: "promo@wsbf.net",
			name: "MG Corbin",
			imageUrl: "images/staff/2425/mg-corbin.png"
	},
	{
			position: "Member At Large",
			email: "member@wsbf.net",
			name: "Cole Turbeville",
			imageUrl: "images/staff/2425/cole-turbeville.png"
	},
    /*{
			position: "Student Media Advisor",
			email: "jalexa5@g.clemson.edu",
			name: "Jackie Alexander",
			imageUrl: "images/wsbflady_100.png"
	}*/
]);

staffModule.controller("StaffCtrl", ["$scope", "staff", function($scope, staff) {
    $scope.staff = staff;
}]);
