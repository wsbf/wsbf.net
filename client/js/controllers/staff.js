"use strict";

var staffModule = angular.module("app.staff", []);

staffModule.constant("staff", [
		{
			position: "General Manager",
			email: "gm@wsbf.net",
			name: "Manning Holmes",
			imageUrl: "images/staff/chair.jpg"
		},
    {
			position: "Chief Announcer",
			email: "announcer@wsbf.net",
			name: "Dev Bryant",
			imageUrl: "images/staff/chair.jpg"
    },
    {
			position: "Chief Engineer",
			email: "chief@wsbf.net",
			name: "Timothy Ross",
			imageUrl: "images/staff/chair.jpg"
    },
    {
			position: "Equipment Engineer",
			email: "equipment@wsbf.net",
			name: "McCormick Emge",
			imageUrl: "images/staff/chair.jpg"
    },
    {
			position: "Computer Engineer",
			email: "computer@wsbf.net",
			name: "Alexander Shelton",
			imageUrl: "images/staff/chair.jpg"
    },
    {
			position: "Events Coordinator",
			email: "events@wsbf.net",
			name: "Reid Stapleton",
			imageUrl: "images/staff/chair.jpg"
    },
    {
			position: "Music Director",
			email: "music@wsbf.net",
			name: "Thomas Williams",
			imageUrl: "images/staff/chair.jpg"
    },
    {
			position: "Production Director",
			email: "production@wsbf.net",
			name: "Emery Cohen",
			imageUrl: "images/staff/chair.jpg"
    }
    /*{
			position: "Promotion Director",
			email: "promo@wsbf.net",
			name: "Amanda Younglund",
			imageUrl: "images/staff/chair.jpg"
	},
	{
			position: "Member At Large",
			email: "member@wsbf.net",
			name: "Mick Thompson",
			imageUrl: "images/staff/chair.jpg"
	},
	{
			position: "Member At Large",
			email: "member@wsbf.net",
			name: "Lizzy Parnell",
			imageUrl: "images/staff/1819/Lizzy.jpg"
	},
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
