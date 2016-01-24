<?php

/**
 * @file logout.php
 * @author Ben Shealy
 *
 * @section DESCRIPTION
 *
 * Logout the current user.
 */
session_start();
session_destroy();

header("Location: /login.html");
exit;
?>
