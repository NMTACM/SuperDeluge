Super Deluge
============

This is a simple scoreboard framework that allows teams to compete to
solve puzzles in different categories. Whenever one team completes a
puzzle in a category, the next puzzle in that category is unlocked for
everyone. (If multiple puzzles have the same point value, then several
may be unlocked.) Puzzles consist of a description, possibly some
amount of related files, and a plaintext answer that the contestants
must figure out and submit.

Scoring
-------

Each team's final score is calculated by summing the fraction of the
total points each team has in each category.

This means that scoring 5 points in a category that no one else has
gotten any points in yet will get you 1 point for your final
score. Getting 5 more points to a total of 10 points in that category
doesn't change your final score while you're still alone in that
category. If another team then gains 10 points in that category, then
each of the two team with 10 points in that category will only get 0.5
points toward their final score for that category.

This encourages teams to try to compete across all of the categories.

Player Usage
------------

Every team has a visible name, and a team hash that is used to submit
answers. The team hash may be kept private, though all another team could do
with it is submit answers and score points as that team.

Installation
------------

Install by placing the files in www/ under your HTTP server's public document
root. Make sure that the inc/ and templates/ folders are writable by the server
process. (This may involve `sudo chown -R www-data inc/ templates` for
example.) Then load install.php in a browser to set your database details. The
settings are automatically saved inside the inc/instance-config.php file.

Administration
--------------

Note that the scoreboard system does not currently have an admin interface for
setting up teams or loading in puzzles. There is an example script at
dist/import-dirtbags.py for importing puzzles from files. Note that puzzle
descriptions are in Markdown format, but also allow HTML tags.

You can access an SQL prompt on most machines by running:

    mysql -u username -p databasename

Adding a team. The hash could even be made to be the same as the team name.

    INSERT INTO team (name,hash) VALUES ('noob', '888');

Activating categories. Every category has a boolean column named `active`,
which controls whether the category shows up at all to users. Every category
also has an integer column `unlocked_value`, which contains the point value of
the highest puzzle to show currently. (As users solve puzzles, they unlock the
later puzzles for everyone, and this is done by increasing the `unlocked_value`
field to the value of the lowest valued puzzle that is greater than the just
solved puzzle's value.)

The following command makes sure that the value of the `unlock_value` fields
for all categories is up-to-date with what puzzles have been solved so far, and
it initializes new categories so their lowest value puzzle is unlocked:

    update category set unlocked_value=(select min(value) from puzzle where category_id=category.id and value > all (select value from puzzle inner join puzzle_solved on puzzle.id=puzzle_id where category_id=category.id));
