<?php

if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);

// 定义应用目录
define('APP_PATH','./App/');

define('BIND_MODULE','Cms');

// 定义页面目录
define("WEB_ROOT", dirname(__FILE__) . "/Cms/");


// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';


// 亲^_^ 后面不需要任何代码了 就是如此简单
/*
ob_start();
ini_set('date.timezone', 'Asia/Shanghai');
define('THINK_PATH', './ThinkPHP/');
define('APP_NAME', 'App');
define('APP_PATH', './App/');
define("WEB_ROOT", dirname(__FILE__) . "/");
define('WEB_CACHE_PATH', WEB_ROOT."Cache/");//网站当前路径
define("RUNTIME_PATH", WEB_ROOT . "Cache/Runtime/Admin/");
define("DatabaseBackDir", WEB_ROOT . "Databases/"); //系统备份数据库文件存放目录
define('APP_DEBUG', TRUE);
require(THINK_PATH . "ThinkPHP.php");
*/
?>