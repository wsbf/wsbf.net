"use strict";var playlistModule=angular.module("app.playlist",["app.database"]);playlistModule.controller("PlaylistCtrl",["$scope","$interval","db",function(t,l,a){t.playlist=[];var n=function(){a.Show.getPlaylist().then(function(t){return a.getAlbumArt(t,64)}).then(function(l){t.playlist=l})};n();var e=l(n,6e4);t.$on("$destroy",function(){l.cancel(e)})}]);