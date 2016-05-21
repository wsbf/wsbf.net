#!/bin/bash
# Fetch JSON responses from the API for client-side testing
#
# TODO: add a username/password prompt for private API endpoints

mkdir -p dev/api/blog
curl "https://wsbf.net/api/blog/preview.php" -o dev/api/blog/preview.php

mkdir -p dev/api/charts
curl "https://wsbf.net/api/charts/albums.php?date1=1461330494&date2=1461935294" -o dev/api/charts/albums.php
curl "https://wsbf.net/api/charts/tracks.php?date1=1461330494&date2=1461935294" -o dev/api/charts/tracks.php

mkdir -p dev/api/defs
curl "https://wsbf.net/api/defs.php?table=airability" -o dev/api/defs/airability
curl "https://wsbf.net/api/defs.php?table=cart_type" -o dev/api/defs/cart_type
curl "https://wsbf.net/api/defs.php?table=days" -o dev/api/defs/days
curl "https://wsbf.net/api/defs.php?table=general_genres" -o dev/api/defs/general_genres
curl "https://wsbf.net/api/defs.php?table=mediums" -o dev/api/defs/mediums
curl "https://wsbf.net/api/defs.php?table=rotations" -o dev/api/defs/rotations
curl "https://wsbf.net/api/defs.php?table=show_times" -o dev/api/defs/show_times
curl "https://wsbf.net/api/defs.php?table=show_types" -o dev/api/defs/show_types
curl "https://wsbf.net/api/defs.php?table=status" -o dev/api/defs/status
curl "https://wsbf.net/api/defs.php?table=teams" -o dev/api/defs/teams

mkdir -p fishbowl

mkdir -p import

mkdir -p library

mkdir -p logbook

mkdir -p dev/api/schedule
curl "https://wsbf.net/api/schedule/schedule.php?day=0" -o dev/api/schedule/schedule.php
curl "https://wsbf.net/api/schedule/show.php?scheduleID=1028" -o dev/api/schedule/show.php

mkdir -p dev/api/shows
curl "https://wsbf.net/api/shows/now.php" -o dev/api/shows/now.php
curl "https://wsbf.net/api/shows/playlist.php" -o dev/api/shows/playlist.php
curl "https://wsbf.net/api/shows/shows.php?page=0" -o dev/api/shows/shows.php

mkdir -p showsub

mkdir -p dev/api/users
curl "https://wsbf.net/api/users/reviews.php" -o dev/api/users/reviews.php
