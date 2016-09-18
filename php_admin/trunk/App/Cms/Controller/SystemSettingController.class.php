<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/30
 * Time: 14:07
 */

namespace Cms\Controller;

use Cms\Controller;
class SystemSettingController extends PublicController{

    public function index()
    {
        $this->display();
    }

    /*
     * 显示管理员以及其所在的管理员组
     * TODO 是否应该只显示该auth对应权限下的内容，而不是所有内容
     * */
    public function admin()
    {
         
        /*$admin_id = $this->admin_permission(C('PRIZE_MANAGE_INDEX'));
        if(!$admin_id){
            $this->error('没有权限！');
        } */

        $map = admin_search();

        $Admin = D('Admin');
        $itemsPerPage = C('ITEMS_PER_PAGE');
        $count = $Admin->get_count($map);

        import("THINK.Page");
        $Page = new \Think\Page($count,$itemsPerPage);
        $show = $Page->show();

        $list = $Admin->lists($map,$Page);

        $AdminGroup = D('AdminGroup');
        $admin_name_list = $Admin->field('aid,nickname')->select();
        $group_name_list = $AdminGroup->field('admin_group_id as group_id,name')->select();

        $this->assign("admin",$admin_name_list);
        $this->assign("group",$group_name_list);
        $this->assign("list", $list);
        $this->assign("page", $show);
        $this->display();
    }

    /*
     * 退出所在组，只有超级管理员可以做这件事情
     * TODO 判断auth是否有操作权限，这个动作应该写到哪里
     * */
    public function delete_admin_permission()
    {
        $admin_id = $this->admin_permission(C('ACTION_ADMIN_QUIT_GROUP'));
        if(!$admin_id)
            $this->error('没有权限');

        $map['aid'] = array('EQ',$_GET['aid']);
        $map['admin_group_id'] = array('IN',$_GET['group_id']);
        $AdminGroupAdmin = D('AdminGroupAdmin');

        if(!empty($_GET['group_id']))
            $AdminGroupAdmin->where($map)->delete();

        $this->redirect('SystemSetting/admin');
    }

    /*
     * 删除管理员，只有超级管理员可以做这件事情
     * TODO 判断auth是否有操作权限，这个动作应该写到哪里
     * */
    public function delete_admin()
    {
        $admin_id = $this->admin_permission(C('ACTION_DELETE_ADMIN'));
        if(!$admin_id)
            $this->error('没有权限');

        if(!empty($_GET['aid'])){
            $aid  = $_GET['aid'];
            $map['aid'] = array('EQ',$aid);

            $Admin = D('Admin');
            $AdminGroupAdmin = D('AdminGroupAdmin');
            $Admin->where($map)->delete();
            $AdminGroupAdmin->where($map)->delete();

            //$CreateTable = new CreateTableController();
            //$CreateTable->drop_certificate_car_req_aid_table($aid);
            //$CreateTable->drop_certificate_video_req_aid_table($aid);
            //$CreateTable->drop_accusation_req_aid_table($aid);
            //$CreateTable->drop_feedback_req_aid_table($aid);
        }

        $this->success('删除成功');
        //$this->redirect('SystemSetting/admin');
    }

    /*
     * 添加管理员到管理员组
     * TODO 判断auth是否有操作权限
     * */
    public function add_admin()
    {
        $admin_id = $this->admin_permission(C('ACTION_GROUP_ADD_ADMIN'));
        if(!$admin_id)
            $this->error('没有权限');

        $aid = $_GET['aid'];
        $group_id = $_GET['group_id'];

        if($aid=='请选择管理员'||$group_id=='请选择管理员组'){
            $this->error('请选择管理员/管理员组');
        }
        else{
            $AdminGroupAdmin = D('AdminGroupAdmin');
            $AdminGroupAdmin->add(array('aid'=>$aid,'admin_group_id'=>$group_id));
        }
        $this->redirect('SystemSetting/admin');
    }

    /*
     * 创建新的管理员
     * TODO 同样有权限问题
     * */
    public function create_admin()
    {
        $admin_id = $this->admin_permission(C('ACTION_CREATE_ADMIN'));
        if(!$admin_id)
            $this->error('没有权限');

        $this->display();
    }

