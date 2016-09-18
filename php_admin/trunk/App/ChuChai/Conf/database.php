<?php

$config_dbs = require_once APP_PATH.'ChuChai/Conf/dev_database.php';
//$config_dbs = require_once APP_PATH.'ChuChai/Conf/online_database.php';

return $config_dbs ?: [];