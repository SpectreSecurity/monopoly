--
-- БД: monopoly
--

-- --------------------------------------------------------

--
-- Структура таблиці gw_users
--

CREATE TABLE  gw_users (
  u_id number ,
  u_name varchar2(240) ,
  u_login varchar2(240) ,
  u_password char(32) DEFAULT NULL,
  service varchar2(240) DEFAULT NULL,
  identity varchar2(2000) DEFAULT NULL
);

--
-- Дамп даних таблиці gw_users
--

INSERT INTO gw_users (u_id, u_name, u_login, u_password, service, identity) VALUES
(1, 'Admin', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'int', '1');
INSERT INTO gw_users (u_id, u_name, u_login, u_password, service, identity) VALUES
(2, 'Сергей', 'matsaks@gmail.com', 'd41d8cd98f00b204e9800998ecf8427e', 'google', 'https://www.google.com/accounts/o8/id?id=AItOawnwrnfo_WCokWFRco7M1b3wDQ8l9bNRlSY');
INSERT INTO gw_users (u_id, u_name, u_login, u_password, service, identity) VALUES 
(3, 'Julia', 'gvozdi4ka@gmail.com', 'd41d8cd98f00b204e9800998ecf8427e', 'google', 'https://www.google.com/accounts/o8/id?id=AItOawmnJoADBKMsIzPpAG7vKKmvw760hYrwgqM');
INSERT INTO gw_users (u_id, u_name, u_login, u_password, service, identity) VALUES
(4, 'Сергей', 'matsaks@gmail.com', 'd41d8cd98f00b204e9800998ecf8427e', 'google', 'https://www.google.com/accounts/o8/id?id=AItOawmxzpkcFdLmWeBpiFrdT01X5SPyA4kBuJU');

-- --------------------------------------------------------

--
-- Структура таблиці m_cfg_auaction
--

CREATE TABLE  m_cfg_auaction (
  auact_code number ,
  auact_desc varchar2(2000)  ,
  auact_sql_tpl clob  ,
  auact_msg_tpl_code varchar2(240)  DEFAULT NULL
) ;

--
-- Дамп даних таблиці m_cfg_auaction
--

INSERT INTO m_cfg_auaction (auact_code, auact_desc, auact_sql_tpl, auact_msg_tpl_code) VALUES
(1, 'Attached auction finish - change owner, take payment', 'update m_gsession_user u1,  m_gsession_map_field mf set u1.user_cash=u1.user_cash-%BID%,  mf.owner_user_id=%BIDDER_USER_ID% \nwhere u1.user_id=''%BIDDER_USER_ID%'' and u1.gsession_id=%GSESSION_ID% and mf.gsession_id=%GSESSION_ID% and mf.field_id=%FIELD_ID%', 'MSG_INFO_AU_ATTACHED_WON');
INSERT INTO m_cfg_auaction (auact_code, auact_desc, auact_sql_tpl, auact_msg_tpl_code) VALUES
(2, 'Public auction finish - change owner, take payment', 'update m_gsession_user u1, m_gsession_user u2, m_gsession_map_field mf set u1.user_cash=u1.user_cash-%BID%, u2.user_cash=u2.user_cash+%BID%, mf.owner_user_id=%BIDDER_USER_ID% \nwhere u1.user_id=''%BIDDER_USER_ID%'' and u1.gsession_id=%GSESSION_ID% and u2.user_id=''%OWNER_USER_ID%'' and u2.gsession_id=%GSESSION_ID% and mf.gsession_id=%GSESSION_ID% and mf.field_id=%FIELD_ID%', 'MSG_INFO_AU_PUBLIC_WON');

-- --------------------------------------------------------

--
-- Структура таблиці m_cfg_faction
--

CREATE TABLE  m_cfg_faction (
  fact_code number ,
  fact_desc varchar2(2000)     ,
  event varchar2(240)  ,
  fact_sql_tpl clob     ,
  fact_msg_tpl clob   
);

--
-- Дамп даних таблиці m_cfg_faction
--

