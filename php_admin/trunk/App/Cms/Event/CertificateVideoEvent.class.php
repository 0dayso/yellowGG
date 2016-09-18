<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/1/30
 * Time: 15:09
 */

namespace Cms\Event;

use Cms\Controller\MessageController;
use Cms\Event;
class CertificateVideoEvent extends PublicEvent{

    public function get_request_data($case,$map,$uid='')
    {
        $Model   = D('CertificateVideoRequest');
        $itemsPerPage = C('ITEMS_PER_PAGE');
        import("THINK.Page");
        switch($case) {
            case 'task_hall_unallocated':
                $map['operation'] = array('EQ',0);
                break;
            case 'task_hall_allocated':
                $map['operation'] = array('EQ',1);
                $map['status']    = array('EQ',0);
                break;
            case 'task_hall_processed'://已认证和取消认证
                $map['operation'] = array('IN',array(2,3));
                break;
            case 'admin_task_unprocessed':
                $map['operation'] = array('EQ',1);
                $map['aid']       = array('EQ',$_SESSION['authId']);
                $map['status']    = array('EQ',0);
                break;
            default:
                break;
        }
        if($uid!='')
            $map['uid'] = array('like','%'.$uid.'%');

        $count = $Model->task_hall_count($map);
        $Page  = new \Think\Page($count, $itemsPerPage);
        $ret   = $Model->task_hall_list($map, $Page,$case);

        $show = $Page->show();
   
        return array(
            'list'=>$ret,
            'page'=>$show,
        );
    }


    /*
     * 任务大厅分配任务
     * @param type string unallocated/allocated
     * @param data array(1=>'1:10010',...,aid=2)
     * */
    public function allocate_task_to_admin($data,$auto=null)
    {
        if($auto == null){
            $admin_id = $this->admin_permission(C('ACTION_ALLOCATE_CERTIFICATE_VIDEO'));
            if(!$admin_id){
                $this->error('没有权限');
                $this->error_message = '没有权限';
            }

            if((!isset($_POST['aid']))||($_POST['aid']=='请选择管理员')){
                $this->error('请选择管理员');
                $this->error_message = '请选择管理员';
            }
        }

        $aid  = $data['aid'];
        $time = date('Y-m-d H:i:s',time());
        $in   = array();
        unset($data['aid']);

        foreach($data as $value){
	        $temp = explode(':',$value);
            $id   = current($temp);
            $certificate_video_id = end($temp);
            array_push($in,$id);
            $logItems[] = array('certificate_video_id'=>$certificate_video_id,'operation'=>1,'aid'=>$aid,'allocate_time'=>$time);
        }
        $dataItem = array('operation' => 1,'aid' => $aid,'allocate_time' => $time);
        $map = array('id'=>array('IN',$in));
        $DataModel = D('CertificateVideoRequest');
        $DataModel->update_multi_items($map,$dataItem);

        $LogModel = D('CertificateVideoRequestLog');
        $LogModel->insert_multi_items($logItems);
    }

    public function show_single_request($case,$id,$certificateVideoId)
    {
        switch($case)
        {
            case 'task_hall_processed':
                $admin_id = $this->admin_permission(C('ACTION_CERTIFICATE_VIDEO_ROOT'));
                break;
            case 'admin_task_unprocessed':
                $admin_id = $this->admin_permission(C('ACTION_CERTIFICATE_VIDEO'));
                break;
            default:
                break;
        }
        if(!$admin_id){
            $this->error('没有权限');
            $this->error_message = '没有权限';
        }

        $Video = D('CertificateVideo');
        $map['id'] = array('EQ',$certificateVideoId);
        $field     = 'id as certificate_video_id,uid,status,p1,p2,p3,p4,replace_content';
        $ret = $Video->get_single_item($map,$field);

        $User = D('User');
        $map = array('uid='.$ret['uid']);
        $userInfo = $User->get_single_item($map,'status');
        $ret['user_status'] = $userInfo['status'];
        $ret['id']   = $id;
        if($ret['replace_content'] != '' && $ret['status'] == 1  ){
            $replace   = json_decode($ret['replace_content'],true);
            $ret['p1'] = $replace['p1'];
            $ret['p2'] = $replace['p2'];
            $ret['p3'] = $replace['p3'];
            $ret['p4'] = $replace['p4'];
        }
        return $ret;
    }

    public function auto_allocate_single_request()
    {
        $Model = D('CertificateVideoRequest');
        $map   = array('operation'=>array('EQ',0));
        $items = $Model->get_limit_items($map,1,'id,certificate_video_id');
        if(count($items)>0){
            $item  = current($items);
            $data  = array();
            $data['aid'] = $_SESSION['authId'];
            array_push($data,$item['id'].':'.$item['certificate_video_id']);
            $this->allocate_task_to_admin($data,$auto=1);
        }
    }

