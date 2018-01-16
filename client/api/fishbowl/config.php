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
 * constant should be switched.
 */
define('SEMESTER', SPRING);

/**
 * At the beginning of each application cycle, the
 * earlier date should be set to the date of the
 * first fullstaff meeting of the semester.
 *
 * TODO: automate these dates somehow?
 */
$SEMESTER_BEGIN = array(
	SPRING => strtotime("5:00 pm January 12, 2018"),
	FALL => strtotime("5:00 pm August 23, 2018")
);

define('REVIEW_BEGIN', $SEMESTER_BEGIN[!SEMESTER]);
define('DEADLINE', $SEMESTER_BEGIN[SEMESTER]);
?>
