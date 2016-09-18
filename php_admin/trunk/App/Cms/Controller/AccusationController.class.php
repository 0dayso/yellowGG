<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/2
 * Time: 10:11
 */

namespace Cms\Controller;

use Cms\Controller;
class AccusationController extends PublicController{

    public function index()
    {
        $map   = array();
        $menu  = $_GET['menu'];
        $type  = $_GET['type'];
        $case  = $menu.'_'.$type;
        $Event = A('Accusation','Event');
        $ret = $Event->get_request_data($case,$map);
        
        if($menu == 'task_hall'){
            $Admin = D('Admin');
            $adminArr = $Admin->get_all();
            $this->assign("admin",$adminArr);
        }

        $this->assign("menu", $menu);
        $this->assign("type", $type);
        $this->assign("list", $ret['list']);
        $this->assign("page", $ret['page']);
        $this->display();
    }

    /*
     * 给管理员分配任务
     * */
    public function allocate_task_to_admin()
    {
        $data  = $_POST;
        $menu  = $_GET['menu'];
        $type  = $_GET['type'];

        $Event = A('Accusation','Event');
        $Event->allocate_task_to_admin($data);

        $this->redirect('Accusation/index',array('menu'=>$menu,'type'=>$type));
    }

    /*
     * 查看被举报人的在已分配任务中的所有举报
     * */
    public function show_single_request()
    {
        $id     = $_GET['id'];
        $Report = D('AccusationRequest');
        $item   = $Report->get_single_item('id='.$id,'uid,offender_uid');

        if(isset($_GET['menu'])){
            $this->redirect('User/user_info',array(
                'menu'=>'account_manage',
                'type'=>'user_list',
                'request_id'=>$id,
                'uid'=>$item['offender_uid'],
                'reporter'=>$item['uid'],
                'accusation'=>true,
            ));
        }
        else{
            $this->redirect('User/user_info',array(
                'menu'=>'account_manage',
                'type'=>'user_list',
                'request_id'=>$id,
                'uid'=>$item['offender_uid'],
                'reporter'=>$item['uid'],
                'accusation'=>true,
            ));
        }
    }

    /*
     * 任务大厅确认删除request
     * */
    public function confirm_delete_request()
    {
        $list    = $_GET['list'];
        $listArr = explode(',',$list);

        $Event = A('Accusation','Event');
        $Event->confirm_delete_request($listArr);

        $this->success('操作成功');
    }
}
