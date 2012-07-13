<?php

if (realpath($_SERVER['SCRIPT_FILENAME']) == str_replace('\\', '/', __FILE__)) {
	// You cannot request this file directly.
	exit;
}

mb_internal_encoding('UTF-8');

require_once 'inc/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
      'cache' => 'templates/cache',
));

require_once 'inc/config.php';
if(file_exists('inc/instance-config.php'))
	require_once 'inc/instance-config.php';

function db_connect() {
	global $config;
	$dsn = 'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'];
	$dbh = new PDO($dsn, $config['db_user'], $config['db_pass']);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}
