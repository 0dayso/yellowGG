<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/17
 * Time: 17:08
 */

namespace Cms\Controller;

use Cms\Controller;
class FeedbackFactoryController extends PublicController{

    public function index()
    {
        $menu = $_GET['menu'];
        $type = $_GET['type'];

        $this->assign("menu", $menu);
        $this->assign("type", $type);
        $case = $menu.'_'.$type;
        switch($case) {
            case 'task_hall_unallocated':
                $Admin = D('Admin');
                $adminArr = $Admin->get_all();
                $this->assign("admin", $adminArr);

                $TaskHall = new TaskHallController();
                $ret = $TaskHall->feedback($type);
                $ret['list'] = $this->get_unallocated_display_list($ret['list']);
                break;
            case 'task_hall_allocated':
                $Admin = D('Admin');
                $adminArr = $Admin->get_all();
                $this->assign("admin", $adminArr);

                $TaskHall = new TaskHallController();
                $ret = $TaskHall->feedback($type);
                $ret['list'] = $this->get_allocated_display_list($ret['list']);
                break;
            case 'task_hall_processed':
                $TaskHall = new TaskHallController();
                $ret = $TaskHall->feedback($type);
                $ret['list'] = $this->get_processed_display_list($ret['list']);
                break;
            case 'admin_task_unprocessed':
                $AdminTask = A('AdminTask');
                $ret = $AdminTask->feedback($type);
                $ret['list'] = $this->get_aid_display_list($ret['list']);
                break;
            default:
                break;
        }
        $this->assign("list", $ret['list']);
        $this->assign("page", $ret['page']);
        $this->display($case);
    }

    /*
     * 获取未分配任务,在未分配任务里查看反馈信息只能走product，因为这个时候还没有
     * 把数据插入到cj_feedback_data
     * todo 都没有处理哪来的最后对话时间
     * */
    protected function get_unallocated_display_list($list)
    {
        $ret = array();
        foreach($list as $value){
            $current = current($value);
            $end     = end($value);
            $temp = array(
                'sender' => $current['sender'],
                'text'   => $end['text'],
                'end_time'=>$end['time'],//反馈时间拿所有记录中最后一条，按时间排序
            );

            array_push($ret,$temp);
        }

        return $ret;
    }

    /*
     * 获取已分配任务数据，这个时候我们在cj_feedback_data表里获取
     * */
    public function get_allocated_display_list($list)
    {
        $ret = array();
        foreach($list as $value){
            $lastItem = end(json_to_array($value['data']));
            $lastFeedbackMessage = $lastItem->text;
            $value['feedback_content'] = $lastFeedbackMessage;
            array_push($ret,$value);
            /*$temp = array(
                'uid'              => $value['uid'],
                'feedback_data_id' => $value['feedback_data_id'],
                'feedback_content' => $lastFeedbackMessage,
                'start_time'       => $value['start_time'],
                'end_time'         => $value['end_time'],
                'allocate_time'    => $value['allocate_time'],
                'allocated_to'     => $value['aid'],
            );

            array_push($ret,$temp);*/
        }

        return $ret;
    }

    /*
     * 获取已分配任务数据，这个时候我们在cj_feedback_data表里获取
     * */
    public function get_processed_display_list($list)
    {
        $ret = array();
        foreach($list as $value){
            $lastItem = end(json_to_array($value['data']));
            $lastFeedbackMessage = $lastItem->text;
            $value['feedback_content'] = $lastFeedbackMessage;
            array_push($ret,$value);
        }

        return $ret;
    }

    /*
     * 获取管理员个人业务
     * */
    public function get_aid_display_list($list)
    {
        $ret = array();
        foreach($list as $value){
            $lastItem = current(json_to_array($value['data']));
            $lastFeedbackMessage = $lastItem->text;
            $value['feedback_content'] = $lastFeedbackMessage;
            array_push($ret,$value);
        }

        return $ret;
    }

