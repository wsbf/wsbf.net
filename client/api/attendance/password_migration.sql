ALTER TABLE attendance_events ADD COLUMN password VARCHAR(255) NOT NULL DEFAULT '' AFTER event_date;
