"use strict";

describe("ScheduleCtrl", function() {
	var scope, db;
	var schedule = [];

	beforeEach(module("app.schedule"));

	beforeEach(inject(function($controller, $injector) {
		scope = $injector.get("$rootScope").$new();
		db = $injector.get("db");

		schedule = [];

		spyOn(db.Schedule, "get").and.returnValue(schedule);

		$controller("ScheduleCtrl", {
			$scope: scope
		});
	}));

	it("should add today's schedule to scope", function() {
		var day = new Date().getDay();

		expect(db.Schedule.get).toHaveBeenCalledWith(day);
		expect(scope.dayID).toEqual(day);
		expect(scope.schedule).toEqual(schedule);
	});
});
