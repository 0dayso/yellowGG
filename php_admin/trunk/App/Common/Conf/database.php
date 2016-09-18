<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/5
 * Time: 18:56
 */

//$database_config   = require_once(APP_PATH."Common/Conf/local_database.php");
//$database_config   = require_once(APP_PATH."Common/Conf/centos_database.php");
$database_config   = require_once(APP_PATH."Common/Conf/dev_database.php");
//$database_config   = require_once(APP_PATH."Common/Conf/server_database.php");
//$database_config   = require_once(APP_PATH."Common/Conf/online_database.php");

return $database_config;