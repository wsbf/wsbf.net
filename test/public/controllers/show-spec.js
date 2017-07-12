"use strict";

describe("ShowListCtrl", function() {
	var scope, stateParams, db;
	var shows = [];

	beforeEach(module("app.show"));

	beforeEach(inject(function($controller, $injector) {
		scope = $injector.get("$rootScope").$new();
		stateParams = {
			page: "0",
			query: ""
		};
		db = $injector.get("db");

		shows = [];

		spyOn(db.Show, "getShows").and.returnValue(shows);

		$controller("ShowListCtrl", {
			$scope: scope,
			$stateParams: stateParams
		});
	}));

	it("should add show list to scope", function() {
		expect(db.Show.getShows).toHaveBeenCalledWith(stateParams.page, stateParams.query);
		expect(scope.page).toEqual(0);
		expect(scope.shows).toEqual(shows);
	});
});

describe("ShowCtrl", function() {
	var scope, stateParams, db, $q;
	var playlist = [];

	beforeEach(module("app.show"));

	beforeEach(inject(function($controller, $injector) {
		scope = $injector.get("$rootScope").$new();
		stateParams = {
			showID: "0"
		};
		db = $injector.get("db");
		$q = $injector.get("$q");

		playlist = [];

		spyOn(db.Show, "getPlaylist").and.returnValue($q.resolve(playlist));

		$controller("ShowCtrl", {
			$scope: scope,
			$stateParams: stateParams
		});
	}));

	it("should add playlist to scope", function() {
		expect(db.Show.getPlaylist).toHaveBeenCalledWith(stateParams.showID);
		expect(scope.playlist).toEqual(playlist);
	});
});
