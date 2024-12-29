"use strict";

var importModule = angular.module("wizbif.import", [
	"ngFileUpload",
	"ui.bootstrap",
	"wizbif.alert",
	"wizbif.database"
]);

importModule.controller("ImportCtrl", ["$scope", "$rootScope", "$uibModal", "db", "Upload", function($scope, $rootScope, $uibModal, db, Upload) {
	$scope.carts = [];
	$scope.albums = [];

	/*
	 * upload multiple files or a single file to the library 
	 * array object 'files' is sent with POST to upload.php which handles uploading
	 * TODO: automatically rename the files to the recognized format defined in directory.php
	 * 		 maybe check metadata?
	 */
	$scope.uploadFiles = function (files) {
        if (files && files.length) {
			Upload.upload({
				url: 'http://localhost:8000/import/upload.php', // endpoint for upload handler
				data: { files: files}
			}).then(function (response) {
				console.log('Success: ' + response.config.data.files.name + ' uploaded');
			}, function (error) {
				console.error('Error uploading file: ' + files.name, error);
			});
			getDirectory(); // refresh the list of files
        }
    };
	
	var getDirectory = function() {
		db.Import.getDirectory()
			.then(function(info) {
				$scope.carts = info.carts;
				$scope.albums = info.albums;
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
