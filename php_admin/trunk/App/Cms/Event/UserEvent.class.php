<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/9
 * Time: 11:35
 */

namespace Cms\Event;

use Cms\Controller\MessageController;
use Think\Cache\Driver\Redis;
use Cms\Event;
class UserEvent extends PublicEvent{

    public function get_account_manage_user_list($case,$map)
    {
        $User = D('User');
        $itemsPerPage = C('ITEMS_PER_PAGE');

        switch($case)
        {
            case 'account_manage/all_user': //所有用户
                //$map['status'] = array('EQ',);
                break;
            case 'account_manage/test_user':   //测试帐号
                $map['status'] = 1;
                break;
            case 'account_manage/push_user':   //地推帐号
                $map['status'] = 2;
                break;
            case 'account_manage/virtual':    //虚拟帐号
                $map['status'] = 3;
                break;
            default:
                break;
        }

        $count = $User->get_account_manage_user_count($map);

        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();
        $list = $User->get_account_manage_user_list($map, $Page);
        //$list = $this->process_account_manage_user_lists($list);


        $userCount = $User->get_account_manage_user_count();
        $pushUserCount = $User->get_account_manage_user_count(array('status'=>array('EQ',3)));
        $carVerifyCount = $User->get_car_pass_count();
        $videoVerifyCount = $User->get_video_pass_count();

        return array(
            'list'=>$list,
            'show'=>$show,
            'userCount'=>$userCount,
            'carVerifyCount'=>$carVerifyCount,
            'videoVerifyCount'=>$videoVerifyCount,
            'pushUserCount'=>$pushUserCount,
        );
    }

    public function get_content_manage_user_list($case,$map)
    {
        $Request = D('UserInfoModifyRequest');
        $itemsPerPage = C('ITEMS_PER_PAGE');

        switch($case)
        {
            case 'content_manage/unprocessed': //未审核用户
                //$map['operation'] = array('EQ',0);
                $map = 'AND operation=0';
                break;
            case 'content_manage/processed':   //已审核用户
                //$map['operation'] = array('EGT',1);
                $map = 'AND operation>0';
                break;
            default:
                break;
        }

        $count = $Request->get_content_manage_count($map);

        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();

        $list = $Request->get_content_manage_list($map, $Page);
        //$list = $this->process_content_manage_user_list($list);

        return array(
            'list'=>$list,
            'show'=>$show,
        );
    }

    protected function process_content_manage_user_list($data)
    {
        $fieldArr = array(
            'nickname' => '昵称',
            'birthday' => '生日',
            'phone'=> '电话',
            'tags' => '标签',
            'height'=>'身高',
            'hometown'=>'家乡',
            'signature' => '个性签名',
            'job' => '职业',
            'movie' => '电影',
            'weekend' => '周末',
            'cooking' => '做菜',
            'travel' => '旅行',
            'restaurant' => '餐馆',
            'sport' => '运动',
        );
        for($i=0;$i<count($data);$i++){
            $data[$i]['field_name'] = $fieldArr[$data[$i]['field_name']];
        }

        return $data;
    }

