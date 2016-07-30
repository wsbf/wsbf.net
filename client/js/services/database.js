"use strict";

var databaseModule = angular.module("app.database", [
	"ngResource"
]);

/**
 * The Database service provides a single interface to server-side data.
 * The entire server API is implemented here so that every controller
 * uses this service instead of $http, which is an Angular service
 * that provides low-level HTTP operations.
 *
 * This service uses Promises, which are an abstraction of callbacks
 * that make asynchronous programming a little better.
 *
 * @param $http      service in module ng
 * @param $q         service in module ng
 * @param $resource  service in module ngResource
 */
databaseModule.service("db", ["$http", "$q", "$resource", function($http, $q, $resource) {

	/**
	 * Get album info through the last.fm API.
	 *
	 * @param artist
	 * @param album
	 */
	var AlbumInfo = $resource("https://ws.audioscrobbler.com/2.0/", {
		api_key: "74e3ab782313ff6e306a5f52a0e043ab",
		format: "json",
		method: "album.getinfo"
	}, {
		get: { method: "GET", cache: true }
	});

	/**
	 * Get album art for an array of tracks or albums.
	 *
	 * size parameter is based on last.fm API
	 *  1: 64 x 64
	 *  2: 174 x 174
	 *
	 * @param items  array of tracks or albums
	 * @param size   size of album art
	 * @return Promise of updated items
	 */
	this.getAlbumArt = function(items, size) {
		return $q.all(items.map(function(item) {
			// TODO: request artist photo?
			if ( item.lb_album === "" ) {
				return $q.resolve(item);
			}

			/* add image URL to each item */
			return AlbumInfo.get({
				artist: item.lb_artist,
				album: item.lb_album
			}).$promise.then(function(info) {
				if ( !info.error ) {
					item.imageUrl = info.album.image[size]["#text"];
				}

				return item;
			});
		}));
	};

	var Defs = $resource("/api/defs.php", {}, {
		get: { method: "GET", isArray: true, cache: true }
	});

	/**
	 * Get a definitions table.
	 *
	 * @param tableName
	 * @return table array
	 */
	this.getDefs = function(tableName) {
		return Defs.get({ table: tableName });
	};

	/**
	 * Get a preview of the most recent blog posts.
	 *
	 * @return Promise of blog posts array
	 */
	this.getBlogPreview = function() {
		return $http.get("/api/blog/preview.php")
			.then(function(res) {
				return res.data;
			});
	};

	/**
	 * Get album charting over a period of time.
	 *
	 * This function removes the millisecond component
	 * from timestamps to be consistent with the API.
	 *
	 * @param date1            start date timestamp
	 * @param date2            end date timestamp
	 * @param general_genreID  general genre ID
	 * @return Promise of chart array
	 */
	this.getTopAlbums = function(date1, date2, general_genreID) {
		return $http.get("/api/charts/albums.php", {
			params: {
				date1: Math.floor(date1 / 1000),
				date2: Math.floor(date2 / 1000),
				general_genreID: general_genreID
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Get the top tracks over a period of time.
	 *
	 * @param date1  start timestamp
	 * @param date2  end timestamp
	 * @return Promise of tracks array
	 */
	this.getTopTracks = function(date1, date2) {
		return $http.get("/api/charts/tracks.php", {
			params: {
				date1: date1,
				date2: date2
			}
		}).then(function(res) {
			return res.data;
		});
	};

	var Schedule = $resource("/api/schedule/schedule.php");

	/**
	 * Get the schedule for a day of the week.
	 *
	 * @param day  day of the week (0 is Sunday, etc.)
	 * @return schedule array
	 */
	this.getSchedule = function(day) {
		return Schedule.query({ day: day });
	};

	var Show = $resource("/api/shows/shows.php");

	/**
	 * Get a list of shows by page or DJ name.
	 *
	 * @param page   page offset
	 * @param query  search term
	 * @return shows array
	 */
	this.getShows = function(page, query) {
		return Show.query({
			page: page,
			term: query
		});
	};

	/**
	 * Get the playlist for a show, or the current
	 * show if no show ID is provided.
	 *
	 * @param showID  show ID
	 * @return Promise of playlist array
	 */
	this.getPlaylist = function(showID) {
		return $http.get("/api/shows/playlist.php", {
			params: {
				showID: showID
			}
		}).then(function(res) {
			return res.data;
		});
	};

	/**
	 * Get the current track.
	 *
	 * @return Promise of track object
	 */
	this.getNowPlaying = function() {
		return $http.get("/api/shows/now.php")
			.then(function(res) {
				return res.data;
			});
	};
}]);
