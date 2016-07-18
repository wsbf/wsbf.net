"use strict";

var importModule = angular.module("wizbif.import", [
	"ui.bootstrap",
	"wizbif.alert",
	"wizbif.database"
]);

importModule.controller("ImportCtrl", ["$scope", "$rootScope", "$uibModal", "db", function($scope, $rootScope, $uibModal, db) {
	$scope.trail = ["Root"];
	$scope.path = "";
	$scope.directories = [];
	$scope.carts = [];
	$scope.artists = [];

	/**
	 * Open an import directory.
	 *
	 * @param index  index of directory in current trail
	 * @param dir    name of subdirectory
	 */
	$scope.openDirectory = function(index, dir) {
		var trail = dir
			? $scope.trail.concat(dir)
			: $scope.trail.slice(0, index + 1);

		trail.shift();

		var path = trail.join("/");

		db.getImportDirectory(path)
			.then(function(info) {
				if ( dir ) {
					$scope.trail.push(dir);
				}
				else {
					$scope.trail.splice(index + 1);
				}

				$scope.path = path;
				$scope.directories = info.directories;
				$scope.carts = info.carts;
				$scope.artists = info.artists;
			});
	};

	$scope.openCart = function(path, filename) {
		$uibModal.open({
			templateUrl: "views/import_cart.html",
			controller: "ImportCartCtrl",
			scope: angular.extend($rootScope.$new(), {
				path: path,
				filename: filename
			})
		});
	};

	$scope.openAlbum = function(path, artist_name) {
		$uibModal.open({
			templateUrl: "views/import_album.html",
			controller: "ImportAlbumCtrl",
			scope: angular.extend($rootScope.$new(), {
				path: path,
				artist_name: artist_name
			}),
			size: "lg"
		});
	};

	// initialize
	$scope.openDirectory(0);
}]);

importModule.controller("ImportAlbumCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.general_genres = db.getDefs("general_genres");
	$scope.mediums = db.getDefs("mediums");
	$scope.album = {
		tracks: []
	};

	$scope.save = function(album) {
		db.importAlbum(album).then(function() {
			alert.success("Album successfully imported.");
			$scope.$close();
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	db.getImportAlbum($scope.path, $scope.artist_name)
		.then(function(album) {
			$scope.album = album;
			$scope.album.path = $scope.path || "";
		});
}]);

importModule.controller("ImportCartCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.cart_types = db.getDefs("cart_type");
	$scope.cart = {};

	$scope.save = function(cart) {
		db.importCart(cart).then(function() {
			alert.success("Cart successfully imported.");
			$scope.$close();
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	db.getImportCart($scope.path, $scope.filename)
		.then(function(cart) {
			$scope.cart = cart;
			$scope.cart.path = $scope.path || "";
		});
}]);
