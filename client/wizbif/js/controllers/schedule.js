"use strict";

var scheduleModule = angular.module("wizbif.schedule", [
	"ngFileUpload",
	"ui.bootstrap",
	"wizbif.alert",
	"wizbif.database"
]);

scheduleModule.controller("ScheduleCtrl", ["$scope", "$q", "$uibModal", "$rootScope", "db", "alert", function($scope, $q, $uibModal, $rootScope, db, alert) {
	$scope.days = db.getDefs("days");
	$scope.show_times = db.getDefs("show_times");
	$scope.schedule = [];

	var getSchedule = function() {
		$q.all($scope.days.map(function(day) {
			return db.Schedule.get(day.dayID);
		})).then(function(daySchedules) {
			$scope.schedule = $scope.show_times.map(function(t) {
				return daySchedules.map(function(day) {
					return _.find(day, { start_time: t.show_time });
				});
			});
		});
	};

	$scope.removeSchedule = function() {
		if ( confirm("Are you sure you want to remove the entire show schedule?")
			&& confirm("So you're absolutely sure? I don't want to have to fix everything if you mess up.")
			&& prompt("Type 'STATHGAR' to show me that you're for real.") === "STATHGAR" ) {
			db.Schedule.clear().then(function() {
				getSchedule();
				alert.success("Schedule successfully cleared.");
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	$scope.getShow = function(scheduleID) {
		$uibModal.open({
			templateUrl: "views/schedule_show.html",
			controller: "ScheduleShowCtrl",
			scope: angular.extend($rootScope.$new(), {
				scheduleID: scheduleID
			})
		});
	};

	$scope.editShow = function(scheduleID, dayID, timeID) {
		$uibModal.open({
			templateUrl: "views/schedule_admin_edit.html",
			controller: "ScheduleEditCtrl",
			scope: angular.extend($rootScope.$new(), {
				scheduleID: scheduleID,
				dayID: dayID,
				timeID: timeID
			})
		}).result.then(getSchedule);
	};

	$scope.removeShow = function(scheduleID) {
		if ( confirm("Remove show " + scheduleID + " from the schedule?") ) {
			db.Schedule.removeShow(scheduleID).then(function() {
				getSchedule();
				alert.success("Show successfully removed.");
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	// initialize
	$q.all([
		$scope.days.$promise,
		$scope.show_times.$promise
	]).then(getSchedule);
}]);

scheduleModule.controller("ScheduleShowCtrl", ["$scope", "db", function($scope, db) {
	$scope.days = db.getDefs("days");
	$scope.show_times = db.getDefs("show_times");
	$scope.show_types = db.getDefs("show_types");
	$scope.show = db.Schedule.getShow($scope.scheduleID);
}]);

scheduleModule.controller("ScheduleEditCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.days = db.getDefs("days");
	$scope.show_times = db.getDefs("show_times");
	$scope.show_types = db.getDefs("show_types");

	$scope.searchUsers = function(term) {
		return db.Users.search(term)
			.then(function(users) {
				users.forEach(function(u) {
					u.name = (u.preferred_name === u.first_name + " " + u.last_name)
						? u.preferred_name
						: u.first_name + " " + u.last_name + " (" + u.preferred_name + ")";
				});

				return users;
			});
	};

	$scope.addHost = function() {
		$scope.show.hosts.push($scope.newHost);
		$scope.newHost = null;
	};

	$scope.save = function(show) {
		db.Schedule.saveShow(show).then(function() {
			alert.success("Show successfully saved.");
			$scope.$close();
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	$scope.show_times.$promise.then(function() {
		var startID = Number.parseInt($scope.timeID);
		var endID = (startID + 1) % $scope.show_times.length;

		if ( $scope.scheduleID ) {
			$scope.show = db.Schedule.getShow($scope.scheduleID);
		}
		else {
			$scope.show = {
				dayID: $scope.dayID,
				start_time: $scope.show_times[startID].show_time,
				end_time: $scope.show_times[endID].show_time,
				hosts: []
			};
		}
	});
}]);

scheduleModule.controller("ScheduleInternsCtrl", ["$scope", "$q", "alert", "db", "Upload", function($scope, $q, alert, db, Upload) {
	$scope.days = db.getDefs("days");
	$scope.show_times = db.getDefs("show_times");
	$scope.schedule = null;

	$scope.interns = {};
	$scope.prevInternTimes = {};
	$scope.stats = {};

	var getSchedule = function() {
		$q.all($scope.days.map(function(day) {
			return db.Schedule.get(day.dayID);
		})).then(function(daySchedules) {
			$scope.schedule = $scope.show_times.map(function(t) {
				return daySchedules.map(function(day) {
					return _.find(day, { start_time: t.show_time });
				});
			});
		});
	};

	$scope.loadInternsCSV = function(file) {
		var reader = new FileReader();

		reader.onload = function(event) {
			var data = event.target.result;
			var interns = data.split("\n")
				.filter(function(line) {
					return (line.length > 0);
				})
				.reduce(function(prev, line) {
					var cells = line.split(",");

					var name = cells[0];
					var slots = cells.slice(1)
						.filter(function(cell) {
							return (cell.length > 0);
						})
						.map(function(cell) {
							var nums = cell.split(" ");

							return {
								dayID: nums[0],
								show_timeID: nums[1]
							};
						});

					prev[name] = slots;
					return prev;
				}, {});

			$scope.interns = interns;
		};

		reader.onerror = function(event) {
			alert.error("Could not load file.");
		};

		reader.readAsText(file);
	};

	$scope.loadPrevInternTimesCSV = function(file) {
		var reader = new FileReader();

		reader.onload = function(event) {
			var data = event.target.result;
			var prevInternTimes = data.split("\n")
				.filter(function(line) {
					return (line.length > 0);
				})
				.reduce(function(prev, line) {
					var cells = line.split(",");

					var name = cells[0];
					var slots = cells.slice(1)
						.filter(function(cell) {
							return (cell.length > 0);
						})
						.map(function(cell) {
							var nums = cell.split(" ");

							return {
								dayID: nums[0],
								show_timeID: nums[1]
							};
						});

					prev[name] = slots;
					return prev;
				}, {});

			$scope.prevInternTimes = prevInternTimes;
		};

		reader.onerror = function(event) {
			alert.error("Could not load file.");
		};

		reader.readAsText(file);
	};

	$scope.scheduleInterns = function(schedule, interns, prevInternTimes) {
		// initialize intern arrays in the schedule
		schedule
			.reduce(function(shows, time) {
				return shows.concat(time);
			}, [])
			.filter(angular.identity)
			.forEach(function(show) {
				show.interns = [];
			});

		// filter intern availability by slots that have shows
		// and that aren't on the intern's previous times
		Object.keys(interns).forEach(function(name) {
			var prevTimes = prevInternTimes[name];

			interns[name] = interns[name]
				.filter(function(slot) {
					return schedule[slot.show_timeID][slot.dayID];
				})
				.filter(function(slot) {
					return !(_.find(prevTimes, slot));
				});
		});

		// attempt to schedule a slot for each intern
		Object.keys(interns).forEach(function(name) {
			if ( interns[name].length === 0 ) {
				alert.error("Unable to schedule " + name);
				return;
			}

			var slots = _.shuffle(interns[name]);
			var spotFound = false;

			// attempt to schedule a slot with no interns
			for ( var i = 0; i < slots.length && !spotFound; i++ ) {
				var slot = slots[i];
				var show = schedule[slot.show_timeID][slot.dayID];

				if ( show.interns.length === 0 ) {
					show.interns.push(name);
					spotFound = true;
				}
			}

			// attempt to schedule a slot with only one intern
			for ( i = 0; i < slots.length && !spotFound; i++ ) {
				var slot = slots[i];
				var show = schedule[slot.show_timeID][slot.dayID];

				if ( show.interns.length < 2 ) {
					show.interns.push(name);
					spotFound = true;
				}
			}

			if ( !spotFound ) {
				alert.error("Unable to schedule " + name);
			}
		});

		// compute stats
		var stats = schedule
			.reduce(function(shows, time) {
				return shows.concat(time);
			}, [])
			.filter(angular.identity)
			.reduce(function(prev, show) {
				if ( show.interns.length === 0 ) {
					prev.empty++;
				}
				else if ( show.interns.length === 1 ) {
					prev.single++;
				}
				else if ( show.interns.length === 2 ) {
					prev.double++;
				}
				prev.total++;

				return prev;
			}, {
				empty: 0,
				single: 0,
				double: 0,
				total: 0
			});

		angular.extend($scope.stats, stats);

		// create blob url
		if ( $scope.scheduleUrl ) {
			window.URL.revokeObjectURL($scope.scheduleUrl);
		}

		var data = schedule.map(function(time) {
			return time.map(function(show) {
				return show
					? show.interns.join(" ")
					: "";
			}).join(",");
		}).join("\n");

		var blob = new Blob([data], { type: "text/csv" });
		$scope.scheduleUrl = window.URL.createObjectURL(blob);
	};

	// initialize
	$q.all([
		$scope.days.$promise,
		$scope.show_times.$promise
	]).then(getSchedule);
}]);
