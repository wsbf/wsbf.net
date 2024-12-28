"use strict";

var importModule = angular.module("wizbif.import", [
	"ui.bootstrap",
	"wizbif.alert",
	"wizbif.database"
]);

importModule.directive('fileModel', ['$parse', function ($parse) {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
			var model = $parse(attrs.fileModel);
			var modelSetter = model.assign;

			element.bind('change', function(){
				scope.$apply(function(){
					modelSetter(scope, element[0].files[0]);
				});
			});
		}
	};
}]).controller("ImportCtrl", ["$scope", "$rootScope", "$uibModal", "db", "alert", function($scope, $rootScope, $uibModal, db, alert) {
	$scope.carts = [];
	$scope.albums = [];

	var getDirectory = function() {
		db.Import.getDirectory()
			.then(function(info) {
				$scope.carts = info.carts;
				$scope.albums = info.albums;
			});
	};

	$scope.uploadFile = function(file, folder) {
		if (!file) {
			alert.error("No file selected.");
			return;
		}

		db.Import.uploadFile(file, folder)
			.then(function(response) {
				alert.success("File uploaded successfully: " + response.filename);
				getDirectory();
			})
			.catch(function(err) {
				alert.error("Upload failed: " + (err.data || err.statusText));
			});
	};

	$scope.deleteFile = function(filename, folder) {
		if (!filename || !folder) {
			alert.error("Missing filename or folder for delete.");
			return;
		}
		db.Import.deleteFile(filename, folder)
			.then(function(response) {
				alert.success("File deleted: " + response.filename);
				getDirectory();
			})
			.catch(function(err) {
				alert.error("Delete failed: " + (err.data || err.statusText));
			});
	};

	$scope.openCart = function(filename) {
		$uibModal.open({
			templateUrl: "views/import_cart.html",
			controller: "ImportCartCtrl",
			scope: angular.extend($rootScope.$new(), {
				filename: filename
			})
		}).result.then(getDirectory);
	};

	$scope.openAlbum = function(album) {
		$uibModal.open({
			templateUrl: "views/import_album.html",
			controller: "ImportAlbumCtrl",
			scope: angular.extend($rootScope.$new(), {
				artist_name: album.artist_name,
				album_name: album.album_name
			}),
			size: "lg"
		}).result.then(getDirectory);
	};

	// initialize
	getDirectory();
}]);

importModule.controller("ImportAlbumCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.general_genres = db.getDefs("general_genres");
	$scope.album = {};

	$scope.save = function(album) {
		db.Import.importAlbum(album).then(function() {
			alert.success("Album successfully imported.");
			$scope.$close();
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	db.Import.getAlbum($scope.artist_name, $scope.album_name)
		.then(function(album) {
			$scope.album = album;
		});
}]);

importModule.controller("ImportCartCtrl", ["$scope", "db", "alert", function($scope, db, alert) {
	$scope.cart_types = db.getDefs("cart_type");
	$scope.cart = {};

	$scope.save = function(cart) {
		db.Import.importCart(cart).then(function() {
			alert.success("Cart successfully imported.");
			$scope.$close();
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	db.Import.getCart($scope.filename)
		.then(function(cart) {
			$scope.cart = cart;
		});
}]);
