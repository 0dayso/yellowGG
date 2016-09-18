<?php

$config_dbs = require_once APP_PATH.'Liehuo/Conf/dev_database.php';
//$config_dbs = require_once APP_PATH.'Liehuo/Conf/online_database.php';

return $config_dbs ?: array();