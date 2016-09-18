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
class CertificateCarEvent extends PublicEvent{

    public function get_request_data($case,$map,$uid='')
    {
        $Model   = D('CertificateCarRequest');
        $itemsPerPage = C('ITEMS_PER_PAGE');
        import("THINK.Page");
        switch($case) {
            case 'task_hall_unallocated':
                //$map['operation'] = array('EQ',0);
                $map = ' cr.operation = 0 ';
                break;
            case 'task_hall_allocated':
                // $map['operation'] = array('EQ',1);
                // $map['status']    = array('EQ',0);
                $map = ' cr.operation = 1 AND cr.status = 0 ';
                break;
            case 'task_hall_processed'://已认证和取消认证
                //$map['operation'] = array('IN',array(2,3));
                $map = ' cr.operation IN(2,3) ';
                break;
            case 'admin_task_unprocessed':
                // $map['operation'] = array('EQ',1);
                // $map['aid']       = array('EQ',$_SESSION['authId']);
                // $map['status']    = array('EQ',0);
                $map = ' cr.operation = 1 AND cr.status = 0 AND cr.aid = '.$_SESSION['authId'];
                break;
            default:
                break;
        }
        if($uid!='')
            $map = " cr.uid LIKE '%".$uid."%' ";
            //$map['uid'] = array('like','%'.$uid.'%');

        $count = $Model->task_hall_count($map);

        $Page  = new \Think\Page($count, $itemsPerPage);
        $ret   = $Model->task_hall_list($map, $Page,$case);
        
        //printr($ret);
        $show = $Page->show();
        //$ret  = unixToTime($ret,'sub_time');

        return array(
            'list'=>$ret,
            'page'=>$show,
        );
    }


    /*
     * 任务大厅分配任务
     * @param type string unallocated/allocated
     * @param data array(1=>'1:1',...,aid=>'1') cj_certificate_car_request.id:cj_certificate_car.id
     * */
    public function allocate_task_to_admin($data,$auto=null)
    {
        if($auto == null){
            $admin_id = $this->admin_permission(C('ACTION_ALLOCATE_CERTIFICATE_CAR'));
            if(!$admin_id){
                $this->error_message = '没有权限';
                $this->error('没有权限');
            }
            if((!isset($_POST['aid']))||($_POST['aid']=='请选择管理员')){
                $this->error_message = '请选择管理员';
                $this->error('请选择管理员');
            }
        }

        $aid  = $data['aid'];
        $time = date('Y-m-d H:i:s',time());
        $in   = array();
        unset($data['aid']);

        foreach($data as $value){
            $temp = explode(':',$value);
            $id   = current($temp);
            $certificate_car_id = end($temp);
            array_push($in,$id);
            $logItems[] = array('certificate_car_id'=>$certificate_car_id,'operation'=>1,'aid'=>$aid,'allocate_time'=>$time);
        }
        $dataItem = array('operation' => 1,'aid' => $aid,'allocate_time' => $time);
        $map = array('id'=>array('IN',$in));
        $DataModel = D('CertificateCarRequest');
        $DataModel->update_multi_items($map,$dataItem);

        $LogModel = D('CertificateCarRequestLog');
        $LogModel->insert_multi_items($logItems);
    }

    public function auto_allocate_single_request()
    {
        $Model = D('CertificateCarRequest');
        $map   = array('operation'=>array('EQ',0));
        $items = $Model->get_limit_items($map,1,'id,certificate_car_id');
        if(count($items)>0){
            $item  = current($items);
            $data  = array();
            $data['aid'] = $_SESSION['authId'];
            array_push($data,$item['id'].':'.$item['certificate_car_id']);
            $this->allocate_task_to_admin($data,$auto=1);
        }
    }

    public function show_single_request($case,$id,$certificateCarId)
    {
        switch($case)
        {
            case 'task_hall_processed':
                $admin_id = $this->admin_permission(C('ACTION_CERTIFICATE_CAR_ROOT'));
                break;
            case 'admin_task_unprocessed':
                $admin_id = $this->admin_permission(C('ACTION_CERTIFICATE_CAR'));
                break;
            default:
                break;
        }
        if(!$admin_id){
            $this->error('没有权限');
            $this->error_message = '没有权限';
        }

        $Model = D('CertificateCar');
        $map['id'] = array('EQ',$certificateCarId);
        $field     = 'id as certificate_car_id,uid,status,p1,p2,car_brand_id,car_model_id,replace_content';
        $ret = $Model->get_single_item($map,$field);
        if($ret['status']==1 && $ret['replace_content'] !=''){
            $newret = json_decode($ret['replace_content'],true);
            $ret['car_brand_id']  = $newret['car_brand_id'];
            $ret['car_model_id']  = $newret['car_model_id'];
            $ret['p1']            = $newret['p1'];
            $ret['p2']            = $newret['p2'];
        }

        $User = D('User');
        $map = array('uid='.$ret['uid']);
        $userInfo = $User->get_single_item($map,'status');
        $ret['user_status'] = $userInfo['status'];

        $Brand = D('CarBrand');
        $temp  = $Brand->where('id='.$ret['car_brand_id'])->field('name')->find();
        $ret['car_brand_name'] = $temp['name'];
        $brand = $Brand->get_all_car_brand();
        $CarEvent = A('Car','Event');
        $brand = $CarEvent->sort_list($brand,$ret['car_brand_id']);

        $Model = D('CarModel');
        $temp  = $Model->where('id='.$ret['car_model_id'])->field('name')->find();
        $ret['car_model_name'] = $temp['name'];
        $CarEvent = A('Car','Event');
        $ret['all_car_model']  = $CarEvent->car_model_display($ret['car_brand_id'],$ret['car_model_id']);
        $ret['show_car_model'] = 1;

        $ret['id'] = $id;

        return array('brand'=>$brand,'list'=>$ret);
    }

