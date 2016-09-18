<?php

$config_cms    = require_once APP_PATH.'Cms/Conf/config.php';
$config_cloud  = include_once APP_PATH.'Cms/Conf/cloud.php';
$config_action = include_once APP_PATH.'Cms/Conf/action.php';
$config_system = include_once APP_PATH.'Cms/Conf/system.php';
$config_state  = include_once APP_PATH.'Cms/Conf/state.php';
$config_dbs    = require_once APP_PATH.'Liehuo/Conf/database.php';

// 当前模块配置
$config_app = array(

  // 模块化
  'DEFAULT_MODULE' => 'Liehuo',

  // 模板主题
  'DEFAULT_THEME' => 'Hplus',

  // 模板替换
  'TMPL_PARSE_STRING' => array(
     '__PUBLIC__' => '/Public',
  ),

);

$config = array_merge(
  $config_cms    ?: array(),
  $config_cloud  ?: array(),
  $config_action ?: array(),
  $config_system ?: array(),
  $config_state  ?: array(),
  $config_app    ?: array(),
  $config_dbs    ?: array()
);
//die(json_encode($config));

return $config;