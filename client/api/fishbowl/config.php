<?php

/**
 * @file fishbowl/config.php
 * @author Ben Shealy
 *
 * Configuration for the fishbowl. These definitions
 * should produce the following timeline:
 *
 * 1) At some point before the beginning of each
 *    semester, the current fishbowl should be archived.
 * 2) From this point until the beginning of the semester,
 *    the fishbowl app should be open to fullstaff.
 * 3) At the beginning of the semester, the fishbowl app
 *    should close. From this point until the first fullstaff
 *    meeting, each member of senior staff should rate
 *    each fishbowl app.
 */
define('SPRING', 0);
define('FALL', 1);

/**
 * At the beginning of each application cycle, this
 * constant should be switched to the value of the NEXT semester
 */
define('SEMESTER', SPRING);

/**
 * At the beginning of each application cycle, the
 * earlier date should be set to the date of the
 * first day of classes of the semester. The later
 * date can be either the end of the semester of sometime
 * in the summer beforee the start of fall semester.
 *
 * TODO: automate these dates somehow?
 */
$SEMESTER_BEGIN = array(
	SPRING => strtotime("5:00 pm January 8, 2026"),
	FALL => strtotime("5:00 pm August 10, 2025")
);

define('REVIEW_BEGIN', $SEMESTER_BEGIN[!SEMESTER]);
define('DEADLINE', $SEMESTER_BEGIN[SEMESTER]);
?>
