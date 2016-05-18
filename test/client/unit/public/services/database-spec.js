"use strict";

describe("app.database", function() {
	var $httpBackend;
	var db;

	beforeEach(module("app.database"));

	beforeEach(inject(function($injector) {
		$httpBackend = $injector.get("$httpBackend");
		db = $injector.get("db");
	}));

	xit("should be able to get album art for a list of albums", function() {

	});

	it("should be able to get a list of recent blog posts", function(done) {
		var posts = [];

		$httpBackend.expectGET("/api/blog/preview.php").respond(posts);

		db.getBlogPreview()
			.then(function(result) {
				expect(result).toEqual(posts);
			})
			.finally(done);

		$httpBackend.flush();
	});

	xit("should be able to get an album chart", function() {

	});

	xit("should be able to get the show schedule", function() {

	});

	xit("should be able to get a list of shows", function() {

	});

	xit("should be able to get a show playlist", function() {

	});

	it("should be able to get the current track", function(done) {
		var track = {};

		$httpBackend.expectGET("/api/shows/now.php").respond(track);

		var result = db.getNowPlaying()
			.then(function(result) {
				expect(result).toEqual(track);
			})
			.finally(done);

		$httpBackend.flush();
	});
});
