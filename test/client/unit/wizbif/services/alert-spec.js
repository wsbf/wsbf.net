"use strict";

describe("wizbif.alert", function() {
	var alert;

	beforeEach(module("wizbif.alert"));

	beforeEach(inject(function($injector) {
		alert = $injector.get("alert");
	}));

	it("should be able to add a success alert", function() {
		var message = "message";

		alert.success(message);

		expect(alert.alerts[0].message).toEqual(message);
	});

	it("should be able to add an info alert", function() {
		var message = "message";

		alert.info(message);

		expect(alert.alerts[0].message).toEqual(message);
	});

	it("should be able to add a warning alert", function() {
		var message = "message";

		alert.warning(message);

		expect(alert.alerts[0].message).toEqual(message);
	});

	it("should be able to add an error alert", function() {
		var message = "message";

		alert.error(message);

		expect(alert.alerts[0].message).toEqual(message);
	});

	it("should be able to remove an alert", function() {
		var message = "message";

		alert.success(message);
		alert.remove(0);

		expect(alert.alerts).toEqual([]);
	});
});
