<?php

require_once 'inc/functions.php';

if (!isset($_GET['id'])) {
	die($twig->render('error.html', array(
		'error' => 'No puzzle ID specified!',
	)));
}

$puzzle_id = $_GET['id'];

$dbh = db_connect();

$query = $dbh->prepare('SELECT * FROM puzzle WHERE id = :id');
$query->bindValue(':id', $puzzle_id);
$query->execute();

$puzzle_row = $query->fetch();
if ($puzzle_row === false) {
	die($twig->render('error.html', array(
		'error' => 'Could not find a puzzle by that ID!',
	)));
}

if ($puzzle_row['category_id'] !== null) {
	$query = $dbh->prepare('SELECT * FROM category WHERE id = :id');
	$query->bindValue(':id', $puzzle_row['category_id']);
	$query->execute();

	$r = $query->fetch();
	if (!$r['active'] || $r['unlocked_value'] < $puzzle_row['value']) {
		die($twig->render('error.html', array(
			'error' => 'This puzzle is not active yet!',
		)));
	}

	$category_name = $r['name'];
} else {
	$category_name = 'Uncategorized';
}

$f_query = $dbh->prepare('SELECT * FROM puzzle_file WHERE puzzle_id = :puzzle_id');
$f_query->bindValue(':puzzle_id', $puzzle_id);
$f_query->execute();

$files = array();
while (($file_row = $f_query->fetch())) {
	$files[] = array("name" => $file_row['name'], "url" => $file_row['url']);
}

echo $twig->render('puzzle.html', array(
	'value' => $puzzle_row['value'],
	'description' => Markdown($puzzle_row['description']),
	'author' => $puzzle_row['author'],
	'category' => $category_name,
	'id' => $puzzle_id,
	'files' => $files,
));
