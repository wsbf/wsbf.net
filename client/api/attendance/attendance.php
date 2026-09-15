<?php
/**
 * Attendance events and attendance records.
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("../fishbowl/config.php");

$EVENT_TYPES = array(
	"full_staff" => "Full staff meeting",
	"virtual_full_staff" => "Virtual full staff meeting",
	"committee" => "Committee meeting",
	"other" => "Special/other"
);

function attendance_events($mysqli, $activeOnly, $username = null)
{
	$where = $activeOnly ? "WHERE e.event_date >= CURDATE()" : "";
	$q = "SELECT e.eventID, e.name, e.event_type, e.event_date, "
		. "IF(a.username IS NULL, 0, 1) AS attending "
		. "FROM attendance_events e "
		. ($username === null ? "LEFT JOIN attendance a ON a.eventID=e.eventID AND 1=0 " : "LEFT JOIN attendance a ON a.eventID=e.eventID AND a.username='$username' ")
		. "$where ORDER BY e.event_date ASC, e.eventID ASC;";
	$events = fetch_array(exec_query($mysqli, $q));
	foreach ($events as &$event) {
		$event["attending"] = (bool) $event["attending"];
		$event["attendees"] = array();
		if (!$activeOnly) {
			$attendeeQuery = "SELECT a.attendanceID, a.username, u.preferred_name, a.attended_at AS attendedAt "
				. "FROM attendance a INNER JOIN users u ON u.username=a.username "
				. "WHERE a.eventID='{$event["eventID"]}' ORDER BY u.preferred_name;";
			$event["attendees"] = fetch_array(exec_query($mysqli, $attendeeQuery));
		}
	}
	return $events;
}

function valid_event($event, $types)
{
	$timestamp = isset($event["eventDate"]) ? strtotime($event["eventDate"]) : false;
	return !empty($event["name"])
		&& isset($types[$event["eventType"]])
		&& !empty($event["eventDate"])
		&& $timestamp !== false
		&& !empty($event["password"]);
}

authenticate();
$mysqli = construct_connection();

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$isAdmin = isset($_GET["admin"]) && $_GET["admin"] == "1";
	if ($isAdmin && !auth_senior_staff($mysqli)) {
		header("HTTP/1.1 404 Not Found"); exit;
	}
	$events = attendance_events($mysqli, !$isAdmin, $_SESSION["username"]);
	foreach ($events as &$event) {
		$event["typeLabel"] = $GLOBALS["EVENT_TYPES"][$event["event_type"]];
	}
	$mysqli->close();
	header("Content-Type: application/json");
	exit(json_encode($events));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$input = json_decode(file_get_contents("php://input"), true);
	if (isset($input["eventID"])) {
		if (!auth_member($mysqli) || empty($input["password"]) || !is_numeric($input["eventID"])) {
			header("HTTP/1.1 404 Not Found"); exit;
		}
		$eventID = $mysqli->escape_string($input["eventID"]);
		$event = exec_query($mysqli, "SELECT * FROM attendance_events WHERE eventID='$eventID' AND event_date >= CURDATE();")->fetch_assoc();
		if (!$event || !password_verify($input["password"], $event["password_hash"])) {
			header("HTTP/1.1 404 Not Found"); exit("Invalid event or password.");
		}
		$username = $mysqli->escape_string($_SESSION["username"]);
		exec_query($mysqli, "INSERT IGNORE INTO attendance (eventID, username, attended_at) VALUES ('$eventID', '$username', NOW());");
		if ($event["event_type"] == "committee") {
			$type = exec_query($mysqli, "SELECT typeID FROM def_fishbowl_log_types WHERE type LIKE '%Committee Meeting%' LIMIT 1;")->fetch_assoc();
			if ($type) {
				$description = $mysqli->escape_string("Attendance: " . $event["name"]);
				exec_query($mysqli, "INSERT INTO fishbowl_log (username, date, log_type, description) SELECT '$username', '{$event["event_date"]}', '{$type["typeID"]}', '$description' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM fishbowl_log WHERE username='$username' AND date='{$event["event_date"]}' AND log_type='{$type["typeID"]}' AND description='$description');");
			}
		}
		$mysqli->close(); exit;
	}
	if (!auth_senior_staff($mysqli) || !valid_event($input, $GLOBALS["EVENT_TYPES"])) {
		header("HTTP/1.1 404 Not Found"); exit("Invalid event.");
	}
	$name = $mysqli->escape_string($input["name"]);
	$type = $mysqli->escape_string($input["eventType"]);
	$date = $mysqli->escape_string(date("Y-m-d", strtotime($input["eventDate"])));
	$hash = $mysqli->escape_string(password_hash($input["password"], PASSWORD_DEFAULT));
	exec_query($mysqli, "INSERT INTO attendance_events (name, event_type, event_date, password_hash, created_by) VALUES ('$name', '$type', '$date', '$hash', '" . $mysqli->escape_string($_SESSION["username"]) . "');");
	$mysqli->close(); exit;
}

if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
	if (!auth_senior_staff($mysqli)) {
		header("HTTP/1.1 404 Not Found"); exit;
	}
	if (isset($_GET["eventID"])) {
		if (!is_numeric($_GET["eventID"])) {
			header("HTTP/1.1 404 Not Found"); exit;
		}
		exec_query($mysqli, "DELETE FROM attendance_events WHERE eventID='" . $mysqli->escape_string($_GET["eventID"]) . "';");
		$mysqli->close(); exit;
	}
	if (!isset($_GET["attendanceID"]) || !is_numeric($_GET["attendanceID"])) {
		header("HTTP/1.1 404 Not Found"); exit;
	}
	$attendanceID = $mysqli->escape_string($_GET["attendanceID"]);
	$record = exec_query($mysqli, "SELECT a.username, e.event_type, e.event_date, e.name FROM attendance a INNER JOIN attendance_events e ON e.eventID=a.eventID WHERE a.attendanceID='$attendanceID';")->fetch_assoc();
	if ($record && $record["event_type"] == "committee") {
		$type = exec_query($mysqli, "SELECT typeID FROM def_fishbowl_log_types WHERE type LIKE '%Committee Meeting%' LIMIT 1;")->fetch_assoc();
		if ($type) {
			$description = $mysqli->escape_string("Attendance: " . $record["name"]);
			exec_query($mysqli, "DELETE FROM fishbowl_log WHERE username='" . $mysqli->escape_string($record["username"]) . "' AND date='" . $mysqli->escape_string($record["event_date"]) . "' AND log_type='{$type["typeID"]}' AND description='$description';");
		}
	}
	exec_query($mysqli, "DELETE FROM attendance WHERE attendanceID='$attendanceID';");
	$mysqli->close(); exit;
}
?>
