<?php

require_once 'inc/functions.php';

$errors = array();

@$teamhash = $_POST['hash'] or $errors[] = "Team hash not set!";
@$puzzle_id = $_POST['puzzle'] or $errors[] = "Puzzle not set!";
@$answer = $_POST['answer'] or $errors[] = "Answer not set!";

$awarded = 0;

function check_answer($teamhash, $puzzle_id, $answer) {
	global $errors, $awarded;

	$dbh = db_connect();

	$query = $dbh->prepare('SELECT id FROM team WHERE hash = :hash');
	$query->bindValue(':hash', $teamhash);
	$query->execute();

	$team_id = $query->fetch();
	if ($team_id === false) {
		$errors[] = "Could not find team with hash " . $teamhash;
		return false;
	}
	$team_id = $team_id['id'];

	$query = $dbh->prepare('SELECT COUNT(*) AS num FROM puzzle WHERE id = :id');
	$query->bindValue(':id', $puzzle_id);
	$query->execute();

	$c = $query->fetch();
	if ($c['num'] == 0) {
		$errors[] = "Could not find puzzle with ID " . $puzzle_id;
		return false;
	}

	$query = $dbh->prepare('SELECT value FROM puzzle WHERE answer = :answer AND id = :id');
	$query->bindValue(':answer', $answer);
	$query->bindValue(':id', $puzzle_id);
	$query->execute();

	$r = $query->fetch();
	if ($r === false) {
		$errors[] = 'Incorrect answer!';
		return false;
	}

	$query = $dbh->prepare('SELECT COUNT(*) as num FROM puzzle_solved WHERE team_id = :team_id AND puzzle_id = :puzzle_id');
	$query->bindValue(':team_id', $team_id);
	$query->bindValue(':puzzle_id', $puzzle_id);
	$query->execute();

	$c = $query->fetch();
	if ($c['num'] > 0) {
		$errors[] = 'This puzzle has already been solved by this team!';
		return false;
	}

	$query = $dbh->prepare('INSERT INTO puzzle_solved (team_id, puzzle_id) VALUES (:team_id, :puzzle_id)');
	$query->bindValue(':team_id', $team_id);
	$query->bindValue(':puzzle_id', $puzzle_id);
	$query->execute();

	$awarded = $r['value'];
}

if (count($errors)==0) {
	check_answer($teamhash, $puzzle_id, $answer);
}

echo $twig->render('claim.html', array(
	'errors' => $errors,
	'awarded' => $awarded,
	'teamhash' => $teamhash,
));
