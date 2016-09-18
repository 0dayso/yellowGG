<?php

$config_dbs = require_once APP_PATH.'Yanzhi/Conf/dev_database.php';
//$config_dbs = require_once APP_PATH.'Yanzhi/Conf/online_database.php';

return $config_dbs ?: array();