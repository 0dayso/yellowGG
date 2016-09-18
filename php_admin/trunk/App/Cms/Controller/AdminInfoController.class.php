<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/11
 * Time: 14:19
 */

namespace Cms\Controller;

use Cms\Controller;
class AdminInfoController extends PublicController{

    public function index()
    {
        $Event = A('AdminInfo','Event');
        $ret   = $Event->get_admin_info();

        $this->assign('list',$ret['list']);
        $this->display();
    }

    /*
     * 修改管理员密码
     * */
    public function change_password()
    {
        $init_pwd           = $_POST['init_pwd'];
        $change_pwd_first   = $_POST['change_pwd_first'];
        $change_pwd_confirm = $_POST['change_pwd_confirm'];

        $Event = A('AdminInfo','Event');
        $ret   = $Event->change_password($init_pwd,$change_pwd_first,$change_pwd_confirm);

        $this->ajaxReturn(array('info'=>$ret));
    }

    /*
     * 管理员上下班打卡
     * */
    public function check_on_work()
    {
        if((isset($_POST['start_working']))&&(!isset($_POST['close_working']))) {
            $userName = $_POST['start_working'];
            $case = 'start_working';
        }
        if((!isset($_POST['start_working']))&&(isset($_POST['close_working']))){
            $userName = $_POST['close_working'];
            $case     = 'close_working';
        }

        $Event = A('AdminInfo','Event');
        $ret   = $Event->check_on_work($case,$userName);

        $this->ajaxReturn(array('info'=>$ret));
    }
}
