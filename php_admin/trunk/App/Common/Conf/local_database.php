<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/5
 * Time: 13:57
 */

return array(
    'DB_TYPE'                   =>  'mysql',
    'DB_HOST'                   =>  'localhost',
    'DB_NAME'                   =>  'cj_admin',
    'DB_USER'                   =>  'root',
    'DB_PWD'                    =>  '',
    'DB_PORT'                   =>  '3306',
    'DB_PREFIX'                 =>  'cj_',

    'RESET_PERMISSION'          => true,
    'RESET_MODEL'               => 'local',//或者server
    'REDIS_START'               => true,//开启redis

    'admin_log_db_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'localhost',
        'DB_NAME'                   =>  'cj_admin_log',
        'DB_USER'                   =>  'root',
        'DB_PWD'                    =>  '',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'cj_',
    ),
    'data_db_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'localhost',
        'DB_NAME'                   =>  'chujian',
        'DB_USER'                   =>  'root',
        'DB_PWD'                    =>  '',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'cj_',
    ),
    'im_server_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'localhost',
        'DB_NAME'                   =>  'chat_log',
        'DB_USER'                   =>  'root',
        'DB_PWD'                    =>  '',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  '',
    ),
    'im_server_redis_config' => array(
        'host'          => '127.0.0.1',
        'port'          => 6379,
        'timeout'       => 1.0,//响应超时
        'persistent'    => false,
        //'expire'        => 3600,//cache保存时间
        //'218.244.157.92',外网，本机测试使用
        //'10.161.185.205',内网，线上上传使用
        //'127.0.0.1',6379 本地
    ),
    'php_server_redis_config' => array(
        'host'          => '127.0.0.1',
        'port'          => 6379,
        'timeout'       => 1.0,//响应超时
        'persistent'    => false,
    ),

    'CERTIFICATE_VIDEO_REQUEST_LOG_MODEL'=> 'cj_certificate_video_request_log_20150130',
);
