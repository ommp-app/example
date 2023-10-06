--
-- Online Module Management Platform
-- 
-- SQL installation file for example module
-- 
-- Author: The OMMP Team
-- Version: 1.0
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Creates the counter table
CREATE TABLE IF NOT EXISTS `{PREFIX}example_counter` (
  `value` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
COMMIT;

-- Init counter
INSERT INTO `{PREFIX}example_counter` (`value`) VALUES (0);
