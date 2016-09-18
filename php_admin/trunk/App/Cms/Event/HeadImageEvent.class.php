<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/10
 * Time: 11:13
 */

namespace Cms\Event;

use Cms\Controller\AliyunController;
use Cms\Controller\MessageController;
use Cms\Event;
class HeadImageEvent extends PublicEvent{

    /*
     * request里的image必须存在于cj_user.head_images里面
     * */
    public function get_image_list($case,$map)
    {
        $itemsPerPage = 98;//C('ITEMS_PER_PAGE');

        switch($case){
            case 'content_manage_unprocessed':
                //$map['operation'] = array('EQ',0);
                $map = 'i.operation=0';
                break;
            case 'content_manage_processed':
                //$map['operation'] = array('EQ',1);
                $map = 'i.operation=1';
                break;
            default:
                break;
        }

        $Request = D('HeadImageModifyRequest');
        $count   = $Request->get_count($map);
        
        import("THINK.Page");
        $Page    = new \Think\Page($count, $itemsPerPage);
        $show    = $Page->show();

        $list    = $Request->get_list($map,$Page);
        $list    = $this->process_list($list);
        
        
        return array(
            'page'=>$show,
            'list'=>$list,
        );
    }

    /*
     * 只显示还存在于用户表head_images字段里的内容
     * */
    public function process_list($list=array())
    {   
        for($i=0;$i<count($list);$i++){
            $exists = $this->image_exists($list[$i]['uid'],$list[$i]['image']);
            if(!$exists){
                unset($list[$i]);
            }
        }

        return $list;
    }

    /*
     * 清空用户自己删除的头像数据对应的request记录
     * 因为用户已经直接删除了图片-server在云服务器里也删除了，
     * 那么在这里我们也不用插入log记录这个动作了。
     * todo 如果有需求 可以以后添加
     * */
    public function clear_user_deleted_image()
    {
        
        $Request = D('HeadImageModifyRequest');
        $sql    = "SELECT id,uid,image FROM cj_head_image_modify_request WHERE id > 0 LIMIT 0,2000 ";
        $sql2   = "SELECT id,uid,image FROM cj_head_image_modify_request WHERE id > 0 LIMIT 2001,4000 ";
        $sql3   = "SELECT id,uid,image FROM cj_head_image_modify_request WHERE id > 0 LIMIT 4001,6000 ";
        $sql4   = "SELECT id,uid,image FROM cj_head_image_modify_request WHERE id > 0 LIMIT 6001,8000 ";

        $items  = $Request->query($sql);
        $items2 = $Request->query($sql2);
        $items3 = $Request->query($sql3);
        $items4 = $Request->query($sql4);
        
        $this->imagesdelhaveuser($items);
        $this->imagesdelhaveuser($items2);
        $this->imagesdelhaveuser($items3);
        $this->imagesdelhaveuser($items4);
        
    }

    public function imagesdelhaveuser($items){
        $in      = array();
        foreach($items as $value){
            $exists = $this->image_exists($value['uid'],$value['image']);
            if(!$exists)
            {
                array_push($in,$value['id']);
            }
        }
        $Request = D('HeadImageModifyRequest');
        $Request->delete_multi_items(array('id'=>array('IN',$in)));
    }

    /*
     * 批量删除用户图片
     * 1、在request里删除request
     * 2、插入log
     * 3、删除云图
     * 4、删除用户表里图
     * $reason 图片违规原因
     * */
    public function delete_head_image($list=array(),$reason='')
    {
        $admin_id = $this->admin_permission(C('ACTION_HEAD_IMAGE_CERTIFICATE'));
        if(!$admin_id)
            $this->error('没有权限');

        $imageArrInCloud    = array();
        $messageUidArr      = array();
        $Request      = D('HeadImageModifyRequest');
        $map          = array('id'=>array('IN',$list));
        $requestItems = $Request->get_multi_items($map);

        //删除user表head_images内容
        $User         = D('User');
        foreach($requestItems as $key=>$value){
            $userItem = $User->get_single_item('uid='.$value['uid'],'head_images');
            $imgArr1  = json_to_array($userItem['head_images']);
            $imgArr2  = array();
            foreach($imgArr1 as $img){
                if($img != $value['image'])
                    array_push($imgArr2,$img);
            }
            $imgJson  = array_to_json($imgArr2);
            $User->update_single_item('uid='.$value['uid'],array('head_images'=>$imgJson));

            $requestItems[$key]['result']    = '管理员删除';//已删除
            $requestItems[$key]['operation'] = 2;//已删除
            $requestItems[$key]['pass_time'] = date('Y-m-d H:i:s',time());//已删除
            $requestItems[$key]['aid']       = $_SESSION['authId'];
            if($reason)
                $requestItems[$key]['reason']    = $reason;

            unset($requestItems[$key]['id']);

            array_push($imageArrInCloud,$value['image']);

            if(!in_array($value['uid'],$messageUidArr))
                array_push($messageUidArr,$value['uid']);
        }

        //插入log
        $Log = D('HeadImageModifyRequestLog');
        $Log->insert_multi_items($requestItems);

        //删除request
        $Request->delete_multi_items(array('id'=>array('IN',$list)));

        //删除云图
        $Ali = new AliyunController();
        $Ali->delete_multi_files($imageArrInCloud,C('ALIYUN_HEADIMG_BUCKET'));

        //删除php server用户基本信息和所有信息
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_user_info($uid);

        //确认删除要给用户发送系统消息
        $Message = new MessageController();
        foreach($messageUidArr as $value){
            $Message->send_system_message($value,'head_image','modify','');
        }
    }

