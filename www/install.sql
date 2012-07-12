CREATE TABLE IF NOT EXISTS `team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `hash` char(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `unlocked_value` int(11),
  `active` boolean NOT NULL default 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `puzzle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` int(11) NOT NULL,
  `answer` varchar(30) NOT NULL,
  `description` text,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `answer` (`answer`),
  FOREIGN KEY (`category_id`) REFERENCES category(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `puzzle_solved` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `puzzle_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `solved` (`team_id`, `puzzle_id`),
  FOREIGN KEY (`team_id`) REFERENCES team(`id`),
  FOREIGN KEY (`puzzle_id`) REFERENCES puzzle(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

