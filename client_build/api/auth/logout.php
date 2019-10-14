<?php

/**
 * @file auth/logout.php
 * @author Ben Shealy
 *
 * Logout the current user.
 */
session_start();
session_destroy();

header("Location: /login/");
exit;
?>