INSERT INTO m_cfg_faction (fact_code, fact_desc, event, fact_sql_tpl, fact_msg_tpl) VALUES
(1, 'start reward', 'onfly', 'update m_gsession_user set user_cash=user_cash+%FPARAM%\nwhere user_id=%USER_ID% and gsession_id=%GSESSION_ID%', 'Игрок %USER_NAME% получил %FPARAM% за прохождение круга');
INSERT INTO m_cfg_faction (fact_code, fact_desc, event, fact_sql_tpl, fact_msg_tpl) VALUES
(2, 'owner payment', 'onsite', 'update m_gsession_user u1, m_gsession_user u2 set u1.user_cash=u1.user_cash-%FTAX%, u2.user_cash=u2.user_cash+%FTAX%\nwhere u1.user_id=''%USER_ID%'' and u1.gsession_id=%GSESSION_ID% and u2.user_id=''%OWNER_USER_ID%'' and u2.gsession_id=%GSESSION_ID%\n', 'Игрок %USER_NAME% оплатил услуги игроку %OWNER_USER_NAME% в размере %FTAX%');
INSERT INTO m_cfg_faction (fact_code, fact_desc, event, fact_sql_tpl, fact_msg_tpl) VALUES
(3, 'tax', 'onsite', 'update m_gsession_user set user_cash=user_cash-%FPARAM_CALC1%\nwhere user_id=%USER_ID% and gsession_id=%GSESSION_ID%', 'Игроку %USER_NAME% пришлось заплатить налоги в размере %FPARAM_CALC1%');
INSERT INTO m_cfg_faction (fact_code, fact_desc, event, fact_sql_tpl, fact_msg_tpl) VALUES
(4, 'lottery', 'onsite', 'update m_gsession_user set user_cash=user_cash+%FPARAM_CALC1%\nwhere user_id=%USER_ID% and gsession_id=%GSESSION_ID%', 'Игрок %USER_NAME% выйграл в лоторее %FPARAM_CALC1%');
INSERT INTO m_cfg_faction (fact_code, fact_desc, event, fact_sql_tpl, fact_msg_tpl) VALUES
(5, 'jump', 'onsite', 'update m_gsession_user set position_field_id=%FPARAM%\nwhere user_id=%USER_ID% and gsession_id=%GSESSION_ID%', 'Игрок %USER_NAME%  перепутал билеты и вернулся на %FPARAM%');
INSERT INTO m_cfg_faction (fact_code, fact_desc, event, fact_sql_tpl, fact_msg_tpl) VALUES
(6, 'give penalty', 'onsite', 'update m_gsession_user set has_penalty=1, penalty_turn=%GTURN%+%ACTIVE_PLAYERS%\nwhere user_id=%USER_ID% and gsession_id=%GSESSION_ID%', 'Игрок %USER_NAME% попал задержан полицией и пропускает ход');
INSERT INTO m_cfg_faction (fact_code, fact_desc, event, fact_sql_tpl, fact_msg_tpl) VALUES
(7, 'casino', 'onsite', 'update m_gsession_user set user_cash=user_cash+%FPARAM_CALC1%\nwhere user_id=%USER_ID% and gsession_id=%GSESSION_ID%', 'Игрок %USER_NAME% сходил в казино  %PAY_TYPE% %FPARAM_CALC1%');
INSERT INTO m_cfg_faction (fact_code, fact_desc, event, fact_sql_tpl, fact_msg_tpl) VALUES
(8, 'stock exchange', 'onsite', 'update m_gsession_map_field set fparam=round(fparam*%FPARAM_CALC1%/100)+1\nwhere gsession_id=%GSESSION_ID% and owner_user_id=%USER_ID% ', 'Акции предприятий игрока %USER_NAME% %EXCH_TYPE%. Доход от предприятий составит %FPARAM_CALC1%% от текущей.');

-- --------------------------------------------------------

--
-- Структура таблиці m_cfg_ftype
--

CREATE TABLE  m_cfg_ftype (
  ftype_code number ,
  field_desc varchar2(2000)     DEFAULT NULL
)  ;

--
-- Дамп даних таблиці m_cfg_ftype
--

INSERT INTO m_cfg_ftype (ftype_code, field_desc) VALUES
(1, 'Start point');
INSERT INTO m_cfg_ftype (ftype_code, field_desc) VALUES
(2, 'General');
INSERT INTO m_cfg_ftype (ftype_code, field_desc) VALUES
(3, 'Event');

-- --------------------------------------------------------

--
-- Структура таблиці m_cfg_language
--

CREATE TABLE  m_cfg_language (
  lang_code varchar2(240)  ,
  lang_name varchar2(240)  
) ;

--
-- Дамп даних таблиці m_cfg_language
--

