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
var index = express.Router();
index.use("/", express.static("./"));

var api = express.Router();
api.get("/defs.php", function(req, res, next) {
	var tablePath = path.join(__dirname, "api/defs", req.query.table);

	fs.readFile(tablePath, "utf-8", function(err, data) {
		if ( err ) {
			return next(err);
		}

		res.send(data);
	});
});
api.use("/", express.static(path.join(__dirname, "api")));

app.use("/api", api);
app.use("/", index);

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
	var addr = server.address().address;
	var port = server.address().port;

	console.log("Listening on port", port);
});
