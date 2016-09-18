<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/7
 * Time: 13:36
 */

namespace Cms\Event;

use Cms\Controller\MessageController;
use Cms\Event;
class ClosureEvent extends PublicEvent{

    /*
     * 封禁帐号列表
     * */
    public function forbidden_user_list($map)
    {
        $User  = D('User');
        $itemsPerPage = C('ITEMS_PER_PAGE');

        $count = $User->get_closure_count($map);

        //载入分页类,核心类
        import("THINK.Page");
        $Page  = new \Think\Page($count, $itemsPerPage);
        $show  = $Page->show();

        $state = C('STATE_ACCUSATION_PROCESS_STATES');
        $list  = $User->get_closure_list($map, $Page);
        /*for($i=0;$i<count($list);$i++){
            $list[$i] = unit_to_time_single($list[$i],'reg_time');
            $list[$i] = sex_to_text_single($list[$i],'sex');
            $list[$i]['dblocking_time'] = date('Y-m-d H:i:s',$list[$i]['dblocking_time']);
            $list[$i]['status'] = $state[$list[$i]['status']];
        }*/

        return array(
            'list'=>$list,
            'show'=>$show,
            'count'=>$count,
        );
    }

/*
 * 封禁用户
 * */
    public function closure($data=array())
    {
        $Log           = D('AccusationRequestLog');
        $Request       = D('AccusationRequest');
        $Message       = new MessageController();
        $Report        = D('Report');

        if($data['id']){
            $requestItem   = $Request->get_single_item('id='.$data['id']);
            $reportItem    = $Report->get_single_item('id='.$requestItem['accusation_id']);
            $uid                = $reportItem['uid'];          // 举报人 uid 
            $offender_uid       = $reportItem['offender_uid']; // 被举报人 uid
            $sub_time           = date('Y-m-d H:i:s',$reportItem['dtime']); // 举报时间
            $accusation_id      = $reportItem['id'];           // 举报表  id （cj_report）

        }else{
            $offender_uid       = $data['uid'];  // 直接操作被举报用户
        }

        $request_id         = $data['id'];
        $reason             = $data['accusation_reason'];
        $status             = $data['accusation_status'];
        $remark             = $data['accusation_remark'];
        $c_from             = $data['c_from'];
        $report_time        = time();
        $accusationState    = C('STATE_ACCUSATION_PROCESS_STATES');

        $aw = ($data['id']!='')?'EQ':'IN'; // EQ 单个 IN 多个

        $userState = $this->check_offender_uid_state($offender_uid);
        if($userState == true) 
            $this->error('该用户已被封禁');


        //修改cj_report数据
        if($accusation_id !='' ){ // 根据客户端举报表（cj_report） 分别处理举报信息
            $map           = array('id'=>array('EQ',$accusation_id));
            $saveInReport  = array('reason'=>$reason,'status'=>$status,'atime'=>$report_time);
            $Report->update_single_item($map,$saveInReport);
        }else{
            // 根据用户id 直接处理所有 举报表信息
            $request_id = $Report->getreportidlsit($data['uid']);

            if($request_id){
                $map           = array('id'=>array('IN',$request_id['idstr']));
                $saveInReport  = array('reason'=>$reason,'status'=>$status,'atime'=>$report_time);
                $Report->update_single_item($map,$saveInReport);
            }
        }

        //修改cj_accusation_request数据
        $saveInRequest = array(
            'operation' => 2,
            'certificate_time' => date('Y-m-d H:i:s',$report_time),
            'status'    => $status,
            'result'    => $accusationState[$status],
            'reason'    => $reason,
            'remark'    => ($remark!='')?$remark:'',
        );
        if($status == 1)
            unset($saveInRequest['reason']);

        if($data['id']){
            $map           = array('accusation_id'=>array("$aw",$request_id));
            $Request->update_single_item($map,$saveInRequest);
        }else{
            $map           = array('accusation_id'=>array("$aw",$request_id['idstr']));
            $Request->update_single_item($map,$saveInRequest);
        }


        if($data['id']){
            //插入log数据
            $logItem  = array(
                'aid' => $_SESSION['authId'],
                'uid' => $uid,
                'offender_uid' => $offender_uid, 
                'accusation_id'=> $requestItem['accusation_id'],
                'result'=>$accusationState[$status],
                'operation' => 2,
                'reason' => $reason,
                'status' => $status,
                'remark' => $remark,
                'c_from' => $c_from,
                'certificate_time'  => date('Y-m-d H:i:s',$report_time),
            );
            if($status == 1)
                unset($logItem['reason']);
            //$Log = D('AccusationRequestLog');
            $Log->insert_single_item($logItem);
        }else{
            if(!empty($request_id) ){ // 有举报记录
                $euid = explode(',',$request_id['uid']);
                foreach (explode(',',$request_id['idstr']) as $key => $val){
                    $remark = ($key==0)?$remark:'';
                    //插入log数据
                    $logItem[$key]  = array(
                        'aid' => $_SESSION['authId'],
                        'uid' => $euid[$key],
                        'offender_uid' => $offender_uid, // 被举报用户
                        'accusation_id'=> $val,          // cj_report id
                        'result'=>$accusationState[$status],
                        'operation' => 2,
                        'reason' => $reason,
                        'status' => $status,
                        'remark' => $remark,
                        'c_from' => $c_from,
                        'certificate_time'  => date('Y-m-d H:i:s',$report_time),
                    );
                    if($status == 1)
                        unset($logItem[$key]['reason']);

                }
                //$Log = D('AccusationRequestLog');
                $Log->insert_multi_items($logItem);
            }else{  // 没有举报记录
                $logItem  = array(
                        'aid' => $_SESSION['authId'],
                        'uid' => '',
                        'offender_uid' => $offender_uid, // 被举报用户
                        'accusation_id'=> '',          // cj_report id
                        'result'=>$accusationState[$status],
                        'operation' => 2,
                        'reason' => $reason,
                        'status' => $status,
                        'remark' => $remark,
                        'c_from' => $c_from,
                        'certificate_time'  => date('Y-m-d H:i:s',$report_time),
                    );

                    if($status == 1)
                        unset($logItem[$key]['reason']);

                    //$Log = D('AccusationRequestLog');
                    $Log->insert_single_item($logItem);
            }   
        }

        if($status>1 && $status!=6 ){
            $User      = D('User');
            $closureTime = C('STATE_CLOSURE_TIME');
            $dBlockingTime = strtotime(date('Y-m-d H:i:s',strtotime($closureTime[$status])));
            $saveInUser = array('dblocking_time'=>$dBlockingTime,'status'=>-1);
            $User->update_single_item('uid='.$offender_uid,$saveInUser);
        }

        //todo 审核结束后发送系统消息
        $bjbuser= D('User')->get_single_item('uid = '.$offender_uid,'nickname');

        

        if($status == 1){
            $messageType = 'failed';
            if($reportItem['report_type']=='1'){
                $orths = '“'.$bjbuser['nickname'].'”的“个人资料”';
            }elseif($reportItem['report_type']=='2'){
                $orths = '“'.$bjbuser['nickname'].'”的“聊天内容”';
            }else{
                $orths = '“'.$bjbuser['nickname'].'”的“动态内容”';
            } 
        }elseif($status == 6 ){
            $messageType = 'havhandle'; 
            if($reportItem['report_type']=='1'){
                $orths = '“'.$bjbuser['nickname'].'”的“个人资料对”';
            }elseif($reportItem['report_type']=='2'){
                $orths = '“'.$bjbuser['nickname'].'”的“聊天内容”';
            }else{
                $orths = '“'.$bjbuser['nickname'].'”的“动态内容”';
            } 
        }else{
            $messageType = 'pass';  
            // 获取该用户成功举报他人的次数
            $ju_num = $Log->where(' status BETWEEN 2 AND 5 AND uid = '.$uid)->count();
            $ju_con = ($ju_num!='')?'您已成功举报'.$ju_num.'次，':'';
            $orths  = '对“'.$bjbuser['nickname'].'”的举报已审核并处理，'.$ju_con;   
        }

        $reason = '';
        
        
        if($data['id']){
            $Message->send_system_message($uid,'certificate_accusation',$messageType,$reason,$sub_time,$orths);
        }else{
            foreach ($euid as   $uid) {
                $Message->send_system_message($uid,'certificate_accusation',$messageType,$reason,$sub_time,$orths);
            }
        }

        //删除用户地理位置信息
        $Location = D('Location');
        $Location->delete_single_item(array('uid'=>array('EQ',$offender_uid)));

        //删除用户redis内token及baseInfo和fullInfo
        $Redis = D('PhpServerRedis');
        $off   = $Redis->delete_user_token($offender_uid);
        $Redis->delete_user_info($offender_uid);

        //自动分配一条记录

        //自动补入一条request
        /*$AdminInfoEvent = A('AdminInfo','Event');
        if($AdminInfoEvent->check_admin_status() == 1){
            $AccusationEvent = A('Accusation','Event');
            $AccusationEvent->auto_allocate_single_request();
        }*/

        if($off==0 && $status!=1){
            $this->error('用户token未删除，请呼叫程序员。');
        }

    }

    

