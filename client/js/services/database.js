/**
 * The Database service provides an interface to server-side data.
 * The entire server API is implemented here so that every controller
 * uses this service instead of using HTTP requests directly.
 *
 * This service uses Promises, which are an abstraction of callbacks
 * that make asynchronous programming a little better.
 */
"use strict";

var databaseModule = angular.module("app.database", [
	"ngResource"
]);

databaseModule.service("db", ["$http", "$q", "$resource", function($http, $q, $resource) {

	var api = {};

	api.Defs = $resource("/api/defs.php", {}, {
		get: { method: "GET", isArray: true, cache: true }
	});

	api.Schedule = $resource("/api/schedule/schedule.php");

	api.Show = $resource("/api/shows/shows.php");

	var _spotifyAuth = null;

	/**
	 * Get album art for an array of tracks or albums.
	 *
	 * The Spotify API seems to consistently provide album art
	 * in sizes 64x64, 300x300, and 640x640, so the size
	 * parameter should be one of these three values.
	 *
	 * @param items  array of tracks or albums
	 * @param size   size of album art
	 * @return Promise of updated items
	 */
	this.getAlbumArt = function(items, size) {
		_spotifyAuth = _spotifyAuth || $http.get("/api/auth/spotify.php").then(function(res) {
			return res.data;
		});

		return _spotifyAuth.then(function(auth) {
			var promises = items.map(function(item) {
				if ( item.lb_album === "" ) {
					return $q.resolve(item);
				}

				return $http
					.get("https://api.spotify.com/v1/search", {
						cache: true,
						headers: {
							Authorization: "Bearer " + auth.access_token
						},
						params: {
							type: "album",
							limit: 1,
							q: "artist:" + item.lb_artist + " " + "album:" + item.lb_album
						}
					})
					.then(function(res) {
						var album = res.data.albums.items[0];

						if ( album ) {
							var image = _.find(album.images, { height: size }) || {};
							item.imageUrl = image.url;
						}

						return item;
					}, function() {
						return item;
					});
			});

			return $q.all(promises);
		});
	};

	/**
	 * Get a definitions table.
	 *
	 * @param tableName
	 * @return table array
	 */
	this.getDefs = function(tableName) {
		return api.Defs.get({ table: tableName });
	};

	this.Blog = {};

	/**
	 * Get a preview of the most recent blog posts.
	 *
	 * @return Promise of blog posts array
	 */
	this.Blog.getPreview = function() {
		return $http.get("/api/blog/preview.php")
			.then(function(res) {
				return res.data;
			});
	};

	this.Charts = {};

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
	this.Charts.getTopAlbums = function(date1, date2, general_genreID) {
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
	this.Charts.getTopTracks = function(date1, date2) {
		return $http.get("/api/charts/tracks.php", {
			params: {
				date1: Math.floor(date1 / 1000),
				date2: Math.floor(date2 / 1000)
			}
		}).then(function(res) {
			return res.data;
		});
	};

	this.Schedule = {};

	/**
	 * Get the schedule for a day of the week.
	 *
	 * @param day  day of the week (0 is Sunday, etc.)
	 * @return schedule array
	 */
	this.Schedule.get = function(day) {
		return api.Schedule.query({ day: day });
	};

	this.Show = {};

	/**
	 * Get a list of shows by page or DJ name.
	 *
	 * @param page
	 * @param query
	 * @return shows array
	 */
	this.Show.getShows = function(page, query) {
		return api.Show.query({
			page: page,
			term: query
		});
	};

	/**
	 * Get the playlist for a show, or the current
	 * show if no show ID is provided.
	 *
	 * @param showID
	 * @return Promise of playlist array
	 */
	this.Show.getPlaylist = function(showID) {
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
	this.Show.getNowPlaying = function() {
		return $http.get("/api/shows/now.php")
			.then(function(res) {
				return res.data;
			});
	};
}]);