    /*
     * 提交认证
     * */
    public function submit_certificate()
    {
        $id                   = $_POST['id'];//cj_certificate_car_request.id
        $uid                  = $_POST['uid'];
        $certificate_car_id   = $_POST['certificate_car_id'];
        $certificateStatusNum = C('STATE_CERTIFICATE_CAR_STATUS_NUM');
        $passState            = $certificateStatusNum[$_POST['status']];
        $car_model_name       = true;
        $myquite              = str_replace(' ','',$_POST['myquite']);
        $carmodels            = D('CertificateCar');
        $carinfo              = $carmodels->get_single_item('uid = '.$uid);


        if(!empty($_POST['new_car_model'])){
            $car_model_name = false;
        }
        elseif(!empty($_POST['model'])){
            $car_model = $_POST['model'];
        }
        else{
            if( $passState == 1)//如果不通过则不需要选车型
                $this->error('请输入车型');
        }

        if( $passState !=1 && $carinfo['status']!=1 && $carinfo['replace_content']!='' ){

            $vimap['replace_content'] = '';
            $carmodels->update_single_item('uid ='.$uid,$vimap);
            D('CertificateCarRequest')->delete_single_item('certificate_car_id = '.$carinfo['id']);
            //发送系统消息
            $Message = new MessageController();
            $Message->send_system_message($uid,'certificate_car','failed',$_POST['status'],'',$myquite);
            $this->del_r_user_info($uid);
            return '操作成功';
            exit;

        }

        if($passState == 1){
            $car_brand = $_POST['brand'];
            $temp       = explode(':',$car_brand);
            $saveInCertificateCar['car_brand_id']     = current($temp);
            $saveInCertificateCar['car_brand_name']   = end($temp);

            if($car_model_name){
                $temp       = explode(':',$car_model);
                $saveInCertificateCar['car_model_id']     = current($temp);
                $saveInCertificateCar['car_model_name']   = end($temp);
            }
            else{
                $temp      = explode(':',$_POST['brand']);
                $saveNewCarModel['brand_id']     = $saveInCertificateCar['car_brand_id'];
                $saveNewCarModel['name']         = $_POST['new_car_model'];
            }

            if(!empty($saveNewCarModel)){
                $Model = D('CarModel');
                $saveInCertificateCar['car_model_id']     = $Model->data($saveNewCarModel)->add();
                $saveInCertificateCar['car_model_name']   = $saveNewCarModel['name'];
            }
        }

        //保存cj_certificate_car_request
        $Request = D('CertificateCarRequest');
        $saveInRequest['certificate_car_id']   = $certificate_car_id;
        $saveInRequest['operation']            = 2;//已处理
        $saveInRequest['aid']                  = $_SESSION['authId'];//$admin_id;
        $saveInRequest['status']               = $passState;
        $saveInRequest['certificate_time']     = date('Y-m-d H:i:s',time());
        $saveInRequest['result']               = $_POST['status'];
        $saveInRequest['remark']               = $_POST['remark'];
        $saveInRequest['uid']                  = $uid;
        $saveInRequest['myquite']              = $myquite;
        $temp = $Request->get_single_item('id='.$id,'allocate_time');
        $saveInRequest['allocate_time']        = $temp['allocate_time'];//
        $Request->update_single_item('id='.$id,$saveInRequest);



        //保存cj_certificate_car
        $saveInCertificateCar['status']        = $passState;
        if($passState == 1){
            $saveInCertificateCar['pass_time'] = strtotime($saveInRequest['certificate_time']);
            if($carinfo['status']==1 && $carinfo['replace_content']!='' ){
                $newcarp = json_decode($carinfo['replace_content'],true);
                $saveInCertificateCar['p1'] = $newcarp['p1'];
                $saveInCertificateCar['p2'] = $newcarp['p2'];
                $saveInCertificateCar['replace_content'] = '';
                $this->aliydel('certificate',$carinfo['p1']);
                $this->aliydel('certificate',$carinfo['p2']);
            }
        }

        $Car = $carmodels;
        $Car->update_single_item(array('id'=>array('eq',$certificate_car_id)),$saveInCertificateCar);


        //保存cj_user
        $saveInUser['car_verify']              = $passState;
        $User  = D('User');
        $User->update_single_item(array('uid'=>array('eq',$uid)),$saveInUser);

        //保存cj_certificate_car_request_log
        $addLog = $saveInRequest;
        $Log   = D('CertificateCarRequestLog');
        $Log->insert_single_item($addLog);

        //TODO 用redis记录用户视频认证状态

        //在cj_location表里修改car_verify字段
        $Location = D('Location');
        $saveInLocation = array('car_verify'=>$passState);
        $Location->update_single_item(array('uid'=>array('EQ',$uid)),$saveInLocation);

        //删除php server用户基本信息和所有信息
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_user_info($uid);

        //发送系统消息
        $Message = new MessageController();
        if($addLog['status'] == 1)
            $messageType = 'pass';
        else
            $messageType = 'failed';
        $Message->send_system_message($uid,'certificate_car',$messageType,$_POST['status'],'',$myquite);

        //自动补入一条request
        $AdminInfoEvent = A('AdminInfo','Event');
        if($AdminInfoEvent->check_admin_status() == 1)
            $this->auto_allocate_single_request();

        return '操作成功';
    }

