<?php

/**
 * @file auth/functions.php
 * @author Ben Shealy
 *
 * Functions for password hashing and validation.
 *
 * Currently, passwords are sent in plaintext by the login
 * and register pages, so the server should use TLS
 * encryption.
 *
 * TODO: These functions are based on the previous production code,
 * but in the future it may be better to use a library that
 * handles salting, hashing, and validating. In either case,
 * these functions should be reviewed for security vulnerabilites.
 */

/**
 * Hash a new password.
 *
 * @param password  plaintext password
 * @return hashed password
 */
function hash_password($password)
{
	// could move MD5 stage to client
	$password = md5($password);

	// salt password with 256 random bits
	$salt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
	$hash = hash("sha512", $salt . $password);

	return $salt . $hash;
}

/**
 * Validate a password against a hashed password.
 *
 * @param password        plaintext password
 * @param hashedPassword  hashed password
 * @return true if the passwords match, false otherwise
 */
function validate_password($password, $hashedPassword)
{
	// could move MD5 stage to client
	$password = md5($password);

	$salt = substr($hashedPassword, 0, 64);
	$hash = substr($hashedPassword, 64, 128);

	$test_hash = hash("sha512", $salt . $password);

	return $test_hash === $hash;
}
?>
