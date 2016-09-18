<?php

return array(

  'conn_base' => array(
    'DB_TYPE'   => 'mysql',
    'DB_HOST'   => 'rdsmemzrymemzry.mysql.rds.aliyuncs.com',
    'DB_NAME'   => 'chujiandw',
    'DB_USER'   => 'cj_admin_backend',
    'DB_PWD'    => '7GtEqU8L4VM0',
    'DB_PORT'   => '3306',
    'DB_PREFIX' => 'cj_',
  ),

  'conn_rdrs' => array(
    'DB_TYPE'   => 'mysql',
    'DB_HOST'   => 'rdsmemzrymemzry.mysql.rds.aliyuncs.com',
    'DB_NAME'   => 'cj_rdrs',
    'DB_USER'   => 'cj_admin_backend',
    'DB_PWD'    => '7GtEqU8L4VM0',
    'DB_PORT'   => 3306,
    'DB_PREFIX' => '',
  ),

  'conn_stat' => array(
    'DB_TYPE'   => 'mysql',
    'DB_HOST'   => 'rds4l952ux9a0e1xkt16.mysql.rds.aliyuncs.com',
    'DB_NAME'   => 'lh_stat',
    'DB_USER'   => 'lh_count123',
    'DB_PWD'    => 'lhapp123',
    'DB_PORT'   => 3306,
    'DB_PREFIX' => '',
  ),

  'conn_im_log' => array(
    'DB_TYPE'   => 'mysql',
    'DB_HOST'   => 'rdsbbumummbfz6n.mysql.rds.aliyuncs.com',
    'DB_NAME'   => 'cjlog',
    'DB_USER'   => 'cj_admin',
    'DB_PWD'    => 'GWXCHwyafYKSu3bI',
    'DB_PORT'   => 3306,
    'DB_PREFIX' => '',
  ),

  'conn_im_sysmsg' => array(
    'DB_TYPE'   => 'mysql',
    'DB_HOST'   => 'rdsbbumummbfz6n.mysql.rds.aliyuncs.com',
    'DB_NAME'   => 'cj_system_msg',
    'DB_USER'   => 'cj_admin',
    'DB_PWD'    => 'GWXCHwyafYKSu3bI',
    'DB_PORT'   => 3306,
    'DB_PREFIX' => '',
  ),

  'redis_default' => array(
    'host'     => 'redisauth.chujianapp.com',
    'port'     => 6379,
    'timeout'  => 0,
    'password' => '954237fe516043bd:Lhapp123',
  ),

  'redis_user' => array(
    'host'     => 'redisuser.chujianapp.com',
    'port'     => 6379,
    'timeout'  => 0,
    'password' => 'c30690277da3464f:Lhapp123',
  ),

  'redis_recommend' => array(
    'host'     => 'd8f56fe41ccc49cf.m.cnhza.kvstore.aliyuncs.com',
    'port'     => 6379,
    'timeout'  => 0,
    'password' => 'd8f56fe41ccc49cf:Lhapp123',
  ),

  'redis_recommend_20160120' => array(
    'host'    => '10.161.174.0',
    'port'    => 6382,
    'timeout' => 0,
  ),

  'redis_im' => array(
    'host'    => '10.161.185.205',
    'port'    => 6380,
    'timeout' => 0,
  ),

  'redis_im_sysmsg' => array(
    'host'    => '10.161.185.205',
    'port'    => 6390,
    'timeout' => 0,
  ),


  // Api根路径
  'api_root_app'       => 'https://api.chujianapp.com/v1.8/index.php/',
  'api_root_recommend' => 'http://218.244.157.252:9090/',
  'api_root_statistic' => 'http://218.244.157.252:9093/',

);