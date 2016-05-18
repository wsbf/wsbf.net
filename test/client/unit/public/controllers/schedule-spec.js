"use strict";

describe("ScheduleCtrl", function() {
	var scope, db, $q;
	var schedule = [];

	beforeEach(module("app.schedule"));

	beforeEach(inject(function($controller, $injector) {
		scope = $injector.get("$rootScope").$new();
		db = $injector.get("db");
		$q = $injector.get("$q");

		schedule = [];

		spyOn(db, "getSchedule").and.returnValue($q.resolve(schedule));

		$controller("ScheduleCtrl", {
			$scope: scope
		});
	}));

	it("should add today's schedule to scope", function() {
		var day = new Date().getDay();

		scope.$digest();

		expect(db.getSchedule).toHaveBeenCalledWith(day);
		expect(scope.day).toEqual(day);
		expect(scope.schedule).toEqual(schedule);
	});
});
