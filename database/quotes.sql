-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: 10.169.0.145
-- Generation Time: Jul 29, 2018 at 11:42 AM
-- Server version: 5.7.17
-- PHP Version: 5.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shinyide2_quotes`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`shinyide2_user`@`%` FUNCTION `newquote`( author_name VARCHAR(128), quote_body VARCHAR(512) ) RETURNS int(11)
BEGIN
    DECLARE aid INT DEFAULT 0;
    DECLARE qid INT DEFAULT 0;
    DECLARE ret INT DEFAULT 0;
    SELECT id, author_id INTO qid, aid FROM quote WHERE match_text = plaintext(quote_body);
    IF FOUND_ROWS() = 0 THEN
        SELECT id INTO aid FROM author WHERE match_text = plaintext(author_name);
        IF FOUND_ROWS() = 0 THEN
            SELECT author_id INTO aid FROM author_alias WHERE match_text = plaintext(author_name);
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
        SELECT id INTO qid FROM author WHERE match_text = plaintext(author_name);
        IF FOUND_ROWS() = 0 THEN
            SELECT id INTO qid FROM author_alias WHERE match_text = plaintext(author_name);
            IF FOUND_ROWS() = 0 THEN
                INSERT INTO author_alias (author_id, name) VALUES (aid, author_name);
                SET ret = 5;
            END IF;
        END IF;

    END IF;
    RETURN ret;
END$$

CREATE DEFINER=`shinyide2_user`@`%` FUNCTION `plaintext`( input VARCHAR(512) ) RETURNS varchar(512) CHARSET utf8
BEGIN
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

CREATE TABLE IF NOT EXISTS `access` (
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
DELIMITER $$
CREATE TRIGGER `insert_access_after` AFTER INSERT ON `access`
 FOR EACH ROW BEGIN
        INSERT INTO quote_access (access_ident,quote_id) SELECT NEW.ident, q.id FROM quote q;
    END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_access_before` BEFORE INSERT ON `access`
 FOR EACH ROW BEGIN
        SET new.token = uuid();
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `author`
--

CREATE TABLE IF NOT EXISTS `author` (
  `id` int(11) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `match_text` varchar(128) DEFAULT NULL,
  `period` varchar(128) DEFAULT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Triggers `author`
--
DELIMITER $$
CREATE TRIGGER `insert_author_before` BEFORE INSERT ON `author`
 FOR EACH ROW BEGIN
        SET NEW.match_text = plaintext(NEW.name);
    END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_author_before` BEFORE UPDATE ON `author`
 FOR EACH ROW BEGIN
        SET NEW.match_text = plaintext(NEW.name);
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `author_alias`
--

CREATE TABLE IF NOT EXISTS `author_alias` (
  `id` int(11) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `match_text` varchar(128) DEFAULT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Triggers `author_alias`
--
DELIMITER $$
CREATE TRIGGER `insert_author_alias_before` BEFORE INSERT ON `author_alias`
 FOR EACH ROW BEGIN
        SET NEW.match_text = plaintext(NEW.name);
    END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_author_alias_before` BEFORE UPDATE ON `author_alias`
 FOR EACH ROW BEGIN
        SET NEW.match_text = plaintext(NEW.name);
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `quote`
--

CREATE TABLE IF NOT EXISTS `quote` (
  `id` int(11) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `quote_text` varchar(512) DEFAULT NULL,
  `match_text` varchar(512) DEFAULT NULL,
  `times_used` int(11) NOT NULL DEFAULT '0',
  `last_used_by` int(11) NOT NULL DEFAULT '0',
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Triggers `quote`
--
DELIMITER $$
CREATE TRIGGER `insert_quote_after` AFTER INSERT ON `quote`
 FOR EACH ROW BEGIN
        INSERT INTO quote_access (access_ident,quote_id) SELECT a.ident, NEW.id FROM access a;
    END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_quote_before` BEFORE INSERT ON `quote`
 FOR EACH ROW BEGIN
        SET NEW.match_text = plaintext(NEW.quote_text);
    END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_quote_before` BEFORE UPDATE ON `quote`
 FOR EACH ROW BEGIN
        SET NEW.match_text = plaintext(NEW.quote_text);
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `quote_access`
--

CREATE TABLE IF NOT EXISTS `quote_access` (
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

CREATE TABLE IF NOT EXISTS `request_history` (
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
  ADD UNIQUE KEY `author_match_text_idx` (`match_text`) USING BTREE;

--
-- Indexes for table `author_alias`
--
ALTER TABLE `author_alias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `author_alias_match_text_idx` (`match_text`) USING BTREE,
  ADD KEY `author_alias_ibfk_1` (`author_id`);

--
-- Indexes for table `quote`
--
ALTER TABLE `quote`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quote_match_text_idx` (`match_text`) USING BTREE,
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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
