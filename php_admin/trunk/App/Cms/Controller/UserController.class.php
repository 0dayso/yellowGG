<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/9
 * Time: 13:57
 */

namespace Cms\Controller;

use Cms\Controller;
use Cms\Controller\MessageController;
use Think\Cache\Driver\Redis;
class UserController extends PublicController{

    /*
     * 用户列表
     * */
    public function index()
    {
        $menu  = $_GET['menu'];
        $type  = $_GET['type'];
        $case  = $menu.'/'.$type;
        $map   = user_search();
        $Event = A('User','Event');

        if($menu == 'account_manage'){
            $ret   = $Event->get_account_manage_user_list($case,$map);
            $numwm = $Event->getwmnum();// 男女用户数量
            //$online= A('Location','Event')->getonlineuser();  // 在线人数
        }


        if($menu == 'content_manage'){
            $ret   = $Event->get_content_manage_user_list($case,$map);
        }

        if($menu == 'account_manage'){
            
            $this->assign('numwm',$numwm);
            //$this->assign('online',$online);
            $this->assign('userCarPass',$ret['carVerifyCount']);
            $this->assign('userVideoPass',$ret['videoVerifyCount']);
            $this->assign('userCount',$ret['userCount']);
            $this->assign('pushUserCount',$ret['pushUserCount']);
        }
        $this->assign('menu',$menu);
        $this->assign("type", $type);
        $this->assign("list", $ret['list']);
        $this->assign("page", $ret['show']);

        if($menu == 'account_manage')
            $this->display('index');
        else
            $this->display('content_manage_user_list');
    }

    /*
     * 查看单个用户信息
     * */
    public function user_info()
    {
        $menu  = $_GET['menu'];
        $type  = $_GET['type'];
        $uid   = $_GET['uid']?trim($_GET['uid']):$_GET['request_id'];

        $Event = A('User','Event');
        $ret   = $Event->get_account_manage_user_info($uid);
        require_once("./ThinkPHP/Library/Think/Emoji.class.php");

        // 内容图片转换
        $emoji['nickname']  = emoji_unified_to_html($ret['user']['nickname']);
        /*$emoji['tags']      = emoji_unified_to_html($ret['user']['tags']);
        $emoji['signature'] = emoji_unified_to_html($ret['user']['signature']);
        $emoji['movie']     = emoji_unified_to_html($ret['user']['movie']);
        $emoji['weekend']   = emoji_unified_to_html($ret['user']['weekend']);
        $emoji['cooking']   = emoji_unified_to_html($ret['user']['cooking']);
        $emoji['travel']    = emoji_unified_to_html($ret['user']['travel']);
        $emoji['restaurant']= emoji_unified_to_html($ret['user']['restaurant']);
        $emoji['sport']     = emoji_unified_to_html($ret['user']['sport']);*/
        $emoji['job']       = emoji_unified_to_html($ret['user']['job']);
        // 获取用户最后一次登录时间
        //$login = D('Location')->search($ret['user']['uid'],'update_time');

        $this->assign("accusation_request",$ret['accusation_request']);
        $this->assign("user", $ret['user']);
        $this->assign("chat",$ret['chat']);
        /*$this->assign("accusation",$ret['accusation']);
        $this->assign('imglistlog',$ret['imglistlog']);
        $this->assign('textlistlog',$ret['textlistlog']);
        $this->assign('sendtext',$ret['sendtext']);
        $this->assign("car", $ret['car']);
        $this->assign("video", $ret['video']);*/
        $this->assign("location", $ret['location']);
        $this->assign("ccar", $ret['ccar']);
        $this->assign("cvideo", $ret['cvideo']);
        $this->assign("authentication", $ret['authentication']);
        $this->assign('menu',$menu);
        $this->assign("type", $type);
        $this->assign("emoji", $emoji);
        $this->assign("uid", $uid);


        $this->assign('select',A('Search','Event')->classtagselecthtml());
        $this->display('account_manage_user_info');
    }