    /*
     * 查看帐号管理内用户个人信息
     * */
    public function get_account_manage_user_info($uid)
    {
        $map['uid'] = array('EQ',$uid);

        $User = D('User');
        //$userInfo = $User->get_account_manage_single_user_info($map);
        $userInfo = $User->newget_account_manage_single_user_info($uid);

        //$userInfo = unit_to_time_single($userInfo,'reg_time');
        //$userInfo = sex_to_text_single($userInfo,'sex');
        //$userInfo = dblocking_time_to_state($userInfo,'dblocking_time');
        //$userInfo = certificate_to_state($userInfo,'car_verify');
        //$userInfo = certificate_to_state($userInfo,'video_verify');


        if($userInfo['server_version']=='1' || $userInfo['status'] == 3 ){ // 头像版本
            $head     = json_decode($userInfo['album'],true);
            $this->assign('head1',$head);
        }else{
            $headImg  = json_to_array($userInfo['head_images']);
            $head     = $this->show_head_img($headImg);
            if (count($head) > 1) {
                $this->assign("head1", $head[0]);
                $this->assign("head2", $head[1]);
            } else {
                $this->assign("head1", $head[0]);
            }
        }

        //获取聊天记录,如果是从举报进入页面则查看举报与被举报人的对话，如果不是的话则显示被举报人与所有人的对话
        /*$Chat         = D('ChatLog');
        if(isset($_GET['accusation'])){
            $accusationRequest = array('id'=>$_GET['request_id'],'process'=>'yes');
            $chatLog  = $Chat->get_dialog($uid,$_GET['reporter'],C('CHAT_LOG_REVIEW_TIME'));
        }
        else{
            $accusationRequest = array('id'=>$_GET['request_id'],'process'=>'no');
            $chatLog  = $Chat->get_user_chat_log($uid,C('CHAT_LOG_REVIEW_TIME'));
        }*/


        /*//获取举报及封禁记录
        $Accusation     = D('AccusationRequestLog');
        $map            = array(
            'offender_uid'=>array('EQ',$uid),
            'operation'=>array('GT',1),
        );
        $accusationLog  = $Accusation->where($map)->order('id DESC')->select();
        $ClosureEvent   = A('Closure','Event');
        $accusationLog  = $ClosureEvent->process_closure_log($accusationLog);*/


        /*// 操作记录开始
        $CertificateVideoLog = D('CertificateVideoRequestLog');
        $map = array('uid'=>array('EQ',$uid),'operation'=>array('EGT',2));
        $field = 'uid,aid,allocate_time,certificate_time,result,remark';
        $videoLog = $CertificateVideoLog->get_info_with_aid_nickname($map,0,$field);

        $CertificateCarLog = D('CertificateCarRequestLog');
        $map = array('uid'=>array('EQ',$uid),'operation'=>array('EGT',2));
        $field = 'uid,aid,allocate_time,certificate_time,result,remark';
        $carLog = $CertificateCarLog->get_info_with_aid_nickname($map, 0,$field);

        // 警告记录
        $tmodel = D('SendtextLog');
        $sendtext['count']  = $tmodel->field('id')->where("uid = $uid ")->count();
        $sendtext['list']   = $tmodel->get_multi_items("uid =   $uid ");
        // 材料记录图片
        $imglog     = D('HeadImageModifyRequestLog');
        $imglistlog = $imglog->get_multi_items(" uid = {$uid} AND reason >0 ",'reason,aid,pass_time');
        // 材料记录文字
        $textlog      = D('UserInfoModifyRequestLog');
        $textlistlog  = $textlog->field('reason,aid,pass_time,field_value')->where(" uid = {$uid} AND reason > 0 ")->order('pass_time desc')->select();
        //操作记录结束*/

        $CertificateVideo = D('CertificateVideo');
        $map = 'uid='.$uid;
        $cVideo = $CertificateVideo->get_single_item($map,'');
        //$cVideo = $this->certificate_state_text($cVideo,'video','status');
        $CertificateCar = D('CertificateCar');
        $map = 'uid='.$uid;
        $cCar   = $CertificateCar->get_single_item($map);
        //$cCar = $this->certificate_state_text($cCar,'car','status');


        // 认证
        $authentication = D('UserTag')->authentication($uid);
        //printr($authentication);

        //经纬度
        if($userInfo['status']==3){
            $location = D('Location')->get_multi_items('uid = '.$uid,'lat,lng');
        }


        return array(
            'user'=>$userInfo,
            /*'chat'=>$chatLog,
            'accusation'=>$accusationLog,
            'car'=>$carLog,
            'video'=>$videoLog,
            'sendtext'=>$sendtext,
            'imglistlog'=>$imglistlog,
            'textlistlog'=>$textlistlog,*/
            'ccar'=>$cCar,
            'cvideo'=>$cVideo,
            'location'=>$location[0],
            'authentication'=>$authentication,
            'accusation_request'=>$accusationRequest,
        );
    }

    // 用户操作记录
    public function getcarvideolog($uid){

        $CertificateVideoLog = D('CertificateVideoRequestLog');
        $map = array('uid'=>array('EQ',$uid),'operation'=>array('EGT',2));
        $field = 'uid,aid,allocate_time,certificate_time,result,remark';
        $videoLog = $CertificateVideoLog->get_info_with_aid_nickname($map,0,$field);

        $CertificateCarLog = D('CertificateCarRequestLog');
        $map = array('uid'=>array('EQ',$uid),'operation'=>array('EGT',2));
        $field = 'uid,aid,allocate_time,certificate_time,result,remark';
        $carLog = $CertificateCarLog->get_info_with_aid_nickname($map, 0,$field);

        // 警告记录
        $tmodel = D('SendtextLog');
        $sendtext['count']  = $tmodel->field('id')->where("uid = $uid ")->count();
        $sendtext['list']   = $tmodel->get_multi_items("uid =   $uid ");
        // 材料记录图片
        $imglog     = D('HeadImageModifyRequestLog');
        $imglistlog = $imglog->get_multi_items(" uid = {$uid} AND reason >0 ",'reason,aid,pass_time');
        // 材料记录文字
        $textlog      = D('UserInfoModifyRequestLog');
        $textlistlog  = $textlog->field('reason,aid,pass_time,field_value')->where(" uid = {$uid} AND reason > 0 ")->order('pass_time desc')->select();

        // 标签log
        $taglogsql  = " SELECT t.*,a.nickname
                        FROM cj_admin_log.cj_tag_log as t
                        LEFT JOIN cj_admin.cj_admin as a ON t.aid = a.aid
                        WHERE t.uid = {$uid} AND t.certificate > 0 ";
        $taglogchar = D('User')->query($taglogsql);

        // 图片log
        $surgingsql  = " SELECT t.*,a.nickname
                        FROM      cj_admin_log.cj_surging_log as t
                        LEFT JOIN cj_admin.cj_admin as a ON t.aid = a.aid
                        WHERE t.uid = {$uid}  ";
        $surginglog  = D('User')->query($surgingsql);

        return array(
            'car'=>$carLog,
            'video'=>$videoLog,
            'sendtext'=>$sendtext,
            'imglistlog'=>$imglistlog,
            'textlistlog'=>$textlistlog,
            'tagloglist'=>$taglogchar,
            'surginglog'=>$surginglog,
        );

    }

