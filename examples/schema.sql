-- PdoFish2 example schema
-- Run this against a MySQL/MariaDB database before executing index.php

CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `posts` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11)      NOT NULL,
  `title`      VARCHAR(255) NOT NULL,
  `body`       TEXT         NOT NULL,
  `status`     VARCHAR(20)  NOT NULL DEFAULT 'draft',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`name`, `email`) VALUES
  ('Alice', 'alice@example.com'),
  ('Bob',   'bob@example.com');

INSERT INTO `posts` (`user_id`, `title`, `body`, `status`) VALUES
  (1, 'Hello World',  'My first post.',      'published'),
  (1, 'Draft Post',   'Work in progress.',   'draft'),
  (2, 'Bob\'s Post',  'Hello from Bob.',     'published');
