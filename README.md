wsbf.net
========

The WSBF website, written in Angular.

This repository is an attempt to integrate all of the various fragments of WSBF's web presence, which includes:

- The public website [wsbf.net](http://wsbf.net)
- The private website for DJs and senior staff [new.wsbf.net](http://new.wsbf.net)
- Max's streaming website [new.wsbf.net/jpgr](http://new.wsbf.net/jpgr)
- The development website [dev.wsbf.net](http://dev.wsbf.net)
- Mobile site [m.wsbf.net](http://m.wsbf.net)
- Darkice and Icecast [wsbf.net:8000](http://wsbf.net:8000)
- Studio A webcam
- ZAutomate
- RDS Sender

The ultimate goal is to bring all of these resources into one "WSBF" service for end users, DJs, and senior staff.

## Installation

The development setup requires `node` and `npm`.

Pull this repository into `/var/www/dev` and run `npm install` to deploy `dev.wsbf.net`. Run `npm run build` to copy the necessary files into `/var/www/wsbf` and deploy `wsbf.net`.

## Contributing

- Fork this repository
- Develop, push your changes to your fork
- Create a Pull Request in this repository with your changes

Currently, server-side development (PHP scripts) must be done on the web server because I don't have a test suite in place to mock the database and other PHP services.

## TODO

### General

- consider applying to [madewithangular.com](https://www.madewithangular.com)
- create a specification for DJ points

### Server

- use Let's Encrypt for TLS encryption
- replace MySQL queries with prepared statements/stored procedures
- write script to merge fragmented shows in `shows` table
- polish database (remove HTML entities like `&amp;`, check for invalid values, etc.)
- upgrade MySQL and PHP to newest versions
- complete the PHP implementation of RDS sender
- add server-side authentication/redirect to /wizbif directory

### Client (public)

- develop unit tests with Karma and Jasmine
- add typeahead directive to any view with DJ name search
- try to integrate components into angular app:

```
	twitter social stream
	chat widget
	audio streams
```
- use DJ profile pics for schedule view
- add messages for empty content

### Client (private)

- discuss removing `def_mediums`, `libcart`.`play_mask`

- monthly breakdown of album reviews by DJ
- album checkout interface
- interface to request/grant/deny access to intern status and show times
- maybe have account registration be approved
- user profile photos
- logbook
- "spaz sheet" interface for logbook (with captcha)
- move views into modals where appropriate
- move services and controllers into separate files
- merge Import and Library control panels
- add help content/minigame to home page
- streamline fishbowl review process