    /*
     * 提交认证
     * */
    public function submit_certificate()
    {
        $id   = $_POST['id'];
        $uid  = $_POST['uid'];
        $certificate_video_id  = $_POST['certificate_video_id'];
        $certificateStatusNum = C('STATE_CERTIFICATE_VIDEO_STATUS_NUM');
        $myquite              = str_replace(' ','',$_POST['myquite']);

        $usermodel  = D('User');
        $videomodel = D('CertificateVideo');
        $videoinfo   = $videomodel->get_single_item('uid = '.$uid);
        if($videoinfo['status']==1 && $videoinfo['replace_content'] !='' && $_POST['status']!='通过认证'){
            $vimap['replace_content'] = '';
            $videomodel->update_single_item('uid ='.$uid,$vimap);
            D('CertificateVideoRequest')->delete_single_item('certificate_video_id = '.$videoinfo['id']);
            //发送系统消息
            $Message = new MessageController();
            $Message->send_system_message($uid,'certificate_video','failed',$_POST['status'],'',$myquite);
            $this->del_r_user_info($uid);
            return '操作成功';
            exit;
        }



        //保存cj_certificate_video_request
        $Request = D('CertificateVideoRequest');
        $saveInRequest['certificate_video_id'] = $certificate_video_id;
        $saveInRequest['operation']            = 2;//在管理员个人页面为1,在任务大厅为2
        $saveInRequest['aid']                  = $_SESSION['authId'];//$admin_id;
        $saveInRequest['status']               = $certificateStatusNum[$_POST['status']];
        $saveInRequest['certificate_time']     = date('Y-m-d H:i:s',time());
        $saveInRequest['result']               = $_POST['status'];
        $saveInRequest['remark']               = $_POST['remark'];
        $saveInRequest['uid']                  = $uid;
        $saveInRequest['myquite']              = $myquite;
        $temp = $Request->get_single_item('id='.$id,'allocate_time');
        $saveInRequest['allocate_time']        = $temp['allocate_time'];//
        $Request->update_single_item('id='.$id,$saveInRequest);

        //保存cj_certificate_video
        $saveInCertificateVideo['status']        = $saveInRequest['status'];
        if($saveInRequest['status']==1){
            if($videoinfo['status']==1 && $videoinfo['replace_content'] !='' ){
                $videoreplace = json_decode($videoinfo['replace_content'],true);
                $saveInCertificateVideo['p1'] = $videoreplace['p1'];
                $saveInCertificateVideo['p2'] = $videoreplace['p2'];
                $saveInCertificateVideo['p3'] = $videoreplace['p3'];
                $saveInCertificateVideo['p4'] = $videoreplace['p4'];
                $saveInCertificateVideo['replace_content'] = '';
                $this->aliydel('certificate',$videoinfo['p1']);
                $this->aliydel('certificate',$videoinfo['p2']);
                $this->aliydel('certificate',$videoinfo['p3']);
                $this->aliydel('certificate',$videoinfo['p4']);
            }
            $saveInCertificateVideo['pass_time'] = strtotime($saveInRequest['certificate_time']);
        }

        $Video = $videomodel;
        $Video->update_single_item(array('id'=>array('eq',$certificate_video_id)),$saveInCertificateVideo);

        //保存cj_user
        $saveInUser['video_verify']              = $saveInRequest['status'];
        $User  = $usermodel;
        $User->update_single_item(array('uid'=>array('eq',$uid)),$saveInUser);

        //保存cj_certificate_video_request_log
        $addLog = $saveInRequest;
        $Log   = D('CertificateVideoRequestLog');
        $Log->insert_single_item($addLog);

        //TODO 用redis记录用户视频认证状态

        //删除php server用户基本信息和所有信息
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_user_info($uid);

        //在cj_location表里修改car_verify字段
        $Location = D('Location');
        $saveInLocation = array('video_verify'=>$saveInRequest['status']);
        $Location->update_single_item(array('uid'=>array('EQ',$uid)),$saveInLocation);

        //发送系统消息
        $Message = new MessageController();
        if($addLog['status'] == 1)
            $messageType = 'pass';
        else
            $messageType = 'failed';
        $Message->send_system_message($uid,'certificate_video',$messageType,$_POST['status'],'',$myquite);

        //自动补入一条request
        $AdminInfoEvent = A('AdminInfo','Event');
        if($AdminInfoEvent->check_admin_status() == 1)
            $this->auto_allocate_single_request();

        return '操作成功';
    }

