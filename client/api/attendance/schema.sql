CREATE TABLE attendance_events (
	eventID INT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(255) NOT NULL,
	event_type ENUM('full_staff', 'virtual_full_staff', 'committee', 'other') NOT NULL,
	event_date DATE NOT NULL,
	password_hash VARCHAR(255) NOT NULL,
	created_by VARCHAR(255) NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (eventID),
	INDEX (event_date),
	INDEX (event_type)
);

CREATE TABLE attendance (
	attendanceID INT UNSIGNED NOT NULL AUTO_INCREMENT,
	eventID INT UNSIGNED NOT NULL,
	username VARCHAR(255) NOT NULL,
	attended_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (attendanceID),
	UNIQUE KEY attendance_event_user (eventID, username),
	INDEX (username),
	CONSTRAINT attendance_event_fk FOREIGN KEY (eventID) REFERENCES attendance_events(eventID) ON DELETE CASCADE
);
