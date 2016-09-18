<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/1
 * Time: 19:23
 */

namespace Cms\Controller;

use Cms\Controller;
class CertificateCarController extends PublicController{

    public function index()
    {
        $map   = array();//certificate_car_search();
        $menu  = $_GET['menu'];
        $type  = $_GET['type'];

        if($_GET['keyword'])
            $uid = $_GET['keyword'];
        
        $case  = $menu.'_'.$type;
        $Event = A('CertificateCar','Event');
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

        $Event = A('CertificateCar','Event');
        $Event->allocate_task_to_admin($data);

        $this->redirect('CertificateCar/index',array('menu'=>'task_hall','type'=>$type));
    }

    /*
     * 显示单个请求数据
     * */
    public function show_single_request()
    {
        $menu = $_GET['menu'];
        $type = $_GET['type'];
        $certificate_car_id = $_GET['certificate_car_id'];
        $id    = $_GET['id'];
        $case  = $menu.'_'.$type;
        $Event = A('CertificateCar','Event');
        $ret   = $Event->show_single_request($case,$id,$certificate_car_id);

        $this->assign("menu", $menu);
        $this->assign("type", $type);
        $this->assign('brand',$ret['brand']);
        $this->assign('list',$ret['list']);
        $this->display();
    }


    /*
     * 认证
     * */
    public function submit_certificate()
    {
        $Event = A('CertificateCar','Event');
        $ret   = $Event->submit_certificate();

        $this->ajaxReturn(array('info'=>$ret));
    }

    /*
     * 取消认证
     * */
    public function undo_certificate()
    {
        $uid   = $_GET['uid'];
        $Event = A('CertificateCar','Event');
        $Event->undo_certificate($uid);
        $this->success('操作成功');
    }

    /*
     * 任务大厅确认删除request
     * */
    public function confirm_delete_request()
    {
        $list    = $_GET['list'];//传进来的是cj_certificate_car_request.id序列
        $listArr = explode(',',$list);

        $Event = A('CertificateCar','Event');
        $Event->confirm_delete_request($listArr);

        $this->success('操作成功');
    }

    /**
     * @图片替换
     * 
     **/
    public function user_car_pic_mod(){
        if(IS_POST){
            
            $p1 = $_FILES['p1'];
            $p2 = $_FILES['p2'];
            if( $p1['name']=='' && $p2['name'] =='' ){
                $this->error('请选择图片！');
            }

            $imgurl = $_POST['curl'].'/'; // 图片路径
            if($p1['name']!=''){ 
                $bucket  = 'certificate';
                $pic     = $imgurl.$p1['name'];
                $del     = $this->aliydel($bucket ,$pic );  // 删除图片
                $ret     = $this->aliyup($bucket,$pic,$p1['tmp_name']); // 上传新图片
                $statp1  = $ret->status;
            }   

            if($p2['name']!=''){ 
                $bucket  = 'certificate';
                $pic     = $imgurl.$p2['name'];
                $del     = $this->aliydel($bucket , $pic );  // 删除图片
                $ret     = $this->aliyup($bucket,$pic,$p2['tmp_name']); // 上传新图片
                $statp2  = $ret->status;
            }   

            if($statp2==200 || $statp1==200){
                $this->success('修改成功！');
            }



        }    
    }


}