    /*
     * 取消认证的时候需要：
     * 1、修改cj_user表的car_verify字段;
     * 2、修改cj_certificate_car的status字段
     * 3、插入cj_certificate_car_request_log;
     * 4、修改cj_certificate_car_request该uid下的status,remark,
     *    前提是cj_certificate_car_request还有该uid的记录
     * */
    public function undo_certificate($uid)
    {
        $admin_id = $this->admin_permission(C('ACTION_UNDO_CERTIFICATE_VIDEO'));
        if(!$admin_id)
            $this->error('没有权限');

        $map = array('uid'=>array('EQ',$uid));

        $User = D('User');
        $state = 2;//取消认证时,state为认证失败
        $saveInUser = array('video_verify'=>$state);
        $User->update_single_item($map,$saveInUser);

        $Certificate = D('CertificateVideo');
        $saveInCertificateVideo = array('status'=>$state);
        $Certificate->update_latest_item($uid,$saveInCertificateVideo);

        $Log  = D('CertificateVideoRequestLog');
        $addLog['operation']            = 3;//取消认证
        $addLog['aid']                  = $_SESSION['authId'];//$admin_id;
        $addLog['status']               = 2;
        $addLog['certificate_time']     = date('Y-m-d H:i:s',time());
        $addLog['result']               = '取消认证';
        $addLog['uid']                  = $uid;
        $Log->insert_single_item($addLog);

        $Request = D('CertificateVideoRequest');
        $saveInRequest = $addLog;
        $temp = $Certificate->get_multi_items('uid='.$uid,'id');
        foreach($temp as $value){
            $in[] = $value['id'];
        }
        $map = array('certificate_video_id'=>array('IN',$in));
        $Request->update_multi_items($map,$saveInRequest);

        //在cj_location表里修改video_verify字段
        $Location = D('Location');
        $saveInLocation = array('video_verify'=>$state);
        $Location->update_single_item(array('uid'=>array('EQ',$uid)),$saveInLocation);

        //删除php server用户基本信息和所有信息
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_user_info($uid);
    }

    public function confirm_delete_request($list)
    {
        $admin_id = $this->admin_permission(C('ACTION_CONFIRM_CERTIFICATE_VIDEO_PROCESSED'));
        if(!$admin_id)
            $this->error('没有权限');

        $Req     = D('CertificateVideoRequest');
        $map['id'] = array('IN',$list);
        $Req->delete_multi_items($map,$list);
    }

    // 根据年月获取视频类审核信息
    public function getdatevideo($date,$aid=''){
        $video = D('CertificateVideoRequestLog');
        /*$map['status']  = 1;// 通过
        $no['status']  = array('gt',1); // 不通过  */
        if($aid){
            $awhere  =  ' AND aid = '.$aid;
        }     

        $sql = "SELECT  date_format(certificate_time,'%Y%m%d') AS days,count(id) AS num,date_format(certificate_time,'%d') AS d
                FROM cj_certificate_video_request_log 
                WHERE status  {status} AND date_format(certificate_time,'%Y-%m') = '{$date}' {$awhere} 
                GROUP BY days ";

        $pass = $video->query(str_replace('{status}',' = 1',$sql));
        $stop = $video->query(str_replace('{status}',' > 1',$sql));

        for($i=1; $i<=31; $i++){ 

            $day['pass'][$i] = 0;
            $day['stop'][$i] = 0;

            foreach ($pass as $key => $value) {
                if(intval($value['d']) == $i ) 
                    $day['pass'][$i] = $value['num'];
                
            }
            foreach ($stop as $key => $value) {
                if(intval($value['d']) == $i ) 
                    $day['stop'][$i] = $value['num'];
                
            }
            /*$map['certificate_time'] = array('like','%'.$date.$das.'%');
            $day['pass'][$i]  = $video->where($map)->count();*/

            /*$no['certificate_time']  = array('like','%'.$date.$das.'%');
            $day['stop'][$i]  = $video->where($no)->count();*/

            $day['count'][$i] =  $day['pass'][$i] + $day['stop'][$i];
        }
       
        return $day;
    }

    // 视频操作日志
    public function operationlog($data){
        $map['status']               = array('gt',0);
        ($data['aid']>0)?$map['aid'] = $data['aid']:'';
        ($data['uid']>0)?$map['uid'] = $data['uid']:'';
        $map['certificate_time']     = array('between',array($data['s_date'],$data['t_date']));
 
        $itemsPerPage = C('ITEMS_PER_PAGE');
        $count = D('CertificateVideoRequestLog')->where($map)->count();
        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();
        
        $vlist = D('CertificateVideoRequestLog')->field('certificate_time,uid,aid,result,remark')->where($map)->limit($Page->firstRow,$Page->listRows)->select();
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
