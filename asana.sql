-- Asana.tasks definition

CREATE TABLE `tasks` (
  `internal_id` int(11) NOT NULL AUTO_INCREMENT,
  `asana_id` bigint(20) NOT NULL,
  PRIMARY KEY (`internal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
