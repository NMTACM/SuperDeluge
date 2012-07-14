<?php

require_once 'inc/functions.php';

$dbh = db_connect();

// Key is id, value is name
$team_names = array();
// Key is category name, value is array (key is team id, value is score)
$cat_team_scores = array();
// Key is team id, value is final score
$team_total_scores = array();

$team_query = $dbh->prepare('SELECT id, name FROM team');
$team_query->execute();

while(($team_row = $team_query->fetch())) {
	$team_names[ $team_row['id'] ] = $team_row['name'];
	$team_total_scores[ $team_row['id'] ] = 0;
}

$cat_query = $dbh->prepare('SELECT * FROM category WHERE active=1');
$cat_query->execute();

while(($cat_row = $cat_query->fetch())) {
	$score_query = $dbh->prepare('SELECT team.id, IFNULL(score,0) AS score FROM team LEFT JOIN (SELECT team_id, SUM(puzzle.value) AS score FROM puzzle_solved INNER JOIN puzzle ON puzzle_id=puzzle.id WHERE puzzle.category_id = :category_id GROUP BY team_id) as team_scores on team_id=team.id');
	$score_query->bindValue(':category_id', $cat_row['id']);
	$score_query->execute();

	$cat_team_scores[ $cat_row['name'] ] = array();
	$cat_score_total = 0;
	while(($score_row = $score_query->fetch())) {
		$cat_team_scores[ $cat_row['name'] ][ $score_row['id'] ] = $score_row['score'];
		$cat_score_total += $score_row['score'];
	}

	if ($cat_score_total != 0) {
		foreach ($cat_team_scores[ $cat_row['name'] ] as $team_id => $team_score) {
			$team_total_scores[ $team_id ] += $team_score / $cat_score_total;
		}
	}
}

echo $twig->render('scoreboard.html', array(
	'team_names' => $team_names,
	'cat_team_scores' => $cat_team_scores,
	'team_total_scores' => $team_total_scores,
));
