"use strict";var showModule=angular.module("app.show",["ui.router","app.database"]);showModule.controller("ShowListCtrl",["$scope","$stateParams","db",function(o,t,e){o.page=Number.parseInt(t.page),o.shows=e.Show.getShows(t.page,t.query)}]),showModule.controller("ShowCtrl",["$scope","$stateParams","db",function(o,t,e){o.playlist=[],e.Show.getPlaylist(t.showID).then(function(t){o.playlist=t})}]);