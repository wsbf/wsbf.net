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
		var promises = $scope.days.map(function(d) {
			return db.Schedule.get(d.dayID);
		});

		$q.all(promises).then(function(schedule) {
			$scope.schedule = $scope.show_times.map(function() {
				return new Array($scope.days.length);
			});

			schedule
				.reduce(function(prev, shows) {
					return prev.concat(shows);
				}, [])
				.forEach(function(s) {
					$scope.schedule[s.show_timeID][s.dayID] = s;
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

	$scope.editShow = function(scheduleID, dayID, show_timeID) {
		$uibModal.open({
			templateUrl: "views/schedule_admin_edit.html",
			controller: "ScheduleEditCtrl",
			scope: angular.extend($rootScope.$new(), {
				scheduleID: scheduleID,
				dayID: dayID,
				show_timeID: show_timeID
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
	$scope.general_genres = db.getDefs("general_genres");
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
		if ( $scope.scheduleID ) {
			$scope.show = db.Schedule.getShow($scope.scheduleID);
		}
		else {
			$scope.show = {
				dayID: $scope.dayID,
				show_timeID: $scope.show_timeID,
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

	/**
	 * Load some kind of intern data from a CSV file.
	 *
	 * The file should have the following format:
	 *
	 * [name],"[dayID],[show_timeID]","[dayID],[show_timeID]" ...
	 * [name],"[dayID],[show_timeID]","[dayID],[show_timeID]" ...
	 * ...
	 *
	 * The returned object has the following structure:
	 *
	 * {
	 *   "[name]": [
	 *     { dayID: [dayID], show_timeID: [show_timeID] },
	 *     ...
	 *   ],
	 *   ...
	 * }
	 *
	 * @param file
	 * @return Promise of intern data object
	 */
	var loadCSV = function(file) {
		return $q(function(resolve, reject) {
			var reader = new FileReader();

			reader.onload = function(event) {
				var data = event.target.result;
				var output = data.split("\n")
					.filter(function(line) {
						return (line.length > 0);
					})
					.reduce(function(prev, line) {
						var cells = line.split(",");

						var name = cells[0];
						var slots = cells.slice(1)
							.filter(function(cell) {
								return (cell.length > 3);
							})
							.map(function(cell) {
								var nums = cell.split(" ");

								return {
									dayID: Number.parseInt(nums[0]),
									show_timeID: Number.parseInt(nums[1])
								};
							});

						prev[name] = slots;
						return prev;
					}, {});

				resolve(output);
			};

			reader.onerror = function() {
				alert.error("Could not load file.");
				reject();
			};

			reader.readAsText(file);
		});
	};

	/**
	 * Load intern availability data from a CSV file.
	 *
	 * @param file
	 */
	$scope.loadInternsCSV = function(file) {
		if ( !file ) {
			return;
		}

		loadCSV(file).then(function(interns) {
			$scope.interns = interns;
		});
	};

	/**
	 * Load previous intern times from a CSV file.
	 *
	 * @param file
	 */
	$scope.loadPrevInternTimesCSV = function(file) {
		if ( !file ) {
			return;
		}

		loadCSV(file).then(function(prevInternTimes) {
			$scope.prevInternTimes = prevInternTimes;
		});
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

		// initialize prev intern times if necessary
		Object.keys(interns).forEach(function(name) {
			prevInternTimes[name] = prevInternTimes[name] || [];
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

		// create blob url for schedule
		if ( $scope.scheduleUrl ) {
			window.URL.revokeObjectURL($scope.scheduleUrl);
		}

		var data1 = schedule.map(function(time) {
			return time.map(function(show) {
				return show
					? show.interns.join(" ")
					: "";
			}).join(",");
		}).join("\n");

		var blob1 = new Blob([data1], { type: "text/csv" });
		$scope.scheduleUrl = window.URL.createObjectURL(blob1);

		// create blob url for prev intern times
		if ( $scope.prevInternTimesUrl ) {
			window.URL.revokeObjectURL($scope.scheduleUrl);
		}

		var prevInternTimes = angular.copy($scope.prevInternTimes);

		schedule.forEach(function(time, show_timeID) {
			time.forEach(function(show, dayID) {
				if ( !show ) {
					return;
				}

				show.interns.forEach(function(intern) {
					prevInternTimes[intern].push({
						dayID: dayID,
						show_timeID: show_timeID
					});
				});
			});
		});

		var data2 = Object.keys(prevInternTimes).map(function(name) {
			var slots = prevInternTimes[name].map(function(slot) {
				return slot.dayID + " " + slot.show_timeID;
			});

			return [name].concat(slots).join(",");
		}).join("\n");

		var blob2 = new Blob([data2], { type: "text/csv" });
		$scope.prevInternTimesUrl = window.URL.createObjectURL(blob2);
	};

	// initialize
	$q.all([
		$scope.days.$promise,
		$scope.show_times.$promise
	]).then(getSchedule);
}]);
