"use strict";

describe("CarouselCtrl", function() {
	var scope, db, $q;
	var posts;

	beforeEach(module("app.carousel"));

	beforeEach(inject(function($controller, $injector) {
		scope = $injector.get("$rootScope").$new();
		db = $injector.get("db");
		$q = $injector.get("$q");

		posts = [];

		spyOn(db.Blog, "getPreview").and.returnValue($q.resolve(posts));

		$controller("CarouselCtrl", {
			$scope: scope
		});
	}));

	it("should add blog previews to scope", function() {
		scope.$digest();

		expect(db.Blog.getPreview).toHaveBeenCalled();
		expect(scope.posts).toEqual(posts);
	});
});
