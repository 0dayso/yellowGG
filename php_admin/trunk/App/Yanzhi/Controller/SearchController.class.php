<?php
/**
 * Created by PhpStorm.
 * User: zsh
 * Date: 2015/5/15
 * Time: 17:17
 */

namespace Yanzhi\Controller;

use Cms\Controller\MessageController;
use Think\Cache\Driver\Redis;

class SearchController extends PublicController{

    //标签搜索
    public function stag(){
        $this->assign('list',A('Search','Event')->getlist($_GET));
        $this->display();
    }
    //用户搜索
    public function suser(){
        $data = A('Search','Event')->getuserlist($_GET);
        $this->assign('list',$data['list']);
        $this->assign('page',$data['page']);
        $this->display();
    }
        

    // 金币搜索
    public function gold(){
        $this->assign('list',A('Search','Event')->getgoldlist($_GET));
        $this->display();
    }

    // 金币比例
    public function testgold(){
        $this->assign('goldfb',A('Search','Event')->luotgold());
        layout(false);
        echo $this->fetch();
    }

    // 查看金币详情
    public function goldinfo(){
        $this->assign('list',A('Search','Event')->getgoldinfo($_GET));
        $this->display();
    }



    // 奖品列表
    public function prizelist(){
        
        $this->assign('list',A('Search','Event')->hostpasiz('今日兑换'));
        $this->display();
    }

    // 奖品编辑
    public function editprize(){
        if(IS_POST){

            $p1 = $_FILES['file'];
            if($p1['name']!=''){ // 修改图片
                $bucket  = 'surging';
                $surging['thumb'] = $surging['resource'] = rand().$p1['name'];
                $ret     = $this->aliyup($bucket,$surging['thumb'],$p1['tmp_name']); // 上传新图片
                if($_POST['pic']!=''){
                    $del     = $this->aliydel($bucket,$_POST['pic']);  // 删除图片
                }
            }

            $surging['description']  = str_replace(' ','',$_POST['dis']);
            D('Surging')->update_single_item('id = '.$_POST['id'],$surging);

            $this->success('修改成功！');

        }else{
            if($_GET['id']==''){
                $this->redirect('search/prizelist');
            }
            $this->assign('info',A('Search','Event')->gatalltagsurging($_GET['id']));
            $this->display('addacprize');
        }
    }

    // 添加 奖品
    public function addacprize(){
        if(IS_POST){
            $post = $_POST;
            $p1 = $_FILES['file'];
            if($p1['name']!=''){ // 修改图片
                $bucket  = 'surging';
                $thumb   = md5($p1['name']).end(explode($p1['name']));
                $ret     = $this->aliyup($bucket,$thumb,$p1['tmp_name']); // 上传新图片
            }

            // 添加动态
            $s_data['uid']          = 10002;
            $s_data['type']         = 1;
            $s_data['description']  = trim($post['dis']);
            $s_data['create_time']  = time();
            $s_data['status']       = 1;
            $s_data['thumb']        = $s_data['resource'] = $thumb;
            $s_data['create_time']  = time();
            $surging_id  = D('Surging')->add($s_data);


            // 添加动态关系链 cj_user_tag_surging
            $u_tag['uid']         = 10002;
            $u_tag['user_tag_id'] = trim($post['u_tag']);
            $u_tag['surging_id']  = $surging_id;
            $ts_id = D('UserTagSurging')->add($u_tag);

            if($ts_id!='' && $surging_id!=''){
                $this->success('添加成功！');
            }

        }else{
            $this->assign('info',A('Search','Event')->getsystemtag());
            $this->display();
        }
    }

    // 活动列表
    public function hostlist(){

        $this->assign('list',A('Search','Event')->hostpasiz('活动'));
        $this->assign('fc',ACTION_NAME);
        $this->display('prizelist');
    }

    // 添加活动 
    public function host(){
        $this->assign('info',A('Search','Event')->getsystemtag('活动'));
        $this->assign('fc',ACTION_NAME);
        $this->assign('action','/index.php/search/addacprize');
        $this->display('addacprize');
    }
    
    // 活动编辑 
    public function deithost(){
        $this->assign('info',A('Search','Event')->infopasize($_GET['id']));
        $this->assign('fc',ACTION_NAME);
        $this->assign('action','/index.php/search/editprize');
        $this->display('addacprize');
    }
 
