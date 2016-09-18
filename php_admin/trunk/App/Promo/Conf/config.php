<?php

$config = require_once APP_PATH.'Liehuo/Conf/config.php';

// 当前模块配置
$config_app =
[

  // 模块化
  'DEFAULT_MODULE' => 'Promo',

  // 模板主题
  'DEFAULT_THEME'  => 'Default',
  'TMPL_FILE_DEPR' => '-',
  'LAYOUT_ON'      => false,

  'SHOW_PAGE_TRACE' => false,

];

$config = array_merge(
  $config        ?: [],
  $config_app    ?: []
);
//die(json_encode($config));

return $config;