INSERT INTO m_cfg_language (lang_code, lang_name) VALUES
('eng', 'English');
INSERT INTO m_cfg_language (lang_code, lang_name) VALUES
('rus', 'Russian');
INSERT INTO m_cfg_language (lang_code, lang_name) VALUES
('ukr', 'Ukrainian');

-- --------------------------------------------------------

--
-- Структура таблиці m_cfg_map
--

CREATE TABLE  m_cfg_map (
  map_id number ,
  name varchar2(2000)     
) ;

--
-- Дамп даних таблиці m_cfg_map
--

INSERT INTO m_cfg_map (map_id, name) VALUES
(1, 'simple');

-- --------------------------------------------------------

--
-- Структура таблиці m_cfg_map_fgroup
--

CREATE TABLE  m_cfg_map_fgroup (
  fgroup_id number   ,
  map_id number ,
  fgroup_name varchar2(240)  ,
  fgparam varchar2(1000)  DEFAULT NULL
) ;

--
-- Дамп даних таблиці m_cfg_map_fgroup
--

INSERT INTO m_cfg_map_fgroup (fgroup_id, map_id, fgroup_name, fgparam) VALUES
(1, 1, 'G1', NULL);
INSERT INTO m_cfg_map_fgroup (fgroup_id, map_id, fgroup_name, fgparam) VALUES
(2, 1, 'G2', NULL);
INSERT INTO m_cfg_map_fgroup (fgroup_id, map_id, fgroup_name, fgparam) VALUES
(3, 1, 'G3', NULL);
INSERT INTO m_cfg_map_fgroup (fgroup_id, map_id, fgroup_name, fgparam) VALUES
(4, 1, 'G4', NULL);
INSERT INTO m_cfg_map_fgroup (fgroup_id, map_id, fgroup_name, fgparam) VALUES
(5, 1, 'G5', NULL);
INSERT INTO m_cfg_map_fgroup (fgroup_id, map_id, fgroup_name, fgparam) VALUES
(6, 1, 'G6', NULL);
INSERT INTO m_cfg_map_fgroup (fgroup_id, map_id, fgroup_name, fgparam) VALUES
(7, 1, 'G7', NULL);
INSERT INTO m_cfg_map_fgroup (fgroup_id, map_id, fgroup_name, fgparam) VALUES
(8, 1, 'G8', NULL);
INSERT INTO m_cfg_map_fgroup (fgroup_id, map_id, fgroup_name, fgparam) VALUES
(9, 1, 'G9', NULL);

-- --------------------------------------------------------

--
-- Структура таблиці m_cfg_map_field
--

CREATE TABLE  m_cfg_map_field (
  field_id number   ,
  map_id number ,
  fcode number ,
  name varchar2(2000)     ,
  ftype_code number ,
  fgroup_id number DEFAULT NULL,
  fact_code number DEFAULT NULL,
  fact_cond varchar2(1000)     DEFAULT NULL,
  fparam varchar2(240)     DEFAULT NULL,
  fparam_calc1 varchar2(1000)     DEFAULT NULL,
  fparam_calc2 varchar2(1000)     DEFAULT NULL
  ) ;

--
-- Дамп даних таблиці m_cfg_map_field
--

INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(1, 1, 1, 'C1', 1, NULL, 1, NULL, '30000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(2, 1, 2, 'C2', 2, 1, 2, NULL, '1000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(3, 1, 3, 'C3', 2, 1, 2, NULL, '1500', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(4, 1, 4, 'C4', 2, 1, 2, NULL, '1250', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(5, 1, 5, 'C5', 3, NULL, 4, 'ROUND( RAND( ) *1000 /1000 ) *1000 * ROUND( RAND( ) *100 )', NULL, 'ROUND( RAND( ) *100 /100 ) *round(ABS(%MAX_USER_CASH%-%USER_CASH%)/300) * ROUND( RAND( ) *100 )+100', NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(6, 1, 6, 'C6', 2, 9, 2, NULL, '4000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(7, 1, 7, 'C7', 2, 2, 2, NULL, '2000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(8, 1, 8, 'C8', 2, 2, 2, NULL, '2300', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(9, 1, 9, 'C9', 2, 2, 2, NULL, '2500', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(10, 1, 10, 'C10', 3, NULL, 6, 'ROUND(RAND())*ROUND(RAND())', NULL, NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(11, 1, 11, 'C11', 2, 3, 2, NULL, '2000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(12, 1, 12, 'C12', 2, 3, 2, NULL, '3000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(13, 1, 13, 'C13', 3, NULL, 3, NULL, NULL, 'ROUND( RAND( ) * (ABS(%USER_PROPERTY%)*30/100) )+100', NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(14, 1, 14, 'C14', 2, 9, 2, NULL, '8000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(15, 1, 15, 'C15', 2, 4, 2, NULL, '4000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(16, 1, 16, 'C16', 2, 4, 2, NULL, '3500', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(17, 1, 17, 'C17', 3, NULL, 8, 'ROUND(RAND())*ROUND(RAND())', NULL, 'ROUND(RAND()*180)+50', NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(18, 1, 18, 'C18', 2, 5, 2, NULL, '5000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(19, 1, 19, 'C19', 2, 5, 2, NULL, '6500', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(20, 1, 20, 'C20', 2, 5, 2, NULL, '10000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(21, 1, 21, 'C21', 3, NULL, 4, NULL, NULL, 'ROUND( RAND( ) *100 /100 ) *round(ABS(%USER_CASH%)/300) * ROUND( RAND( ) *100 )+100', NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(22, 1, 22, 'C22', 2, 9, 2, NULL, '8000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(23, 1, 23, 'C23', 2, 6, 2, NULL, '5500', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(24, 1, 24, 'C24', 2, 6, 2, NULL, '6000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(25, 1, 25, 'C25', 2, 6, 2, NULL, '3000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(26, 1, 26, 'C26', 3, NULL, 5, 'ROUND(RAND())*ROUND(RAND())', '10', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(27, 1, 27, 'C27', 2, 7, 2, NULL, '15000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(28, 1, 28, 'C28', 2, 7, 2, NULL, '20000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(29, 1, 29, 'C29', 2, 9, 2, NULL, '20000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(30, 1, 30, 'C30', 3, NULL, 7, NULL, NULL, '(ROUND( RAND( ) *100 /100 ) *round(ABS(%USER_CASH%)/200) * ROUND( RAND( ) *100 )+100)*(ROUND(RAND())*(ROUND(RAND()))-1)', NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(31, 1, 31, 'C31', 2, 8, 2, NULL, '10000', NULL, NULL);
INSERT INTO m_cfg_map_field (field_id, map_id, fcode, name, ftype_code, fgroup_id, fact_code, fact_cond, fparam, fparam_calc1, fparam_calc2) VALUES
(32, 1, 32, 'C32', 2, 8, 2, NULL, '25000', NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблиці m_cfg_message
--

CREATE TABLE  m_cfg_message (
  msg_code varchar2(240)  ,
  msg_desc clob  ,
  msg_class VARCHAR2(240)  
) ;

--
-- Дамп даних таблиці m_cfg_message
--

INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_AU_ATTACHED_OPENED', '', 'au');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_AU_ATTACHED_WON', '', 'au');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_AU_CLOSED', '', 'au');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_AU_MAKEBID', '', 'au');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_AU_PUBLIC_OPENED', '', 'au');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_AU_PUBLIC_WON', '', 'au');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_AU_USER_JOIN', '', 'au');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_AU_USER_LEAVE', '', 'au');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_DICE', '', 'gm');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_DL_ACCEPTED', '', 'dl');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_DL_CANCELED', '', 'dl');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_DL_OPENED', '', 'dl');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_DL_REJECTED', '', 'dl');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_DL_TERMINATED', '', 'dl');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_GS_CREATED', '', 'gm');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_GS_FINISHED', '', 'gm');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_GS_STARTED', '', 'gm');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_GS_TERMINATED', '', 'gm');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_GS_USER_JOIN', '', 'gm');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_GS_USER_LOSE', '', 'gm');
INSERT INTO m_cfg_message (msg_code, msg_desc, msg_class) VALUES
('MSG_INFO_GS_USER_WIN', '', 'gm');

-- --------------------------------------------------------

--
-- Структура таблиці m_cfg_message_lang
--

CREATE TABLE  m_cfg_message_lang (
  msgl_id number   ,
  msg_code varchar2(240)  ,
  lang_code varchar2(240)  ,
  msg clob  ,
  is_tpl varchar2(1)
) ;

--
-- Дамп даних таблиці m_cfg_message_lang
--

INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(1, 'MSG_INFO_AU_ATTACHED_OPENED', 'eng', 'Nonpublic auction AU%AUCT_ID% started', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(2, 'MSG_INFO_AU_ATTACHED_OPENED', 'rus', 'Непубличный аукцион AU%AUCT_ID% начат', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(3, 'MSG_INFO_AU_ATTACHED_WON', 'eng', 'Player %BIDDER_USER_NAME% won the auction AU%AUCT_ID%! He paid %BID% and became owner of %FIELD_NAME%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(4, 'MSG_INFO_AU_ATTACHED_WON', 'rus', 'Игрок %BIDDER_USER_NAME% выиграл аукцион AU%AUCT_ID%! Он оплатил %BID% и стал владельцем %FIELD_NAME%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(5, 'MSG_INFO_AU_CLOSED', 'eng', 'Auction AU%AUCT_ID% closed', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(6, 'MSG_INFO_AU_CLOSED', 'rus', 'Аукцион AU%AUCT_ID% закрыт', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(7, 'MSG_INFO_AU_MAKEBID', 'eng', 'Player %USER_NAME% make a bid %BID% on auction AU%AUCT_ID%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(8, 'MSG_INFO_AU_MAKEBID', 'rus', 'Игрок %USER_NAME% сделал ставку %BID% на аукционе AU%AUCT_ID%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(9, 'MSG_INFO_AU_PUBLIC_OPENED', 'eng', 'Player %HOLDER_USER_NAME% opened auction AU%AUCT_ID%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(10, 'MSG_INFO_AU_PUBLIC_OPENED', 'rus', 'Игрок %HOLDER_USER_NAME% открыл аукцион AU%AUCT_ID%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(11, 'MSG_INFO_AU_PUBLIC_WON', 'eng', 'Player %BIDDER_USER_NAME% won the auction AU%AUCT_ID%! He paid %BID% to player %HOLDER_USER_NAME% and became owner of %FIELD_NAME%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(12, 'MSG_INFO_AU_PUBLIC_WON', 'rus', 'Игрок %BIDDER_USER_NAME% выиграл аукцион AU%AUCT_ID%! Он заплатил %BID% игроку %HOLDER_USER_NAME% и стал владельцем %FIELD_NAME%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(13, 'MSG_INFO_AU_USER_JOIN', 'eng', 'Player %USER_NAME% joined to auction AU%AUCT_ID%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(14, 'MSG_INFO_AU_USER_JOIN', 'rus', 'Игрок %USER_NAME% принял участие в аукционе AU%AUCT_ID%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(15, 'MSG_INFO_AU_USER_LEAVE', 'eng', 'Player %USER_NAME% leave auction AU%AUCT_ID%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(16, 'MSG_INFO_AU_USER_LEAVE', 'rus', 'Игрок %USER_NAME% покинул аукцион AU%AUCT_ID%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(17, 'MSG_INFO_DICE', 'eng', 'Player %USER_NAME% rolled the dice %LAST_DICE1%:%LAST_DICE2%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(18, 'MSG_INFO_DICE', 'rus', 'Игрок %USER_NAME% бросил кости %LAST_DICE1%:%LAST_DICE2%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(19, 'MSG_INFO_DL_ACCEPTED', 'rus', 'Игрок %DEAL_OPPONENT_USER_NAME% принял условия сделки DL%DEAL_ID% игрока %DEAL_HOLDER_USER_NAME%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(20, 'MSG_INFO_DL_CANCELED', 'rus', 'Игрок %DEAL_HOLDER_USER_NAME% отменил сделку DL%DEAL_ID%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(21, 'MSG_INFO_DL_OPENED', 'rus', 'Игрок %DEAL_HOLDER_USER_NAME% предложил сделку DL%DEAL_ID% игроку %DEAL_OPPONENT_USER_NAME%', 1);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(22, 'MSG_INFO_DL_REJECTED', 'rus', 'Игрок %DEAL_OPPONENT_USER_NAME% отказал в совершении сделки DL%DEAL_ID% от игрока %DEAL_HOLDER_USER_NAME%', 0);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(23, 'MSG_INFO_DL_TERMINATED', 'rus', 'Сделка DL%DEAL_ID% автоматически отменена по истечению срока действия.', 0);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(24, 'MSG_INFO_GS_CREATED', 'rus', 'Игрок %USER_NAME% создал игровую сессию %CREATESTAMP%. Игра автоматически начнется при соединении %G_GS_MAX_PLAYERS% игроков или по истечению %G_GS_START_TIMEOUT% минут при наличии %G_GS_MIN_PLAYERS% игроков.', 0);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(26, 'MSG_INFO_GS_TERMINATED', 'rus', 'Игра прервана', 0);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(27, 'MSG_INFO_GS_STARTED', 'rus', 'Игра началась STARTSTAMP. Игрок %HOLDER_USER_NAME% первым бросает кости.', 0);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(28, 'MSG_INFO_GS_USER_JOIN', 'rus', 'Игрок %USER_NAME% присоединился к игре', 0);
INSERT INTO m_cfg_message_lang (msgl_id, msg_code, lang_code, msg, is_tpl) VALUES
(29, 'MSG_INFO_GS_USER_LOSE', 'rus', 'Игрок %TARGET_USER_NAME% проиграл. Все имущество возвращается в фонд государства.', 0);

-- --------------------------------------------------------

--
-- Структура таблиці m_cfg_saction
--

CREATE TABLE  m_cfg_saction (
  sact_code number ,
  sact_desc varchar2(2000)     ,
  chance number ,
  sact_sql_tpl clob     
) ;

--
-- Дамп даних таблиці m_cfg_saction
--

INSERT INTO m_cfg_saction (sact_code, sact_desc, chance, sact_sql_tpl) VALUES
(1, 'Uprise prices', 10, '');
INSERT INTO m_cfg_saction (sact_code, sact_desc, chance, sact_sql_tpl) VALUES
(2, 'reduction of prices', 10, '');
INSERT INTO m_cfg_saction (sact_code, sact_desc, chance, sact_sql_tpl) VALUES
(3, 'Total tax', 5, '');

-- --------------------------------------------------------

--
-- Структура таблиці m_user
--

CREATE TABLE  m_user (
  user_id number    ,
  login varchar2(240)      ,
  name varchar2(1000)     ,
  passwd varchar2(240)     ,
  service varchar2(240)     ,
  identity varchar2(2000)     
) ;

--
-- Дамп даних таблиці m_user
--

INSERT INTO m_user (user_id, login, name, passwd, service, identity) VALUES
(1, 'admin', 'admin', 'admin', '', '1');
INSERT INTO m_user (user_id, login, name, passwd, service, identity) VALUES
(2, 'msa', 'msa', 'msa', '', '2');
INSERT INTO m_user (user_id, login, name, passwd, service, identity) VALUES
(3, 'gvozdi4ka', 'gvozdi4ka', 'gvozdi4ka', '', '3');

--
-- Constraints for dumped tables
--

--
-- Constraints for table m_cfg_auaction
--
ALTER TABLE m_cfg_auaction
  ADD CONSTRAINT m_cfg_auaction_ibfk_1 FOREIGN KEY (auact_msg_tpl_code) REFERENCES m_cfg_message (msg_code);

--
-- Constraints for table m_cfg_map_fgroup
--
ALTER TABLE m_cfg_map_fgroup
  ADD CONSTRAINT m_cfg_map_fgroup_ibfk_1 FOREIGN KEY (map_id) REFERENCES m_cfg_map (map_id);

--
-- Constraints for table m_cfg_map_field
--
ALTER TABLE m_cfg_map_field
  ADD CONSTRAINT m_cfg_map_field_ibfk_1 FOREIGN KEY (map_id) REFERENCES m_cfg_map (map_id),
  ADD CONSTRAINT m_cfg_map_field_ibfk_2 FOREIGN KEY (ftype_code) REFERENCES m_cfg_ftype (ftype_code),
  ADD CONSTRAINT m_cfg_map_field_ibfk_3 FOREIGN KEY (fact_code) REFERENCES m_cfg_faction (fact_code),
  ADD CONSTRAINT m_cfg_map_field_ibfk_4 FOREIGN KEY (fgroup_id) REFERENCES m_cfg_map_fgroup (fgroup_id);

--
-- Constraints for table m_cfg_message_lang
--
ALTER TABLE m_cfg_message_lang
  ADD CONSTRAINT m_cfg_message_lang_ibfk_1 FOREIGN KEY (msg_code) REFERENCES m_cfg_message (msg_code),
  ADD CONSTRAINT m_cfg_message_lang_ibfk_2 FOREIGN KEY (lang_code) REFERENCES m_cfg_language (lang_code);


