"use strict";
var app = angular.module("app", ["ngRoute", "ui.bootstrap"]);

app.config(["$routeProvider", function($routeProvider) {
	$routeProvider
		.when("/", { templateUrl: "views/home.html" })
		.when("/user", { templateUrl: "views/user.html" })
		.when("/schedule", { templateUrl: "views/schedule.html", controller: "ScheduleCtrl" })
		.when("/archives", { templateUrl: "views/archives.html", controller: "ArchivesCtrl" })
		.otherwise("/");
}]);

/**
 * Currently, controllers and views are being developed by hard-coding
 * data that will eventually be provided by the server. Eventually a
 * "database" or "api" service should be developed to abstract the use
 * of $http in controllers.
 */
app.controller("MainCtrl", ["$scope", function($scope) {
	// temporary object for days
	$scope.days = [
		"Sunday",
		"Monday",
		"Tuesday",
		"Wednesday",
		"Thursday",
		"Friday",
		"Saturday"
	];

	// temporary object for user
	$scope.user = {
		username: "Automation",
		first_name: "Otto",
		last_name: "Mation",
		preferred_name: "Otto Mation",
		statusID: 0,
		has_picture: 1,
		shows: [
			{
				scheduleID: -1,
				show_name: "The Best of WSBF",
				dayID: 1,
				start_time: "03:00:00",
				end_time: "05:00:00",
				show_typeID: 8,
				description: "It's really late."
			}
		]
	};
}]);

app.controller("ScheduleCtrl", ["$scope", "$http", function($scope, $http) {
	// temporary object for days
	$scope.days = [
		"Sunday",
		"Monday",
		"Tuesday",
		"Wednesday",
		"Thursday",
		"Friday",
		"Saturday"
	];

	// temporary code for show schedule times
	// TODO: we should make a `def_show_times` table!
	$scope.start_times = [
		"01:00:00",
		"03:00:00",
		"05:00:00",
		"07:00:00",
		"09:00:00",
		"11:00:00",
		"12:30:00",
		"14:00:00",
		"15:30:00",
		"17:00:00",
		"19:00:00",
		"21:00:00",
		"23:00:00",
	];
	$scope.schedule = [];

	var getSchedule = function(day) {
		$http.get("/api/schedule/schedule.php", { params: { day: day } })
			.then(function(res) {
				var schedule = res.data;

				// combine shows with multiple hosts (into array instead of string)
				schedule = schedule.reduce(function(array, s) {
					var i = _.findIndex(array, { start_time: s.start_time });

					if ( i === -1 ) {
						s.preferred_name = [s.preferred_name];
						array.push(s);
					}
					else {
						array[i].preferred_name.push(s.preferred_name);
					}

					return array;
				}, []);

				// temporary code to transform schedule from api to table
				$scope.schedule[day] = $scope.start_times.map(function(start_time) {
					return _.find(schedule, { start_time: start_time });
				});
			});
	};

	for ( var i = 0; i < 7; i++ ) {
		getSchedule(i);
	}
}]);

app.controller("ArchivesCtrl", ["$scope", function($scope) {
	// temporary array of show types
	$scope.show_types = [
		"Rotation",
		"Specialty",
		"Jazz",
		"Sports/Talk",
		"Rotating Specialty",
		"Special Programming",
		"Live Sessions",
		"Free Form",
		"Automation"
	];

	// temporary array of archives
	$scope.archives = [
		{
			showID: "24063",
			show_hosts: ["Otto Mation"],
			show_name: "",
			start_time: 1448316000000,
			end_time: 1448325000000
		}
	];
}]);
