<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/1/30
 * Time: 16:51
 */

namespace Liehuo\Event;
use Think\Controller;

class PublicEvent extends Controller{
    public $error_message = array();

    public function __construct()
    {
        parent::__construct();
        $this->error_message = '';
    }

    /*
     * 管理员进行某项操作时查询其是否有权限
     * @input action 操作id,cj_action.id
     * @output 如果有权限，返回admin_id
     *         如果没有权限,返回false
     * */
    public function admin_permission($action)
    {
        $ret = null;
        //$actionArr = $_SESSION['action'];
        $AdminGroupAdmin = D('AdminGroupAdmin');
        $adminHaveAction = $AdminGroupAdmin->get_admin_all_permission($_SESSION['authId']);
        $actionArr = array_column($adminHaveAction,'action_id');
        
        foreach($actionArr as $value){
            if($action == $value)
                return $action;
        }

        return false;
    }

    //删除php server用户基本信息和所有信息
    public function del_r_user_info($uid){
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_user_info($uid);
    }

    //删除php server用户 标签 、动态 缓存
    public function del_r_user_tag_surging($usertagid){
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_user_tag($usertagid);
        $phpServer->delete_user_surging($usertagid);
    }

    /*
     * 上传文件
     * @name        名称
     * @resources   上传资源
     *
     * */
    public function aliyup($bucket,$name,$resources){
        require_once("./ThinkPHP/Library/Org/AliyunOss/sdk.class.php");
        $Aliyun  = new \ALIOSS();
        return $Aliyun->upload_file_by_file($bucket,$name,$resources);
    }

    /*
     * 删除文件
     * @name        名称
     * @resources   上传资源
     *
     * */
    public function aliydel($bucket,$resources){
        require_once("./ThinkPHP/Library/Org/AliyunOss/sdk.class.php");
        $Aliyun  = new \ALIOSS();
        return $Aliyun->delete_object($bucket,$resources);
    }



} 