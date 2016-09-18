<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/11
 * Time: 14:20
 */

namespace Cms\Event;

use Cms\Event;
class AdminInfoEvent extends PublicEvent{

    public function get_admin_info()
    {
        $Admin = D('Admin');
        $aid   = $_SESSION['authId'];
        $ret   = $Admin->get_single_item('aid='.$aid);

        if(time($ret['start_working_time'])<time())
            $ret['start_working_time'] = '未打卡';

        return array(
            'list'=>$ret,
        );
    }

    /*
     * 修改密码
     * */
    public function change_password($initPwd,$firPwd,$conPwd)
    {
        $aid        = $_SESSION['authId'];
        $Admin      = D('Admin');
        $adminItem  = $Admin->get_single_item('aid='.$aid,'pwd');

        if($adminItem['pwd'] != md5($initPwd)) {
            $ret = '密码错误,请重新输入';
        }
        elseif(strlen($firPwd)<6){
            $ret = '请输入新的6位密码';
        }
        elseif($firPwd != $conPwd){
            $ret = '两次密码输入不相同,请重新输入';
        }
        else{
            $Admin->update_single_item('aid='.$aid,array('pwd'=>md5($firPwd)));
            $ret = '操作成功';
        }

        return $ret;
    }

    /*
     * 上下班打卡
     * */
    public function check_on_work($case,$userName)
    {
        if($this->check_backend_admin() == 0)
            $this->error('非客服组成员无需打卡');

        $Admin = D('Admin');
        $aid   = $_SESSION['authId'];
        $item  = $Admin->get_single_item('aid='.$aid,'nickname,start_working_time,status');
        $time  = date('Y-m-d H:i:s',time());
        $date  = date('Y-m-d');
        $Log   = D('AdminWorkingLog');

        $saveInAdmin = array();

        if($userName != $item['nickname']){
            $ret = '用户名输入错误';
        }
        elseif($case == 'start_working'){
            //更新admin&working_log表
            $saveInAdmin['start_working_time'] = $time;
            $saveInAdmin['last_login_time']    = $time;
            $saveInAdmin['status']             = 1;//上班状态为1
            $saveInLog                         = array('aid'=>$aid,'close_working_time'=>$time,'time'=>$date);

            $Admin->update_single_item('aid='.$aid,$saveInAdmin);
            $Log->insert_single_item($saveInLog);
            $this->auto_allocated_request();

            $ret = '操作成功';
        }
        elseif($case == 'close_working'){
            $_SESSION['working']               = 0;
            $saveInAdmin['close_working_time'] = $time;
            $saveInAdmin['status']             = 0;//下班状态为0
            $saveInLog                         = array('aid'=>$aid,'close_working_time'=>$time,'time'=>$date);
            $Admin->update_single_item('aid='.$aid,$saveInAdmin);
            $Log->insert_single_item($saveInLog);
            $ret = '操作成功';
        }
        else{
            $ret = 'ERROR,NO CASE RETURN';
        }
        return $ret;
    }

    /*
     * 上班打卡自动分配，自动分配任务,任务只分给客服组，组长不属于客服组，而属于超级管理员，
     * 客服组内的管理员对于：
     *    1、ACTION_CERTIFICATE_VIDEO
     *    2、ACTION_CERTIFICATE_CAR
     *    3、ACTION_CERTIFICATE_ACCUSATION
     * 3个request对应的数据形式如：
     *    1、video array(1=>'1:10010',...,aid=>2)
     *    2、car   array(1=>'1:10010',...,aid=>2)
     * 其中冒号前面是request数据表的自增id，后面是certificate原始数据表自增id
     * */
    public function auto_allocated_request()
    {
        $adminCnt = $this->check_backend_admin();

        if($adminCnt == 0)
            return;

        $avgPercentage = C('AUTO_ALLOCATE_TASK_AVG');
        $this->get_video_avg_request($adminCnt,$avgPercentage);
        $this->get_car_avg_request($adminCnt,$avgPercentage);
        $this->get_accusation_avg_request($adminCnt,$avgPercentage);
    }

    /*
     * 检查管理员是否是客服组成员
     * @return ret 不是客服组成员则返回0，是则返回客服组成员数量
     * */
    public function check_backend_admin()
    {
        $ret = 0;

        //找出客服组group_id
        $AdminGroup = D('AdminGroup');
        $map  = array('name'=>array('EQ','客服组'));
        $temp = $AdminGroup->get_single_item($map);
        $group_id = $temp['admin_group_id'];

        //找出客服组下成员
        $AdminGroupAdmin = D('AdminGroupAdmin');
        $aidArr = $AdminGroupAdmin->get_multi_items('admin_group_id='.$group_id,'aid');
        $aid    = 0;
        foreach($aidArr as $value){
            if($value['aid'] == $_SESSION['authId']){
                $aid = $_SESSION['authId'];
                break;
            }
        }

        if($aid != 0)
            $ret = count($aidArr);

        return $ret;
    }

    /*
     * 检查客服组成员上下班状态，如果是下班状态则不需要在审核一条完成后自动补入新审核需求
     * */
    public function check_admin_status()
    {
        $ret = 0;

        if($this->check_backend_admin() != 0){
            $AdminModel = D('Admin');
            $map = array('aid'=>array('EQ',$_SESSION['authId']));
            $status = $AdminModel->get_single_item($map,'status');
            if($status['status'] == 1)
                $ret = 1;
        }

        return $ret;
    }