    // 活动后台
    public function achostset(){
        if (IS_POST) {
            if(!empty($_POST)){

                foreach ($_POST['utag'] as $key => $value) {
                    $ke = explode('-',$value);
                    if(!isset($array[$ke[0]])){
                        $array[$ke[0]] = '';
                    }
                    $array[$ke[0]] = $ke[1].','.$array[$ke[0]];
                }
                foreach ($array as $key => $value) {
                    $tag .= $value;                 
                }
                $post['tag'] = implode(',',array_unique(explode(',',rtrim($tag,','))));
                $post['uid'] = implode(',',array_keys($array));
                $list = A('Search','Event')->getachostset('',$post);

            }

        }else{
            $list = A('Search','Event')->getachostset($_GET);
        }
        
        $this->assign('list',$list);
        $this->assign('select',D('Library')->get_multi_items('title !=""'));
        $this->display();
    
    }


    // 库列表
    public function library(){
        $this->assign('list',D('Activity')->searchs());
        $this->display();
    }

    // 添加运营活动
    public function addlibrary(){
        if(IS_POST){
            if($_FILES['image']['name']!=''){
                $pic     = date('YmdHis',time()).rand(1000,9999);
                $ret     = $this->aliyup('cjstatic','activity/'.$pic,$_FILES['image']['tmp_name']);
                if($ret->status == 200){
                    $data['image'] = $pic;
                }
            }
            $data['title']       = $_POST['title'];
            $data['description'] = trim($_POST['description']);
            $data['valid_begin'] = strtotime($_POST['valid_begin']);
            $data['valid_end']   = strtotime($_POST['valid_end']);
            $data['create_time'] = time();
            $id = D('Activity')->add($data);
            if($id>0){
                $this->success('添加成功！');
            }
        }else{
            $this->display();   
        }
    }

    // edit库
    public function editlibrary(){
        if(IS_POST){
            if($_FILES['image']['name']!=''){
                $pic     = date('YmdHis',time()).rand(1000,9999);
                $ret     = $this->aliyup('cjstatic','activity/'.$pic,$_FILES['image']['tmp_name']);
                if($ret->status == 200){
                    $url = D('Activity')->search('id = '.$_POST['id'],'image');
                    $this->aliydel('cjstatic',$url['image']);
                    $data['image'] = $pic;
                }
            }
            $data['title']       = $_POST['title'];
            $data['description'] = trim($_POST['description']);
            $data['valid_begin'] = strtotime($_POST['valid_begin']);
            $data['valid_end']   = strtotime($_POST['valid_end']);
            $data['create_time'] = time();
            $id = D('Activity')->where('id = '.$_POST['id'])->save($data);
            if($id!==false){
                $this->success('修改成功！');
            }
        }else{
            $this->assign('info',D('Activity')->search('id ='.$_GET['id']));
            $this->display('addlibrary');   
        }
    }

    // 删除库用户
    /*public function deluser(){
        if(IS_POST){
            $exp = explode('-',$_POST['id']);
            $map['id'] =  $exp[0];
            $map['uid']        =  $exp[1];
            $del = D('LibraryUser')->where($map)->delete();
            if($del!==false){
                echo 'ok';
            }
        }
    }*/

    // 标签审核
    public function tagexamine(){
        $this->assign('list',A('Search','Event')->tagck($_GET['type']));
        $this->display();
    }

    // 标签任务大厅
    public function tagfor(){
        $getdata = A('Search','Event')->tagfor($_GET['type']);
        $this->assign('list',$getdata['list']);
        $this->assign('page',$getdata['page']);
        $Admin = D('Admin');
        $adminArr = $Admin->get_all();
        $this->assign("admin",$adminArr);
        $this->assign('selects',A('Search','Event')->tagclass());
        $this->display();
    }

    // 标签分配任务
    public function allocate_task_to_tag(){
        if(IS_POST){
            A('Search','Event')->allocate_task_to_tag($_POST);
        }
    }
    // 标签重新分配任务
    public function allocate_task_again_to_tag(){
        if(IS_POST){
            A('Search','Event')->allocate_task_again_to_tag($_POST);
        }
    }


    // 标签全部通过
    public function tagko(){
        if(IS_POST){
            $tag = trim($_POST['list']);
            A('Search','Event')->deltagredis($tag); // 删除 tag redis
            A('Search','Event')->sendimtag($tag);   // 给用户发 im
            $update = D('UserTag')->where("id IN($tag) ")->data(array('certificate'=>2))->save();
            if($update!==false){
                echo 'ok';
            }
        }

    }

    // 标签确认
    public function confirm_tag_request(){
        if(IS_POST){
            $id = trim($_POST['list']);
            if($id!= ''){
                $data['confirm']      = $_SESSION['authId'];
                $data['confirm_time'] = time();
                D('TagLog')->update_multi_items("  tag_id IN($id) ",$data);
                echo 'ok';
            }else{
                echo 'no';
            }
        }
    }

