"use strict";

describe("AlbumChartCtrl", function() {
	var scope, db, $q;
	var albums;

	beforeEach(module("app.chart"));

	beforeEach(inject(function($controller, $injector) {
		scope = $injector.get("$rootScope").$new();
		db = $injector.get("db");
		$q = $injector.get("$q");

		albums = [];

		spyOn(db, "getDefs");
		spyOn(db, "getTopAlbums").and.returnValue($q.resolve(albums));

		$controller("AlbumChartCtrl", {
			$scope: scope
		});
	}));

	it("should add current chart to scope", function() {
		scope.$digest();

		expect(db.getTopAlbums).toHaveBeenCalled();
		expect(scope.albums).toEqual(albums);
	});
});
