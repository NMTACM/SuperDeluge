<?php

require_once 'inc/functions.php';

$dbh = db_connect();

$cat_query = $dbh->prepare('SELECT * FROM category WHERE active = 1');
$cat_query->execute();

$categories = array();
while(($cat_row = $cat_query->fetch())) {
	$category = array();
	$category['name'] = $cat_row['name'];

	$puz_query = $dbh->prepare('SELECT * FROM puzzle WHERE category_id = :category_id AND value <= :unlocked_value ORDER BY value');
	$puz_query->bindValue(':category_id', $cat_row['id']);
	$puz_query->bindValue(':unlocked_value', $cat_row['unlocked_value']);
	$puz_query->execute();

	$puzzles = array();
	while(($puz_row = $puz_query->fetch())) {
		$puzzles[] = $puz_row;
	}
	$category['puzzles'] = $puzzles;

	$categories[] = $category;
}

echo $twig->render('puzzleboard.html', array(
	'categories' => $categories,
));