    // 我的未审核标签
    public function mytag(){
        $data = A('Search','Event')->mytag();
        $this->assign('list',$data['list']);
        $this->assign('page',$data['page']);
        $this->assign('selects',A('Search','Event')->tagclass());
        $this->display('tagfor');
    }

    //标签审核通过
    public function confirm_all_pass(){
        $id = trim($_POST['list']);
        if(IS_POST &&  $id != '' ){
            $tdata['certificate']      = 2;
            $tdata['certificate_time'] = time();
            D('UserTag')->update_multi_items("  id IN($id) ",$tdata);
            $tldata['ck_time']      = time();
            $tldata['certificate']  = 2;
            D('TagLog')->update_multi_items("  tag_id IN($id) ",$tldata);
            A('Search','Event')->cleartaginfo($id);
            echo 'ok';
        }else{
            echo 'no';
        }
    }


    // 标签删除
    public function delete_tag(){
        if(IS_POST){
            echo A('Search','Event')->delete_tag($_POST['id'],$_POST['name'],$_POST['type']);
        }
    }

    // 便签不通过
    public function tagnopass(){
        if(IS_POST){
            $id = trim($_POST['id']);
            A('Search','Event')->deltagredis($id); // 删除 tag redis
            A('Search','Event')->tagnopass($id,$_POST['remarks']);
            echo 'ok';
        }

    } 

    // 发放金币
    public function sendgold(){
        if(IS_POST){
            $info = A('Search','Event')->sendgold($_POST['user'],$_POST['golds'],$_POST['lib']);
            $this->success('金币发放完成！'.$info);
        }
    }

    // 邀请码
    public function invitation(){
        $code_type = $_GET['code_type'];
        $this->assign('list',A('Search','Event')->invitation($code_type,$_GET));
        $this->display();
    }   

    // 查看邀请证码注册的用户列表
    public function regcodelist(){
        $code = $_GET['code'];
        $this->assign('list',A('Search','Event')->regcodelist($code));
        $this->display();
    }

    // 生成验证码
    public function codecreate(){
        $code = A('Search','Event')->codecreate($_POST['type'],$_POST['num']);
        echo 'ok';
      
    }

    // 验证码备注修改
    public function editinvbz(){
        if(IS_POST){
            $map['id']    = $_POST['id'];
            $data['memo'] = str_replace(' ','',$_POST['memo']);
            D('InvitationCode')->update_single_item($map,$data);
            echo 'ok';
        }

    }

    // 更改上限
    public function updatelimit(){
        if(IS_POST){
            $map['id']           = $_POST['id'];
            $data['used_limit']  = intval($_POST['used_limit']);
            D('InvitationCode')->update_single_item($map,$data);
            echo 'ok';
        }

    }

    // 获取用户动态内容
    public function get_surging_html(){
        $uid = $_POST['uid'];

        if($_POST['where']=='banner_id'){
            $and = ' and banner_id =  '.$_POST['id'];
        }
        if($_POST['where']=='recommend_id'){
            $and = ' and recommend_id =  '.$_POST['id'];
        }

        $uset = D('SetSurging')->field('surging_id')->where('uid ='.$uid.$and)->select();
        if(empty($uset)){
            $uset = D('SetSurging')->field('surging_id')->where('uid ='.$uid)->select();
        }

        $uset = array_column($uset,'surging_id');
        $authentication = D('UserTag')->authentication($uid,'noget');
        $this->assign('authentication',get_group($authentication,'ut_id'));
        $this->assign('uset',$uset);
        $this->assign('uid',$uid);
        $this->assign('post',$_POST);
        layout(false);
        echo $this->fetch();
    }