    public function accusationrequestlog($uid){

        //获取举报及封禁记录
        $Accusation     = D('AccusationRequestLog');
        $map            = array(
            'offender_uid'=>array('EQ',$uid),
            'operation'=>array('GT',1),
        );
        $accusationLog  = $Accusation->where($map)->order('id DESC')->select();
        return A('Closure','Event')->process_closure_log($accusationLog);

    }


    /*
     * 文字审核
     * */
    public function modify_single_field($id,$value)
    {
        $admin_id = $this->admin_permission(C('ACTION_USER_INFO_CERTIFICATE'));
        if(!$admin_id)
            $this->error('没有权限');

        $Request = D('UserInfoModifyRequest');
        $item    = $Request->get_single_item('id='.$id);
        $field   = $item['field_name'];

        $User    = D('User');
        $User->update_single_item('uid='.$item['uid'],array($field=>$value));

        if($value == $item['field_value']){
            $result = '已审核';
            $operation = 1;
        }
        else{
            $result = '已修改';
            $operation = 2;
        }

        $saveInRequest = array(
            'aid'=>$_SESSION['authId'],
            'operation'=> $operation,
            'pass_time'=>date('Y-m-d H:i:s',time()),
            'field_value'=>$value,
            'result'=>$result,
        );
        $Request->update_single_item('id='.$id,$saveInRequest);

        $saveInLog = $saveInRequest;
        $saveInLog['uid'] = $item['uid'];
        $saveInLog['field_name'] = $item['field_name'];
        $saveInLog['field_value'] = $item['field_value'];
        $Log = D('UserInfoModifyRequestLog');
        $Log->insert_single_item($saveInLog);

        //如果修改了用户资料，则需要发送系统消息
        if($operation == 2){
            $Message = new MessageController();
            $Message->send_system_message($item['uid'],'user_info','modify','');
        }

        $this->delete_items_same_uid_field($item['uid'],$item['field_name'],$item['sub_time']);
    }

    /*
     * 删除同一人同一字段除最大提交时间外的其他记录
     * */
    public function delete_items_same_uid_field($uid,$field,$sub_time)
    {
        $Model = D('UserInfoModifyRequest');
        $map = array(
            'uid'=>array('EQ',$uid),
            'field_name'=>array('EQ',$field),
            'sub_time'=>array('LT',$sub_time),
        );
        $Model->delete_multi_items($map);
    }

    // 修改文字审核字段
    public function worldsehe($data){

        $model = D('UserInfoModifyRequest');

        // 获取用户最近提交的一次信息记录（一条记录可能有多个）

        $where = 'uid ='.$data['uid'].' AND field_name = "'.$data['field'].'" AND field_value != ""';
        $map   = $model->where($where)->order('id DESC')->limit('0,1')->find();
        $mapid['id'] = $map['id'];


        if($mapid['id']!=''){
            $das['field_value'] = '　';
            $das['reason']      = $data['reason'];
            $model->update_single_item($mapid,$das);
        }

        // 获取信息
        $info = $model->get_single_item($mapid);
        if(!empty($info)){
            // 写入log日志
            unset($info['id']);
            $info['aid']         =  $_SESSION['authId'];
            $info['pass_time']   =  date('Y-m-d H:i:s');
            $info['field_value'] =  trim($data['title']);
            $info['reason']      =  $data['reason'];
            D('UserInfoModifyRequestLog')->insert_single_item($info);
            $userdata[$info['field_name']] = ' ';
        }else{
            // 写入log日志
            $loginfo['uid']         =  $data['uid'];
            $loginfo['aid']         =  $_SESSION['authId'];
            $loginfo['reason']      =  $data['reason'];
            $loginfo['operation']   =  3;
            $loginfo['pass_time']   =  date('Y-m-d H:i:s');
            $loginfo['field_name']  =  trim($data['field']);
            $loginfo['field_value'] =  trim($data['title']);
            D('UserInfoModifyRequestLog')->insert_single_item($loginfo);

            $info['uid'] = $data['uid'];
            $userdata[$data['field']] = ' ';
        }


        // 修改user 表中的信息
        $umap['uid'] = $info['uid'];
        D('User')->update_single_item($umap,$userdata);

        //删除php server用户基本信息和所有信息
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_user_info($info['uid']);

        //确认删除要给用户发送系统消息
        $Message = new MessageController();
        $Message->send_system_message($data['uid'],'user_info','modify',trim($data['f_name']));
    }

