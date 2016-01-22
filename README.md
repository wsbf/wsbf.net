wsbf.net
========

The WSBF website, written in Angular.

This repository is an attempt to integrate all of the various fragments of WSBF's web presence, which includes:

- The public website [wsbf.net](wsbf.net)
- The private website for DJs and senior staff [new.wsbf.net](new.wsbf.net)
- Max's streaming website [new.wsbf.net/jpgr](new.wsbf.net/jpgr)
- The development website [dev.wsbf.net](dev.wsbf.net)
- Old Wordpress website [new.wsbf.net/wordpress](new.wsbf.net/wordpress)
- Mobile site [m.wsbf.net](m.wsbf.net)
- Studio A webcam
- ZAutomate
- RDS Program

The ultimate goal is to bring all of these resources into one "WSBF" service for end users, DJs, and senior staff.

## TODO

- when this repository can be moved to dev.wsbf.net, commit all PHP scripts under /api. From there, frontend development can be done locally or through webserver, and backend development can be done on webserver, but both environments should push and pull from github, so that they are in sync before deploying to wsbf.net.
- use gulp to create automated build.
- write script to merge fragmented shows in `shows` table
- write script to remove automation archives older than 6 months
