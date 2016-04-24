"use strict";
var fs = require("fs");
var path = require("path");
var express = require("express");
var logger = require("morgan");
var bodyParser = require("body-parser");

// initialize app
var app = express();

// initialize middleware
app.use(logger("dev"));
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));

// define routes
var authenticated = true;

app.get("/api/defs.php", function(req, res, next) {
	var tablePath = path.join(__dirname, "api/defs", req.query.table);

	fs.readFile(tablePath, "utf-8", function(err, data) {
		if ( err ) {
			return next(err);
		}

		res.send(data);
	});
});

app.post("/api/login.php", function(req, res) {
	if ( req.body.username && req.body.password ) {
		authenticated = true;
		res.redirect("/wizbif/");
	}
	else {
		res.redirect("/login.html");
	}
});

app.get("/api/logout.php", function(req, res) {
	authenticated = false;
	res.redirect("/login.html");
});

app.post("/api/schedule/show.php", function(req, res) {
	res.status(200).end();
});

app.get("/api/users/user.php", function(req, res, next) {
	if ( !authenticated ) {
		res.status(401).end();
	}
	else {
		next();
	}
});

app.use("/api", express.static(path.join(__dirname, "api")));
app.use("/", express.static("./"));

// define 404 handler
app.use(function(req, res, next) {
	var err = new Error("Not Found: " + req.originalUrl);
	err.status = 404;
	next(err);
});

// define error handler
app.use(function(err, req, res, next) {
	console.error(err);
	res.status(err.status || 500).send({ message: err.message });
});

// start HTTP web server
var server = app.listen(8080, function() {
	console.log("Listening on port", server.address().port);
});
