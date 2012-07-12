<?php

if (realpath($_SERVER['SCRIPT_FILENAME']) == str_replace('\\', '/', __FILE__)) {
	// You cannot request this file directly.
	exit;
}

mb_internal_encoding('UTF-8');

require_once 'inc/config.php';
include_once 'inc/instance-config.php';
