<?php
namespace Cms\Controller;

class UserScoringGroupController extends UserScoringController
{

  public function index()
  {
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    $dat['list'] = $mod->plist(100)->lists();//C('ITEMS_PER_PAGE')
    $ids = array_column($dat['list'],'id');
    if($ids)
    {
      $map = array('group_id' => array('in',$ids));
      $dat['group_ranges'] = D('UserScoringGroupRange')->lists($map);
    }
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function add()
  {
    $dat['ranges'] = D('UserScoringRange')->lists() ?: array();
    $this->data = array_merge($this->data ?: array(),$dat);
    //die(json_encode($this->data));
    $this->display('edit');
  }

  public function edit()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    if(!$dat['item'] = $mod->find($id))
    {
      $this->error('对象不存在!');
    }
    $dat['ranges_this'] = D('UserScoringGroupRange')->where(array('group_id' => $id))->getField('range_id',true) ?: array();
    $this->data = $dat;
    $this->add();
  }

  public function save()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D(CONTROLLER_NAME);
    $dat = $mod->create();

    if(!$dat)
    {
      $err = $mod->getError();
      $this->error($err);
    }
    // add
    if($isadd = $id < 1)
    {
        $id = (int)$mod->add($dat);
    }
    // edit
    elseif($mod->where(array('id' => $id))->save($dat) === false)
    {
      $this->error('保存失败!');
    }
    if($id >= 1)
    {
      D('UserScoringGroupRange')->save_group_range($id,I('range_id'));
      // 删除打分班次redis
      D('PhpServerRedis')->delscoring();
      $this->success('保存成功!',U('index'));
    }
    else
    {
      $this->error('保存失败!');
    }
  }

  public function del()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D(CONTROLLER_NAME);
    if($id < 1)
    {
      $this->error('ID错误');
    }
    elseif(false === $mod->where(array('id' => $id))->delete())
    {
      $this->error('删除失败');
    }
    else
    {
      D('UserScoringGroupRange')->where(array('group_id' => $id))->delete();
    }
    // 删除打分班次redis
    D('PhpServerRedis')->delscoring();
    $this->success('操作成功!',U('index'));
  }

}