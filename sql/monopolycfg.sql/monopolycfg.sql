-- phpMyAdmin SQL Dump
-- version 3.5.2
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Час створення: Жов 03 2012 р., 03:50
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
-- Структура таблиці `gw_users`
--

CREATE TABLE IF NOT EXISTS `gw_users` (
  `u_id` int(11) NOT NULL AUTO_INCREMENT,
  `u_name` varchar(240) NOT NULL,
  `u_login` varchar(240) NOT NULL,
  `u_password` char(32) DEFAULT NULL,
  `service` varchar(240) DEFAULT NULL,
  `identity` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`u_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Дамп даних таблиці `gw_users`
--

INSERT INTO `gw_users` (`u_id`, `u_name`, `u_login`, `u_password`, `service`, `identity`) VALUES
(1, 'Admin', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'int', '1'),
(2, 'Сергей', 'matsaks@gmail.com', 'd41d8cd98f00b204e9800998ecf8427e', 'google', 'https://www.google.com/accounts/o8/id?id=AItOawnwrnfo_WCokWFRco7M1b3wDQ8l9bNRlSY'),
(3, 'Julia', 'gvozdi4ka@gmail.com', 'd41d8cd98f00b204e9800998ecf8427e', 'google', 'https://www.google.com/accounts/o8/id?id=AItOawmnJoADBKMsIzPpAG7vKKmvw760hYrwgqM'),
(4, 'Сергей', 'matsaks@gmail.com', 'd41d8cd98f00b204e9800998ecf8427e', 'google', 'https://www.google.com/accounts/o8/id?id=AItOawmxzpkcFdLmWeBpiFrdT01X5SPyA4kBuJU');

-- --------------------------------------------------------

--
-- Структура таблиці `m_cfg_auaction`
--

CREATE TABLE IF NOT EXISTS `m_cfg_auaction` (
  `auact_code` int(11) NOT NULL,
  `auact_desc` varchar(2000) COLLATE utf8_bin NOT NULL,
  `auact_sql_tpl` text COLLATE utf8_bin NOT NULL,
  `auact_msg_tpl_code` varchar(240) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`auact_code`),
  KEY `auact_msg_tpl_code` (`auact_msg_tpl_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Дамп даних таблиці `m_cfg_auaction`
--

INSERT INTO `m_cfg_auaction` (`auact_code`, `auact_desc`, `auact_sql_tpl`, `auact_msg_tpl_code`) VALUES
(1, 'Attached auction finish - change owner, take payment', 'update m_gsession_user u1,  m_gsession_map_field mf set u1.user_cash=u1.user_cash-%BID%,  mf.owner_user_id=%BIDDER_USER_ID% \nwhere u1.user_id=''%BIDDER_USER_ID%'' and u1.gsession_id=%GSESSION_ID% and mf.gsession_id=%GSESSION_ID% and mf.field_id=%FIELD_ID%', 'MSG_INFO_AU_ATTACHED_WON'),
(2, 'Public auction finish - change owner, take payment', 'update m_gsession_user u1, m_gsession_user u2, m_gsession_map_field mf set u1.user_cash=u1.user_cash-%BID%, u2.user_cash=u2.user_cash+%BID%, mf.owner_user_id=%BIDDER_USER_ID% \nwhere u1.user_id=''%BIDDER_USER_ID%'' and u1.gsession_id=%GSESSION_ID% and u2.user_id=''%OWNER_USER_ID%'' and u2.gsession_id=%GSESSION_ID% and mf.gsession_id=%GSESSION_ID% and mf.field_id=%FIELD_ID%', 'MSG_INFO_AU_PUBLIC_WON');

-- --------------------------------------------------------

--
-- Структура таблиці `m_cfg_faction`
--

CREATE TABLE IF NOT EXISTS `m_cfg_faction` (
  `fact_code` int(11) NOT NULL,
  `fact_desc` varchar(2000) COLLATE cp1251_general_cs NOT NULL,
  `event` enum('onfly','onsite') COLLATE cp1251_general_cs NOT NULL,
  `fact_sql_tpl` text COLLATE cp1251_general_cs NOT NULL,
  `fact_msg_tpl` text COLLATE cp1251_general_cs,
  PRIMARY KEY (`fact_code`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs;

--
-- Дамп даних таблиці `m_cfg_faction`
--

INSERT INTO `m_cfg_faction` (`fact_code`, `fact_desc`, `event`, `fact_sql_tpl`, `fact_msg_tpl`) VALUES
(1, 'start reward', 'onfly', 'update m_gsession_user set user_cash=user_cash+%FPARAM%\nwhere user_id=%USER_ID% and gsession_id=%GSESSION_ID%', 'Игрок %USER_NAME% получил %FPARAM% за прохождение круга'),
(2, 'owner payment', 'onsite', 'update m_gsession_user u1, m_gsession_user u2 set u1.user_cash=u1.user_cash-%FTAX%, u2.user_cash=u2.user_cash+%FTAX%\nwhere u1.user_id=''%USER_ID%'' and u1.gsession_id=%GSESSION_ID% and u2.user_id=''%OWNER_USER_ID%'' and u2.gsession_id=%GSESSION_ID%\n', 'Игрок %USER_NAME% оплатил услуги игроку %OWNER_USER_NAME% в размере %FTAX%'),
(3, 'tax', 'onsite', 'update m_gsession_user set user_cash=user_cash-%FPARAM_CALC1%\nwhere user_id=%USER_ID% and gsession_id=%GSESSION_ID%', 'Игроку %USER_NAME% пришлось заплатить налоги в размере %FPARAM_CALC1%'),
(4, 'lottery', 'onsite', 'update m_gsession_user set user_cash=user_cash+%FPARAM_CALC1%\nwhere user_id=%USER_ID% and gsession_id=%GSESSION_ID%', 'Игрок %USER_NAME% выйграл в лоторее %FPARAM_CALC1%'),
(5, 'jump', 'onsite', 'update m_gsession_user set position_field_id=%FPARAM%\nwhere user_id=%USER_ID% and gsession_id=%GSESSION_ID%', 'Игрок %USER_NAME%  перепутал билеты и вернулся на %FPARAM%'),
(6, 'give penalty', 'onsite', 'update m_gsession_user set has_penalty=1, penalty_turn=%GTURN%+%ACTIVE_PLAYERS%\nwhere user_id=%USER_ID% and gsession_id=%GSESSION_ID%', 'Игрок %USER_NAME% попал задержан полицией и пропускает ход'),
(7, 'casino', 'onsite', 'update m_gsession_user set user_cash=user_cash+%FPARAM_CALC1%\nwhere user_id=%USER_ID% and gsession_id=%GSESSION_ID%', 'Игрок %USER_NAME% сходил в казино  %PAY_TYPE% %FPARAM_CALC1%'),
(8, 'stock exchange', 'onsite', 'update m_gsession_map_field set fparam=round(fparam*%FPARAM_CALC1%/100)+1\nwhere gsession_id=%GSESSION_ID% and owner_user_id=%USER_ID% ', 'Акции предприятий игрока %USER_NAME% %EXCH_TYPE%. Доход от предприятий составит %FPARAM_CALC1%% от текущей.');

-- --------------------------------------------------------

--
-- Структура таблиці `m_cfg_ftype`
--

CREATE TABLE IF NOT EXISTS `m_cfg_ftype` (
  `ftype_code` int(11) NOT NULL,
  `field_desc` varchar(2000) COLLATE cp1251_general_cs DEFAULT NULL,
  UNIQUE KEY `ftype_code` (`ftype_code`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs;

--
-- Дамп даних таблиці `m_cfg_ftype`
--

INSERT INTO `m_cfg_ftype` (`ftype_code`, `field_desc`) VALUES
(1, 'Start point'),
(2, 'General'),
(3, 'Event');

-- --------------------------------------------------------

--
-- Структура таблиці `m_cfg_language`
--

CREATE TABLE IF NOT EXISTS `m_cfg_language` (
  `lang_code` varchar(240) COLLATE utf8_bin NOT NULL,
  `lang_name` varchar(240) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`lang_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Дамп даних таблиці `m_cfg_language`
--

INSERT INTO `m_cfg_language` (`lang_code`, `lang_name`) VALUES
('eng', 'English'),
('rus', 'Russian'),
('ukr', 'Ukrainian');

-- --------------------------------------------------------

--
-- Структура таблиці `m_cfg_map`
--

CREATE TABLE IF NOT EXISTS `m_cfg_map` (
  `map_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(2000) COLLATE cp1251_general_cs NOT NULL,
  PRIMARY KEY (`map_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs AUTO_INCREMENT=2 ;

--
-- Дамп даних таблиці `m_cfg_map`
--

INSERT INTO `m_cfg_map` (`map_id`, `name`) VALUES
(1, 'simple');

-- --------------------------------------------------------

--
-- Структура таблиці `m_cfg_map_fgroup`
--

CREATE TABLE IF NOT EXISTS `m_cfg_map_fgroup` (
  `fgroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `map_id` int(11) NOT NULL,
  `fgroup_name` varchar(240) COLLATE utf8_bin NOT NULL,
  `fgparam` varchar(1000) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`fgroup_id`),
  KEY `map_id` (`map_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=10 ;

--
-- Дамп даних таблиці `m_cfg_map_fgroup`
--

INSERT INTO `m_cfg_map_fgroup` (`fgroup_id`, `map_id`, `fgroup_name`, `fgparam`) VALUES
(1, 1, 'G1', NULL),
(2, 1, 'G2', NULL),
(3, 1, 'G3', NULL),
(4, 1, 'G4', NULL),
(5, 1, 'G5', NULL),
(6, 1, 'G6', NULL),
(7, 1, 'G7', NULL),
(8, 1, 'G8', NULL),
(9, 1, 'G9', NULL);

-- --------------------------------------------------------

--
-- Структура таблиці `m_cfg_map_field`
--

CREATE TABLE IF NOT EXISTS `m_cfg_map_field` (
  `field_id` int(11) NOT NULL AUTO_INCREMENT,
  `map_id` int(11) NOT NULL,
  `fcode` int(11) NOT NULL,
  `name` varchar(2000) COLLATE cp1251_general_cs NOT NULL,
  `ftype_code` int(11) NOT NULL,
  `fgroup_id` int(11) DEFAULT NULL,
  `fact_code` int(11) DEFAULT NULL,
  `fact_cond` varchar(1000) COLLATE cp1251_general_cs DEFAULT NULL,
  `fparam` varchar(240) COLLATE cp1251_general_cs DEFAULT NULL,
  `fparam_calc1` varchar(1000) COLLATE cp1251_general_cs DEFAULT NULL,
  `fparam_calc2` varchar(1000) COLLATE cp1251_general_cs DEFAULT NULL,
  PRIMARY KEY (`field_id`),
  KEY `ftype_code` (`ftype_code`),
  KEY `map_id` (`map_id`),
  KEY `fgroup_id` (`fgroup_id`),
  KEY `fact_code` (`fact_code`),
  KEY `fgroup_id_2` (`fgroup_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs AUTO_INCREMENT=33 ;

--
-- Дамп даних таблиці `m_cfg_map_field`
--

INSERT INTO `m_cfg_map_field` (`field_id`, `map_id`, `fcode`, `name`, `ftype_code`, `fgroup_id`, `fact_code`, `fact_cond`, `fparam`, `fparam_calc1`, `fparam_calc2`) VALUES
(1, 1, 1, 'C1', 1, NULL, 1, NULL, '30000', NULL, NULL),
(2, 1, 2, 'C2', 2, 1, 2, NULL, '1000', NULL, NULL),
(3, 1, 3, 'C3', 2, 1, 2, NULL, '1500', NULL, NULL),
(4, 1, 4, 'C4', 2, 1, 2, NULL, '1250', NULL, NULL),
(5, 1, 5, 'C5', 3, NULL, 4, 'ROUND( RAND( ) *1000 /1000 ) *1000 * ROUND( RAND( ) *100 )', NULL, 'ROUND( RAND( ) *100 /100 ) *round(ABS(%MAX_USER_CASH%-%USER_CASH%)/300) * ROUND( RAND( ) *100 )+100', NULL),
(6, 1, 6, 'C6', 2, 9, 2, NULL, '4000', NULL, NULL),
(7, 1, 7, 'C7', 2, 2, 2, NULL, '2000', NULL, NULL),
(8, 1, 8, 'C8', 2, 2, 2, NULL, '2300', NULL, NULL),
(9, 1, 9, 'C9', 2, 2, 2, NULL, '2500', NULL, NULL),
(10, 1, 10, 'C10', 3, NULL, 6, 'ROUND(RAND())*ROUND(RAND())', NULL, NULL, NULL),
(11, 1, 11, 'C11', 2, 3, 2, NULL, '2000', NULL, NULL),
(12, 1, 12, 'C12', 2, 3, 2, NULL, '3000', NULL, NULL),
(13, 1, 13, 'C13', 3, NULL, 3, NULL, NULL, 'ROUND( RAND( ) * (ABS(%USER_PROPERTY%)*30/100) )+100', NULL),
(14, 1, 14, 'C14', 2, 9, 2, NULL, '8000', NULL, NULL),
(15, 1, 15, 'C15', 2, 4, 2, NULL, '4000', NULL, NULL),
(16, 1, 16, 'C16', 2, 4, 2, NULL, '3500', NULL, NULL),
(17, 1, 17, 'C17', 3, NULL, 8, 'ROUND(RAND())*ROUND(RAND())', NULL, 'ROUND(RAND()*180)+50', NULL),
(18, 1, 18, 'C18', 2, 5, 2, NULL, '5000', NULL, NULL),
(19, 1, 19, 'C19', 2, 5, 2, NULL, '6500', NULL, NULL),
(20, 1, 20, 'C20', 2, 5, 2, NULL, '10000', NULL, NULL),
(21, 1, 21, 'C21', 3, NULL, 4, NULL, NULL, 'ROUND( RAND( ) *100 /100 ) *round(ABS(%USER_CASH%)/300) * ROUND( RAND( ) *100 )+100', NULL),
(22, 1, 22, 'C22', 2, 9, 2, NULL, '8000', NULL, NULL),
(23, 1, 23, 'C23', 2, 6, 2, NULL, '5500', NULL, NULL),
(24, 1, 24, 'C24', 2, 6, 2, NULL, '6000', NULL, NULL),
(25, 1, 25, 'C25', 2, 6, 2, NULL, '3000', NULL, NULL),
(26, 1, 26, 'C26', 3, NULL, 5, 'ROUND(RAND())*ROUND(RAND())', '10', NULL, NULL),
(27, 1, 27, 'C27', 2, 7, 2, NULL, '15000', NULL, NULL),
(28, 1, 28, 'C28', 2, 7, 2, NULL, '20000', NULL, NULL),
(29, 1, 29, 'C29', 2, 9, 2, NULL, '20000', NULL, NULL),
(30, 1, 30, 'C30', 3, NULL, 7, NULL, NULL, '(ROUND( RAND( ) *100 /100 ) *round(ABS(%USER_CASH%)/200) * ROUND( RAND( ) *100 )+100)*(ROUND(RAND())*(ROUND(RAND()))-1)', NULL),
(31, 1, 31, 'C31', 2, 8, 2, NULL, '10000', NULL, NULL),
(32, 1, 32, 'C32', 2, 8, 2, NULL, '25000', NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблиці `m_cfg_message`
--

CREATE TABLE IF NOT EXISTS `m_cfg_message` (
  `msg_code` varchar(240) COLLATE utf8_bin NOT NULL,
  `msg_desc` text COLLATE utf8_bin NOT NULL,
  `msg_class` enum('au','gm','gn','dl') COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`msg_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Дамп даних таблиці `m_cfg_message`
--

INSERT INTO `m_cfg_message` (`msg_code`, `msg_desc`, `msg_class`) VALUES
('MSG_INFO_AU_ATTACHED_OPENED', '', 'au'),
('MSG_INFO_AU_ATTACHED_WON', '', 'au'),
('MSG_INFO_AU_CLOSED', '', 'au'),
('MSG_INFO_AU_MAKEBID', '', 'au'),
('MSG_INFO_AU_PUBLIC_OPENED', '', 'au'),
('MSG_INFO_AU_PUBLIC_WON', '', 'au'),
('MSG_INFO_AU_USER_JOIN', '', 'au'),
('MSG_INFO_AU_USER_LEAVE', '', 'au'),
('MSG_INFO_DICE', '', 'gm'),
('MSG_INFO_DL_ACCEPTED', '', 'dl'),
('MSG_INFO_DL_CANCELED', '', 'dl'),
('MSG_INFO_DL_OPENED', '', 'dl'),
('MSG_INFO_DL_REJECTED', '', 'dl'),
('MSG_INFO_DL_TERMINATED', '', 'dl'),
('MSG_INFO_GS_CREATED', '', 'gm'),
('MSG_INFO_GS_FINISHED', '', 'gm'),
('MSG_INFO_GS_STARTED', '', 'gm'),
('MSG_INFO_GS_TERMINATED', '', 'gm'),
('MSG_INFO_GS_USER_JOIN', '', 'gm'),
('MSG_INFO_GS_USER_LOSE', '', 'gm'),
('MSG_INFO_GS_USER_WIN', '', 'gm');

-- --------------------------------------------------------

--
-- Структура таблиці `m_cfg_message_lang`
--

CREATE TABLE IF NOT EXISTS `m_cfg_message_lang` (
  `msgl_id` int(11) NOT NULL AUTO_INCREMENT,
  `msg_code` varchar(240) COLLATE utf8_bin NOT NULL,
  `lang_code` varchar(240) COLLATE utf8_bin NOT NULL,
  `msg` text COLLATE utf8_bin NOT NULL,
  `is_tpl` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`msgl_id`),
  UNIQUE KEY `msg_code` (`msg_code`,`lang_code`),
  KEY `lang_code` (`lang_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=30 ;

--
-- Дамп даних таблиці `m_cfg_message_lang`
--

INSERT INTO `m_cfg_message_lang` (`msgl_id`, `msg_code`, `lang_code`, `msg`, `is_tpl`) VALUES
(1, 'MSG_INFO_AU_ATTACHED_OPENED', 'eng', 'Nonpublic auction AU%AUCT_ID% started', 1),
(2, 'MSG_INFO_AU_ATTACHED_OPENED', 'rus', 'Непубличный аукцион AU%AUCT_ID% начат', 1),
(3, 'MSG_INFO_AU_ATTACHED_WON', 'eng', 'Player %BIDDER_USER_NAME% won the auction AU%AUCT_ID%! He paid %BID% and became owner of %FIELD_NAME%', 1),
(4, 'MSG_INFO_AU_ATTACHED_WON', 'rus', 'Игрок %BIDDER_USER_NAME% выиграл аукцион AU%AUCT_ID%! Он оплатил %BID% и стал владельцем %FIELD_NAME%', 1),
(5, 'MSG_INFO_AU_CLOSED', 'eng', 'Auction AU%AUCT_ID% closed', 1),
(6, 'MSG_INFO_AU_CLOSED', 'rus', 'Аукцион AU%AUCT_ID% закрыт', 1),
(7, 'MSG_INFO_AU_MAKEBID', 'eng', 'Player %USER_NAME% make a bid %BID% on auction AU%AUCT_ID%', 1),
(8, 'MSG_INFO_AU_MAKEBID', 'rus', 'Игрок %USER_NAME% сделал ставку %BID% на аукционе AU%AUCT_ID%', 1),
(9, 'MSG_INFO_AU_PUBLIC_OPENED', 'eng', 'Player %HOLDER_USER_NAME% opened auction AU%AUCT_ID%', 1),
(10, 'MSG_INFO_AU_PUBLIC_OPENED', 'rus', 'Игрок %HOLDER_USER_NAME% открыл аукцион AU%AUCT_ID%', 1),
(11, 'MSG_INFO_AU_PUBLIC_WON', 'eng', 'Player %BIDDER_USER_NAME% won the auction AU%AUCT_ID%! He paid %BID% to player %HOLDER_USER_NAME% and became owner of %FIELD_NAME%', 1),
(12, 'MSG_INFO_AU_PUBLIC_WON', 'rus', 'Игрок %BIDDER_USER_NAME% выиграл аукцион AU%AUCT_ID%! Он заплатил %BID% игроку %HOLDER_USER_NAME% и стал владельцем %FIELD_NAME%', 1),
(13, 'MSG_INFO_AU_USER_JOIN', 'eng', 'Player %USER_NAME% joined to auction AU%AUCT_ID%', 1),
(14, 'MSG_INFO_AU_USER_JOIN', 'rus', 'Игрок %USER_NAME% принял участие в аукционе AU%AUCT_ID%', 1),
(15, 'MSG_INFO_AU_USER_LEAVE', 'eng', 'Player %USER_NAME% leave auction AU%AUCT_ID%', 1),
(16, 'MSG_INFO_AU_USER_LEAVE', 'rus', 'Игрок %USER_NAME% покинул аукцион AU%AUCT_ID%', 1),
(17, 'MSG_INFO_DICE', 'eng', 'Player %USER_NAME% rolled the dice %LAST_DICE1%:%LAST_DICE2%', 1),
(18, 'MSG_INFO_DICE', 'rus', 'Игрок %USER_NAME% бросил кости %LAST_DICE1%:%LAST_DICE2%', 1),
(19, 'MSG_INFO_DL_ACCEPTED', 'rus', 'Игрок %DEAL_OPPONENT_USER_NAME% принял условия сделки DL%DEAL_ID% игрока %DEAL_HOLDER_USER_NAME%', 1),
(20, 'MSG_INFO_DL_CANCELED', 'rus', 'Игрок %DEAL_HOLDER_USER_NAME% отменил сделку DL%DEAL_ID%', 1),
(21, 'MSG_INFO_DL_OPENED', 'rus', 'Игрок %DEAL_HOLDER_USER_NAME% предложил сделку DL%DEAL_ID% игроку %DEAL_OPPONENT_USER_NAME%', 1),
(22, 'MSG_INFO_DL_REJECTED', 'rus', 'Игрок %DEAL_OPPONENT_USER_NAME% отказал в совершении сделки DL%DEAL_ID% от игрока %DEAL_HOLDER_USER_NAME%', 0),
(23, 'MSG_INFO_DL_TERMINATED', 'rus', 'Сделка DL%DEAL_ID% автоматически отменена по истечению срока действия.', 0),
(24, 'MSG_INFO_GS_CREATED', 'rus', 'Игрок %USER_NAME% создал игровую сессию %CREATESTAMP%. Игра автоматически начнется при соединении %G_GS_MAX_PLAYERS% игроков или по истечению %G_GS_START_TIMEOUT% минут при наличии %G_GS_MIN_PLAYERS% игроков.', 0),
(26, 'MSG_INFO_GS_TERMINATED', 'rus', 'Игра прервана', 0),
(27, 'MSG_INFO_GS_STARTED', 'rus', 'Игра началась STARTSTAMP. Игрок %HOLDER_USER_NAME% первым бросает кости.', 0),
(28, 'MSG_INFO_GS_USER_JOIN', 'rus', 'Игрок %USER_NAME% присоединился к игре', 0),
(29, 'MSG_INFO_GS_USER_LOSE', 'rus', 'Игрок %TARGET_USER_NAME% проиграл. Все имущество возвращается в фонд государства.', 0);

-- --------------------------------------------------------

--
-- Структура таблиці `m_cfg_saction`
--

CREATE TABLE IF NOT EXISTS `m_cfg_saction` (
  `sact_code` int(11) NOT NULL,
  `sact_desc` varchar(2000) COLLATE cp1251_general_cs NOT NULL,
  `chance` int(11) NOT NULL,
  `sact_sql_tpl` text COLLATE cp1251_general_cs NOT NULL,
  PRIMARY KEY (`sact_code`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs;

--
-- Дамп даних таблиці `m_cfg_saction`
--

INSERT INTO `m_cfg_saction` (`sact_code`, `sact_desc`, `chance`, `sact_sql_tpl`) VALUES
(1, 'Uprise prices', 10, ''),
(2, 'reduction of prices', 10, ''),
(3, 'Total tax', 5, '');

-- --------------------------------------------------------

--
-- Структура таблиці `m_user`
--

CREATE TABLE IF NOT EXISTS `m_user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id user',
  `login` varchar(240) COLLATE cp1251_general_cs NOT NULL COMMENT 'login',
  `name` varchar(1000) COLLATE cp1251_general_cs NOT NULL,
  `passwd` varchar(240) COLLATE cp1251_general_cs NOT NULL,
  `service` varchar(240) COLLATE cp1251_general_cs NOT NULL,
  `identity` varchar(2000) COLLATE cp1251_general_cs NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs AUTO_INCREMENT=4 ;

--
-- Дамп даних таблиці `m_user`
--

INSERT INTO `m_user` (`user_id`, `login`, `name`, `passwd`, `service`, `identity`) VALUES
(1, 'admin', 'admin', 'admin', '', '1'),
(2, 'msa', 'msa', 'msa', '', '2'),
(3, 'gvozdi4ka', 'gvozdi4ka', 'gvozdi4ka', '', '3');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `m_cfg_auaction`
--
ALTER TABLE `m_cfg_auaction`
  ADD CONSTRAINT `m_cfg_auaction_ibfk_1` FOREIGN KEY (`auact_msg_tpl_code`) REFERENCES `m_cfg_message` (`msg_code`);

--
-- Constraints for table `m_cfg_map_fgroup`
--
ALTER TABLE `m_cfg_map_fgroup`
  ADD CONSTRAINT `m_cfg_map_fgroup_ibfk_1` FOREIGN KEY (`map_id`) REFERENCES `m_cfg_map` (`map_id`);

--
-- Constraints for table `m_cfg_map_field`
--
ALTER TABLE `m_cfg_map_field`
  ADD CONSTRAINT `m_cfg_map_field_ibfk_1` FOREIGN KEY (`map_id`) REFERENCES `m_cfg_map` (`map_id`),
  ADD CONSTRAINT `m_cfg_map_field_ibfk_2` FOREIGN KEY (`ftype_code`) REFERENCES `m_cfg_ftype` (`ftype_code`),
  ADD CONSTRAINT `m_cfg_map_field_ibfk_3` FOREIGN KEY (`fact_code`) REFERENCES `m_cfg_faction` (`fact_code`),
  ADD CONSTRAINT `m_cfg_map_field_ibfk_4` FOREIGN KEY (`fgroup_id`) REFERENCES `m_cfg_map_fgroup` (`fgroup_id`);

--
-- Constraints for table `m_cfg_message_lang`
--
ALTER TABLE `m_cfg_message_lang`
  ADD CONSTRAINT `m_cfg_message_lang_ibfk_1` FOREIGN KEY (`msg_code`) REFERENCES `m_cfg_message` (`msg_code`),
  ADD CONSTRAINT `m_cfg_message_lang_ibfk_2` FOREIGN KEY (`lang_code`) REFERENCES `m_cfg_language` (`lang_code`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