    /*
     * 文字审核全部通过
     * */
    public function confirm_all_pass($list=array())
    {
        $admin_id = $this->admin_permission(C('ACTION_USER_INFO_CERTIFICATE_ALL_PASS'));
        if(!$admin_id)
            $this->error('没有权限');

        $saveInRequest = array(
            'aid'=>$_SESSION['authId'],
            'operation'=>1,
            'pass_time'=>date('Y-m-d H:i:s',time()),
            'result'=>'已审核',
        );

        $Request = D('UserInfoModifyRequest');
        $map     = array('id'=>array('IN',$list));
        $requestItems = $Request->get_multi_items($map);
        $Request->update_multi_items($map,$saveInRequest);

        foreach($list as $value){
            $item    = $Request->get_single_item('id='.$value);
            unset($item['id']);
            $logItems[] = $item;
        }
        $Log = D('UserInfoModifyRequestLog');
        $Log->insert_multi_items($logItems);

        //删除多余的request
        foreach($requestItems as $value){
            $this->delete_items_same_uid_field($value['uid'],$value['field_name'],$value['sub_time']);
        }
        return false;
    }

    /*
     * 文字审核确认结束
     * */
    public function confirm_delete_request($list=array())
    {
        $admin_id = $this->admin_permission(C('ACTION_USER_INFO_REQUEST_DELETE'));
        if(!$admin_id)
            $this->error('没有权限');

        $Request = D('UserInfoModifyRequest');

        foreach($list as $value){
            $item    = $Request->get_single_item('id='.$value);
            $item['operation'] = 3;
            $item['result'] = '已并删除request';
            unset($item['id']);
            $logItems[] = $item;
        }
        $Log = D('UserInfoModifyRequestLog');
        $Log->insert_multi_items($logItems);

        $map     = array('id'=>array('IN',$list));
        $Request->delete_multi_items($map);
    }

    /*
     * 将用户帐号修改成地推帐号
     * */
    public function add_to_push_account($uid)
    {
        $this->admin_permission(C('ACTION_ADD_PUSH_USER'));
        $User = D('User');
        $User->update_single_item('uid='.$uid,array('status'=>2));
    }

    /*
     * 取消地推帐号
     * */
    public function undo_push_account($uid)
    {
        $this->admin_permission(C('ACTION_UNDO_PUSH_USER'));
        $User = D('User');
        $User->update_single_item('uid='.$uid,array('status'=>0));
    }

    /*
     * 将用户帐号修改成测试帐号
     * */
    public function add_to_test_account($uid)
    {
        //todo 权限管理
        $User = D('User');
        $temp = $User->get_single_item('uid='.$uid,'uid');
        if($temp == null)
            $this->error('没有该用户，请重新输入');

        $User->update_single_item('uid='.$uid,array('status'=>1));
    }

    /*
     * 清除用户缓存，只能对测试帐号使用
     * 1、删除redis fullinfo baseinfo
     * 2、删除location数据
     * */
    public function clear_user_cache($uid)
    {
        $Redis = D('PhpServerRedis');
        $Redis->delete_user_info($uid);

        $Location = D('Location');
        $Location->delete_single_item('uid='.$uid);
    }

    public function change_user_state($uid,$type)
    {
        $map        = array();
        $User       = D('User');
        $map['uid'] = array('EQ',$uid);
        switch($type){
            case 'test'://将帐号置为测试帐号时，清除帐号缓存
                $this->clear_user_cache($uid);
                $data = array('status'=>1);
                break;
            case 'normal':
                $data = array('status'=>0);
                break;
            case 'push':
                $data = array('status'=>2);
                break;
            default:
                break;
        }
        $User->update_single_item($map,$data);
        // 写入操作日志
        $lomap['type'] = $data['status'];
        $lomap['uid']  = $uid;
        $lomap['time'] = time();
        $lomap['aid']  = $_SESSION['authId'];
        D('NumberOperationLog')->insert_single_item($lomap);

    }

