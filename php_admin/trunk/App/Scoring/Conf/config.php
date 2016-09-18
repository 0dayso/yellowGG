<?php

$config = require_once APP_PATH.'Yanzhi/Conf/config.php';

// 当前模块配置
$config_app = array(

  // 模块化
  'DEFAULT_MODULE' => 'Scoring',

  // 用户ID Session Key
  'USER_AUTH_KEY'  => 'auth-id-scoring',

);

$config = array_merge(
  $config        ?: array(),
  $config_app    ?: array()
);
//die(json_encode($config));

return $config;