    public function getcarvideolog(){
        $uid = $_POST['uid'];
        $ret = A('User','Event')->getcarvideolog($uid);
        $this->assign('imglistlog',$ret['imglistlog']);
        $this->assign('textlistlog',$ret['textlistlog']);
        $this->assign('sendtext',$ret['sendtext']);
        $this->assign("car", $ret['car']);
        $this->assign("video", $ret['video']);
        $this->assign("tagloglist", $ret['tagloglist']);
        $this->assign("surginglog", $ret['surginglog']);
        layout(false);
        echo $this->fetch();
    }
    public function accusationrequestlog(){
        $uid = $_POST['uid'];
        $ret = A('User','Event')->accusationrequestlog($uid);
        $this->assign("accusation",$ret);
        layout(false);
        echo $this->fetch();
    }


    //账号注册趋势图
    public function trend(){
        $_GET['year']  = ($_GET['year']=='')?date('Y',time()):$_GET['year'];     // 年分
        $_GET['month'] = ($_GET['month']=='')?date('Y-m',time()):$_GET['month']; // 月分

        $info = A('User','Event')->yearmothtrend($_GET);

        $this->assign('info',$info);
        $this->assign('date',$_GET);
        $this->display();
    }

    /*
     * 修改文字审查
     * */
    public function modify_content_info()
    {
        A('User','Event')->worldsehe($_POST);
        echo 1;
    }

    /*
     * 修改用户信息中的某个字段
     * */
    public function modify_single_field()
    {
        $id    = $_POST['id'];
        $value = $_POST['value'];

        $Event = A('User','Event');
        $Event->modify_single_field($id,$value);

        $this->ajaxReturn(array('info'=>'操作成功'));
    }

    /*
     * 内容管理-文字审查-全部通过
     * 事实上没有做任何的原始数据更新,只需要修改request的aid..和插入log信息
     * */
    public function confirm_all_pass()
    {
        $list = explode(',',$_POST['list']);

        $Event = A('User','Event');
        $Event->confirm_all_pass($list);

        $this->ajaxReturn(array('info'=>'操作成功'));
        exit;
    }

    /*
     * 内容管理-文字审查-确认审查结束
     * 事实上没有做任何的原始数据更新,只需要修改request的aid..和插入log信息
     * */
    public function confirm_delete_request()
    {
        $list = explode(',',$_POST['list']);

        $Event = A('User','Event');
        $Event->confirm_delete_request($list);

        $this->ajaxReturn(array('info'=>'操作成功'));
    }

    /*
     * 变更手机号
     * */
    public function change_user_phone_num()
    {
        $admin_id = $this->admin_permission(C('ACTION_MODIFY_USER_INFO'));
        if(!$admin_id)
            $this->error('没有权限');
        $uid   = $_GET['uid'];
        $phone = $_GET['phone'];

        $User  = D('User');
        $User->update_single_item(array('uid'=>array('EQ',$uid)),array('phone'=>$phone));
        $this->success('操作成功');
    }

    /*
     * 修改用户资料
     * */
    public function modify_user_info()
    {
        $admin_id = $this->admin_permission(C('ACTION_MODIFY_USER_INFO'));
        if(!$admin_id)
            $this->error('没有权限');
        $Model = D('User');
        $Model->update_single_item('uid='.$_GET['uid'],$_POST);
        $this->success('操作成功');
    }

    /*
     * 清除用户缓存
     * 清除用户php server redis里的fullinfo和baseinfo
     * 清除用户在location里的数据
     * */
    public function clear_user_cache()
    {
        //todo 权限管理
        $uid = $_GET['uid'];
        $Event = A('User','Event');
        $Event->clear_user_cache($uid);

        $this->success('操作成功');
    }

    /*
     * 更改用户状态，只更改3种状态：正常、测试、地推
     * */
    public function change_user_state()
    {
        $uid  = $_GET['uid'];
        $type = $_GET['change_user_state'];
        $Event = A('User','Event');
        $Event->change_user_state($uid,$type);

        $this->success('操作成功');
    }

    /*
     * 将用户帐号修改成测试帐号
     * */
    public function add_to_test_account()
    {
        $Event = A('User','Event');
        $Event->add_to_test_account(trim($_GET['uid']));

        $this->success('操作成功');
    }

    /*
     *
     * */
    public function undo_test_account()
    {
        //todo 权限管理
        $Event = A('User','Event');
        $Event->undo_test_account(trim($_GET['uid']));

        $this->success('操作成功');
    }

    /*
     * 将用户帐号修改成地推帐号
     * */
    public function add_to_push_account()
    {
        $Event = A('User','Event');
        $Event->add_to_push_account(trim($_GET['uid']));

        $this->success('操作成功');
    }

