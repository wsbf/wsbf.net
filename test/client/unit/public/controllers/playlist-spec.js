"use strict";

describe("PlaylistCtrl", function() {
	var scope, db, $q;
	var playlist;

	beforeEach(module("app.playlist"));

	beforeEach(inject(function($controller, $injector) {
		scope = $injector.get("$rootScope").$new();
		db = $injector.get("db");
		$q = $injector.get("$q");

		playlist = [];

		spyOn(db, "getPlaylist").and.returnValue($q.resolve(playlist));
		spyOn(db, "getAlbumArt").and.returnValue($q.resolve(playlist));

		$controller("PlaylistCtrl", {
			$scope: scope
		});
	}));

	it("should add current playlist with album art to scope", function() {
		scope.$digest();

		expect(db.getPlaylist).toHaveBeenCalled();
		expect(db.getAlbumArt).toHaveBeenCalled();
		expect(scope.playlist).toEqual(playlist);
	});

	xit("should update playlist every 60 seconds", function() {

	});
});
