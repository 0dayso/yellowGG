<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/5
 * Time: 18:56
 */
/*$database_config   = require_once("./App/Common/Conf/database.php");
$cloud_config      = require_once("./App/Cms/Conf/cloud.php");
$action_config     = require_once("./App/Cms/Conf/action.php");
$system_config     = require_once("./App/Cms/Conf/system.php");
$navigation_config = require_once("./App/Cms/Conf/navigation.php");*/

$database_config   = require_once(APP_PATH."Common/Conf/database.php");
//$frame_config =

$config = array(
    'URL_MODEL'                 =>  1, // PATHINFO模式为1,REWRITE模式为2，rewrite模式url可以不带index
    'VAR_URL_PARAMS'            => '_URL_',
    'SESSION_AUTO_START'        =>  true,
    'TMPL_ACTION_ERROR'         =>  THINK_PATH . 'Tpl/dispatch_jump.tpl',
    'TMPL_ACTION_SUCCESS'       =>  THINK_PATH . 'Tpl/dispatch_jump.tpl',
    'USER_AUTH_ON'              =>  true,
    'USER_AUTH_TYPE'            =>  1,      // 默认认证类型 1 登录认证 2 实时认证
    'USER_AUTH_KEY'             =>  'authId',   // 用户认证SESSION标记
    'ADMIN_AUTH_KEY'            =>  'administrator',
    'USER_AUTH_MODEL'           =>  'admin',    // 默认验证数据表模型
    'AUTH_PWD_ENCODER'          =>  'md5',  // 用户认证密码加密方式
    //'USER_AUTH_GATEWAY'         =>  'Cms/Public/login',// 默认认证网关
    'USER_AUTH_GATEWAY'         =>  'index.php/Cms/Public/index',///system.php/Public/index
    'NOT_AUTH_MODULE'           =>  'Cms/Public',   // 默认无需认证模块
    'REQUIRE_AUTH_MODULE'       =>  '',     // 默认需要认证模块
    'NOT_AUTH_ACTION'           =>  '',     // 默认无需认证操作
    'REQUIRE_AUTH_ACTION'       =>  '',     // 默认需要认证操作
    'GUEST_AUTH_ON'             =>  false,    // 是否开启游客授权访问
    'GUEST_AUTH_ID'             =>  0,        // 游客的用户ID
    'DB_LIKE_FIELDS'            =>  'title|remark',
    'RBAC_ROLE_TABLE'           =>  'think_role',
    'RBAC_USER_TABLE'           =>  'think_user',
    'RBAC_ACCESS_TABLE'         =>  'think_access',
    'RBAC_NODE_TABLE'           =>  'think_node',
    'SHOW_PAGE_TRACE'           =>  true ,   //显示调试信息
    'URL_CASE_INSENSITIVE'      =>  true,    //url不区分大小写
    'URL_HTML_SUFFIX'           =>  '',      //默认生成url的时候后缀为空
    //'APP_USE_NAMESPACE'       =>    false, //不启用命名空间

    //应用类库不再需要使用命名空间
    'APP_USE_NAMESPACE'         =>true,
    'LAYOUT_ON'                 =>true,
    'LAYOUT_NAME'               =>'layout',

    // 关闭缓存
    'DB_FIELD_CACHE'            =>false,
    'TMPL_CACHE_ON'             =>false,


    //加载Conf下的其他配置文件
    'LOAD_EXT_CONFIG' => 'cloud,action,system,state',
);

return array_merge(
    $database_config,
    $config
);