    /*
     * 取消地推帐号
     * */
    public function undo_push_account()
    {
        $Event = A('User','Event');
        $Event->undo_push_account(trim($_GET['uid']));

        $this->success('操作成功');
    }

    // ajax 获取用户的聊天记录
    public function getchatlog(){
        if(IS_POST){
            $uid = $_POST['uid'];
            $day = $_POST['day'];
            $rep = $_POST['rep'];
            $chat  = A('User','Event')->getchatlogeve($uid,$rep,$day);
            echo $chat;
        }
    }

    // 聊天记录监控
    public function chatlogmonitor(){

        
        $logm = D('ChatLog');

        $date = date('Ymd',time());
        if($_GET['date']!=''){
            $date = str_replace('-','',$_GET['date']);
        }
        $table = 'send'.$date;

        //$table = 'send20150403';
        $_GET['key'] = urldecode($_GET['key']); 

        $limit = C('ITEMS_PER_PAGE');
        $count = $logm->get_day_wlog($table,$_GET['key'],'','count',$_GET['texttype']);
        
        $Page  = new \Think\Page($count[0]['num'], $limit);
        $show = $Page->show();

        $log = $logm->get_day_wlog($table,$_GET['key'],$Page,'',$_GET['texttype']);
    

        $this->assign('data',$_GET);
        $this->assign('chat',$log);
        $this->assign('page',$show);
        $this->display();
    }

    // 用户金币增加扣除
    public function glodchange(){
        if(IS_POST){
            echo A('User','Event')->glodchange($_POST);
        }


    }  

    // 虚拟账号添加
    public function virtualadd(){
        if(IS_POST){

            $userm = D('User')->get_single_item("phone = '{$_POST['phone']}' ",'uid');
            if($userm['uid']!=''){
                $this->error('手机号不可以重复！');
            }
            $auth = $_POST['auth'];
            unset($_POST['auth']);
 
            if( $_POST['nickname'] ==''  &&   $_POST['phone'] ==''   ){
                $this->error('请填写内容！');
            }
            $_POST['password']   = md5($_POST['phone'].'4jfr84fjad');
            $_POST['status']     = 3; // 虚拟账号
            $_POST['birthday']   = str_replace('.','-',$_POST['birthday']);  // 虚拟账号
            $_POST['reg_time']   = time();
            $_POST['gold_coin_cnt']    = rand(10,60);
            $_POST['server_version']   = 1;
            $lat = $_POST['lat'];
            $lng = $_POST['lng'];
            unset($_POST['lat']);
            unset($_POST['lng']);

            $uid = D('User')->add($_POST);


            if($uid > 0 ){

                if($lat!='' && $lng!=''){
                    $post_data['phone']    =  $_POST['phone'];
                    $post_data['password'] =  $_POST['phone'];
                    $loginfo = c_url($post_data,'http://'.API_TAG.'/v1.1/index.php/auth/login');
                    $whereis['uid']   = $uid;
                    $whereis['token'] = $loginfo['token'];
                    $whereis['lat']   = $lat;
                    $whereis['lng']   = $lng;
                    c_url($whereis,'http://'.API_TAG.'/v1.1/index.php/lbs/update_location');
                }

                $p1 = $_FILES['face_url'];

                // 添加头像
                if($p1['name']!='')
                    A('User','Event')->virtualhead($uid,$p1);

                // 添加车辆认证
                if( $auth['car_verify'] == 1 && $auth['car_model_id'] !=''  )
                    A('User','Event')->virtualcar($uid,$auth,$_FILES['car_p']);

                // 添加视频认证
                if($auth['video_verify'] == 1 && count($_FILES['video_p']['name'])==1 )
                    A('User','Event')->virtualvideo($uid,$_FILES['video_p']); 

                $this->success('创建成功！');
            }else{
                $this->error('创建用户失败！');
            }
        }else{
            $this->assign('brand',D('CarBrand')->get_multi_items());
            $this->display();
        }
    }

    // 虚拟账号添加相册
    public function virtualphoto(){
        
        if(!empty($_FILES['face_url']['name'][0])){
            A('User','Event')->virtualphoto($_POST['uid'],$_FILES['face_url']);
            $this->success('添加完成！');
        }else{
            $this->error('请选择图片！');
        }
    }

