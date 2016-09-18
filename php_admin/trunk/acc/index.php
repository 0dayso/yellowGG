<?php
// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<')) die('Require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);

// 定义应用目录
define('APP_ROOT',__DIR__.'/../');
define('APP_PATH',APP_ROOT.'App/');

// 定义绑定模块
define('BIND_MODULE','ChuChai');

// 引入ThinkPHP入口文件
require APP_ROOT.'ThinkPHP-v3.2.3/ThinkPHP.php';