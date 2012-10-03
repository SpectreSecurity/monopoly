<?php
define("G_CRLOG_DIR", dirname(__FILE__).'/logs/');//"logs/");
define("G_CRLOG_FILE_TPL", "_%date%.log");


define("G_MODE_VIEW", "0");
define("G_MODE_PLAY", "1");
define("G_LOG_LVL_DEBUG", "0");

define("G_GS_GSTATUS_ACTIVE", "1");
define("G_GS_GSTATUS_INACTIVE", "2");
define("G_GS_GSTATE_CREATED", "0");
define("G_GS_GSTATE_STARTED", "1");
define("G_GS_GSTATE_FINISHED", "2");
define("G_GS_GSTATE_TERMINATED", "3");
define("G_GS_FTYPE_START", "1");
define("G_GS_FTYPE_GENERAL", "2");
define("G_GS_FTYPE_EVENT", "3");
define("G_GS_MSGTYPE_INFO", "1");
define("G_GS_MSGTYPE_TURNINFO", "2");
define("G_GS_MSGTYPE_ACTMSG", "3");
define("G_GS_MSGTYPE_AUMSG", "4");
define("G_GS_MSGTYPE_DLMSG", "5");

define("G_AU_AUCT_TYPE_ATTACHED", "1");
define("G_AU_AUCT_TYPE_PUBLIC", "2");
define("G_AU_AUCT_STATUS_ACTIVE", "1");
define("G_AU_AUCT_STATUS_INACTIVE", "2");
define("G_AU_AUCT_STATE_OPENED", "opened");
define("G_AU_AUCT_STATE_CLOSED", "closed");
define("G_AU_AUCT_STATE_TERMINATED", "terminated");
define("G_AU_AUCT_USER_STATE_ON", "on");
define("G_AU_AUCT_USER_STATE_OFF", "off");

define("G_GS_FGROUP_FGPARAM_DELTA", "1");
define("G_DL_DEAL_STATUS_ACTIVE", "1");
define("G_DL_DEAL_STATUS_INACTIVE", "2");
define("G_DL_DEAL_STATE_OPENED", "opened");
define("G_DL_DEAL_STATE_ACCEPTED", "accepted");
define("G_DL_DEAL_STATE_REJECTED", "rejected");
define("G_DL_DEAL_STATE_TERMINATED", "terminated");
define("G_DL_DEAL_STATE_CANCELED", "canceled");
define("G_DL_DDIRECTION_GIVE", "give");
define("G_DL_DDIRECTION_RECEIVE", "receive");
//Gsession game init consts
define("G_MSG_INFO_DICE", "Игрок %USER_NAME% бросил кости %LAST_DICE1%:%LAST_DICE2%");
define("G_MSG_INFO_ONSITE", "Игрок %USER_NAME% попал на поле %FIELD_NAME%");
define("G_TXT_INCOME", "прибыль");
define("G_TXT_LOSS", "убыток");
define("G_TXT_STOCK_UP", "понялись");
define("G_TXT_STOCK_DOWN", "опустились");
?>