    // 虚拟账号倒入
    public function virtual(){
        $this->display();
    }
    
    // 获取车型
    public function getcarmodel(){
        $map['brand_id'] = $_POST['id'];
        $list = D('CarModel')->get_multi_items($map);

        foreach ($list as $key => $val) {
            $option .= "<option value={$val['id']}>{$val['name']}</option>";
        }
        echo $option; 
    }

    // 虚拟账号认证添加
    public function virtualauthadd(){

        $this->display();
    }

    // 接口添加标签
    public function addtagapi(){
        $uid    = $_POST['uid'];

        $have   = D('UserTag')->get_single_item(" title = '{$_POST['tag']}' and uid = {$_POST['uid']} ",'id');

        if($have['id'] !=''){
            $this->success('标签已存在！');
        }else{
            // 创建标签
            $userinfo = D('User')->search('uid = '.$uid,'sex');

            $posttag['uid']         = $uid;
            $posttag['title']       = $_POST['tag'];
            $posttag['verify']      = 1;
            $posttag['sex']         = $userinfo['sex'];
            $posttag['verify_time'] = time();
            $posttag['create_time'] = time();
            $posttag['certificate'] = 2;
            $posttag['tag_class_id'] = intval($_POST['tag_class_id']);
            $addid  = D('UserTag')->add($posttag);
            A('Search','Event')->user_tag_info_ch($uid,'add');
            $this->del_r_user_info($uid);
            if($addid>0){
                $this->redirect('user/user_info',array('menu'=>'account_manage','type'=>'virtual','uid'=>$uid,'tags'=>'show'));
            }else{
                $this->error('失败');
            }
        }
    }

    // 添加标签图片
    public function addtagimages(){

        if(empty($_FILES['thumb']['name'][0])){
            $this->error('请添加图片！');
        }
        $usertag = D('UserTag');
        $uid     = trim($_POST['uid']);
        $u_t_id  = $_POST['user_tag_id'];
        $dstdesc = explode('@@',$_POST['description']);

        if($_POST['dstime']=='ttlime'  && $_POST['send_time'] !='' ){
            if(API_TAG=='api.chujian.im'){
                $option = C('php_server_user_info_v2');
            }else{
                $option = C('php_server_redis_config');
            }
            $Redis = new Redis($option);
            $SurgingTiming = D('SurgingTiming');
            foreach($_FILES['thumb']['name'] as $key=>$val){
                // 上传图片
                $upname = surgingmakeFileName($_FILES['thumb']['name'][$key]);
                $up = $this->aliyup('surging',$upname,$_FILES['thumb']['tmp_name'][$key]);
                if( $up->status == 200 ){
                    $s_data['uid']   = $uid;
                    $s_data['type']  = 1;
                    $s_data['thumb'] = $upname;
                    $s_data['description']  = $dstdesc[$key];
                    $s_data['user_tag_id'] = $u_t_id;
                    $time   = strtotime($_POST['send_time']);
                    $bzarray['type'] = 2;
                    $bzarray['resource'] = $s_data;

                    $timing[$key]['uid']      = $uid;
                    $timing[$key]['user_tag'] = $u_t_id;
                    $timing[$key]['thumb']    = $upname;
                    $timing[$key]['timing_time'] = $time;
                    $timing[$key]['description'] = $dstdesc[$key];
                    $red = $Redis->zAdd('mitimg_up',$time,json_encode($bzarray));
                }
            }
            // 记录定时动态
            $SurgingTiming->addAll($timing);
        }else{
            foreach($_FILES['thumb']['name'] as $key=>$val){
                // 上传图片
                $upname = surgingmakeFileName($_FILES['thumb']['name'][$key]);
                $up = $this->aliyup('surging',$upname,$_FILES['thumb']['tmp_name'][$key]);
                if( $up->status == 200 ){
                    // 添加 cj_surging
                    $s_data['uid']   = $uid;
                    $s_data['type']  = 1;
                    $s_data['thumb'] = $upname;
                    $s_data['description'] = $dstdesc[$key];
                    $s_data['create_time'] = time();
                    $s_data['status']      = $uid;
                    $surging_id = D('Surging')->add($s_data);
                    // 更改 cj_user_tag 动态数量，更新更改时间
                    if($surging_id>0){
                        $usertaginfo = $usertag->search('id = '.$u_t_id);
                        $usertag->where('id='.$u_t_id)->setInc('surging_cnt');
                        $dataut['verify']         = 1;
                        /*$dataut['thumb_up_cnt']   = $dataut['t_thumb_up_cnt'] = rand(20,50);
                        $dataut['t_thumb_cnt_res']= $dataut['t_thumb_up_cnt']-$usertaginfo['t_thumb_down_cnt'];
                        $dataut['thumb_up_time']  = time();*/
                        $dataut['update_time']    = time();
                        $usertag->where('id='.$u_t_id)->save($dataut);
                        // 添加 cj_user_tag_surging 关系链
                        $uts_data['uid']         = $uid;
                        $uts_data['user_tag_id'] = $u_t_id;
                        $uts_data['surging_id']  = $surging_id;
                        D('UserTagSurging')->add($uts_data);
                    }
                }
                // 删除标签缓存
                $serphp = D('PhpServerRedis');
                $serphp->delete_user_tag($u_t_id);
                $serphp->delete_user_surging($u_t_id);
            }

        }

        $this->redirect('user/user_info',array('menu'=>'account_manage','uid'=>$uid,'tag'=>'show'));
    }

