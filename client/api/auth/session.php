<?php

/**
 * @file /auth/session.php
 * @author Jai Agarwal
 */
session_start();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {

    header("Content-Type: application/json");

    if (isset($_SESSION["username"])) {
        // Return the username as a JSON object
        echo json_encode(["username" => $_SESSION["username"]]);
    } else {
        echo json_encode(["username" => null]);
    }

    exit;
}
?>