    /*
     * 每天第一个上班打卡的人登录时生成平均每人需要分配的任务量
     * */
    public function get_video_avg_request($adminCnt,$avgPercentage)
    {
        $map            = array('operation'=>array('EQ',0));
        $RequestModel   = D('CertificateVideoRequest');
        $requestItem    = $RequestModel->get_multi_items($map);
        $itemCnt        = count($requestItem);
        $itemAvg        = (int)(($itemCnt/$avgPercentage)/$adminCnt);
        $date           = date('Y-m-d',time());
        $saveInRequestCount = array();

        if($itemAvg<1)//平均小于1条就不需要自动分配了
            return;

        //分配视频认证request并插入log
        $RequestCount   = D('RequestCount');
        $tempItem       = $RequestCount->get_single_item('time='.$date);
        if(($tempItem!=null)&&($tempItem['video_allocate_status']==1)){
            $itemAvg = $tempItem['video_request_count'];
        }
        else{
            $saveInRequestCount = array(
                'video_request_count'=>$itemAvg,
                'video_admin_count'=>$adminCnt,
                'video_allocate_status'=>1,
            );
        }

        $requestData    = array();
        for($i=0;$i<$itemAvg;$i++){
            array_push($requestData,$requestItem[$i]['id'].':'.$requestItem[$i]['certificate_video_id']);
        }
        $requestData['aid'] = $_SESSION['authId'];
        $Event              = A('CertificateVideo','Event');
        $Event->allocate_task_to_admin($requestData,$auto=1);

        if($tempItem == null){
            $saveInRequestCount['time'] = $date;
            $RequestCount->insert_single_item($saveInRequestCount);
        }
        else{
            $RequestCount->update_single_item('time='.$date,$saveInRequestCount);
        }

    }

    public function get_car_avg_request($adminCnt,$avgPercentage)
    {
        $map            = array('operation'=>array('EQ',0));
        $RequestModel   = D('CertificateCarRequest');
        $requestItem    = $RequestModel->get_multi_items($map);
        $itemCnt        = count($requestItem);
        $itemAvg        = (int)(($itemCnt/$avgPercentage)/$adminCnt);
        $date           = date('Y-m-d',time());
        $saveInRequestCount = array();

        if($itemAvg<1)//平均小于1条就不需要自动分配了
            return;

        //分配视频认证request并插入log
        $RequestCount   = D('RequestCount');
        $tempItem       = $RequestCount->get_single_item('time='.$date);
        if(($tempItem!=null)&&($tempItem['car_allocate_status']==1)){
            $itemAvg = $tempItem['car_request_count'];
        }
        else{
            $saveInRequestCount = array(
                'car_request_count'=>$itemAvg,
                'car_admin_count'=>$adminCnt,
                'car_allocate_status'=>1,
            );
        }

        $requestData    = array();
        for($i=0;$i<$itemAvg;$i++){
            array_push($requestData,$requestItem[$i]['id'].':'.$requestItem[$i]['certificate_car_id']);
        }
        $requestData['aid'] = $_SESSION['authId'];
        $Event              = A('CertificateCar','Event');
        $Event->allocate_task_to_admin($requestData,$auto=1);

        if($tempItem == null){
            $saveInRequestCount['time'] = $date;
            $RequestCount->insert_single_item($saveInRequestCount);
        }
        else{
            $RequestCount->update_single_item('time='.$date,$saveInRequestCount);
        }

    }

    public function get_accusation_avg_request($adminCnt,$avgPercentage)
    {
        $map            = array('operation'=>array('EQ',0));
        $RequestModel   = D('AccusationRequest');
        $requestItem    = $RequestModel->get_multi_items($map);
        $itemCnt        = count($requestItem);
        $itemAvg        = (int)(($itemCnt/$avgPercentage)/$adminCnt);
        $date           = date('Y-m-d',time());
        $saveInRequestCount = array();

        if($itemAvg<1)//平均小于1条就不需要自动分配了
            return;

        //分配视频认证request并插入log
        $RequestCount   = D('RequestCount');
        $tempItem       = $RequestCount->get_single_item('time='.$date);
        if(($tempItem!=null)&&($tempItem['accusation_allocate_status']==1)){
            $itemAvg = $tempItem['accusation_request_count'];
        }
        else{
            $saveInRequestCount = array(
                'accusation_request_count'=>$itemAvg,
                'accusation_admin_count'=>$adminCnt,
                'accusation_allocate_status'=>1,
            );
        }

        $requestData    = array();
        for($i=0;$i<$itemAvg;$i++){
            array_push($requestData,$requestItem[$i]['id'].':'.$requestItem[$i]['accusation_id'].':'.$requestItem[$i]['uid'].':'.$requestItem[$i]['offender_uid']);
        }
        $requestData['aid'] = $_SESSION['authId'];
        $Event              = A('Accusation','Event');
        $Event->allocate_task_to_admin($requestData,$auto=1);

        if($tempItem == null){
            $saveInRequestCount['time'] = $date;
            $RequestCount->insert_single_item($saveInRequestCount);
        }
        else{
            $RequestCount->update_single_item('time='.$date,$saveInRequestCount);
        }

    }
}
