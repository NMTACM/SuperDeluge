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

if ($puzzle_row['category_id'] !== false) {
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

echo $twig->render('puzzle.html', array(
	'value' => $puzzle_row['value'],
	'description' => $puzzle_row['description'],
	'author' => $puzzle_row['author'],
	'category' => $category_name,
	'id' => $puzzle_id,
));
