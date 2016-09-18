<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/1/30
 * Time: 15:26
 */

namespace Cms\Controller;

use Cms\Controller;
class CertificateVideoController extends PublicController{

    public function index()
    {
        $map   = array();
        $menu  = $_GET['menu'];
        $type  = $_GET['type'];
        $case  = $menu.'_'.$type;
        
        if($_GET['keyword'])
            $uid = $_GET['keyword'];

        $Event = A('CertificateVideo','Event');
        $ret = $Event->get_request_data($case,$map,$uid);

        require_once("./ThinkPHP/Library/Think/Emoji.class.php");
        if(!empty($ret['list'])){
            foreach($ret['list'] as $key => $val ){
                $ret['list'][$key]['nickname'] =  emoji_unified_to_html($val['nickname']);
            }
        }

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
        $type  = $_GET['type'];

        $Event = A('CertificateVideo','Event');
        $Event->allocate_task_to_admin($data);

        $this->redirect('CertificateVideo/index',array('menu'=>'task_hall','type'=>$type));
    }

    /*
     * 显示单个请求数据
     * */
    public function show_single_request()
    {
        $menu = $_GET['menu'];
        $type = $_GET['type'];
        $certificate_video_id = $_GET['certificate_video_id'];
        $id    = $_GET['id'];
        $case  = $menu.'_'.$type;
        $Event = A('CertificateVideo','Event');
        $list  = $Event->show_single_request($case,$id,$certificate_video_id);
        
        $this->assign("menu", $menu);
        $this->assign("type", $type);
        $this->assign('list', $list);
        $this->display();
    }


    /*
     * 认证
     * */
    public function submit_certificate()
    {
        $Event = A('CertificateVideo','Event');
        $ret   = $Event->submit_certificate();

        $this->ajaxReturn(array('info'=>$ret));
    }

    /*
     * 取消视频认证
     * */
    public function undo_certificate()
    {
        $uid   = $_GET['uid'];
        $Event = A('CertificateVideo','Event');
        $Event->undo_certificate($uid);
        $this->success('操作成功');
    }

    /*
     * 任务大厅确认删除request
     * */
    public function confirm_delete_request()
    {
        $list    = $_GET['list'];
        $listArr = explode(',',$list);

        $Event = A('CertificateVideo','Event');
        $Event->confirm_delete_request($listArr);

        $this->success('操作成功');
    }
}
