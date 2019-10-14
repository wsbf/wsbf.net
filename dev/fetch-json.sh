#!/bin/bash
# Fetch JSON responses from the API for client-side testing.
#
# Note that you will only be able to fetch responses from
# API endpoints that you are authorized to use. In general,
# if you are on senior staff then you should be able to
# fetch everything.

echo -n "Username: "
read username

echo -n "Password: "
read -s password
echo

# parse PHPSESSID from Set-Cookie header
#
# TODO: Not sure if the matching code is guaranteed to parse all session ID's
# correctly. A more robust solution using sed or awk or a more sophisticated
# scripting language like NodeJS may be required.
#
RES=$(curl -i "https://wsbf.net/api/auth/login.php" -d "{\"username\":\"$username\",\"password\":\"$password\"}" 2> /dev/null | grep -w PHPSESSID)
PHPSESSID=${RES:22:26}

mkdir -p dev/api/blog
curl -# "https://wsbf.net/api/blog/preview.php" -o dev/api/blog/preview.php

mkdir -p dev/api/charts
curl -# "https://wsbf.net/api/charts/albums.php?date1=1461330494&date2=1461935294" -o dev/api/charts/albums.php
curl -# "https://wsbf.net/api/charts/tracks.php?date1=1461330494&date2=1461935294" -o dev/api/charts/tracks.php

mkdir -p dev/api/defs
curl -# "https://wsbf.net/api/defs.php?table=airability" -o dev/api/defs/airability
curl -# "https://wsbf.net/api/defs.php?table=cart_type" -o dev/api/defs/cart_type
curl -# "https://wsbf.net/api/defs.php?table=days" -o dev/api/defs/days
curl -# "https://wsbf.net/api/defs.php?table=general_genres" -o dev/api/defs/general_genres
curl -# "https://wsbf.net/api/defs.php?table=rotations" -o dev/api/defs/rotations
curl -# "https://wsbf.net/api/defs.php?table=show_times" -o dev/api/defs/show_times
curl -# "https://wsbf.net/api/defs.php?table=show_types" -o dev/api/defs/show_types
curl -# "https://wsbf.net/api/defs.php?table=status" -o dev/api/defs/status
curl -# "https://wsbf.net/api/defs.php?table=teams" -o dev/api/defs/teams

mkdir -p dev/api/fishbowl
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/fishbowl/app.php" -o dev/api/fishbowl/app.php
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/fishbowl/fishbowl.php" -o dev/api/fishbowl/fishbowl.php
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/fishbowl/review.php?id=43" -o dev/api/fishbowl/review.php

mkdir -p dev/api/import
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/import/album.php?artist_name=Chick+Corea+Trio&album_name=Trilogy" -o dev/api/import/album.php
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/import/cart.php?filename=Layers.mp3" -o dev/api/import/cart.php
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/import/directory.php?path=albums" -o dev/api/import/directory.php

mkdir -p dev/api/library
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/library/album.php?albumID=5761" -o dev/api/library/album.php
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/library/library.php?rotationID=0&page=1" -o dev/api/library/library.php

# TODO: show.php and track.php return 404 because user isn't signed into logbook
mkdir -p dev/api/logbook
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/logbook/show.php" -o dev/api/logbook/show.php
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/logbook/listener_count.php" -o dev/api/logbook/listener_count.php
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/logbook/track.php?album_code=K919" -o dev/api/logbook/track.php

mkdir -p dev/api/schedule
curl -# "https://wsbf.net/api/schedule/schedule.php?day=0" -o dev/api/schedule/schedule.php
curl -# "https://wsbf.net/api/schedule/show.php?scheduleID=1029" -o dev/api/schedule/show.php

mkdir -p dev/api/shows
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/shows/archives.php?page=1" -o dev/api/shows/archives.php
curl -# "https://wsbf.net/api/shows/now.php" -o dev/api/shows/now.php
curl -# "https://wsbf.net/api/shows/playlist.php" -o dev/api/shows/playlist.php
curl -# "https://wsbf.net/api/shows/shows.php?page=0" -o dev/api/shows/shows.php

mkdir -p dev/api/showsub
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/showsub/request_list.php" -o dev/api/showsub/request_list.php

mkdir -p dev/api/users
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/users/reviews.php?date1=1459483200&date2=1462075200" -o dev/api/users/reviews.php
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/users/search.php?term=ott" -o dev/api/users/search.php
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/users/user.php" -o dev/api/users/user.php
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/users/users_admin.php" -o dev/api/users/users_admin.php
curl -# -b PHPSESSID="$PHPSESSID" "https://wsbf.net/api/users/users.php" -o dev/api/users/users.php