    /*
     *
     * */
    public function undo_test_account($uid)
    {
        //todo 权限管理
        $User = D('User');
        $User->update_single_item('uid='.$uid,array('status'=>0));
    }

    //一组图片超过8张则只显示8张,分两组输出
    protected function show_head_img($userImg=array())
    {
        $ret = array();
        $cnt=count($userImg);
        if ($cnt>4) {
            $ret[0] = array_slice($userImg, 0, 4);
            $ret[1] = array_slice($userImg, 4, $cnt - 4);
        } else
            $ret[0] = $userImg;

        return $ret;
    }

    /*
     * 认证状态文字转换
     * */
    protected function certificate_state_text($data=array(),$type='',$field='')
    {
        if(!isset($data[$field])){
            $data[$field] = -1;
            $data[$type] = '未提交';
        }
        elseif($data[$field] == -1)
            $data[$type] = '未提交';
        elseif($data[$field] == 0)
            $data[$type] = '等待审核';
        elseif($data[$field] == 1)
            $data[$type] = '已通过';
        elseif($data[$field] == 2)
            $data[$type] = '未通过';
        else
            $this->error('错误的认证状态');

        return $data;
    }

    /*
     * 处理帐号管理用户列表中的认证状态及相关数字信息转换成文字信息
     * */
    protected function process_account_manage_user_lists($list)
    {
        $ret = array();

        foreach($list as $value){
            switch($value['video_verify']){
                case '-1':
                    $value['video_verify'] = '未提交视频认证照';
                    break;
                case '0':
                    $value['video_verify'] = '等待视频审核';
                    break;
                case '1':
                    $value['video_verify'] = '通过视频认证';
                    break;
                case '2':
                    $value['video_verify'] = '视频认证失败';
                    break;
                default:
                    break;
            }

            switch($value['car_verify']){
                case '-1':
                    $value['car_verify'] = '未提交车辆认证照';
                    break;
                case '0':
                    $value['car_verify'] = '等待车辆审核';
                    break;
                case '1':
                    $value['car_verify'] = '通过车辆认证';
                    break;
                case '2':
                    $value['car_verify'] = '车辆认证失败';
                    break;
            }

            if($value['sex'] == 0)
                $value['sex'] = '男';
            else
                $value['sex'] = '女';

            $value['reg_time'] = date('Y-m-d H:i:s',$value['reg_time']);

            array_push($ret,$value);
        }

        return $ret;
    }

    //  获取车辆待评分
    public function getcarcount(){

        $sql = "SELECT COUNT(u.uid) AS num
                FROM cj_user AS u 
                LEFT JOIN cj_certificate_car AS c ON u.uid = c.uid
                WHERE c.status = 1 AND u.c_rate = '' ORDER BY u.uid ASC LIMIT 0,1
                 ";
        return D('User')->query($sql);
    }

    //  获取车辆待评分
    public function getcar($uid){
        if($uid!=''){
            $and = 'AND u.uid = '.$uid;
        }
        $sql = "SELECT u.*,c.* 
                FROM cj_user AS u 
                LEFT JOIN cj_certificate_car AS c ON u.uid = c.uid
                WHERE c.status = 1 AND u.c_rate = '' $and GROUP BY u.uid  ORDER BY rand() ASC LIMIT 0,1
                 ";

        return D('User')->query($sql);
    }

