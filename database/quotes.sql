-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: XX.XX.XX.XX
-- Generation Time: Jan 26, 2020 at 11:30 AM
-- Server version: 5.7.17
-- PHP Version: 5.6.37

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shinyide2_quotes`
--
DROP DATABASE IF EXISTS `shinyide2_quotes`;
CREATE DATABASE IF NOT EXISTS `shinyide2_quotes` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `shinyide2_quotes`;

DELIMITER $$
--
-- Functions
--
DROP FUNCTION IF EXISTS `md5text`$$
CREATE DEFINER=`shinyide2_user`@`%` FUNCTION `md5text` (`input` VARCHAR(512)) RETURNS CHAR(32) CHARSET utf8 BEGIN
    DECLARE pos SMALLINT DEFAULT 1; 
    DECLARE len SMALLINT DEFAULT 1;
    DECLARE output VARCHAR(512) DEFAULT '';
    DECLARE ch CHAR(1);
    SET len = CHAR_LENGTH( input );
    REPEAT
        BEGIN
            SET ch = MID( input, pos, 1 );
            IF ch REGEXP '[[:alnum:]]' THEN
                IF ch <> ' ' THEN
                    SET output = CONCAT(output, ch);
                END IF;
            END IF;
            SET pos = pos + 1;
        END;
    UNTIL pos > len END REPEAT;
    RETURN MD5(LOWER(output));
END$$

DROP FUNCTION IF EXISTS `newaccesstoken`$$
CREATE DEFINER=`shinyide2_user`@`%` FUNCTION `newaccesstoken` (`input` VARCHAR(255)) RETURNS VARCHAR(36) CHARSET utf8 BEGIN
    DECLARE id INT DEFAULT 0;
    DECLARE tk VARCHAR(36);

    INSERT INTO access (name, requests_per_period, time_period) values (plaintext(input), 10, 15);
    SELECT token INTO tk FROM access WHERE ident = LAST_INSERT_ID();
    return tk;
END$$

DROP FUNCTION IF EXISTS `newquote`$$
CREATE DEFINER=`shinyide2_user`@`%` FUNCTION `newquote` (`author_name` VARCHAR(128), `quote_body` VARCHAR(512)) RETURNS INT(11) BEGIN
    DECLARE aid INT DEFAULT 0;
    DECLARE qid INT DEFAULT 0;
    DECLARE ret INT DEFAULT 0;
    SELECT id, author_id INTO qid, aid FROM quote WHERE md5_text = md5text(quote_body);
    IF FOUND_ROWS() = 0 THEN
        SELECT id INTO aid FROM author WHERE md5_text = md5text(author_name);
        IF FOUND_ROWS() = 0 THEN
            SELECT author_id INTO aid FROM author_alias WHERE md5_text = md5text(author_name);
            IF FOUND_ROWS() = 0 THEN
                INSERT INTO author (name, period) VALUES (author_name, '');
                INSERT INTO quote (author_id, quote_text) VALUES (LAST_INSERT_ID(), quote_body);
                SET ret = 3;
            ELSE
                INSERT INTO quote (author_id, quote_text) VALUES (aid, quote_body);
                SET ret = 1;
            END IF;
        ELSE
            INSERT INTO quote (author_id, quote_text) VALUES (aid, quote_body);
            SET ret = 1;
        END IF;
    ELSE
        SELECT id INTO qid FROM author WHERE md5_text = md5text(author_name);
        IF FOUND_ROWS() = 0 THEN
            SELECT id INTO qid FROM author_alias WHERE md5_text = md5text(author_name);
            IF FOUND_ROWS() = 0 THEN
                INSERT INTO author_alias (author_id, name) VALUES (aid, author_name);
                SET ret = 5;
            END IF;
        END IF;

    END IF;
    RETURN ret;
END$$

DROP FUNCTION IF EXISTS `plaintext`$$
CREATE DEFINER=`shinyide2_user`@`%` FUNCTION `plaintext` (`input` VARCHAR(512)) RETURNS VARCHAR(512) CHARSET utf8 BEGIN
    DECLARE pos SMALLINT DEFAULT 1; 
    DECLARE len SMALLINT DEFAULT 1;
    DECLARE output VARCHAR(512) DEFAULT '';
    DECLARE ch CHAR(1);
    SET len = CHAR_LENGTH( input );
    REPEAT
        BEGIN
            SET ch = MID( input, pos, 1 );
            IF ch REGEXP '[[:alnum:]]' THEN
                IF ch <> ' ' THEN
                    SET output = CONCAT(output, ch);
                END IF;
            END IF;
            SET pos = pos + 1;
        END;
    UNTIL pos > len END REPEAT;
    RETURN LOWER(output);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `access`
--

DROP TABLE IF EXISTS `access`;
CREATE TABLE `access` (
  `ident` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` char(36) NOT NULL,
  `requests_per_period` int(11) NOT NULL DEFAULT '0',
  `time_period` int(11) NOT NULL DEFAULT '0',
  `created_when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end_dated` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Triggers `access`
--
DROP TRIGGER IF EXISTS `insert_access_after`;
DELIMITER $$
CREATE TRIGGER `insert_access_after` AFTER INSERT ON `access` FOR EACH ROW BEGIN
        INSERT INTO quote_access (access_ident,quote_id) SELECT NEW.ident, q.id FROM quote q;
    END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `insert_access_before`;
