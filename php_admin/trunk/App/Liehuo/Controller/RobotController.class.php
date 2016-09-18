<?php
namespace Liehuo\Controller;
use Liehuo\Model;

class RobotController extends PublicController
{

  public function index()
  {
    $sex  = intval($_GET['sex']);
    $list = M('RobotChatSet')->where('del =0 and sex = '.$sex)->order('num asc')->select(); 
    $this->assign('list',$list);
    $this->display('index');
  }

  public function set()
  {
     $id     = $_POST['id'];
     $num    = intval($_POST['num']);
     $content= trim($_POST['content']);
     $sleep  = intval($_POST['sleep']);
     $c_type = intval($_POST['c_type']);
     $sex    = intval($_POST['sex']);

     $have = M('RobotChatSet')->field('num')->where('del = 0 and num = '.$num.' and sex = '.$sex)->find(); 
     if($num == $have['num'] && $id == '' ){
        $this->error('第几条回复,不得重复！');   
     }
     if($content == ''){
        $this->error('内容不能为空！');
     }
     if($num == 0){
        $this->error('请设置第几条回复');
     }
     $data['id']     = $id;
     $data['num']    = $num;
     $data['content']= $content;
     $data['sleep']  = $sleep;
     $data['c_type'] = $c_type;
     $data['sex']    = $sex;
     if($id==''){
        unset($data['id']);
        M('RobotChatSet')->add($data);
     }else{
        M('RobotChatSet')->save($data);
     }
     $this->redirect('index',array('sex'=>$sex)); 
  }

  public function del()
  {
     $id  = intval($_GET['id']);
     $sex  = intval($_GET['sex']);
     if($id == '' )
     $this->error('内容不存在！');
     $data['id']    = $id;
     $data['num']   = 10000;
     $data['del']   = 1;
     M('RobotChatSet')->save($data);
     $this->redirect('index',array('sex'=>$sex)); 
  }


  //运营账号列表
  public function user()
  {
    $mod = D('UserBase');
    $map = $mod->get_filters(true);
    $map['type'] = Model\UserBaseModel::TYPE_ROBOT;
    $dat['list'] = $mod->plist($this->page_size,$map)->klist('uid','','reg_time desc,uid desc');
    $dat['list'] = $mod->format_nickname_all($dat['list'] ?: []);
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['user_types'] = $mod->user_types ?: array();
    
    
    $rds    = $mod->get_redis();
    $robots = $rds->hGetAll('robot_sex_list') ?: [];
    
    if(!empty($dat['list'])){
      foreach ($dat['list'] as $key => $value) {
         if(in_array($value['uid'], $robots)){
             $dat['list'][$key]['robot'] = 1;
         }
      }
    }
    /*echo '<pre>';
    print_r($robots);
    die();*/

    $this->data = $dat;
    $this->display();

  }

  //设置运用
  public function robotset(){
    $uid = intval($_GET['uid']);
    $sex = intval($_GET['sex']);
    if($uid <= 1200000){
        $this->error('用户出错');
    }
    $mod = D('UserBase');
    $rds = $mod->get_redis();
    $rds->sAdd('robot_list',$uid);
    $rds->hSet('robot_sex_list',$sex,$uid);
    $this->redirect('user');

  }

  //删除运用账号
  public function robotdel(){
    $uid = intval($_GET['uid']);
    $sex = intval($_GET['sex']);
    if($uid <= 1200000){
        $this->error('用户出错');
    }
    $mod = D('UserBase');
    $rds = $mod->get_redis();
    $rds->sRem('robot_list',$uid);
    $rds->hDel('robot_sex_list',$sex);
    $this->redirect('user');
  }


  // 运营账号列表
  public function virtuals()
  {
    isset($_REQUEST['type']) || $_REQUEST['type'] = Model\UserBaseModel::TYPE_VIRTUAL;
    $mod = D('UserBase');
    $map = $mod->get_filters(true);
    $dat['list'] = $mod->plist($this->page_size,$map)->klist('uid','','reg_time desc,uid desc');
    if($dat['list'])
    {
      $rds = $mod->get_redis();
      $dat['list'] = array_map(function($v) use($rds)
      {
        $v['remark'] = $rds->hGet('php_robot_'.$v['uid'],'remark');
        return $v;
      },$dat['list'] ?: []);
      $dat['list'] = $mod->format_nickname_all($dat['list'] ?: []);
    }
    $this->pager = $mod->pager;
    $dat['user_types'] = $mod->user_types ?: [];
    $this->data = $dat;
    $this->display();
  }

  // 设置运营账号备注
  public function remark()
  {
    $uid = (int)$_REQUEST['uid'];
    $rmk = trim($_REQUEST['remark']);
    if(!$uid) $this->error('参数错误');
    elseif(!$rmk)
    {
      D('UserBase')->get_redis()->hDel('php_robot_'.$uid,'remark');
    }
    else
    {
      D('UserBase')->get_redis()->hSet('php_robot_'.$uid,'remark',$rmk);
    }
    $this->success('操作成功');
  }

}