    /*
     * 确认创建新的管理员
     * */
    public function confirm_create_admin()
    {
        $data = $_GET;
        $verify = new \Think\Verify();

        if($data['nickname']=='')
            $this->error('请填写管理员昵称');
        elseif($data['email'==''])
            $this->error('请填写管理员email');
        elseif($data['pwd']=='')
            $this->error('请填写管理员密码');
        elseif(!$verify->check($data['find_code'],''))
            $this->error('验证码有误');
        else{

        }

        $data['pwd'] = md5($data['pwd']);
        $data['create_time'] = date('y-m-d H:i:s',time());
        $Admin = D('Admin');
        $newAdminId = $Admin->insert_single_item($data);

        /*创建管理员的同时添加任务表*/
        //$CreateTable = new CreateTableController();
        //$CreateTable->create_certificate_car_req_aid_table($newAdminId);
        //$CreateTable->create_certificate_video_req_aid_table($newAdminId);
        //$CreateTable->create_accusation_req_aid_table($newAdminId);
        //$CreateTable->create_feedback_req_aid_table($newAdminId);

        $this->redirect('SystemSetting/admin');
    }

    /*
     * 显示管理员组及其拥有的权限
     * TODO 权限问题
     * */
    public function group()
    {
        $map = group_search();

        $Group = D('AdminGroup');
        $itemsPerPage = C('ITEMS_PER_PAGE');
        $count = $Group->get_count($map);

        /*import("THINK.Page");
        $Page = new \Think\Page($count,$itemsPerPage);
        $show = $Page->show();*/

        //$list = $Group->lists($map,$Page);

        $AdminGroup = D('AdminGroup');
        $Action     = D('Action');
        $group      = $AdminGroup->field('admin_group_id,name')->select();
        $action     = $Action->field('action_id,name')->select();

        $list       = $AdminGroup->get_multi_items();

        $this->assign("group",$group);
        $this->assign("action",$action);
        $this->assign("list", $list);
        //$this->assign("page", $show);
        $this->display();
    }

    // 管理权限员操作页面
    public function groupchmod(){

        if(!empty($_POST)){
            if(count($_POST['action_id'])==0){
                $this->redirect('groupchmod');
                exit;
            }  
            foreach ($_POST['action_id'] as  $value) {
                $data[] = array('action_id'=>$value,'admin_group_id'=>$_POST['admin_group_id']);
            }
            // 删除管理员的权限
            $dagroup = D('ActionAdminGroup');
            $dagroup->delete_single_item('admin_group_id ='.$_POST['admin_group_id']);
            $add = $dagroup->insert_multi_items($data);
            $this->success('修改管理员信息成功！');        
        }else{
            $one   = D('Action')->get_multi_items('action_id IN (5,13,14,15,16,17,18,19,20,24,25,26,30,34,47,48,49,50,51,52,53,54,55,56,57)');
            $two   = D('Action')->get_multi_items('action_id IN (3,4,7,8,9,10,11,12,21,22,23,27,28,29,32,33,35,36,44,46)');
            $san   = D('Action')->get_multi_items('action_id IN ( 1,2,6,31,37,38,39,40,41,42,43,45)');

            $gstr    = D('ActionAdminGroup')->get_multi_items('admin_group_id = '.$_GET['admin_group_id'],'action_id');
            $this->assign('one',$one);
            $this->assign('two',$two);
            $this->assign('san',$san);
            $this->assign('gstr',$gstr);
            $this->assign('gid',$_GET['admin_group_id']);
            $this->display();
        }
    }

    /*
     * 删除管理员组权限
     * TODO 权限问题
     * */
    public function delete_group_permission()
    {
        $admin_id = $this->admin_permission(C('ACTION_DELETE_ADMIN_GROUP_PERMISSION'));
        if(!$admin_id)
            $this->error('没有权限');

        $ActionAdminGroup = D('ActionAdminGroup');
        $map['admin_group_id'] = array('EQ',$_GET['admin_group_id']);
        $map['action_id'] = array('EQ',$_GET['action_id']);

        if(!empty($_GET['action_id']))
            $ActionAdminGroup->where($map)->delete();

        $this->redirect('SystemSetting/group');
    }

