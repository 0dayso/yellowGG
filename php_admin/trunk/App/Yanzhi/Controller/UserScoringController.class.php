<?php
namespace Yanzhi\Controller;

class UserScoringController extends PublicController
{

  public function index()
  {
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    $dat['list'] = $mod->plist(100)->lists_with_range();//C('ITEMS_PER_PAGE')
    if($ids = array_unique(array_column($dat['list'],'uid')))
    {
      $map = array('uid' => array('in',$ids));
      $dat['users'] = D('UserBase')->field(array('uid','nickname','phone'))->klist('uid',$map);
    }
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function index_with_group()
  {
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    $dat['list'] = $mod->plist(100)->lists();//C('ITEMS_PER_PAGE')
    if($ids = array_unique(array_column($dat['list'],'group_id')))
    {
      $map = array('group_id' => array('in',$ids));
      $dat['group_ranges'] = D('UserScoringGroupRange')->lists($map);
    }
    if($ids = array_unique(array_column($dat['list'],'uid')))
    {
      $map = array('uid' => array('in',$ids));
      $dat['users'] = D('UserBase')->field(array('uid','nickname','phone'))->lists($map);
      $dat['users'] = array_combine(array_column($dat['users'],'uid'),$dat['users']);
    }
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function add()
  {
    $dat['groups'] = D('UserScoringGroup')->lists() ?: array();
    $dat['ranges'] = D('UserScoringRange')->lists() ?: array();
    $this->data = array_merge($this->data ?: array(),$dat);
    $this->display('edit');
  }

  public function edit()
  {
    $uid = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    if(!$dat['item'] = $mod->find($uid))
    {
      $this->error('对象不存在!');
    }
    $this->data = $dat;
    $this->add();
  }

  public function save()
  {
    $uid = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    $mod = D(CONTROLLER_NAME);
    $usr = D('UserBase');
    $dat = $mod->create();
    // 用户注册
    if(trim($_REQUEST['act']) == 'reg')
    {
      $udt = array();
      $udt['nickname'] = trim($_REQUEST['nickname']);
      $udt['phone']    = trim($_REQUEST['phone']);
      $udt['sex']      = (int)$_REQUEST['sex'];
      $udt['password'] = $_REQUEST['password'];
      if(!$udt['nickname'])
      {
        $this->error('昵称不能为空');
      }
      elseif(!$udt['password'])
      {
        $this->error('密码不能为空');
      }
      elseif(!preg_match('/^1[34578]\d{9}$/i',$udt['phone']))
      {
        $this->error('手机号格式错误');
      }
      elseif($usr->where(array('phone' => $udt['phone']))->count('uid') > 0)
      {
        $this->error('手机号已被注册');
      }
      else
      {
        $udt['password'] = $usr->password_encrypt($udt['password']);
        $udt['reg_time'] = time();
        $uid = (int)$usr->add($udt);
      }
    }
    $dat['uid'] = $uid;
    if($uid < 1)
    {
      $this->error('用户ID错误');
    }
    if((int)$dat['group_id'] < 1 && (int)$dat['range_id'] < 1)
    {
      $this->error('请选择班次或分组');
    }
    if(!$dat)
    {
      $err = $mod->getError();
      $this->error($err);
    }
    if(!$user = $usr->find($uid))
    {
      $this->error('用户不存在!');
    }
    // add
    if($isadd = $mod->where(array('uid' => $uid))->count('uid') < 1)
    {
      $mod->add($dat);
    }
    // edit
    elseif(false === $mod->where(array('uid' => $uid))->save($dat))
    {
      $this->error('保存失败!');
    }
    // 设置用户为打分团
    if($uid >= 1 && (int)$user['type'] !== 2)
    {
      $usr->where(array('uid' => $uid,'type' => 0))->save(array('type' => 1));
      D('PhpServerRedis')->del_user_info($uid);
    }
    // 删除打分班次redis
    $mod->del_user_range_cache();
    D('PhpServerRedis')->delscoring();
    $this->success('保存成功!',U('index'));
  }

  protected function user_reg()
  {
    //
  }

  public function del()
  {
    $uid = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    $mod = D(CONTROLLER_NAME);
    if($uid < 1)
    {
      $this->error('用户ID错误');
    }
    elseif(false === $mod->where(array('uid' => $uid))->delete())
    {
      $this->error('删除失败');
    }
    else
    {
      D('UserBase')->where(array('uid' => $uid,'type' => 1))->save(array('type' => 0));
      D('PhpServerRedis')->del_user_info($uid);
    }
    // 删除打分班次redis
    $mod->del_user_range_cache();
    D('PhpServerRedis')->delscoring();
    $this->success('操作成功!',U('index'));
  }

}