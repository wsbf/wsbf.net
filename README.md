wsbf.net
========

The WSBF website, written in Angular.

This repository is an attempt to integrate all of the various fragments of WSBF's web presence, which includes:

1. The public website [wsbf.net](wsbf.net)
2. The private website for DJs and senior staff [new.wsbf.net](new.wsbf.net)
3. Max's streaming website [new.wsbf.net/jpgr](new.wsbf.net/jpgr)
4. The charting functionality from the old site [new.wsbf.net/old_wsbf/wsbf](new.wsbf.net/old_wsbf/wsbf)
5. The development website [dev.wsbf.net](dev.wsbf.net)
6. Old Wordpress website
7. Mobile site [m.wsbf.net](m.wsbf.net)
8. Studio A webcam
9. ZAutomate
10. RDS Program

The ultimate goal is to bring all of these resources into one "WSBF" service for end users, DJs, and senior staff.

Right now, the web server is somewhat cluttered with directories, so a proposed directory structure is given below, which should be expanded as the organization of resources becomes clear.

	/var/www
	├── dev/
	│   └── ... (same structure as wsbf/)
	└── wsbf/
		├── api/
		└── wizbif/

TODO: when this repository can be moved to dev.wsbf.net, commit all PHP scripts under /api and /wizbif/api. From there, frontend development can be done locally or through webserver, and backend development can be done on webserver, but both environments should push and pull from github, and they should be in sync before deploying to wsbf.net.

TODO: Use gulp to create automated build.
