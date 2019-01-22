wsbf.net
========

This repository contains the code for all of WSBF's web presence, which includes the following sites:

- https://wsbf.net
- https://wsbf.net/mobile/ (also http://m.wsbf.net)
- https://wsbf.net/stream/ (formerly http://stream.wsbf.net:8000)
- https://wsbf.net/dj/     (formerly http://new.wsbf.net)
- https://dev.wsbf.net

## Installation

The development setup requires `node` and `npm`.

	git clone https://github.com/wsbf/wsbf.net [directory]
	cd [directory]
	npm install
	npm run build

	Ensure that the following links are created to ensure full website functionality:
	- client_build/camera -> /var/www/camera
	- client_build/dj/storage/music -> /var/www/Jemaine
	- client_build/dj/storage/shows -> /var/www/Bret

## Contributing

- Fork this repository
- Develop, push your changes to your fork
- Create a Pull Request in this repository with your changes

Currently, server-side development (PHP scripts) must be done on the web server because I don't have a test suite in place to mock the database and other PHP services.
