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

	// upload later on form submit or something similar
	$scope.submit = function() {
		if ($scope.form.file.$valid && $scope.file) {
		$scope.upload($scope.file);
		}
	};

	// upload multiple files:
	$scope.uploadFiles = function (files) {
        if (files && files.length) {
			for (var i = 0; i < files.length; i++) {
                Upload.upload({
					url: 'https://wsbf.net/api/import/upload.php',
					data: { file: file[i] }
                }).then(function (response) {
                    console.log('Success: ' + response.config.data.file.name + ' uploaded');
                }, function (error) {
                    console.error('Error uploading file: ' + file.name, error);
                });
            }
			getDirectory();
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
