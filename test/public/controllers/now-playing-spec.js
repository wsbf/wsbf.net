"use strict";

describe("NowPlayingCtrl", function() {
	var scope, db, $q;
	var track;

	beforeEach(module("app.now-playing"));

	beforeEach(inject(function($controller, $injector) {
		scope = $injector.get("$rootScope").$new();
		db = $injector.get("db");
		$q = $injector.get("$q");

		track = {};

		spyOn(db, "getNowPlaying").and.returnValue($q.resolve(track));
		spyOn(db, "getAlbumArt").and.returnValue($q.resolve([track]));

		$controller("NowPlayingCtrl", {
			$scope: scope
		});
	}));

	it("should add current track with album art to scope", function() {
		scope.$digest();

		expect(db.getNowPlaying).toHaveBeenCalled();
		expect(db.getAlbumArt).toHaveBeenCalled();
		expect(scope.track).toEqual(track);
	});

	xit("should update current track every 10 seconds", function() {

	});
});
