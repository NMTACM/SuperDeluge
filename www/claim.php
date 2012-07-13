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

	// Find their team
	$query = $dbh->prepare('SELECT id FROM team WHERE hash = :hash');
	$query->bindValue(':hash', $teamhash);
	$query->execute();

	$team_id = $query->fetch();
	if ($team_id === false) {
		$errors[] = "Could not find team with hash " . $teamhash;
		return false;
	}
	$team_id = $team_id['id'];

	// See if the puzzle exists
	$query = $dbh->prepare('SELECT COUNT(*) AS num FROM puzzle WHERE id = :id');
	$query->bindValue(':id', $puzzle_id);
	$query->execute();

	$count_puz = $query->fetch();
	if ($count_puz['num'] == 0) {
		$errors[] = "Could not find puzzle with ID " . $puzzle_id;
		return false;
	}

	// Check if they got the right answer
	$query = $dbh->prepare('SELECT value, category_id FROM puzzle WHERE answer = :answer AND id = :id');
	$query->bindValue(':answer', $answer);
	$query->bindValue(':id', $puzzle_id);
	$query->execute();

	$puz_row = $query->fetch();
	if ($puz_row === false) {
		$errors[] = 'Incorrect answer!';
	}

	// Check if they have solved this one before
	$query = $dbh->prepare('SELECT COUNT(*) as num FROM puzzle_solved WHERE team_id = :team_id AND puzzle_id = :puzzle_id');
	$query->bindValue(':team_id', $team_id);
	$query->bindValue(':puzzle_id', $puzzle_id);
	$query->execute();

	$count_solved = $query->fetch();
	if ($count_solved['num'] > 0) {
		$errors[] = 'This puzzle has already been solved by this team!';
	}

	if (count($errors) > 0)
		return false;

	$awarded = $puz_row['value'];

	$dbh->beginTransaction();

	// Record that this team solved the puzzle
	$query = $dbh->prepare('INSERT INTO puzzle_solved (team_id, puzzle_id) VALUES (:team_id, :puzzle_id)');
	$query->bindValue(':team_id', $team_id);
	$query->bindValue(':puzzle_id', $puzzle_id);
	$query->execute();

	// Unlock the next puzzle
	$query = $dbh->prepare('SELECT MIN(value) AS nextvalue FROM puzzle WHERE category_id = :category_id AND value > :thisvalue');
	$query->bindValue(':category_id', $puz_row['category_id']);
	$query->bindValue(':thisvalue', $awarded);
	$query->execute();

	$next = $query->fetch();
	$nextvalue = $next['nextvalue'];
	if ($nextvalue !== null) {
		$query = $dbh->prepare('UPDATE category SET unlocked_value = :nextvalue WHERE id = :category_id');
		$query->bindValue(':nextvalue', $nextvalue);
		$query->bindValue(':category_id', $puz_row['category_id']);
		$query->execute();
	}

	$dbh->commit();
}

if (count($errors)==0) {
	check_answer($teamhash, $puzzle_id, $answer);
}

echo $twig->render('claim.html', array(
	'errors' => $errors,
	'awarded' => $awarded,
	'teamhash' => $teamhash,
));
