<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/12
 * Time: 13:54
 */

namespace Cms\Controller;

use Cms\Controller;
class ClosureController extends PublicController{

    /*
     * 封禁帐号列表
     * */
    public function index()
    {
        $map    = array();

        $Event = A('Closure','Event');  
        $ret   = $Event->forbidden_user_list($map); 

        /*$this->assign("list", $ret['list']);
        $this->assign("page", $ret['show']);*/
        $this->assign("ret", $ret);
        $this->display();
    }

    /*
     * 封禁帐号
     * */
    public function closure()
    {
        $admin_id = $this->admin_permission(C('ACTION_CERTIFICATE_ACCUSATION'));
        if(!$admin_id)
            $this->error('没有权限');

        $Event = A('Closure','Event');
        $Event->closure($_GET);
        
        if($_GET['request_id']!='' || (int)$_REQUEST['c_from'] == 2)//c_from=2:操作来自用户资料页
        {
            $this->redirect('Closure/index',array('menu'=>'admin_task','type'=>'unprocessed'));
        }else{
            $this->redirect('Accusation/index',array('menu'=>'admin_task','type'=>'unprocessed'));
        }   
    }

    /*
     * 该条request offender_uid已经被封禁
     * */
    public function already_forbidden()
    {
        $Event = A('Closure','Event');
        $Event->already_forbidden($_GET['id'],$_GET['accusation_id'],$_GET['uid'],$_GET['sub_time']);

        $this->redirect('Accusation/index',array('menu'=>'admin_task','type'=>'unprocessed'));
    }

    /*
     * 解除封禁
     * 修改cj_user表里的dblocking字段
     * 同时插入一条记录
     * */
    public function undo_forbidden()
    {
        $uid   = $_GET['uid'];
        $Event = A('Closure','Event');
        $Event->undo_forbidden($uid);

        $this->success('操作成功');
    }

    /*
     * 永久封禁
     * */
    public function forever_forbidden()
    {
        $uid   = $_GET['uid'];
        $Event = A('Closure','Event');
        $Event->forever_forbidden($uid);

        $this->success('操作成功');
    }
}