    /*
     * 查看用户的反馈信息
     * */
    public function check_user_feedback()
    {
        $menu = $_GET['menu'];
        $type = $_GET['type'];
        $uid  = $_GET['uid'];
        $case = $menu.'_'.$type;
        $feedback_data_id = 0;

        switch($case) {
            case 'task_hall_unallocated':
                $sender  = $_GET['sender'];
                $Product = D('Product');
                $list    = $Product->get_multi_items(array('sender'=>array('EQ',$sender)));
                $list    = $this->get_user_feedback_dialog($list);
                break;
            case 'task_hall_allocated':
                $feedback_data_id = $_GET['feedback_data_id'];
                $Log     = D('FeedbackProcessLog');
                $list    = $Log->get_multi_items(array('uid'=>array('EQ',$uid)));
                if(count($list) == 0)
                    $list = array();
                $list    = $this->get_user_feedback_dialog($list,$feedback_data_id,$uid);
                break;
            case 'task_hall_processed':
                $Log     = D('FeedbackProcessLog');
                $list    = $Log->get_multi_items(array('uid'=>array('EQ',$uid)));//limit 10
                if(count($list) == 0)
                    $list = array();
                $list    = $this->get_user_feedback_dialog($list,$feedback_data_id,$uid);
                break;
            case 'admin_task_unprocessed':
                $feedback_data_id = $_GET['feedback_data_id'];
                //todo 查看历史对话记录
                $Log     = D('FeedbackProcessLog');
                //$list    = $Log->get_multi_items(array('feedback_data_id'=>array('EQ',$feedback_data_id)));
                $list    = $Log->get_multi_items(array('uid'=>array('EQ',$uid)));//limit 10
                if(count($list) == 0)
                    $list = array();
                $list    = $this->get_user_feedback_dialog($list,$feedback_data_id,$uid);

                $Req     = D('FeedbackReqAid');
                $table   = $_SESSION['table']['admin_feedback_request_table'];
                $remark  = $Req->get_single_item(array('feedback_data_id'=>array('EQ',$feedback_data_id)),$table,'remark');
                $this->assign("remark",$remark['remark']);
                break;
            default:
                break;
        }

        $this->assign("feedback_data_id",$feedback_data_id);
        $this->assign("menu",$menu);
        $this->assign("type",$type);
        $this->assign("list",$list);
        $this->display();
    }

    public function get_user_feedback_dialog($list,$feedbackDataId=null,$uid=null,$aid=null)
    {
        $ret = array('dialog'=>array());
        if($uid == null){
            if(count($list)==0)return $ret;
            foreach($list as $value){
                $temp = array(
                    'time'     => $value['time'],
                    'sender'   => '用户'.$value['sender'].':',
                    'texttype' => $value['texttype'],
                    'text'     => $value['text'],
                    'sender_type'=> '1',
                );
                array_push($ret['dialog'],$temp);
            }
            $endItem = end($list);
            $ret['uid'] = $endItem['sender'];
            $ret['end_time'] = $endItem['time'];
        }
        else{
            $ret['dialog'] = $this->get_feedback_data($feedbackDataId,$ret['dialog']);
            $list = get_group($list,'feedback_data_id');
            if(count($list)>0){
                foreach($list as $value){
                    if(count($value)>0){
                        $tempItem = current($value);
                        $logFeedbackDataId = $tempItem['feedback_data_id'];
                        if($logFeedbackDataId!=$feedbackDataId){
                            $ret['dialog'] = $this->get_feedback_data($logFeedbackDataId,$ret['dialog']);
                        }

                        foreach($value as $subValue){
                            $temp = array(
                                'time'     => $subValue['atime'],
                                'sender'   => '管理员'.$subValue['aid'].':',
                                'texttype' => '1',//todo 管理员可能回复的是图片或者其他链接
                                'text'     => $subValue['text'],
                                'sender_type'=> '2',
                            );
                            array_push($ret['dialog'],$temp);
                        }
                    }
                }
                $ret['dialog'] = sort_array($ret['dialog'],'time','asc');
            }
            $ret['uid']      = $uid;
            //$ret['end_time'] = $aidItem['end_time'];
        }

        return $ret;
    }

    public function get_feedback_data($feedbackDataId,$ret)
    {
        $FeedbackData = D('FeedbackData');
        $aidItem      = $FeedbackData->get_single_item(array('id'=>array('EQ',$feedbackDataId)));
        $jsonItems    = json_to_array($aidItem['data']);
        foreach($jsonItems as $value){
            $temp = array(
                'time'     => $value->time,
                'sender'   => '用户'.$value->sender.':',
                'texttype' => $value->texttype,
                'text'     => $value->text,
                'sender_type'=> '1',
            );
            array_push($ret,$temp);
        }

        return $ret;
    }

