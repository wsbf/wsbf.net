wsbf.net
========

The WSBF website, written in Angular.

This repository is an attempt to integrate all of the various fragments of WSBF's web presence, which includes:

- The public website [wsbf.net](http://wsbf.net)
- The private website for DJs and senior staff [new.wsbf.net](http://new.wsbf.net)
- Max's streaming website [new.wsbf.net/jpgr](http://new.wsbf.net/jpgr)
- The development website [dev.wsbf.net](http://dev.wsbf.net)
- Mobile site [m.wsbf.net](http://m.wsbf.net)
- Darkice and Icecast [stream.wsbf.net:8000](http://stream.wsbf.net:8000)
- Studio A webcam
- ZAutomate
- RDS Program

The ultimate goal is to bring all of these resources into one "WSBF" service for end users, DJs, and senior staff.

## Installation

Pull this repository into `/var/www/dev` and run `npm install` to deploy `dev.wsbf.net`. Run `npm run build` to copy the necessary files into `/var/www/wsbf` and deploy `wsbf.net`.

## TODO

- front-end development can be done locally or through web server, and back-end development can be done on web server, but both environments should push and pull from github, so that they are in sync before deploying to wsbf.net.
- use jasmine/karma to create unit tests
- write script to merge fragmented shows in `shows` table
- write script to remove automation archives older than 6 months
