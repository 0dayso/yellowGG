<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/5
 * Time: 14:01
 */

return array(
    'DB_TYPE'                   =>  'mysql',
    'DB_HOST'                   =>  'rdsmemzrymemzry.mysql.rds.aliyuncs.com',
    'DB_NAME'                   =>  'cj_admin',
    'DB_USER'                   =>  'cj_admin_backend',
    'DB_PWD'                    =>  '7GtEqU8L4VM0',
    'DB_PORT'                   =>  '3306',
    'DB_PREFIX'                 =>  'cj_',
    'DB_CHARSET'                => 'utf8mb4',


    'RESET_PERMISSION'          => true,
    'RESET_MODEL'               => 'server',//或者server
    'REDIS_START'               => true,//开启redis

    'admin_db_config' =>  array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'rdsmemzrymemzry.mysql.rds.aliyuncs.com',
        'DB_NAME'                   =>  'cj_admin',
        'DB_USER'                   =>  'cj_admin_backend',
        'DB_PWD'                    =>  '7GtEqU8L4VM0',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'cj_',
    ),
    'admin_log_db_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'rdsmemzrymemzry.mysql.rds.aliyuncs.com',
        'DB_NAME'                   =>  'cj_admin_log',
        'DB_USER'                   =>  'cj_admin_backend',
        'DB_PWD'                    =>  '7GtEqU8L4VM0',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'cj_',
        'DB_CHARSET'                => 'utf8mb4',
    ),
    'data_db_config_closed' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'rdsmemzrymemzry.mysql.rds.aliyuncs.com',
        'DB_NAME'                   =>  'chujian',
        'DB_USER'                   =>  'cj_admin_backend',
        'DB_PWD'                    =>  '7GtEqU8L4VM0',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'cj_',
        'DB_CHARSET'                => 'utf8mb4',
    ),

    // 颜值版数据库
    'datadw_db_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'rdsmemzrymemzry.mysql.rds.aliyuncs.com',
        'DB_NAME'                   =>  'chujiandw',
        'DB_USER'                   =>  'cj_admin_backend',
        'DB_PWD'                    =>  '7GtEqU8L4VM0',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'cj_',
    ),

    // IM聊天记录
    'db_config_score_im' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'rdsbbumummbfz6n.mysql.rds.aliyuncs.com',
        'DB_NAME'                   =>  'cjlog2',
        'DB_USER'                   =>  'cj_admin',
        'DB_PWD'                    =>  'GWXCHwyafYKSu3bI',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  '',
    ),

    // IM系统消息
    'db_config_score_imsys' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'rdsbbumummbfz6n.mysql.rds.aliyuncs.com',
        'DB_NAME'                   =>  'cj_system_msg2',
        'DB_USER'                   =>  'cj_admin',
        'DB_PWD'                    =>  'GWXCHwyafYKSu3bI',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  '',
    ),

    'im_server_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'rdsbbumummbfz6n.mysql.rds.aliyuncs.com',
        'DB_NAME'                   =>  'cjlog',
        'DB_USER'                   =>  'cj_admin',
        'DB_PWD'                    =>  'GWXCHwyafYKSu3bI',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  '',
    ),
    'im_system_msg' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'rdsbbumummbfz6n.mysql.rds.aliyuncs.com',
        'DB_NAME'                   =>  'cj_system_msg',
        'DB_USER'                   =>  'cj_admin',
        'DB_PWD'                    =>  'GWXCHwyafYKSu3bI',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  '',
    ),

    /*'im_server_redis_config' => array(
            'host' => '10.161.174.0',
            'port' => '6381',
            'timeout' => '5.1',
            'persistent'    => false,

        ),*/
    // im 升级版群发消息
    'system_message' => array( 
        'host' => '10.161.185.205',
        'port' => '6390',
        'timeout' => '0.1',
    ),

    'system_message_score' => array(
        'host' => '10.162.43.38',
        'port' => '6390',
        'timeout' => '0.1',
    ),

    'im_server_redis_send_config' => array(
        'host'          => '10.161.185.205',
        'port'          => 6390,
        'timeout'       => 5.0,//响应超时
        'persistent'    => false,
    ),
    'im_server_redis_config' => array(
        'host'          => '10.161.185.205',
        'port'          => 6380,
        'timeout'       => 5.0,//响应超时
        'persistent'    => false,
    ),
    'im_server_qq_redis_config' => array(
        'host'          => '10.161.185.205',
        'port'          => 6380,
        'timeout'       => 5.0,//响应超时
        'persistent'    => false,
    ),
    'php_server_redis_config' => array(
        'host'          => '10.161.174.0',
        'port'          => 6383,
        'timeout'       => 0.1,//响应超时
        'persistent'    => false,
    ),
    'user_token' => array(
        'host'          => '10.161.174.0',
        'port'          => 6379,
        'timeout'       => 0.1,//响应超时
        'persistent'    => false,
    ),

    'php_server_user_info' => array(
        'host'          => '10.161.174.0',
        'port'          => 6383,
        'timeout'       => 1.0,//响应超时
        'persistent'    => false,
    ),
    'php_server_user_info_v2' => array(
        'host'          => '10.161.174.0',
        'port'          => 6384,
        'timeout'       => 1.0,//响应超时
        'persistent'    => false,
    ),

    // 打分班次
    'score_info' => array(
        'host'          => '10.161.174.0',
        'port'          => '6390',
        'timeout'       => '0.1',
    ),
    //
    'user_info_v6' => array(
        'host'          => '10.161.174.0',
        'port'          => '6390',
        'timeout'       => '0.1',
    ),

    'CERTIFICATE_VIDEO_REQUEST_LOG_MODEL'=> 'cj_certificate_video_request_log_20150130',
);