    /*
     * 添加管理员组权限
     * TODO 权限问题
     * */
    public function add_group_permission()
    {
        $admin_id = $this->admin_permission(C('ACTION_ADD_ADMIN_GROUP_PERMISSION'));
        if(!$admin_id)
            $this->error('没有权限');

        $data = $_GET;
        if(($data['admin_group_id']=='请选择组')||($data['action_id']=='请选择权限')){
            $this->error('请选择组/权限');
        }
        else{
            $ActionAdminGroup = D('ActionAdminGroup');
            $map = array(
                'admin_group_id' => array('EQ',$data['admin_group_id']),
                'action_id' => array('EQ',$data['action_id']),
            );
            $count = $ActionAdminGroup->where($map)->count();
            if($count>0)
                $this->error('该管理员组已拥有该权限');
            else
                $ActionAdminGroup->add($data);
        }

        $this->redirect('SystemSetting/group');
    }

    /*
     * 创建新的管理员组
     * TODO 权限问题
     * */
    public function create_group()
    {
        $admin_id = $this->admin_permission(C('ACTION_CREATE_ADMIN_GROUP'));
        if(!$admin_id)
            $this->error('没有权限');

        if(!empty($_POST['create-group'])){
            $AdminGroup = D('AdminGroup');
            $data['name'] = $_POST['create-group'];
            $AdminGroup->data($data)->add();
        }
        else{
            $this->error('请填写管理员组名称');
        }

        $this->redirect('SystemSetting/group');
    }

    /*
     * 删除管理员组
     * 删除组的同时还要删除其中的管理员
     * 同时要删除该组所有的权限
     * TODO 权限问题
     * */
    public function delete_group()
    {
        $admin_id = $this->admin_permission(C('ACTION_DELETE_ADMIN_GROUP'));
        if(!$admin_id)
            $this->error('没有权限');

        $group_id = $_GET['admin_group_id'];
        $AdminGroup = D('AdminGroup');
        $AdminGroupAdmin = D('AdminGroupAdmin');
        $ActionAdminGroup = D('ActionAdminGroup');
        $AdminGroup->where('admin_group_id='.$group_id)->delete();
        $AdminGroupAdmin->where('admin_group_id='.$group_id)->delete();
        $ActionAdminGroup->where('admin_group_id='.$group_id)->delete();

        $this->redirect('SystemSetting/group');
    }

    public function check_user_image()
    {
        $User = D('User');
        /*$map  = array('reg_time'=>array('GT',0));
        $temp = $User->where($map)->field('MAX(reg_time)')->find();
        $maxRegTime = current($temp);
        $temp = $User->where($map)->field('MIN(reg_time)')->find();
        $minRegTime = current($temp);*/
        $ret = array();
        $day = strtotime(date('Y-2-1'));
        $today = strtotime(date('Y-m-d'));
        $dayStart = $day;
        $map = array();
        for($i=0;$i<=($today-$day)/86400;$i++){
            $dayEnd   = $dayStart+86400;
            $map['reg_time'] = array(array('gt',$dayStart),array('lt',$dayEnd));
            $img = $User->where($map)->field('head_images')->select();
            //$sql = $User->getLastSql();
            $imgCnt = 0;
            foreach($img as $value){
                $imgArr = json_to_array($value['head_images']);
                $imgCnt += count($imgArr);
            }
            $userCnt = $User->where($map)->field('uid')->count();
            $img_avg = (float)($imgCnt/$userCnt);
            $img_avg = sprintf("%.2f", $img_avg);
            $ret[] = array('today'=>date('Y-m-d',$dayStart),'user_cnt' =>$userCnt,'img_cnt'=>$imgCnt,'img_avg'=>$img_avg);
            $dayStart+=86400;
        }

        $this->assign('list',$ret);
        $this->display();
    }

