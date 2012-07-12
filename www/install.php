<?php

require_once 'inc/functions.php';

if (file_exists('inc/instance-config.php')) {
	die("Already installed!");
}

?>
<!DOCTYPE HTML>
<html>
<head>
<title>Install</title>
</head>
<body>
<?php

if (isset($_POST['do_install'])) {
?>
<h1>Installing</h1>
<?php

$config['db_user'] = $_POST['db_user'];
$config['db_pass'] = $_POST['db_pass'];
$config['db_name'] = $_POST['db_name'];
$config['db_host'] = $_POST['db_host'];

function do_install() {
	global $config;

	$dsn = 'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'];
	$dbh = new PDO($dsn, $config['db_user'], $config['db_pass']);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	print "<p>Database connection successful!</p>";

	$sql = @file_get_contents('install.sql') or die("Couldn't load install.sql.");
	preg_match_all("/(^|\n)((SET|CREATE|INSERT).+)\n\n/msU", $sql, $queries);
	$queries = $queries[2];
	
	foreach ($queries as &$query) {
		$dbh->exec($query);
	}

	print "<p>Database install successful!</p>";

	$ic = fopen('inc/instance-config.php', 'w');
	fwrite($ic, "<?php\n");
	fwrite($ic, '$config[\'db_user\'] = \'' . addslashes($config['db_user'])) . "'\n";
	fwrite($ic, '$config[\'db_pass\'] = \'' . addslashes($config['db_pass'])) . "'\n";
	fwrite($ic, '$config[\'db_name\'] = \'' . addslashes($config['db_name'])) . "'\n";
	fwrite($ic, '$config[\'db_host\'] = \'' . addslashes($config['db_host'])) . "'\n";
	fclose($ic);
}

try {
	do_install();
} catch (Exception $e) {
	print "<p>Error: " . $e->getMessage() . "</p>";
}

} else {
?>
<h1>Install</h1>

<form name="install" method="post">
<h2>Database Info</h2>
<div>Username:<br/>
<input type="text" name="db_user" /></div>
<div>Password:<br/>
<input type="password" name="db_pass" /></div>
<div>Database:<br/>
<input type="text" name="db_name" /></div>
<div>Host:<br/>
<input type="text" name="db_host" value="localhost" /></div>

<div>
<input type="submit" value="Submit" name="do_install" />
</div>

</form>
<?php
}
?>
</body>
</html>