    public function addsetsurging(){

        if(IS_POST){

            if(empty($_POST)){
                $this->error('请选择动态！');
            }

            $uid  = intval($_POST['uid']);
            $id   = $_POST['id'];
            $type = $_POST['type'];
            $userinfo  = D('User')->search('uid = '.$uid);
            $surging  = D('Surging');
            $tagmodel = D('UserTag');
            $recommenduser = D('RecommendUser');
            $content  = array();
            $i=0;

            foreach ($_POST['surgings'] as $key => $value) {
                $ex = explode(':',$value);
                $data[$i]['surging_id'] = $ex[1];
                $data[$i]['user_tag_id'] = $ex[0];
                $data[$i]['uid']         = $uid;
                if($_POST['where']=='banner_id'){
                    $data[$i]['banner_id'] = $id;
                }
                if($_POST['where']=='recommend_id'){
                    $data[$i]['recommend_id'] = $id;
                    // 向content 里添加内容
                    $surginginfo = $surging->search('id='.$ex[1],'thumb,resource');
                    $usertaginfo = $tagmodel->search('id = '.$ex[0],'title');
                    $content[$i]['surging_id']  = $ex[1];
                    $content[$i]['user_tag_id'] = $ex[0];
                    $content[$i]['title']       = $usertaginfo['title'];
                    $content[$i]['thumb']       = 'http://surging.chujian.im/'.$surginginfo['thumb'];
                    if(trim($surginginfo['resource']) != ''){
                        $content[$i]['resource'] = 'http://surging.chujian.im/'.$surginginfo['resource'];
                    }else{
                        $content[$i]['resource'] = '';
                    }

                }
                $i++;
            }

            if(!empty($content) && $_POST['where']=='recommend_id'){
                $content_json = json_encode(array('tag_info'=>$content,'uid'=>$userinfo['uid']));
                $recommenduser->where(" group_id = $id and uid = $uid ")->save(array('content'=>$content_json));
                D('PhpServerRedis')->updateusertagsurging($id); // 更新redis
            }

            if($_POST['where']=='banner_id'){
                $and = " uid = $uid and banner_id = ".$id;
            }
            if($_POST['where']=='recommend_id'){
                $and = " uid = $uid and  recommend_id = ".$id;
            }

            $model = D('SetSurging');
            $model->delete_single_item($and);
            $model->addAll($data);

            if($_POST['type']){
                $this->redirect('banner/bannertwo',array('id'=>$id,'type'=>$type));
            }else{
                $this->redirect('recommend/putinfoadduser',array('id'=>$id));
            }

        }
    }

    public function judgetime(){
        $begin = strtotime($_POST['valid_begin']);
        $end   = strtotime($_POST['valid_end']);
        $id    = trim($_POST['id']);

        if($end <= $begin){
            echo '结束时间必须大于开始时间';
            exit;
        }

        $map['valid_end']   = array('egt',$begin);
        if($id){
            $map['id']      = array('NEQ',$id);
        }

        $info = D(trim($_POST['type']))->where($map)->find();

        if(!empty($info)){
            echo 'have';
        }else{
            echo 'ok';
        }

    }


    public function taglistuser(){
        $id = $_GET['id'];
        $this->assign('list',D('UserTag')->get_multi_items('id = '.$id,'uid,create_time'));
        $this->display();
    }

    // 红点推送
    public function redpushlist(){
        $this->assign('list',D('RedPush')->where()->order('id desc')->select());
        $this->display();
    }

    // 红点推送
    public function redpush(){
        if(IS_POST){
            $ret = A('Search','Event')->redpush($_POST);
        }else{
            $this->spermission(50);
            $this->display();
        }
    }


    // 后台发系统消息
    public function adminsend(){
        if(IS_POST){
            $ret = A('Search','Event')->adminsend($_POST);
            if(is_int($ret) == true && $ret >0 ){
                $this->success('发送成功！');
            }
        }else{
            $this->spermission(50);
            $this->display();
        }
    }

    // 搜索用户
    public function ajaxsearchuser(){
        $phone = $_POST['phone'];
        if(strlen($phone) >=5 ) {

            $User = D('User');
            $uid = implode(',', array_column($User->searchs(" server_version = 1 and phone like '" . $phone . "%' ", 'uid'), 'uid'));
            $list = $User->searchs(" uid IN($uid) ", 'uid,nickname,face_url');
            if (!empty($list)) {
                foreach ($list as $key => $val) {
                    $li .= '<li  class="xxim_childnode"  >
							<img src="http://headimage.oss-cn-hangzhou.aliyuncs.com/' . $val['face_url'] . '" class="xxim_oneface">
							<div class="xxim_onename">
							    <span>&nbsp;' . $val['nickname'] . '<br>UID： ' . $val['uid'] . ' </span>
                            </div>

						</li>';
                }
                echo $li;
            }


        }

    }

    // 查询验证码
    public function  phonecode(){
        $code = D('Phone')->search("phone = ".intval($_POST['phone']),'code');
        if($code['code']){
            echo '验证码：'.$code['code'];
        }else{
            echo '没有找到验证码！';
        }
    }

    public function phonecode_base()
    {
        $code = D('PhoneBase')->where(array('phone' => $_REQUEST['phone']))->find();
        if($code['code'])
        {
          echo '验证码：'.$code['code'];
        }else{
          echo '没有找到验证码！';
        }
    }

    //选择热门动态
    public function selecthot(){
        $this->spermission(56);
        $this->assign('list',A('Search','Event')->selecthot($_GET['uid']));
        $this->display();
    }