    // 工作量统计
    public function workcount(){
        if($_POST['date']){
            $nowd  = $_POST['date'];
        }else{
            $nowd  = date('Y-m',time());
        }

        $aid = intval($_POST['aid']);

        // 获取文字认证信息
        $words = D('UserInfoModifyRequestLog')->getdatewords($nowd,$aid);

        // 获取图片认证信息
        $images = D('HeadImageModifyRequestLog')->getdateimage($nowd,$aid);

        // 获取视频认证类信息
        $video = A('CertificateVideo','Event')->getdatevideo($nowd,$aid);
        // 获取车辆认证
        $car   = A('CertificateCar','Event')->getdatecar($nowd,$aid);
        // 整合视频和车辆的总通过数量
        foreach ($car['pass'] as $key => $value) {
            $passed[] = $value + $video['pass'][$key]; 
        }

        // 整合风控 文字和图片的总通过数量
        foreach ($words['pass'] as $key => $value) {
            $wimall[] = $value + $images['pass'][$key]; 
        }
 
        // 获取举报类信息
        $accus = A('Accusation','Event')->getdateaccsation($nowd,$aid);
        // 获取管理员列表
        $alist = D('Admin')->get_multi_items('','aid,nickname');
         

        $this->assign('images',$images);    // 图片风控
        $this->assign('words',$words);     // 文字风控
        $this->assign('car',$car);         // 车的认证
        $this->assign('video',$video);     // 视频认证
        $this->assign('passed',$passed);   // 车 + 视频的认证总量
        $this->assign('accus',$accus);     // 举报信息
        $this->assign('alist',$alist);     // 管理员列表
        $this->assign('aid',$_POST['aid']);// 管理员列表
        $this->assign('wimall',$wimall);   // 文字+视频认证总量
        $this->assign('date',$nowd);
        $this->display();
    }

    // 操作日志
    public function operation(){   
         // 获取管理员列表
        $alist = D('Admin')->get_multi_items('','aid,nickname');
        $type  = $_GET['type'];
        $aid   = $_GET['aid'];
        if( $type  ){
            switch ($type) {
                case '1':
                    $video = A('CertificateVideo','Event')->operationlog($_GET);
                    $this->assign('video',$video);
                    break;
                case '2':
                    $car   = A('CertificateCar','Event')->operationlog($_GET);
                    $this->assign('car',$car);
                    break;
                case '3':
                    $request = A('Accusation','Event')->operationlog($_GET);
                    $this->assign('request',$request);
                    break;
                case '4':
                    $uinfo = A('HeadImage','Event')->operationlog($_GET);
                    $this->assign('uinfo',$uinfo);
                    break;
                case '5':
                    $rate  = A('Rate','Event')->operationlog($_GET);
                    $this->assign('rate',$rate);
                default:
                    $nmber = A('User','Event')->operationlog($_GET);

                    $this->assign('nmber',$nmber);
                    break;
            }

        }
      
        $this->assign('alist',$alist);     // 管理员列表
        $this->assign('post',$_GET);     // 時間
        $this->display();
    }

    // 群发推送消息
    public function pushmessage(){
         
        $admin_id = $this->admin_permission(C('SENTMESSAGE_GROUP'));
        if(!$admin_id){
            $this->error('没有权限！');
            exit;
        }   

        if(IS_POST){
            A('User','Event')->forconditions($_POST);
            $this->success('发送成功！');
            exit;
        }

        // 警告列表
        if(IS_GET){
            $tmodel = D('SendtextLog');
            $count  = $tmodel->field('id')->where("uid = {$_GET['uid']}")->count();
            $list   = $tmodel->get_multi_items("uid = {$_GET['uid']}"); 
            $this->assign('num',$count);
            $this->assign('list',$list);
        }

        // 分批发布
        $count = D('User')->field('uid')->where()->count();
        $limit = ceil($count/5000);
        for($i=1; $i <= $limit; $i++){
            $start = ($i-1)*5000; 
            $op .= "<option value=$start,5000 >$start,5000</option>";
        }
        $this->assign('op',$op);
        $this->display();
           
    }



}
