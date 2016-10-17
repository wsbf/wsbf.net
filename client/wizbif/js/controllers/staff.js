"use strict";

var staffModule = angular.module("wizbif.staff", [
	"wizbif.database"
]);

staffModule.controller("StaffCtrl", ["$scope", "alert", "db", function($scope, alert, db) {
	$scope.positions = db.getDefs("positions");
	$scope.staff = [];
	$scope.newMember = {};

	var getStaff = function() {
		db.Staff.get().then(function(staffArray) {
			// sort staff members by position, ensure one-to-one mapping
			$scope.staff = $scope.positions.map(function(p) {
				return _.find(staffArray, { positionID: p.positionID });
			});
		});
	};

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

	$scope.addStaffMember = function(staff, member) {
		if ( staff[member.positionID] ) {
			alert.error("Cannot add a staff member to a non-empty position.");
		}
		else {
			var tempMember = {
				positionID: member.positionID,
				username: member.user.username,
				preferred_name: member.user.preferred_name
			};

			db.Staff.addMember(tempMember).then(function() {
				alert.success("Staff member successfully added.");

				staff[member.positionID] = tempMember;
				$scope.newMember = {};
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	$scope.removeStaffMember = function(staff, positionID) {
		if ( confirm("Remove " + staff[positionID].preferred_name + " from staff position?") ) {
			db.Staff.removeMember(positionID).then(function() {
				alert.success("Staff member successfully removed.");

				staff[positionID] = null;
			}, function(res) {
				alert.error(res.data || res.statusText);
			});
		}
	};

	// initialize
	$scope.positions.$promise.then(getStaff);
}]);
