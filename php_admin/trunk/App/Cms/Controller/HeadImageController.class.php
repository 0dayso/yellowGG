<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/10
 * Time: 11:12
 */

namespace Cms\Controller;

use Cms\Controller;
class HeadImageController extends PublicController{

    /*
     * 显示用户上传的图片
     * 注意：除了request表外，我们还要判断图片是不是存在--即有没有被用户自己删除
     * */
    public function index()
    {
        $menu  = $_GET['menu'];
        $type  = $_GET['type'];
        $case  = $menu.'_'.$type;
        $map   = array();

        $Event = A('HeadImage','Event');
        $ret   = $Event->get_image_list($case,$map);

        $this->assign('menu',$menu);
        $this->assign("type", $type);
        $this->assign("list", $ret['list']);
        $this->assign("page", $ret['page']);

        $this->display();
    }

    /*
     * 内容审查删除用户头像
     * 注：允许删除所有图片
     * @param post['list']=array(0=>41,1=>42) request_id
     * */
    public function delete_head_image()
    {
        $list    = $_POST['list'];
        $reason  = $_POST['reason'];
        $Event = A('HeadImage','Event');
        $Event->delete_head_image($list,$reason);
        $this->ajaxReturn(array('info'=>'操作成功'));
    }

    /*
     * 个人资料页面删除用户头像，分两部分
     * 一部分还存在于request里；
     * 另一部分已经不存在于request里；
     * */
    public function user_info_delete_head_image()
    {
        $uid          = $_GET['uid'];
        $reason       = $_POST['reason'];
        unset($_POST['reason']);
        $list         = $_POST;
        $requestList  = array();
        $Request      = D('HeadImageModifyRequest');
        $map          = array();

        $userinfo = D('User')->field('server_version,status,album')->where("uid = $uid")->find();

        if($userinfo['status']==3 || $userinfo['server_version']==1)
        {
            $dls = array();
            // 删除头像
            $img = $_POST['face_url'];
            if($img != '')
            {
                $this->aliydel('headimage',$img);
                $url = D('User')->where("uid = $uid ")->data(array('face_url'=>''))->save();
                if($url !== false)
                {
                    $this->del_r_user_info($uid);
                    $Message = new MessageController();
                    $Message->send_system_message($uid,'head_image','modify');
                    $dls[] = array('uid' => $uid,'image' => $img);//写日志
                    $this->success('操作成功');
                }
            }

            // 删除相册
            $album = array_flip(json_decode($userinfo['album'],true));
            foreach($_POST['album_list'] as $key => $value)
            {
                if(array_key_exists($value,$album))
                {
                    $al = $this->aliydel('headimage',$value);
                    unset($album[$value]);
                    $dls[] = array('uid' => $uid,'image' => $value);//写日志
                }
            }
            $albums = json_encode(array_flip($album));
            if($album!=''){
                $data['album'] = $albums;
                $af = D('User')->where("uid = $uid ")->data($data)->save();
                if($af !==false){
                    $this->del_r_user_info($uid);
                    $Message = new MessageController();
                    $Message->send_system_message($uid,'head_image','modify');
                    $this->success('操作成功');
                }
            }

            //判断任务，写入日志
            $lgs = array();
            $rqs = array();
            foreach($dls as $k => $v)
            {
                $req = $Request->get_single_item($v,'id') ?: array();
                $v += array(
                    'result'    => 'request里不存在，管理员直接在个人页面删除',
                    'operation' => 4,
                    'pass_time' => date('Y-m-d H:i:s'),
                    'aid'       => $_SESSION['authId'],
                    'reason'    => $reason,
                );
                if($req['id'])//存在任务
                {
                    $v['result']    = '管理员删除';
                    $v['operation'] = 2;
                    $rqs[] = $req['id'];
                }
                $lgs[] = $v;
            }
            $Request->delete_multi_items(array('id' => array('in',$rqs)));//删除任务
            D('HeadImageModifyRequestLog')->insert_multi_items($lgs);//写入日志

        }else{
            foreach($list as $key=>$value){
                $map['uid']   = array('EQ',$uid);
                $map['image'] = array('EQ',$value);
                $item         = $Request->get_single_item($map,'id');
                if($item != null){
                    array_push($requestList,$item['id']);
                    unset($list[$key]);
                }
            }
            //存在于request里的走任务大厅未审核批量删除流程;
            if(count($requestList)>0){
                $Event = A('HeadImage','Event');
                $Event->delete_head_image($requestList,$reason);
            }
            //不存在于request里的直接在user表里删除，然后插入log
            if(count($list)>0){
                $Event = A('HeadImage','Event');
                $Event->user_info_delete_head_image($uid,$list,$reason);
            }

            $this->success('操作成功');
        }


    }

    /*
     * 清除用户自己删除的头像对应的request
     * */
    public function clear_user_deleted_image()
    {
        $Event = A('HeadImage','Event');
        $Event->clear_user_deleted_image();

        $this->success('操作成功');
    }

    /*
     * 任务大厅未审核-确认审核
     * */
    public function confirm_all_pass()
    {
        $list  = $_POST['list'];
        $Event = A('HeadImage','Event');
        $Event->confirm_all_pass($list);
        $this->ajaxReturn(array('info'=>'操作成功'));
    }

    /*
     * 任务大厅已审核-确认审核结束
     * */
    public function confirm_all_certificated()
    {
        $list  = $_POST['list'];
        $Event = A('HeadImage','Event');
        $Event->confirm_all_certificated($list);
        $this->ajaxReturn(array('info'=>'操作成功'));
    }

    // 显示完整图片
    public function show_img_full()
    {
      die('<html><body><img src="'.I('request.src').'"></body></html>');
    }

}