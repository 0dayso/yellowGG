<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/5
 * Time: 13:57
 */

return array(
    'DB_TYPE'                   =>  'mysql',
    'DB_HOST'                   =>  '127.0.0.1',
    'DB_NAME'                   =>  'cj_admin',
    'DB_USER'                   =>  'root',
    'DB_PWD'                    =>  '111111',
    'DB_PORT'                   =>  '3306',
    'DB_PREFIX'                 =>  'cj_',

    'RESET_PERMISSION'          => true,
    'RESET_MODEL'               => 'local',
    'REDIS_START'               => true,

    'admin_log_db_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  '127.0.0.1',
        'DB_NAME'                   =>  'cj_admin_log',
        'DB_USER'                   =>  'root',
        'DB_PWD'                    =>  '111111',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'cj_',
    ),
    'data_db_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  '127.0.0.1',
        'DB_NAME'                   =>  'chujian',
        'DB_USER'                   =>  'root',
        'DB_PWD'                    =>  '111111',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'cj_',
    ),
    'im_server_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  '127.0.0.1',
        'DB_NAME'                   =>  'chat_log',
        'DB_USER'                   =>  'root',
        'DB_PWD'                    =>  '111111',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  '',
    ),
    'im_server_redis_config' => array(
        'host'          => '127.0.0.1',
        'port'          => 6379,
        'timeout'       => 1.0,
        'persistent'    => false,
    ),
    'php_server_redis_config' => array(
        'host'          => '127.0.0.1',
        'port'          => 6379,
        'timeout'       => 1.0,
        'persistent'    => false,
    ),

    'CERTIFICATE_VIDEO_REQUEST_LOG_MODEL'=> 'cj_certificate_video_request_log_20150130',
);