    // 账号设置记录操作日志
    public function operationlog($data){

        ($data['aid']>0)?$map['aid'] = $data['aid']:'';
        ($data['uid']>0)?$map['uid'] = $data['uid']:'';
        $map['time'] = array('between',array(strtotime($data['s_date']),strtotime($data['t_date'])));

        $itemsPerPage = C('ITEMS_PER_PAGE');
        $count = D('NumberOperationLog')->where($map)->count();

        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();

        $vlist = D('NumberOperationLog')->where($map)->limit($Page->firstRow,$Page->listRows)->select();

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


    // 按年或月统计用户注册趋势
    public function yearmothtrend($data=array()){
        $year  = $_GET['year'];     // 年分
        $month = $_GET['month']; // 月分

        $user  = D('User');

        $ysql  = "SELECT count(uid) AS num,from_unixtime(reg_time,'%m') AS timd 
                  FROM `cj_user` 
                  WHERE server_version = 1 AND from_unixtime(reg_time,'%Y') = '{$year}' GROUP BY timd ORDER BY timd ASC";

        $msql  = "SELECT count(uid) AS num,from_unixtime(reg_time,'%d') AS timd 
                  FROM `cj_user` 
                  WHERE server_version = 1 AND from_unixtime(reg_time,'%Y-%m') = '{$month}' GROUP BY timd ORDER BY timd ASC";

        $data['year']  = $user->query($ysql);
        $data['month'] = $user->query($msql);


        if(!empty($data['year'])){

            foreach ($data['year'] as $key => $value) {
                $list['ye'] .= "[{$value['timd']},{$value['num']}],";
            }
            $list['ye'] = trim($list['ye'],',');
        }

        if(!empty($data['month'])){

            foreach ($data['month'] as $key => $value) {
                $list['m'] .= "[{$value['timd']},{$value['num']}],";
            }
            $list['m'] = trim($list['m'],',');
        }

        return $list;
    }

    // 根据条件获取用户id
    public function forconditions($data){
        $rz    = $data['rz'];
        $total = ($data['level'] !='' )?" AND u.total = {$data['level']} ":'';
        $sex   = ($data['sex']   !='' )?" AND u.sex   = {$data['sex']} ":'';
        $uid   = trim($data['uid'],' ');
        //$batch = ($data['batch']!='')?" LIMIT {$data['batch']} ":''; // 发送的批次


        if($uid==''){

            if($rz !='' ){ // 认证用户
                $sql = "SELECT u.uid
                            FROM cj_user AS u 
                            LEFT JOIN  cj_certificate_{$rz} AS c ON u.uid = c.uid 
                            WHERE c.status != '-1' $total $sex GROUP BY u.uid ";
            }else{
                $sql = "SELECT u.uid FROM cj_user AS u WHERE u.status != -1 $total $sex  ORDER BY u.uid ASC  ";
            }

            $list = D('User')->query($sql);
        }else{
            $list[0]['uid']   = $udata['uid'] = $uid;
            $udata['content'] = $data['content'];
            $udata['s_type']  = $data['send_t'];
            $udata['reason']  = $data['reason'];
            $udata['nikname'] = $_SESSION['nickname'];
            $udata['addtime'] = time();
            D('SendtextLog')->insert_single_item($udata);
        }
        if(!empty($list)){
            $Message = new MessageController();
            $Message ->systemMessage(array_column($list,'uid'),$data['content']);
        }


    }


    public function getchatlogeve($uid,$receiver='',$day){
        if($receiver == '' ){
            $chat  = D('ChatLog')->get_user_chat_log($uid,$day);
        }else{
            $chat  = D('ChatLog')->get_dialog($uid,$receiver,$day);
        }
        if(!empty($chat)){
            require_once("./ThinkPHP/Library/Think/Emoji.class.php");
            foreach ($chat as $key => $vo) {
                $sender = ($uid==$vo['sender'])?'Ta':$vo['sender'];
                $recver = ($uid==$vo['recver'])?'Ta':$vo['recver'];

                if($vo['texttype'] == 2 ){
                    $arr = json_decode($vo['text'],true);
                    $conim = '<img src="'.$arr['thumbnailPhotoUrl'].'" width=50 height=50 >';
                }elseif($vo['texttype'] == 5){
                    $arrjw = json_decode($vo['text'],true);
                    $conim = '<img src="'.$arrjw['locationImageUrl'].'"  width=20 height=20 >经纬度：  '.$arrjw['locationLongitude'].','.$arrjw['locationLatitude'].' :'.$arrjw['locationAddress'];
                }else{
                    $conim = emoji_unified_to_html($vo['text']);
                }
                $div .= "<div class=chat-pl >
                           <p class=chat-pl-p>
                               {$vo['time']}  　　
                               {$sender}
                           </p>
                           <p class=chat-pl-p2>跟
                              {$recver}
                           </p>

                           <p>说：　
                                {$conim}
                            </p>
                           ";
            }
            return $div;
        }

    }
    // 男女数量以及比例
    public function getwmnum(){
        $model = D('User');
        $num['wnum']  = $model->field('uid')->where('sex = 1')->count(); // 女
        $num['mnum']  = $model->field('uid')->where('sex = 0')->count(); // 男
        $num['bl']    =  sprintf('%.1f',$num['mnum']/$num['wnum']);
        return $num;
    }




    /**
     *  @ 添加虚拟用户的头像、相册、标签
     *  @ uid    int    用户id
     *  @ type   str    图片类型 face 、headimages 、tag
     *  @ picurl array  图片名称
     *  @ root   str    图片路径
     **/
    public function addphone($uid,$type,$picurl,$root="/upload/excel/img/"){


        $User    = D('User');

        if( $type == 'face'){
            $bucket         =  'headimage';        // 标志
            $da['face_url'] =  makeFileName($uid); // 山传到阿里云服务器的图片及路径    
            $url            =  $root.$picurl;      // 要上传的图片地址
            $ret            =  $this->aliyup($bucket,$da['face_url'],$url);
            $User->update_single_item('uid='.$uid,$da);
        }elseif($type == 'headimages' ){
            $bucket   =  'headimage';        // 标志
            $picurl   =  explode(',',$picurl);
            foreach ($picurl as $k => $value){
                $head_images =  $userimg[$k] =  makeFileName($uid); // 山传到阿里云服务器的图片及路径
                $url         =  $root.$value;       // 要上传的图片地址
                $ret         =  $this->aliyup($bucket,$head_images,$url);
            }
            $da['head_images'] = json_encode($userimg);
            $User->update_single_item('uid='.$uid,$da);
        }

    }

    // 虚拟账号添加相册
    public function virtualphoto($uid,$data){


        $User    = D('User');

        $images  = $User->get_single_item('uid = '.$uid,'album');
        $images  = json_decode($images['album']);
        if(empty($images)){
            $images = array();
        }

        $bucket   =  'headimage';               // 标志
        foreach ($data['tmp_name'] as $k => $value){
            $head_images =  makeFileName($uid); // 山传到阿里云服务器的图片及路径
            $ret         =  $this->aliyup($bucket,$head_images,$value);
            if($ret->status == 200 ){
                array_push($images,$head_images);
            }
        }
        $da['album'] = json_encode($images);
        $User->update_single_item('uid='.$uid,$da);
        $this->del_r_user_info($uid);
    }



    // 添加头像
    public function virtualhead($uid,$p1){

        $bucket  = 'headimage';

        $pic     = makeFileName($uid);
        $ret     = $this->aliyup($bucket,$pic,$p1['tmp_name']);
        if($ret->status == 200 ){
            D('User')->update_single_item('uid = '.$uid,array('face_url'=>$pic));
        }
    }

    // 添加车辆认证
    public function virtualcar($uid,$carinfo,$file){

        $bucket  = 'certificate';

        $pic1    = makeFileName($uid);
        $ret1    = $this->aliyup($bucket,$pic1,$file['tmp_name'][0]);
        if($ret1->status == 200 ){
            D('User')->update_single_item('uid = '.$uid,array('car_verify'=>1));
            $car_model_name = D('CarModel')->where('id='.$carinfo['car_model_id'])->find();
            $car_brand_name = D('CarBrand')->get_single_item('id='.$carinfo['car_brand_id'],'name');
            $usercar['uid']            = $uid;
            $usercar['p1']             = $pic1;
            $usercar['p2']             = $pic1;
            $usercar['status']         = 1;
            $usercar['sub_time']       = time();
            $usercar['pass_time']      = time();
            $usercar['car_model_id']   = $carinfo['car_model_id'];
            $usercar['car_model_name'] = $car_model_name['name'];
            $usercar['car_brand_id']   = $carinfo['car_brand_id'];
            $usercar['car_brand_name'] = $car_brand_name['name'];
            $carid = D('CertificateCar')->insert_single_item($usercar);
        }
    }
    // 添加视频认证
    public function virtualvideo($uid,$file){

        $bucket  = 'certificate';

        $pic1    = makeFileName($uid);
        $ret1    = $this->aliyup($bucket,$pic1,$file['tmp_name'][0]);
        if($ret1->status == 200   ){
            D('User')->update_single_item('uid = '.$uid,array('video_verify'=>1));
            $uservideo['uid']            = $uid;
            $uservideo['p1']             = $pic1;
            $uservideo['p2']             = $pic1;
            $uservideo['p3']             = $pic1;
            $uservideo['p4']             = $pic1;
            $uservideo['status']         = 1;
            $uservideo['sub_time']       = time();
            $uservideo['pass_time']      = time();
            D('CertificateVideo')->insert_single_item($uservideo);
        }
    }

    /* 删除用户动态
     * $uid 用户id
     * $id  动态id
     * $reason 违规原因！
     */
    public function delusersurging($uid,$id,$reason){
        $model = D('Surging');
        $UserTagSurging = D('UserTagSurging');
        $map = " id IN (".trim($id,',').") and uid = $uid ";

        $info = $model->where($map)->select();
        $ones = explode(',',trim($id,','));

        $tag  = $UserTagSurging->search('surging_id='.$ones[0],'user_tag_id');

        if(!empty($info)){
            $taginfo = D('UserTag')->search('id ='.$tag['user_tag_id'],'title');
            foreach($info as $k => $val){
                // 删除记录 动态
                $model->where('id = '.$val['id'])->delete();
                // 删除关联表
                $UserTagSurging->where('surging_id='.$val['id'])->delete();

                if($val['thumb']!=''){
                    $this->aliydel('surging',$val['thumb']);  // 删除资源
                }
                $data[$k]['uid']     = $val['uid'];
                $data[$k]['tag']     = $taginfo['title'];
                $data[$k]['surging'] = $val['id'];
                $data[$k]['status']  = 2;
                $data[$k]['aid']     = $_SESSION['authId'];
                $data[$k]['confirm'] = $_SESSION['authId'];
                $data[$k]['reason']  = $reason;
                $data[$k]['confirm_time']     = time();
                $data[$k]['certificate_time'] = time();
            }
            // 写入log
            D('SurgingLog')->addAll($data);
        }

        // 修改动态数
        $surging_cnt = $UserTagSurging->where('user_tag_id='.$tag['user_tag_id'])->count();
        D('UserTag')->where('id ='.$tag['user_tag_id'])->save(array('surging_cnt'=>$surging_cnt));

        $suringinfo = A('Search','Event')->gatalltagsurging($ones[0]);

        // 删除缓存
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_user_surging($tag['user_tag_id']);
        $phpServer->delete_user_tag($tag['user_tag_id']);

        // 删除用户缓存
        $this->del_r_user_info($uid);

        //发送系统消息
        $Message = new MessageController();
        $reason  = '您的动态（'.$suringinfo['title'].'标签）违反了初见用户条款，经审查我们已做出删除处理。初见坚决反对色情、违法等不良行为，若发现多次违禁我们将对该账号做封停处理！';
        $Message->send_system_message($uid,'pushmessage','system',$reason);
        echo 'ok';
    }

    /*
     *  批量发放金币
     *  uid  50001 / 50001,50002
     * */
    public function sendgold($uid,$num){
        $list = D('User')->field('uid')->where("uid IN ($uid)")->select();
        foreach($list as $k => $val){
            $data[$k]['uid'] = $val['uid'];
            $data[$k]['num'] = $num;
            $data[$k]['state'] = 0;
            $data[$k]['source'] = 6;
            $data[$k]['memo'] = '活动发放';
            $data[$k]['send_time']  = time();
            $data[$k]['valid_time'] = time()+(3600*24*3);
        }
        $add = D('CoinIncome')->addAll($data);
        if($add!==false){
            $this->success('金币发放成功！');
        }
    }

    // 添加用户详情页金币
    public function glodchange($post){
        $user = D('User');
        $glod = intval($post['glod']);
        $uid  = trim($post['uid']);
        if($post['type'] == 'add' ){
            $data['uid']    = $uid;
            $data['num']    = $glod;
            $data['state']  = 0;
            $data['source'] = 6;
            $data['memo']   = '活动发放';
            $data['send_time']  = time();
            $data['valid_time'] = time()+(3600*24*3);
            $gi = D('CoinIncome')->add($data);
            // 3.发送 im 消息
            $Message     = new MessageController();
            $Message->send_system_message($uid,'search','golds');
            return 'ok';
        }else{
            // 扣除金币
            $useri = D('user')->field('gold_coin_cnt')->where('uid = '.$uid)->find();
            if($useri['gold_coin_cnt']<=0){
                return 'no';
                exit;
            }
            $glod  = $useri['gold_coin_cnt'] - $glod;
            if($glod <= 0 ){
                $glod = 0;
            }
            $kcok = $user->where("uid = $uid ")->save(array('gold_coin_cnt'=>$glod));
            if($kcok!==false){
                $ilog['uid']    = $uid;
                $ilog['num']    = $glod;
                $ilog['memo']   = '活动扣除';
                $ilog['create_time'] = time();
                // 写入log
                D('CoinLog')->add($ilog);
            }
            //删除php server用户基本信息和所有信息
            $phpServer = D('PhpServerRedis');
            $phpServer->delete_user_info($uid);
            return 'ok';
        }


    }


    public function clearuserall($uid){
        $this->del_r_user_info($uid);
        $list = D('UserTag')->searchs('uid = '.$uid,'id');
        if(!empty($list)){
            foreach($list as $k => $val ){
                $this->del_r_user_tag_surging($val['id']);
            }
        }

    }

    // 获取今日用户
    public function userdate($date){
        $user     = D("User");
        $userlist = $user->searchs(" from_unixtime(reg_time,'%Y-%m-%d') = '{$date}' and server_version = 1  ",'uid'); // 2
        $userlist = implode(',',array_column($userlist,'uid'));
        if($userlist!=''){
            $and = " AND uid NOT IN($userlist) ";
        }
        $userlist = D('Location')->searchs(" from_unixtime(update_time,'%Y-%m-%d') = '{$date}' $and  ",'uid'); // 1,2
        $userlist = implode(',',array_column($userlist,'uid'));
        return $user->field('uid')->where(" uid IN ($userlist) AND   server_version = 1 ")->count();

    }


}