    public function delusersurging(){
        if(IS_POST){
            echo A('User','Event')->delusersurging($_POST['uid'],$_POST['id'],$_POST['reason']);
        }
    }

    // 批量发金币
    public function sendgold(){
        if(IS_POST){
            $uid  = str_replace(' ','',$_POST['uid']);
            $gold = intval($_POST['gold']);
            if( $uid=='' || $gold =='' ){
                $this->error('内容填写错误！');
            }
            echo A('User','Event')->sendgold($uid,$gold);
        }else{
            $this->spermission(55);
            $this->display();
        }
    }

    public function latlngchange(){
        if(IS_POST){

            /*$post_data['phone']    =  $_POST['phone'];
            $post_data['password'] =  $_POST['phone'];
            $loginfo = c_url($post_data,'http://'.API_TAG.'/v1.1/index.php/auth/login');*/
            $token = D('PhpServerRedis')->get_user_token($_POST['uid']);
            if($token==''){
                $post_data['phone']    =  $_POST['phone'];
                $post_data['password'] =  $_POST['password'];
                $result = c_url($post_data,'http://'.API_TAG.'/v1.1/index.php/auth/login');
                $token  = $result['token'];
            }

            $whereis['uid']   = $_POST['uid'];
            $whereis['token'] = $token;
            $whereis['lat']   = $_POST['lat'];
            $whereis['lng']   = $_POST['lng'];

            $logised = c_url($whereis,'http://'.API_TAG.'/v1.1/index.php/lbs/update_location');

            $this->redirect('user/user_info',array('menu'=>'account_manage','type'=>'virtual','uid'=>$_POST['uid']));


        }
    }

     /*public function useraddtext(){
        for($i=0;$i<1;$i++){
            $data[$i]['phone']      = '16666666666';
            $data[$i]['password']   = md5('c123456'.'4jfr84fjad');
            $data[$i]['status']     = 0;
            $data[$i]['reg_time']   = time();
            $data[$i]['server_version']   = 1;
            $data[$i]['sex']        = 0;
        }

        $add = D('User')->addAll($data);
        echo 'ok';


    }*/

    // 清理用户所有的缓存
    public function clearuserall(){
        $uid = $_POST['uid'];
        A('User','Event')->clearuserall($uid);
        echo 'ok';
    }


    // 修改用户修改密码
    public function chmodpwduser(){
        if(IS_POST){
            $data['password'] = md5(trim($_POST['pwd']).'4jfr84fjad');
            if($_POST['uid']!=''){
                $yes = D('User')->where('uid = '.$_POST['uid'])->save($data);
                if($yes !== false ){
                    $this->success('密码修改成功！');
                }else{
                    $this->error('修改失败');
                }
            }else{
                $this->error('修改失败');
            }
        }else{
            $this->display();
        }
    }


    // 账号分析
    public function userdate(){
        $date = trim($_GET['date']);
        if($date!=''){
            $date = date('Y-m-d',strtotime($_GET['date']));
        }else{
            $date = date('Y-m-d',time());
        }
        $this->assign('num',A('User','Event')->userdate($date));
        $this->assign('date',$date);
        $this->display();
    }


}