    /*
     * 用户已经被封禁,通知举报人,处理request
     * @param id              int cj_accusation_request.id
     * @param accusationId    int cj_report.id
     * @param uid             int 举报人初见号
     * */
    public function already_forbidden($id)
    {
        //更新cj_report
        $saveInReport = array(
            'status'=> 1,//拒绝受理
            'remark'=> '用户已经被封禁',
            'atime'=>time(),
        );
        $Request       = D('AccusationRequest');
        $requestItem   = $Request->get_single_item('id='.$id);
        $Report        = D('Report');
        $reportItem    = $Report->get_single_item('id='.$requestItem['accusation_id']);
        $uid           = $requestItem['uid'];
        $accusationId  = $requestItem['accusation_id'];
        $subTime       = date('Y-m-d H:i:s',$reportItem['dtime']);

        $Report->update_single_item('id='.$accusationId,$saveInReport);

        //更新cj_accusation_request
        $saveInRequest = array(
            'aid'=>$_SESSION['authId'],
            'operation'=>2,//已处理
            'status'=>1,//拒绝受理
            'certificate_time'=>date('Y-m-d H:i:s',time()),
            'result'=> '拒绝受理',
            'remark'=> '用户已被封禁',
        );
        $Request = D('AccusationRequest');
        $Request->update_single_item('id='.$id,$saveInRequest);

        //插入log
        $saveInLog                  = $saveInRequest;
        $saveInLog['uid']           = $reportItem['uid'];
        $saveInLog['offender_uid']  = $reportItem['offender_uid'];
        $saveInLog['accusation_id'] = $requestItem['accusation_id'];
        $Log     = D('AccusationRequestLog');
        $Log->insert_single_item($saveInLog);

        //发送系统消息
        $Message = new MessageController();
        $messageType = 'already_forbidden';
        $reason = '用户已被封禁';
        $Message->send_system_message($uid,'certificate_accusation',$messageType,$reason,$subTime);

        //自动分配一条记录
        $AccusationEvent = A('Accusation','Event');
        $AccusationEvent->auto_allocate_single_request();
    }

