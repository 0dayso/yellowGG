<?php
namespace Common\Controller;

class AdminController extends CommonController
{

  public function index()
  {
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    $dat['list'] = $mod->plist(100)->lists();//C('ITEMS_PER_PAGE')
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $this->pager->show();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function view()
  {
    $id = (int)$_REQUEST['id'] ?: (int)$_REQUEST['aid'];
    $mod = D(CONTROLLER_NAME);
    $this->display();
  }

  public function add()
  {
    $dat = array();
    //$dat['auth_rules'] = D('AdminAuthRule')->klist('','','sort,id') ?: array();
    $dat['auth_rules'] = D('AdminAuthRule')->getAllByGroup();
    $this->data = array_merge($this->data ?: array(),$dat);
    $this->display('edit');
  }

  public function edit()
  {
    $id = (int)$_REQUEST['id'] ?: (int)$_REQUEST['aid'];
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    if(!$dat['item'] = $mod->find($id))
    {
      $this->error('对象不存在!');
    }
    $dat['auth_access'] = D('AdminAuthAccess')->klist('rule_id',array('auth_id' => $id)) ?: array();
    $this->data = $dat;
    $this->add();
  }

  public function save()
  {
    $id = (int)$_REQUEST['id'] ?: (int)$_REQUEST['aid'];
    $mod = D(CONTROLLER_NAME);
    $dat = $mod->create();
    if($dat['nickname'] == '')  $this->error('名称不能为空');
    elseif($dat['email'] == '') $this->error('Email不能为空');
    elseif($dat === false)
    {
      $err = $mod->getError();
      $this->error($err);
    }
    if($dat['pwd'] != '') $dat['pwd'] = md5($dat['pwd']);
    else unset($dat['pwd']);
    $cnt = $mod->where(array('nickname' => $dat['nickname']))->count('aid');
    // add
    if($isadd = $id < 1)
    {
      $dat['create_time'] = date('Y-m-d H:i:s');
      if($cnt > 0) $this->error('登录名已存在');
      else $id = (int)$mod->add($dat);
    }
    // edit
    elseif(!$old = $mod->find($id))                                 $this->error('对象不存在');
    elseif($dat['nickname'] != $old['nickname'] && $cnt > 0)        $this->error('登录名已存在');
    elseif($dat['pwd'] && md5($_REQUEST['pwd_old']) != $old['pwd']) $this->error('原密码错误');
    else
    {
      if($mod->where(array('aid' => $id))->save($dat) === false) $this->error('保存失败');
    }
    if($id < 1)
    {
      $this->error('保存失败');
    }
    else
    {
      if(!$isadd && $dat['pwd']) D('OperLog')->log('system',
      [
        '修改管理员密码',
        '管理员'   => $dat['nickname'],
        '管理员ID' => $id,
      ]);
      else D('OperLog')->log('system',
      [
        $isadd ? '新增管理员' : '修改管理员资料',
        '管理员'   => $dat['nickname'],
        '管理员ID' => $id,
      ]);
      $this->save_auth_access($id,$_REQUEST['rules']);//更新权限
      $this->success('保存成功',U('index'));
    }
  }

  protected function save_auth_access($id,$arr = array())
  {
    $dat = array();
    if(is_string($arr)) $arr = preg_split('/\s*,\s*/',$arr);
    if($id >= 1 && is_array($arr))
    {
      $mod = D('AdminAuthAccess');
      $mod->where(array('auth_id' => $id))->delete();
      foreach($arr as $vid)
      {
        $vid = (int)$vid;
        if($vid >= 1) $dat[] = array('auth_id' => $id,'rule_id' => $vid);
      }
      if($dat) $mod->addAll($dat);
      D('Auth')->reset();//重置权限
    }
    return $dat;
  }

  public function del()
  {
    $id = (int)$_REQUEST['id'] ?: (int)$_REQUEST['aid'];
    $mod = D(CONTROLLER_NAME);
    if(!$mod->limit(1)->delete($id)) $this->error('操作失败');
    else
    {
      D('AdminAuthAccess')->where(array('auth_id' => $id))->delete();
      $this->success('操作成功',U('index'));
    }
  }


  // 权限管理
  public function auth_rule()
  {
    $mod = D('AdminAuthRule');
    $dat = array();
    $dat['list'] = $mod->plist(100)->lists('',array('sort,id'));//C('ITEMS_PER_PAGE')
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $this->pager->show();
    $this->data = $dat;
    $this->display();
  }

  public function auth_rule_save()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D('AdminAuthRule');
    $dat = $mod->create();
    if($dat['name'] == '') $this->error('标识不能为空');
    elseif($dat === false)
    {
      $err = $mod->getError();
      $this->error($err);
    }
    $cnt = $mod->where(array('name' => $dat['name']))->count('id');
    // add
    if($isadd = $id < 1)
    {
      if($cnt > 0) $this->error('标识已存在');
      else $id = (int)$mod->add($dat);
    }
    // edit
    elseif(!$old = $mod->find($id))                  $this->error('对象不存在');
    elseif($dat['name'] != $old['name'] && $cnt > 0) $this->error('标识已存在');
    elseif($mod->where(array('id' => $id))->save($dat) === false)
    {
      $this->error('保存失败');
    }
    if($id < 1)
    {
      $this->error('保存失败');
    }
    else
    {
      if($dat['sort'] == '') $mod->where(array('id' => $id))->save(array('sort' => array('exp','id * 10')));
      D('Auth')->reset();//重置权限
      $this->success('保存成功',U('auth_rule'));
    }
  }

  public function auth_rule_del()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D('AdminAuthRule');
    if(!$mod->limit(1)->delete($id)) $this->error('操作失败');
    else
    {
      D('AdminAuthAccess')->where(array('rule_id' => $id))->delete();
      D('Auth')->reset();//重置权限
      $this->success('操作成功',U('auth_rule'));
    }
  }

}