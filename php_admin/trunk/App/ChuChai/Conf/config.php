<?php

$config     = require_once APP_PATH.'Liehuo/Conf/config.php';
$config_dbs = require_once APP_PATH.'ChuChai/Conf/database.php';

// 当前模块配置
$config_app =
[

  // 模块化
  'DEFAULT_MODULE' => 'ChuChai',

  // 模板主题
  'DEFAULT_THEME'  => 'Default',
  'TMPL_FILE_DEPR' => '-',
  'LAYOUT_ON'      => false,
  'TMPL_PARSE_STRING' =>
  [
    '__PUBLIC__' => 'http://adm.chujianapp.com/Public',
  ],

  'SHOW_PAGE_TRACE' => true,

];

$config = array_merge(
  $config        ?: []
  ,$config_app   ?: []
  ,$config_dbs   ?: []
);
//die(json_encode($config));

return $config;