    //当前热门
    public function nowhot(){
        $this->spermission(57);
        $this->assign('list',A('Search','Event')->nowhot());
        $this->display();
    }
    // 删除当前热门
    public function delhost(){
        if(IS_POST){
            A('Search','Event')->delhost($_POST['user_tag_id']);
            echo 'ok';
        }
    }


    // 修改标签类别
    public function change(){
        if(IS_AJAX){
            $tagid   = $_POST['tag'];
            $change  = D('UserTag')->where('id ='.$tagid)->save(array('tag_class_id'=>$_POST['clect']));
            if($change!==false){
                echo 'ok';
            }
        }else{
            $this->assign('list',A('Search','Event')->changeusertag());
            $this->display();
        }

    }


    public function  tagclasschange(){
        if($_POST['classid']==''){
            echo 'no';exit;
        }
        $id = intval($_POST['id']);
        $ch = D('UserTag')->where(" id = {$id} ")->save(array('tag_class_id'=>$_POST['classid']));
        D('PhpServerRedis')->delete_user_tag($id);
        $Message  = new MessageController();
        $info     = D('UserTag')->search('id = '.$id,'title');
        $content  = '为了更好的体验，我们已将您的（'.$info['title'].'）标签移至更为精准的分类下，还请您知晓！';
        $Message->send_system_message($info['uid'], 'search', 'tag',$scontent);
        if($ch!==false){
            echo 'ok';
        }
    }

    // 模拟点赞
    public function visaup(){
        if(IS_POST){
            $info = A('Search','Event')->sendup($_POST['tagid'],$_POST['uid'],$_POST['num']);
            if($info=='no'){
                echo 'no';
            }else{
                echo 'ok';
            }
        }
    }

    // 后台活动
    public function settatclass(){
        $this->assign('list',D('TagClass')->searchs('type = 1'));
        $this->display();
    }

    public function settatclassadd(){
        if(IS_POST){
            $data['name']        = trim($_POST['name']);
            $data['addtime']     = time();
            $data['type']        = 1;
            $data['image']       = 'test.img';
            $data['valid_begin'] = strtotime(trim($_POST['valid_begin']));
            $data['valid_end']   = strtotime(trim($_POST['valid_end']));
            if($_POST['id']){
                $data['id'] = $_POST['id'];
                $id = D('TagClass')->save($data);
            }else{
                $sql = 'SELECT max(id) as id FROM cj_tag_class WHERE id <50000 ';
                $max = D('User')->query($sql);
                $data['id'] = $max[0][id]+1;
                $id = D('TagClass')->add($data);
            }

            if($id){
                $this->redirect('search/settatclass');
            }else{
                $this->error('添加失败！');
            }
        }else{
            $this->assign('info',D('TagClass')->search('id ='.$_GET['id']));
            $this->display();
        }

    }

    public function delsettatclass(){
        if(IS_POST){
            $id  = $_POST['id'];
            $del = D('TagClass')->delete_single_item("id = $id");
            if($del !==false){
                echo 'ok';
            }else{
                echo 'no';
            }
        }
    }





    /*public function imports(){
        $content  = $_SERVER['DOCUMENT_ROOT'].'/Public/class/class_1.txt';
        $content  = file_get_contents($content);

        //获取本文件目录的文件夹地址

        $filesnames = scandir($_SERVER['DOCUMENT_ROOT'].'/Public/class/img');

        foreach ($filesnames as $name) {
            if($name != '.' || $name != '..' ){
                $imgname = explode('_',$name);
                $img[$imgname[0]] = $imgname[1];
            }
        }
        $content  = explode(',',$content);
        $k = 50000;
        foreach($content as  $val){
            $name  = explode('_',$val);
            $data['name']  = $name[1];
            $data['image'] = $img[$name[0]];
            $data['type']  = 0;
            $data['addtime']  = time();
            $data['id']    = $k;
            $k++;
            D('TagClass')->add($data);
        }
    }*/
    // 手动点赞记录
    public function praiselist(){
        $this->assign('list',A('Search','Event')->praiselist($_GET['tag']));
        $this->display();
    }

    // 清理标已删除的标签的动态
    /*public function delnotusertag(){


        $sql = "SELECT  s.*
                FROM cj_surging as s
                WHERE  s.id NOT IN (SELECT surging_id FROM cj_user_tag_surging) ";
        $list= D('User')->query($sql);

        $surging = D('Surging');
        foreach($list as $k =>$val){
            if($val['thumb']!=''){
                $this->aliydel('surging',$val['thumb']);
            }
            $surging->where('id = '.$val['id'])->delete();
        }
        echo 'ok';

    }*/


      
}