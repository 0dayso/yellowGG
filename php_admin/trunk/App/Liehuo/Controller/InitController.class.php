<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/30
 * Time: 15:09
 */

namespace Liehuo\Controller;
use Think\Controller;

class InitController extends Controller
{

    /*
     * 登录初始化
     * */
    public function init($authInfo=array())
    {
        $adminId = $authInfo['aid'];
        $this->save_admin_id($authInfo);
        $this->get_admin_permission($adminId);
        $this->get_request_table($adminId);
    }

    /*
     * 获取管理员信息并存储到session
     * */
    protected function save_admin_id($authInfo)
    {
        $_SESSION[C('USER_AUTH_KEY')]=$authInfo['aid'];//记录认证标记，必须有。其他信息根据情况取用。
        $_SESSION['email']=$authInfo['email'];
        $_SESSION['nickname']=$authInfo['nickname'];
    }

    /*
     * 获取管理员权限并存储到session
     * */
    protected function get_admin_permission($adminId)
    {
        $AdminGroupAdmin = D('AdminGroupAdmin');//
        $adminHaveAction = $AdminGroupAdmin->get_admin_all_permission($adminId);
        $_SESSION['action'] = array();
        foreach($adminHaveAction as $value){
            array_push($_SESSION['action'],current($value));
        }
    }

    /*
     * 获取管理员的请求任务表
     * */
    protected function get_request_table($adminId)
    {
        //$_SESSION['table'] = array();
        //$_SESSION['table']['admin_certificate_car_request_table']    = C('CERTIFICATE_CAR_REQUEST_AID').$adminId;
        //$_SESSION['table']['admin_certificate_video_request_table']  = C('CERTIFICATE_VIDEO_REQUEST_AID').$adminId;
        //$_SESSION['table']['admin_accusation_request_table']         = C('ACCUSATION_REQUEST_AID').$adminId;
        //$_SESSION['table']['admin_feedback_request_table']           = C('FEEDBACK_REQUEST_AID').$adminId;
    }

}