    /*
     * 解除封禁
     * */
    public function undo_forbidden($uid)
    {
        $userState = $this->check_offender_uid_state($uid);
        if($userState == false)
            $this->error('该用户已解除封禁');

        $User          = D('User');
        $closureTime   = C('STATE_CLOSURE_TIME');

        $dBlockingTime = date('Y-m-d H:i:s',strtotime($closureTime['1']));
        $userData      = array('dblocking_time'=>strtotime($dBlockingTime),'status'=>0);
        $User->update_single_item(array('uid'=>array('EQ',$uid)),$userData);

        $Log       = D('AccusationRequestLog');
        $logData   = array(
            'aid'=>$_SESSION['authId'],
            'offender_uid' => $uid,
            'operation'=> 3,//取消封禁
            'remark' => '管理员个人资料页取消封禁',
            'certificate_time'  => date('Y-m-d H:i:s',time()),
        );
        $Log->insert_single_item($logData);
    }

    /*
     * 永久封禁
     * */
    public function forever_forbidden($uid)
    {
        $userState = $this->check_offender_uid_state($uid);
        if($userState == true)
            $this->error('该用户已被封禁');

        $User          = D('User');
        $closureTime   = C('STATE_CLOSURE_TIME');
        $dBlockingTime = date('Y-m-d H:i:s',strtotime($closureTime['5']));
        $userData      = array('dblocking_time'=>strtotime($dBlockingTime),'status'=>-1);
        $User->update_single_item(array('uid'=>array('EQ',$uid)),$userData);

        $Log       = D('AccusationRequestLog');
        $logData   = array(
            'aid'=>$_SESSION['authId'],
            'offender_uid' => $uid,
            'operation'=>4,//永久封禁
            'status'=>5,
            'remark' => '管理员个人资料页永久封禁',
            'result' => '永久封禁',
            'certificate_time'  => date('Y-m-d H:i:s',time()),
        );
        $Log->insert_single_item($logData);

        //删除用户地理位置信息
        $Location = D('Location');
        $Location->delete_single_item(array('uid'=>array('EQ',$uid)));

        //删除用户redis内token及fullinfo和baseinfo
        $Redis = D('PhpServerRedis');
        $Redis->delete_user_token($uid);
        $Redis->delete_user_info($uid);
    }

    /*
     * 检查被举报用户是否已经解禁--到时间了
     * @return false 被封禁；
     *         true  未封禁;
     * */
    public function check_offender_uid_state($uid)
    {
        $ret  = false;
        $User = D('User');
        $data = $User->get_single_item(array('uid'=>array('EQ',$uid)));
        
        if($data['status'] == '-1')
            $ret = true;

        return $ret;
    }

    /*
     * 处理举报及封禁log
     * @param $logArr array(0=>array()...)
     * */
    public function process_closure_log($logArr)
    {
        $reasonArr = C('STATE_ACCUSATION_PROCESS_REASONS');
        $statusArr = C('STATE_ACCUSATION_PROCESS_STATES');
        for($i=0;$i<count($logArr);$i++){
            $Admin = D('Admin');
            $adminItem            = $Admin->get_single_item('aid='.$logArr[$i]['aid'],'nickname');
            $logArr[$i]['aid']    = $adminItem['nickname'];
            $logArr[$i]['reason'] = $reasonArr[$logArr[$i]['reason']];
            $logArr[$i]['status'] = $statusArr[$logArr[$i]['status']];
        }

        return $logArr;
    }
}