    /*
     * 取消认证的时候需要：
     * 1、修改cj_user表的video_verify字段;
     * 2、修改cj_certificate_video的status字段
     * 3、插入cj_certificate_video_request_log;
     * 4、修改cj_certificate_video_request该uid下的status,remark,
     *    前提是cj_certificate_video_request还有该uid的记录
     * */
    public function undo_certificate($uid)
    {
        $admin_id = $this->admin_permission(C('ACTION_UNDO_CERTIFICATE_CAR'));
        if(!$admin_id)
            $this->error('没有权限');

        $map = array('uid'=>array('EQ',$uid));

        $User = D('User');
        $state = 2;//取消认证时将状态置为认证失败
        $saveInUser = array('car_verify'=>$state);
        $User->update_single_item($map,$saveInUser);

        $Certificate = D('CertificateCar');
        $saveInCertificateCar = array('status'=>$state,'car_brand_name'=>'','car_model_name'=>'');
        //todo 如果已经通过认证，用户在cj_certificate_car里car_brand_id,car_model_id已被修改则不动修改数据
        $Certificate->update_latest_item($uid,$saveInCertificateCar);

        $Log  = D('CertificateCarRequestLog');
        $addLog['operation']            = 3;//取消认证
        $addLog['aid']                  = $_SESSION['authId'];//$admin_id;
        $addLog['status']               = 2;
        $addLog['certificate_time']     = date('Y-m-d H:i:s',time());
        $addLog['result']               = '取消认证';
        $addLog['uid']                  = $uid;
        $Log->insert_single_item($addLog);

        $Request = D('CertificateCarRequest');
        $saveInRequest = $addLog;
        $temp = $Certificate->get_multi_items('uid='.$uid,'id');
        foreach($temp as $value){
            $in[] = $value['id'];
        }
        $map = array('certificate_car_id'=>array('IN',$in));
        $Request->update_multi_items($map,$saveInRequest);

        //在cj_location表里修改car_verify字段
        $Location = D('Location');
        $saveInLocation = array('car_verify'=>$state);
        $Location->update_single_item(array('uid'=>array('EQ',$uid)),$saveInLocation);

        //删除php server用户基本信息和所有信息
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_user_info($uid);
    }

    public function confirm_delete_request($list)
    {
        $admin_id = $this->admin_permission(C('ACTION_CONFIRM_CERTIFICATE_CAR_PROCESSED'));
        if(!$admin_id)
            $this->error('没有权限');

        $Req     = D('CertificateCarRequest');
        $map['id'] = array('IN',$list);
        $Req->delete_multi_items($map,$list);
    }

    // 根据年月获取车类审核信息
    public function getdatecar($date,$aid=''){
        $video = D('CertificateCarRequestLog');
        /*$map['status']  = 1;// 通过
        $no['status']  = array('gt',1); // 不通过  */ 
        if($aid){
            $awhere  =  ' AND aid = '.$aid;
        }     

        $sql = "SELECT  date_format(certificate_time,'%Y%m%d') AS days,count(id) AS num,date_format(certificate_time,'%d') AS d
                FROM cj_certificate_car_request_log 
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

            $day['count'][$i] =  $day['pass'][$i] + $day['stop'][$i];
        }
        return $day;
    }

    // 操作日志
    public function operationlog($data){
        $map['status']           = array('gt',0);
        ($data['aid']>0)?$map['aid'] = $data['aid']:'';
        ($data['uid']>0)?$map['uid'] = $data['uid']:'';
        $map['certificate_time'] = array('between',array($data['s_date'],$data['t_date']));
        
        $itemsPerPage = C('ITEMS_PER_PAGE');
        $count = D('CertificateCarRequestLog')->where($map)->count();
        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();

        $vlist = D('CertificateCarRequestLog')->field('certificate_time,uid,aid,result,remark')->where($map)->limit($Page->firstRow,$Page->listRows)->select();
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
