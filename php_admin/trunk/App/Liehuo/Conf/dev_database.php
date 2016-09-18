<?php

return array(

  'conn_base' => array(
    'DB_TYPE'   =>  'mysql',
    'DB_HOST'   =>  '192.168.83.101',
    'DB_NAME'   =>  'chujiandw_test',
    'DB_USER'   =>  'root',
    'DB_PWD'    =>  'YPCvBYZJkjPTEm3q',
    'DB_PORT'   =>  '3306',
    'DB_PREFIX' =>  'cj_',
    'DB_DEPLOY_TYPE' => 1,    //分布式
    'DB_RW_SEPARATE' => true, //读写分离
  ),

  'conn_wallet' => array(
    'DB_TYPE'   => 'mysql',
    'DB_HOST'   => '192.168.83.101',
    'DB_NAME'   => 'lh_user_wallet',
    'DB_USER'   => 'root',
    'DB_PWD'    => 'YPCvBYZJkjPTEm3q',
    'DB_PORT'   => 3306,
    'DB_PREFIX' => '',
  ),

  'conn_action' => array(
    'DB_TYPE'   => 'mysql',
    'DB_HOST'   => '192.168.83.101',
    'DB_NAME'   => 'lh_user_action',
    'DB_USER'   => 'root',
    'DB_PWD'    => 'YPCvBYZJkjPTEm3q',
    'DB_PORT'   => 3306,
    'DB_PREFIX' => '',
  ),

  'conn_admin' => array(
    'DB_TYPE'   => 'mysql',
    'DB_HOST'   => '192.168.83.101',
    'DB_NAME'   => 'cj_admin',
    'DB_USER'   => 'root',
    'DB_PWD'    => 'YPCvBYZJkjPTEm3q',
    'DB_PORT'   => 3306,
    'DB_PREFIX' => '',
  ),

  'conn_admin_log' => array(
    'DB_TYPE'   => 'mysql',
    'DB_HOST'   => '192.168.83.101',
    'DB_NAME'   => 'cj_admin_log',
    'DB_USER'   => 'root',
    'DB_PWD'    => 'YPCvBYZJkjPTEm3q',
    'DB_PORT'   => 3306,
    'DB_PREFIX' => '',
  ),

  'conn_rdrs' => array(
    'DB_TYPE'   => 'mysql',
    'DB_HOST'   => '192.168.83.101',
    'DB_NAME'   => 'cj_rdrs',
    'DB_USER'   => 'root',
    'DB_PWD'    => 'YPCvBYZJkjPTEm3q',
    'DB_PORT'   => 3306,
    'DB_PREFIX' => '',
  ),

  'conn_stat' => array(
    'DB_TYPE'   => 'mysql',
    'DB_HOST'   => '192.168.83.101',
    'DB_NAME'   => 'lh_stat',
    'DB_USER'   => 'root',
    'DB_PWD'    => 'YPCvBYZJkjPTEm3q',
    'DB_PORT'   => 3306,
    'DB_PREFIX' => '',
  ),

  'conn_im_log' => array(
    'DB_TYPE'   => 'mysql',
    'DB_HOST'   => '192.168.83.100',
    'DB_NAME'   => 'cjlog',
    'DB_USER'   => 'cj',
    'DB_PWD'    => 'cj',
    'DB_PORT'   => 3306,
    'DB_PREFIX' => '',
  ),

  'conn_im_sysmsg' => array(
    'DB_TYPE'   => 'mysql',
    'DB_HOST'   => '192.168.83.100',
    'DB_NAME'   => 'cj_system_msg',
    'DB_USER'   => 'cj',
    'DB_PWD'    => 'cj',
    'DB_PORT'   => 3306,
    'DB_PREFIX' => '',
  ),

  'redis_default' => array(
    'host'    => '192.168.83.101',
    'port'    => 6379,
    'timeout' => 0,
  ),

  'redis_user' => array(
    'host'    => '192.168.83.101',
    'port'    => 6379,
    'timeout' => 0,
  ),

  'redis_live' => array(
    'host'    => '192.168.83.101',
    'port'    => 6379,
    'timeout' => 0,
  ),

  'redis_recommend' => array(
    'host'    => '192.168.83.101',
    'port'    => 6381,
    'timeout' => 0,
  ),

  'redis_im' => array(
    'host'    => '192.168.83.100',
    'port'    => 6379,
    'timeout' => 0,
  ),

  'redis_im_sysmsg' => array(
    'host'    => '192.168.83.100',
    'port'    => 6379,
    'timeout' => 0,
  ),

  'redis_im_timing' => array(
    'host'    => '192.168.83.100',
    'port'    => 6379,
    'timeout' => 0,
  ),


  // Api根路径
  'api_root_app'       => 'http://192.168.83.101/index.php/',
  'api_root_recommend' => 'http://192.168.83.101:9090/',
  'api_root_statistic' => 'http://192.168.83.101:9093/',

);