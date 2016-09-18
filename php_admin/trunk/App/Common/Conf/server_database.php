<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/5
 * Time: 14:01
 */

return array(
    'DB_TYPE'                   =>  'mysql',
    'DB_HOST'                   =>  'localhost',
    'DB_NAME'                   =>  'cj_admin',
    'DB_USER'                   =>  'root',
    'DB_PWD'                    =>  'YPCvBYZJkjPTEm3q',
    'DB_PORT'                   =>  '3306',
    'DB_PREFIX'                 =>  'cj_',

    'RESET_PERMISSION'          => true,
    'RESET_MODEL'               => 'server',//或者server
    'REDIS_START'               => true,//开启redis

    'admin_db_config' =>  array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'localhost',
        'DB_NAME'                   =>  'cj_admin',
        'DB_USER'                   =>  'root',
        'DB_PWD'                    =>  'YPCvBYZJkjPTEm3q',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'cj_',
    ),
    'admin_log_db_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'localhost',
        'DB_NAME'                   =>  'cj_admin_log',
        'DB_USER'                   =>  'root',
        'DB_PWD'                    =>  'YPCvBYZJkjPTEm3q',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'cj_',
    ),
    'data_db_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'localhost',
        'DB_NAME'                   =>  'chujian',
        'DB_USER'                   =>  'root',
        'DB_PWD'                    =>  'YPCvBYZJkjPTEm3q',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  'cj_',
    ),
    'redis_config' => array(
        'DATA_CACHE_TYPE'           => 'Redis',
        'REDIS_HOST'                => '10.105.2.186',
        'REDIS_PORT'                => 6379,
        'DATA_CACHE_TIME'           => 0.1,
    ),
    'im_server_config' => array(
        'DB_TYPE'                   =>  'mysql',
        'DB_HOST'                   =>  'localhost',
        'DB_NAME'                   =>  'cj_im_server',
        'DB_USER'                   =>  'root',
        'DB_PWD'                    =>  '111111',
        'DB_PORT'                   =>  '3306',
        'DB_PREFIX'                 =>  '',
    ),
);
