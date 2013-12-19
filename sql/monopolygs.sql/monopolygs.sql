-- phpMyAdmin SQL Dump
-- version 3.5.2
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Час створення: Жов 03 2012 р., 03:57
-- Версія сервера: 5.5.25a
-- Версія PHP: 5.4.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- БД: `monopoly`
--

-- --------------------------------------------------------

--
-- Структура таблиці `m_gsession`
--

CREATE TABLE IF NOT EXISTS `m_gsession` (
  `gsession_id` int(11) NOT NULL AUTO_INCREMENT,
  `map_id` int(11) NOT NULL,
  `createstamp` timestamp NULL DEFAULT NULL,
  `startstamp` timestamp NULL DEFAULT NULL,
  `endstamp` timestamp NULL DEFAULT NULL,
  `gstatus` int(11) NOT NULL,
  `gstate` tinyint(4) NOT NULL,
  `gturn` int(11) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`gsession_id`),
  KEY `map_id` (`map_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs AUTO_INCREMENT=42 ;

-- --------------------------------------------------------

--
-- Структура таблиці `m_gsession_auction`
--

CREATE TABLE IF NOT EXISTS `m_gsession_auction` (
  `auct_id` int(11) NOT NULL AUTO_INCREMENT,
  `gsession_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `auct_state` enum('opened','closed') COLLATE utf8_bin NOT NULL,
  `auct_status` tinyint(4) NOT NULL,
  `auct_type` tinyint(4) NOT NULL,
  `auct_bid` int(11) NOT NULL,
  `auct_holder_user_id` int(11) DEFAULT NULL,
  `auct_bid_user_id` int(11) DEFAULT NULL,
  `auct_step` int(11) NOT NULL,
  `auct_startstamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `auct_laststamp` timestamp NULL DEFAULT NULL,
  `auct_endstamp` timestamp NULL DEFAULT NULL,
  `last_changed` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`auct_id`),
  UNIQUE KEY `uk_auction_filed` (`gsession_id`,`field_id`,`auct_endstamp`),
  KEY `gsession_id` (`gsession_id`),
  KEY `field_id` (`field_id`),
  KEY `auct_bid_user` (`auct_bid_user_id`),
  KEY `auct_holder_user_id` (`auct_holder_user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=71906 ;

-- --------------------------------------------------------

--
-- Структура таблиці `m_gsession_auction_bid`
--

CREATE TABLE IF NOT EXISTS `m_gsession_auction_bid` (
  `gsession_id` int(11) NOT NULL,
  `auct_id` int(11) NOT NULL,
  `bidder_user_id` int(11) DEFAULT NULL,
  `bid` int(11) NOT NULL,
  `auct_step` int(11) NOT NULL,
  `auct_step_type` enum('bid','end') COLLATE utf8_bin NOT NULL,
  `auct_stepstamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `IX_AUCTION_STEP` (`auct_id`,`auct_step`),
  KEY `gsession_id` (`gsession_id`),
  KEY `bidder_user_id` (`bidder_user_id`),
  KEY `auct_id` (`auct_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `m_gsession_auction_user`
--

CREATE TABLE IF NOT EXISTS `m_gsession_auction_user` (
  `auct_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `auct_user_state` enum('on','off') COLLATE utf8_bin NOT NULL,
  `last_bid` int(11) DEFAULT NULL,
  UNIQUE KEY `IX_AUCT_USER` (`auct_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `m_gsession_deal`
--

CREATE TABLE IF NOT EXISTS `m_gsession_deal` (
  `deal_id` int(11) NOT NULL AUTO_INCREMENT,
  `gsession_id` int(11) NOT NULL,
  `deal_holder_user_id` int(11) NOT NULL,
  `deal_opponent_user_id` int(11) NOT NULL,
  `deal_status` tinyint(4) NOT NULL,
  `deal_state` enum('opened','rejected','accepted','terminated','canceled') COLLATE utf8_bin NOT NULL,
  `deal_startstamp` timestamp NULL DEFAULT NULL,
  `deal_endstamp` timestamp NULL DEFAULT NULL,
  `deal_payment` int(11) DEFAULT NULL,
  `last_changed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`deal_id`),
  KEY `deal_holder_user_id` (`deal_holder_user_id`),
  KEY `deal_user_id` (`deal_opponent_user_id`),
  KEY `gsession_id` (`gsession_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Структура таблиці `m_gsession_deal_list`
--

CREATE TABLE IF NOT EXISTS `m_gsession_deal_list` (
  `deal_id` int(11) NOT NULL,
  `ddirection` enum('give','receive') COLLATE utf8_bin NOT NULL,
  `field_id` int(11) NOT NULL,
  KEY `deal_id` (`deal_id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `m_gsession_log`
--

CREATE TABLE IF NOT EXISTS `m_gsession_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `gsession_id` int(11) NOT NULL,
  `loglevel` int(11) NOT NULL DEFAULT '1',
  `user_id` int(11) DEFAULT NULL,
  `action_desc` varchar(1000) COLLATE cp1251_general_cs DEFAULT NULL,
  `datestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `microtime` bigint(11) NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `gsession_id` (`gsession_id`),
  KEY `user_id` (`user_id`),
  KEY `datestamp` (`datestamp`),
  KEY `microtime` (`microtime`)
) ENGINE=InnoDB  DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs AUTO_INCREMENT=3306575 ;

-- --------------------------------------------------------

--
-- Структура таблиці `m_gsession_map_fgroup`
--

CREATE TABLE IF NOT EXISTS `m_gsession_map_fgroup` (
  `gsession_id` int(11) NOT NULL,
  `map_id` int(11) NOT NULL,
  `fgroup_id` int(11) NOT NULL,
  `fgowner_user_id` int(11) DEFAULT NULL,
  `fgparam` varchar(1000) COLLATE utf8_bin DEFAULT NULL,
  UNIQUE KEY `UK_GS_FG_ID` (`gsession_id`,`fgroup_id`),
  KEY `gsession_id` (`gsession_id`),
  KEY `fgroup_id` (`fgroup_id`),
  KEY `map_id` (`map_id`),
  KEY `fgowner_user_id` (`fgowner_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `m_gsession_map_field`
--

CREATE TABLE IF NOT EXISTS `m_gsession_map_field` (
  `gsession_id` int(11) NOT NULL,
  `map_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `fgroup_id` int(11) DEFAULT NULL,
  `fparam` varchar(240) COLLATE cp1251_general_cs DEFAULT NULL,
  `fparam_calc1` varchar(1000) COLLATE cp1251_general_cs DEFAULT NULL,
  `fparam_calc2` varchar(1000) COLLATE cp1251_general_cs DEFAULT NULL,
  `owner_user_id` int(11) DEFAULT NULL,
  `last_changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `ix_gsession_map_field` (`gsession_id`,`field_id`),
  KEY `gsession_id` (`gsession_id`),
  KEY `map_id` (`map_id`),
  KEY `field_id` (`field_id`),
  KEY `owner_user_id` (`owner_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs;

-- --------------------------------------------------------

--
-- Структура таблиці `m_gsession_msg`
--

CREATE TABLE IF NOT EXISTS `m_gsession_msg` (
  `msg_id` int(11) NOT NULL AUTO_INCREMENT,
  `datestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `gsession_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `msgtype` tinyint(4) NOT NULL,
  `msg_text` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`msg_id`),
  KEY `user_id` (`user_id`),
  KEY `gsession_id` (`gsession_id`),
  KEY `datestamp` (`datestamp`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2390708 ;

-- --------------------------------------------------------

--
-- Структура таблиці `m_gsession_user`
--

CREATE TABLE IF NOT EXISTS `m_gsession_user` (
  `gsu_id` int(11) NOT NULL AUTO_INCREMENT,
  `gsession_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `act_order` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_holder` tinyint(1) NOT NULL DEFAULT '0',
  `has_penalty` tinyint(1) NOT NULL DEFAULT '0',
  `penalty_turn` int(11) DEFAULT NULL,
  `user_cash` int(11) NOT NULL,
  `position_field_id` int(11) NOT NULL,
  `last_dice1` tinyint(4) DEFAULT NULL,
  `last_dice2` tinyint(4) DEFAULT NULL,
  `debitor_stamp` timestamp NULL DEFAULT NULL,
  `last_changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`gsu_id`),
  UNIQUE KEY `UK_GSESSION_USER` (`gsession_id`,`user_id`),
  KEY `gsession_id` (`gsession_id`),
  KEY `user_id` (`user_id`),
  KEY `position_field_id` (`position_field_id`),
  KEY `act_order` (`act_order`)
) ENGINE=InnoDB  DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs AUTO_INCREMENT=68 ;

-- --------------------------------------------------------

--
-- Структура таблиці `m_watchdog`
--

CREATE TABLE IF NOT EXISTS `m_watchdog` (
  `w_id` int(11) NOT NULL AUTO_INCREMENT,
  `watch_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `wstatus` enum('finished','inprogress','failed') COLLATE utf8_bin NOT NULL,
  `wstart_stamp` timestamp NULL DEFAULT NULL,
  `wend_stamp` timestamp NULL DEFAULT NULL,
  `wspend` float DEFAULT NULL,
  PRIMARY KEY (`w_id`),
  UNIQUE KEY `watch_stamp` (`watch_stamp`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=138624 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `m_gsession`
--
ALTER TABLE `m_gsession`
  ADD CONSTRAINT `m_gsession_ibfk_1` FOREIGN KEY (`map_id`) REFERENCES `m_cfg_map` (`map_id`);

--
-- Constraints for table `m_gsession_auction`
--
ALTER TABLE `m_gsession_auction`
  ADD CONSTRAINT `m_gsession_auction_ibfk_1` FOREIGN KEY (`gsession_id`) REFERENCES `m_gsession` (`gsession_id`),
  ADD CONSTRAINT `m_gsession_auction_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `m_cfg_map_field` (`field_id`),
  ADD CONSTRAINT `m_gsession_auction_ibfk_3` FOREIGN KEY (`auct_bid_user_id`) REFERENCES `m_user` (`user_id`),
  ADD CONSTRAINT `m_gsession_auction_ibfk_4` FOREIGN KEY (`auct_holder_user_id`) REFERENCES `m_user` (`user_id`);

--
-- Constraints for table `m_gsession_auction_bid`
--
ALTER TABLE `m_gsession_auction_bid`
  ADD CONSTRAINT `m_gsession_auction_bid_ibfk_1` FOREIGN KEY (`gsession_id`) REFERENCES `m_gsession` (`gsession_id`),
  ADD CONSTRAINT `m_gsession_auction_bid_ibfk_2` FOREIGN KEY (`auct_id`) REFERENCES `m_gsession_auction` (`auct_id`),
  ADD CONSTRAINT `m_gsession_auction_bid_ibfk_3` FOREIGN KEY (`bidder_user_id`) REFERENCES `m_user` (`user_id`);

--
-- Constraints for table `m_gsession_auction_user`
--
ALTER TABLE `m_gsession_auction_user`
  ADD CONSTRAINT `m_gsession_auction_user_ibfk_1` FOREIGN KEY (`auct_id`) REFERENCES `m_gsession_auction` (`auct_id`),
  ADD CONSTRAINT `m_gsession_auction_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `m_user` (`user_id`);

--
-- Constraints for table `m_gsession_deal`
--
ALTER TABLE `m_gsession_deal`
  ADD CONSTRAINT `m_gsession_deal_ibfk_1` FOREIGN KEY (`gsession_id`) REFERENCES `m_gsession` (`gsession_id`),
  ADD CONSTRAINT `m_gsession_deal_ibfk_2` FOREIGN KEY (`deal_holder_user_id`) REFERENCES `m_user` (`user_id`),
  ADD CONSTRAINT `m_gsession_deal_ibfk_3` FOREIGN KEY (`deal_opponent_user_id`) REFERENCES `m_user` (`user_id`);

--
-- Constraints for table `m_gsession_deal_list`
--
ALTER TABLE `m_gsession_deal_list`
  ADD CONSTRAINT `m_gsession_deal_list_ibfk_1` FOREIGN KEY (`deal_id`) REFERENCES `m_gsession_deal` (`deal_id`),
  ADD CONSTRAINT `m_gsession_deal_list_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `m_cfg_map_field` (`field_id`);

--
-- Constraints for table `m_gsession_log`
--
ALTER TABLE `m_gsession_log`
  ADD CONSTRAINT `m_gsession_log_ibfk_1` FOREIGN KEY (`gsession_id`) REFERENCES `m_gsession` (`gsession_id`),
  ADD CONSTRAINT `m_gsession_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `m_user` (`user_id`);

--
-- Constraints for table `m_gsession_map_fgroup`
--
ALTER TABLE `m_gsession_map_fgroup`
  ADD CONSTRAINT `m_gsession_map_fgroup_ibfk_1` FOREIGN KEY (`gsession_id`) REFERENCES `m_gsession` (`gsession_id`),
  ADD CONSTRAINT `m_gsession_map_fgroup_ibfk_2` FOREIGN KEY (`fgroup_id`) REFERENCES `m_cfg_map_fgroup` (`fgroup_id`),
  ADD CONSTRAINT `m_gsession_map_fgroup_ibfk_3` FOREIGN KEY (`map_id`) REFERENCES `m_cfg_map` (`map_id`),
  ADD CONSTRAINT `m_gsession_map_fgroup_ibfk_5` FOREIGN KEY (`fgowner_user_id`) REFERENCES `m_user` (`user_id`);

--
-- Constraints for table `m_gsession_map_field`
--
ALTER TABLE `m_gsession_map_field`
  ADD CONSTRAINT `m_gsession_map_field_ibfk_1` FOREIGN KEY (`gsession_id`) REFERENCES `m_gsession` (`gsession_id`),
  ADD CONSTRAINT `m_gsession_map_field_ibfk_2` FOREIGN KEY (`map_id`) REFERENCES `m_cfg_map` (`map_id`),
  ADD CONSTRAINT `m_gsession_map_field_ibfk_3` FOREIGN KEY (`field_id`) REFERENCES `m_cfg_map_field` (`field_id`),
  ADD CONSTRAINT `m_gsession_map_field_ibfk_4` FOREIGN KEY (`owner_user_id`) REFERENCES `m_user` (`user_id`);

--
-- Constraints for table `m_gsession_msg`
--
ALTER TABLE `m_gsession_msg`
  ADD CONSTRAINT `m_gsession_msg_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `m_user` (`user_id`),
  ADD CONSTRAINT `m_gsession_msg_ibfk_2` FOREIGN KEY (`gsession_id`) REFERENCES `m_gsession` (`gsession_id`);

--
-- Constraints for table `m_gsession_user`
--
ALTER TABLE `m_gsession_user`
  ADD CONSTRAINT `m_gsession_user_ibfk_1` FOREIGN KEY (`gsession_id`) REFERENCES `m_gsession` (`gsession_id`),
  ADD CONSTRAINT `m_gsession_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `m_user` (`user_id`),
  ADD CONSTRAINT `m_gsession_user_ibfk_3` FOREIGN KEY (`position_field_id`) REFERENCES `m_cfg_map_field` (`field_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
