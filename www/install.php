<?php

require_once 'inc/functions.php';

if (file_exists('inc/instance-config.php')) {
	die($twig->render('install.html', array(
		'error' => "Already installed!",
	)));
}

if (isset($_POST['do_install'])) {
	$config['db_user'] = $_POST['db_user'];
	$config['db_pass'] = $_POST['db_pass'];
	$config['db_name'] = $_POST['db_name'];
	$config['db_host'] = $_POST['db_host'];

	$error = null;
	$statuses = array();

	function do_install() {
		global $config, $statuses;

		$dbh = db_connect();
	
		$statuses[] = "Database connection successful!";
	
		$sql = @file_get_contents('install.sql') or die("Couldn't load install.sql.");
		preg_match_all("/(^|\n)((SET|CREATE|INSERT).+)\n\n/msU", $sql, $queries);
		$queries = $queries[2];
	
		foreach ($queries as &$query) {
			$dbh->exec($query);
		}

		$statuses[] = "Database install successful!";

		$ic = fopen('inc/instance-config.php', 'w');
		fwrite($ic, "<?php\n");
		fwrite($ic, '$config[\'db_user\'] = \'' . addslashes($config['db_user']) . "';\n");
		fwrite($ic, '$config[\'db_pass\'] = \'' . addslashes($config['db_pass']) . "';\n");
		fwrite($ic, '$config[\'db_name\'] = \'' . addslashes($config['db_name']) . "';\n");
		fwrite($ic, '$config[\'db_host\'] = \'' . addslashes($config['db_host']) . "';\n");
		fclose($ic);

		$statuses[] = "Wrote config file!";
	}

	try {
		do_install();
	} catch (Exception $e) {
		$error = "Error: " . $e->getMessage();
	}

	echo $twig->render('install.html', array(
		'error' => $error,
		'statuses' => $statuses,
	));
} else {
	// Show install form
	echo $twig->render('install-form.html', array());
}
