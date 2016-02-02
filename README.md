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

The development setup requires `node` and `npm`.

Pull this repository into `/var/www/dev` and run `npm install` to deploy `dev.wsbf.net`. Run `npm run build` to copy the necessary files into `/var/www/wsbf` and deploy `wsbf.net`.

## Development

I'm still trying to learn the best practices for code collaboration on Github, so for now I follow these steps during each development session:

1. Pull the latest commits from Github into your local environment (`git pull origin master`). Always pull __before__ you make any changes to your working tree so that you don't have any conflicts.
2. Develop.
3. Test your changes with `npm run lint` and `npm test`.
4. Commit your changes and push them to Github (`git push -u origin master`).

Alternatively you can fork this repository, pull and push whenever you want, and then make a pull request here when you're ready. I don't know much about pull requests yet.

For server-side development (working on the PHP scripts), you'll need to develop on the web server so that you can test your changes on `dev.wsbf.net` before you commit. In the future, if we can develop a test suite for server scripts that mocks the database and other PHP services then we might be able to develop the PHP scripts locally with confidence. For now, just use the above process on the web server and use your web browser to test your changes.

## TODO

### Server-side

- develop PHP scripts to replace `new.wsbf.net`
- use Let's Encrypt for TLS encryption
- replace MySQL queries with prepared statements/stored procedures
- write script to merge fragmented shows in `shows` table
- write script to remove automation archives
- polish database (remove HTML entities like `&amp;`, check for invalid values, etc.)

### Client-side (public)

- develop unit tests with Karma and Jasmine
- add typeahead directive to any view with DJ name search
- try to integrate components into angular app:

```
	twitter social stream
	chat widget
	audio streams
```
- use DJ profile pics for schedule view
- sort charting by genre

### Client-side (private)

- add server-side authentication/redirect to /wizbif directory
- develop views and controllers to replace `new.wsbf.net`
- develop a monthly breakdown of album reviews by DJ
- develop album checkout interface
- consider having `checkout` and `logbook` users
- or merge login and show select for logbook
- develop a "spaz sheet" interface to logbook view (with captcha)

### Miscellaneous

- consider applying to [madewithangular.com](https://www.madewithangular.com)
- create a specification for DJ points