    /*
     * request表里可能存留用户自己替换图片后删除的request
     * 需要将这部分无效request清除
     * todo
     * */
    public function delete_request_image_not_exists()
    {

    }
    // reason 违规原因
    public function user_info_delete_head_image($uid,$list,$reason=0)
    {
        $User     = D('User');
        $userItem = $User->get_single_item('uid='.$uid,'head_images');
        $imgArr1  = json_to_array($userItem['head_images']);
        $imgArr2  = array();
        $log      = array();
        $imageArrInCloud = array();
        foreach($imgArr1 as $value){
            if(!in_array($value,$list)){
                array_push($imgArr2,$value);
            }
            else{
                array_push($imageArrInCloud,$value);
                $log[] = array(
                    'result'  => 'request里不存在，管理员直接在个人页面删除',
                    'image'   => $value,
                    'operation' => 4,
                    'pass_time' => date('Y-m-d H:i:s',time()),
                    'aid'      => $_SESSION['authId'],
                    'uid'      => $uid,
                    'reason'   => $reason,
                );
            }
        }
        $imgJson  = array_to_json($imgArr2);
        $User->update_single_item('uid='.$uid,array('head_images'=>$imgJson));

        //删除云图
        $Ali = new AliyunController();
        $Ali->delete_multi_files($imageArrInCloud,C('ALIYUN_HEADIMG_BUCKET'));

        $Log = D('HeadImageModifyRequestLog');
        $Log->insert_multi_items($log);

        //删除php server用户基本信息和所有信息
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_user_info($uid);

        //确认删除要给用户发送系统消息
        $Message = new MessageController();
        $Message->send_system_message($uid,'head_image','modify','');
    }

    /*
     * 未审核确认全部通过
     * 1、将request里的记录修改为operation=1已审核
     * 2、插入log记录
     * */
    public function confirm_all_pass($list=array())
    {
        $admin_id = $this->admin_permission(C('ACTION_HEAD_IMG_CERTIFICATE_ALL_PASS'));
        if(!$admin_id)
            $this->error('没有权限');

        $Request       = D('HeadImageModifyRequest');
        $map           = array('id'=>array('IN',$list));
        $requestItems  = $Request->get_multi_items($map);
        $time          = date('Y-m-d H:i:s',time());

        //update request
        $saveInRequest = array(
            'aid'=>$_SESSION['authId'],
            'operation'=>1,
            'result'=>'已审核',
            'pass_time'=>$time,
        );
        $map = array('id'=>array('IN',$list));
        $Request->update_multi_items($map,$saveInRequest);

        //insert logs
        $saveInLog = array();
        foreach($requestItems as $value){
            $value['aid']        = $_SESSION['authId'];
            $value['operation']  = 1;
            $value['result']     = '已审核';
            $value['pass_time']  = $time;
            unset($value['id']);
            $saveInLog[]         = $value;
        }
        $Log = D('HeadImageModifyRequestLog');
        $Log->insert_multi_items($saveInLog);
    }

    /*
     * 已审核确认全部通过
     * 1、将request里的记录删除
     * 2、插入log记录
     * */
    public function confirm_all_certificated($list)
    {
        $admin_id = $this->admin_permission(C('ACTION_HEAD_IMG_REQUEST_DELETE'));
        if(!$admin_id)
            $this->error('没有权限');

        $Request       = D('HeadImageModifyRequest');
        $map           = array('id'=>array('IN',$list));
        $requestItems  = $Request->get_multi_items($map);
        $time          = date('Y-m-d H:i:s',time());

        //delete request items
        $Request->delete_multi_items($map);

        //insert logs
        //aid and pass_time record action info
        foreach($requestItems as $value){
            $value['aid']        = $_SESSION['authId'];
            $value['operation']  = 3;
            $value['result']     = '已审核内确认';
            $value['pass_time']  = $time;
            unset($value['id']);
            $saveInLog[]         = $value;
        }
        $Log = D('HeadImageModifyRequestLog');
        $Log->insert_multi_items($saveInLog);
    }

    /*
     * 判断某个用户的图片是否存在
     * */
    protected function image_exists($uid,$image='')
    {
        $User     = D('User');
        $item     = $User->get_single_item('uid='.$uid);
        if($item['face_url']==$image){
            return true;
        }
        if($item['server_version'] == 0){
            $imageArr = json_to_array($item['head_images']);
        }
        if($item['server_version'] == 1){
            $imageArr = json_to_array($item['album']);
        }
        $ret      = in_array($image,$imageArr);
        return $ret;
    }



    // 图片操作日志
    public function operationlog($data){
        $map['operation'] = array('gt',0);
        ($data['aid']>0)?$map['aid'] = $data['aid']:'';
        ($data['uid']>0)?$map['uid'] = $data['uid']:'';
        $map['pass_time'] = array('between',array($data['s_date'],$data['t_date']));
        
        $itemsPerPage = C('ITEMS_PER_PAGE');
        $count = D('HeadImageModifyRequestLog')->where($map)->count();
        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();


        $vlist = D('HeadImageModifyRequestLog')->where($map)->limit($Page->firstRow,$Page->listRows)->select();
        $user  = D('Admin')->get_multi_items('','aid,nickname');
        foreach ($user as $k => $u) {
            $uarray[$u['aid']] = $u['nickname'];
        }
        
        foreach ($vlist as $key => $value) {
            $vlist[$key]['aid'] = strtr($vlist[$key]['aid'],$uarray);
        }
        $data['list'] = $vlist;
        $data['show'] = $show;
        return $data;
    } 





}
