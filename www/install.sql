CREATE TABLE IF NOT EXISTS `team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `hash` char(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`),
  UNIQUE KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `unlocked_value` int(11),
  `active` boolean NOT NULL default 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `puzzle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` int(11) NOT NULL,
  `answer` varchar(100) NOT NULL,
  `description` mediumtext,
  `notes` text,
  `author` varchar(40),
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`answer`),
  FOREIGN KEY (`category_id`) REFERENCES category(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `puzzle_solved` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `puzzle_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`team_id`),
  UNIQUE KEY (`team_id`, `puzzle_id`),
  FOREIGN KEY (`team_id`) REFERENCES team(`id`),
  FOREIGN KEY (`puzzle_id`) REFERENCES puzzle(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `puzzle_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `url` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`puzzle_id`),
  UNIQUE KEY (`puzzle_id`, `name`),
  UNIQUE KEY (`puzzle_id`, `url`),
  FOREIGN KEY (`puzzle_id`) REFERENCES puzzle(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