    public function reply()
    {
        $map   = array();
        $menu  = $_GET['menu'];
        $type  = $_GET['type'];
        $reply = $_GET['reply'];
        $uid   = $_GET['uid'];


        $acceptTime = date('Y-m-d H:i:s',time());
        $feedback_data_id = $_GET['feedback_data_id'];
        $Req = D('FeedbackReqAid');
        $map['feedback_data_id'] = array('EQ',$feedback_data_id);
        $data = array(
            'text' => $reply,
            'atime' => $acceptTime,
        );
        $table = $_SESSION['table']['admin_feedback_request_table'];
        $Req->update_single_item($map,$table,$data);

        $Log = D('FeedbackProcessLog');
        $data = array(
            'aid'=>$_SESSION['authId'],
            'uid'=> $uid,
            'feedback_data_id'=>$feedback_data_id,
            'text'=>$reply,
            'atime'=> $acceptTime,
        );
        $Log->insert_single_item($data);

        $this->redirect('FeedbackFactory/check_user_feedback',array('menu'=>$menu,'type'=>$type,'uid'=>$uid));
    }

    /*
     * 管理员备注
     * */
    public function remark()
    {
        $param = $_GET;
        /*$menu  = $_GET['menu'];
        $type  = $_GET['type'];
        $uid   = $_GET['uid'];*/

        $feedback_data_id = $param['feedback_data_id'];
        $remark  = $param['remark'];
        unset($param['remark']);
        //unset($param['feedback_data_id']);

        $Req     = D('FeedbackReqAid');
        $table   = $_SESSION['table']['admin_feedback_request_table'];
        $map     = array('feedback_data_id'=>array('EQ',$feedback_data_id));
        $data    = array('remark'=>$remark);
        $Req->update_single_item($map,$table,$data);

        //$this->success('备注成功');
        $this->redirect('FeedbackFactory/check_user_feedback',$param);
    }

    /*
     * 给管理员分配任务
     * 在已分配里再分配，html传来的数据包含feedback_data_id和aid-之前分配的aid
     * */
    public function allocate_task_to_admin()
    {
        $param = array();
        $data  = $_POST;
        $menu  = $_GET['menu'];
        $type  = $_GET['type'];

        $admin_id = $this->admin_permission(C('ACTION_ALLOCATE_FEEDBACK'));
        if(!$admin_id)
            $this->error('没有权限');

        if((!isset($_POST['aid']))||($_POST['aid']=='请选择管理员')){
            $this->error('请选择管理员');
        }
        else{
            $aid   = $data['aid'];
            unset($data['aid']);
            $param['certificate'] = 'feedback';
            $param['aid']         = $aid;
            $param['type']        = $type;
        }

        $Allocate = A('AllocateFactory');
        switch($type){
            case 'unallocated':
                $Allocate->allocate($param,$data);
                break;
            case 'allocated':
                foreach($data as $key=>$value){
                    $temp = explode(':',$value);
                    $data[$key] = array(
                        'feedback_data_id' => current($temp),
                        'aid'              => end($temp),
                    );
                }
                $Allocate->allocate($param,$data);
                break;
            default:
                break;
        }

        $this->redirect('FeedbackFactory/index',array('menu'=>$menu,'type'=>$type));
    }

    public function end_dialog()
    {
        $menu = $_GET['menu'];
        $type = $_GET['type'];
        $feedbackDataId = $_GET['feedback_data_id'];
        $AidReqModel = D('FeedbackReqAid');
        $table = $_SESSION['table']['admin_feedback_request_table'];
        $map = array('feedback_data_id'=>array('EQ',$feedbackDataId));
        $data = $AidReqModel->get_single_item($map,$table);
        $AidReqModel->delete_single_item($map,$table);
        $AllocatedReqModel = D('FeedbackReqAllocated');
        $AllocatedReqModel->delete_single_item($map);
        $ProcessedReqModel = D('FeedbackReqProcessed');
        $ProcessedReqModel->insert_single_item(array(
            'aid' => $_SESSION['authId'],
            'feedback_data_id'=>$feedbackDataId,
            'dtime'=>$data['dtime'],
        ));

        $this->redirect('FeedbackFactory/index',array('menu'=>$menu,'type'=>$type));
    }

    /*
     * 自动分配任务
     * */
    public function auto_allocate()
    {

    }
}