DELIMITER $$
CREATE TRIGGER `insert_access_before` BEFORE INSERT ON `access` FOR EACH ROW BEGIN
        SET new.token = uuid();
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `author`
--

DROP TABLE IF EXISTS `author`;
CREATE TABLE `author` (
  `id` int(11) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `md5_text` char(32) DEFAULT NULL,
  `period` varchar(128) DEFAULT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Triggers `author`
--
DROP TRIGGER IF EXISTS `insert_author_before`;
DELIMITER $$
CREATE TRIGGER `insert_author_before` BEFORE INSERT ON `author` FOR EACH ROW BEGIN
        SET NEW.md5_text = md5text(NEW.name);
    END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `update_author_before`;
DELIMITER $$
CREATE TRIGGER `update_author_before` BEFORE UPDATE ON `author` FOR EACH ROW BEGIN
        SET NEW.md5_text = md5text(NEW.name);
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `author_alias`
--

DROP TABLE IF EXISTS `author_alias`;
CREATE TABLE `author_alias` (
  `id` int(11) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `md5_text` char(32) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Triggers `author_alias`
--
DROP TRIGGER IF EXISTS `insert_author_alias_before`;
DELIMITER $$
CREATE TRIGGER `insert_author_alias_before` BEFORE INSERT ON `author_alias` FOR EACH ROW BEGIN
        SET NEW.md5_text = md5text(NEW.name);
    END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `update_author_alias_before`;
DELIMITER $$
CREATE TRIGGER `update_author_alias_before` BEFORE UPDATE ON `author_alias` FOR EACH ROW BEGIN
        SET NEW.md5_text = md5text(NEW.name);
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `quote`
--

DROP TABLE IF EXISTS `quote`;
CREATE TABLE `quote` (
  `id` int(11) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `quote_text` varchar(512) DEFAULT NULL,
  `md5_text` char(32) DEFAULT NULL,
  `times_used` int(11) NOT NULL DEFAULT '0',
  `last_used_by` int(11) NOT NULL DEFAULT '0',
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Triggers `quote`
--
DROP TRIGGER IF EXISTS `insert_quote_after`;
DELIMITER $$
CREATE TRIGGER `insert_quote_after` AFTER INSERT ON `quote` FOR EACH ROW BEGIN
        INSERT INTO quote_access (access_ident,quote_id) SELECT a.ident, NEW.id FROM access a;
    END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `insert_quote_before`;
DELIMITER $$
CREATE TRIGGER `insert_quote_before` BEFORE INSERT ON `quote` FOR EACH ROW BEGIN
        SET NEW.md5_text = md5text(NEW.quote_text);
    END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `update_quote_before`;
DELIMITER $$
CREATE TRIGGER `update_quote_before` BEFORE UPDATE ON `quote` FOR EACH ROW BEGIN
        SET NEW.md5_text = md5text(NEW.quote_text);
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `quote_access`
--

DROP TABLE IF EXISTS `quote_access`;
CREATE TABLE `quote_access` (
  `access_ident` int(11) NOT NULL,
  `quote_id` int(11) NOT NULL,
  `times_used` int(11) NOT NULL DEFAULT '0',
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `request_history`
--

DROP TABLE IF EXISTS `request_history`;
CREATE TABLE `request_history` (
  `accessed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `remote` varchar(256) NOT NULL,
  `access_ident` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access`
--
ALTER TABLE `access`
  ADD PRIMARY KEY (`ident`) USING BTREE;

--
-- Indexes for table `author`
--
ALTER TABLE `author`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `author_md5_text_idx` (`md5_text`) USING BTREE;

--
-- Indexes for table `author_alias`
--
ALTER TABLE `author_alias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `author_alias_md5_text_idx` (`md5_text`) USING BTREE,
  ADD KEY `author_alias_ibfk_1` (`author_id`);

--
-- Indexes for table `quote`
--
ALTER TABLE `quote`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quote_md5_text_idx` (`md5_text`) USING BTREE,
  ADD KEY `quote_ibfk_1` (`author_id`);

--
-- Indexes for table `quote_access`
--
ALTER TABLE `quote_access`
  ADD PRIMARY KEY (`access_ident`,`quote_id`) USING BTREE,
  ADD KEY `quote_access_ibfk_2` (`quote_id`);

--
-- Indexes for table `request_history`
--
ALTER TABLE `request_history`
  ADD KEY `request_history_idx1` (`access_ident`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access`
--
ALTER TABLE `access`
  MODIFY `ident` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `author`
--
ALTER TABLE `author`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `author_alias`
--
ALTER TABLE `author_alias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quote`
--
ALTER TABLE `quote`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `author_alias`
--
ALTER TABLE `author_alias`
  ADD CONSTRAINT `author_alias_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `author` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quote`
--
ALTER TABLE `quote`
  ADD CONSTRAINT `quote_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `author` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quote_access`
--
ALTER TABLE `quote_access`
  ADD CONSTRAINT `quote_access_ibfk_1` FOREIGN KEY (`access_ident`) REFERENCES `access` (`ident`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quote_access_ibfk_2` FOREIGN KEY (`quote_id`) REFERENCES `quote` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
