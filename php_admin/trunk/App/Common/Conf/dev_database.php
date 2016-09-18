<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/5
 * Time: 13:57
 */

return array(
    'DB_TYPE'                   =>  'mysql',
    'DB_HOST'                   =>  '192.168.83.101',
    'DB_NAME'                   =>  'cj_admin',
    'DB_USER'                   =>  'root',
    'DB_PWD'                    =>  'YPCvBYZJkjPTEm3q',
    'DB_PORT'                   =>  '3306',
    'DB_PREFIX'                 =>  'cj_',

    'RESET_PERMISSION'          => true,
    'RESET_MODEL'               => 'local',//或者server
    'REDIS_START'               => true,//开启redis

    'admin_log_db_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  '192.168.83.101',
        'DB_NAME'                   =>  'cj_admin_log',
        'DB_USER'                   =>  'root',
        'DB_PWD'                    =>  'YPCvBYZJkjPTEm3q',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'cj_',
    ),
    'data_db_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  '192.168.83.101',
        'DB_NAME'                   =>  'chujian',
        'DB_USER'                   =>  'root',
        'DB_PWD'                    =>  'YPCvBYZJkjPTEm3q',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'cj_',
    ),

    'datadw_db_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  '192.168.83.101',
        'DB_NAME'                   =>  'chujiandw',
        'DB_USER'                   =>  'root',
        'DB_PWD'                    =>  'YPCvBYZJkjPTEm3q',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'cj_',
    ),

    'db_config_score_im' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  '192.168.83.100',
        'DB_NAME'                   =>  'cjlog',
        'DB_USER'                   =>  'cj',
        'DB_PWD'                    =>  'cj',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  '',
    ),

    'cjad_db_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  '192.168.83.101',
        'DB_NAME'                   =>  'cj_ad',
        'DB_USER'                   =>  'root',
        'DB_PWD'                    =>  'YPCvBYZJkjPTEm3q',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'mgad_',
    ),

    'im_server_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  '192.168.83.100',
        'DB_NAME'                   =>  'cjlog',
        'DB_USER'                   =>  'cj',
        'DB_PWD'                    =>  'cj',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  '',
    ),
    'im_system_msg' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  '192.168.83.100',
        'DB_NAME'                   =>  'cj_system_msg',
        'DB_USER'                   =>  'cj',
        'DB_PWD'                    =>  'cj',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  '',
    ),


    'system_message_score' => array(
        'host' => '192.168.83.100',
        'port' => 6379,
        'timeout' => 0.1,
    ),

    'im_server_redis_config' => array(
        'host'          => '192.168.83.100',
        'port'          => 6379,
        'timeout'       => 1.0,//响应超时
        'persistent'    => false,
        //'expire'        => 3600,//cache保存时间
        //'218.244.157.92',外网，本机测试使用
        //'10.161.185.205',内网，线上上传使用
        //'127.0.0.1',6379 本地
    ),
    'php_server_redis_config' => array(
        'host'          => '192.168.83.101',
        'port'          => 6379,
        'timeout'       => 1.0,//响应超时
        'persistent'    => false,
    ),

    'php_server_user_info' => array(
        'host'          => '192.168.83.101',
        'port'          => 6380,
        'timeout'       => 1.0,//响应超时
        'persistent'    => false,
    ),
    'php_server_user_info_v2' => array(
        'host'          => '192.168.83.101',
        'port'          => 6384,
        'timeout'       => 1.0,//响应超时
        'persistent'    => false,
    ),

    'user_token' => array(
        'host'          => '192.168.83.101',
        'port'          => 6379,
        'timeout'       => 0.1,//响应超时
        'persistent'    => false,
    ),
    // 打分班次
    'score_info' => array(
        'host'          => '192.168.83.101',
        'port'          => '6380',
        'timeout'       => '0.1',
    ),
    //
    'user_info_v6' => array(
        'host'          => '192.168.83.101',
        'port'          => '6379',
        'timeout'       => '0.1',
    ),

    // Api根路径
    'api_app_root'   => 'http://192.168.83.101/v1.6/index.php/',
    'api_count_root' => 'http://192.168.83.101:9092/',


    'CERTIFICATE_VIDEO_REQUEST_LOG_MODEL' => 'cj_certificate_video_request_log_20150130',
);
