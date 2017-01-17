"use strict";

var cartModule = angular.module("wizbif.carts", [
	"ui.bootstrap",
	"ui.router",
	"wizbif.alert",
	"wizbif.database"
]);

cartModule.controller("CartsCtrl", ["$scope", "$stateParams", "$uibModal", "$rootScope", "alert", "db", function($scope, $stateParams, $uibModal, $rootScope, alert, db) {
	$scope.cart_types = db.getDefs("cart_type");
	$scope.cart_typeID = $stateParams.cart_typeID;
	$scope.carts = [];

	var getCarts = function(cart_typeID) {
		db.Carts.getCarts(cart_typeID)
			.then(function(carts) {
				$scope.carts = carts;
			});
	};

	$scope.editCart = function(cartID) {
		$uibModal.open({
			templateUrl: "views/carts_admin_edit.html",
			controller: "CartEditCtrl",
			scope: angular.extend($rootScope.$new(), {
				cartID: cartID
			})
		}).result.then(function() {
			getCarts($scope.cart_typeID);
		});
	};

	// initialize
	getCarts($scope.cart_typeID);
}]);

cartModule.controller("CartEditCtrl", ["$scope", "alert", "db", function($scope, alert, db) {
	$scope.cart_types = db.getDefs("cart_type");
	$scope.cart = {};

	var getCart = function(cartID) {
		db.Carts.getCart(cartID)
			.then(function(cart) {
				cart.start_date = new Date(cart.start_date);
				cart.end_date = new Date(cart.end_date);

				$scope.cart = cart;
			});
	};

	$scope.save = function(cart) {
		db.Carts.saveCart(cart).then(function() {
			$scope.$close();
			alert.success("Cart successfully saved.");
		}, function(res) {
			alert.error(res.data || res.statusText);
		});
	};

	// initialize
	getCart($scope.cartID);
}]);
