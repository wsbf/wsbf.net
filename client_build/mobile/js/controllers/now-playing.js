"use strict";var nowPlayingModule=angular.module("app.now-playing",["ngSanitize","com.2fdevs.videogular","com.2fdevs.videogular.plugins.controls","app.database"]);nowPlayingModule.controller("NowPlayingCtrl",["$scope","$interval","db",function(e,o,n){e.config={preload:"none",sources:[{src:"/stream/high",type:"audio/mpeg"},{src:"/stream/8.ogg",type:"audio/ogg"}],theme:"/bower_components/videogular-themes-default/videogular.css"},e.track={};var a=function(){n.Show.getNowPlaying().then(function(e){return n.getAlbumArt([e],300)}).then(function(o){e.track=o[0]})};a();var t=o(a,1e4);e.$on("$destroy",function(){o.cancel(t)})}]);