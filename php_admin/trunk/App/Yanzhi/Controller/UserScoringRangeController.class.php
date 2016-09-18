<?php
namespace Yanzhi\Controller;

class UserScoringRangeController extends UserScoringController
{

  public function index()
  {
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    $dat['list'] = $mod->plist(100)->lists();//C('ITEMS_PER_PAGE')
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function add()
  {
    $this->display('edit');
  }

  public function edit()
  {
    $id = (int)I('request.id');
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    if(!$dat['item'] = $mod->find($id))
    {
      $this->error('对象不存在!');
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function save()
  {
    $id = (int)I('request.id');
    $mod = D(CONTROLLER_NAME);
    $dat = $mod->create();
    if(!$dat)
    {
      $err = $mod->getError();
      $this->error($err);
    }
    elseif($dat['work_start'] >= $dat['work_end'] && $dat['work_end'] > 0)
    {
      $this->error('时间范围错误!');
    }
    // add
    if($isadd = $id < 1)
    {
      $id = $mod->add($dat);
    }
    elseif(false === $mod->where(array('id' => $id))->save($dat))
    {
      $this->error('保存失败!');
    }
    // 删除打分班次redis
    D('UserScoring')->del_user_range_cache();
    D('PhpServerRedis')->delscoring();
    $this->success('保存成功!',U('index'));
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
      D('UserScoringGroupRange')->where(array('range_id' => $id))->delete();
    }
    // 删除打分班次redis
    D('UserScoring')->del_user_range_cache();
    D('PhpServerRedis')->delscoring();
    $this->success('操作成功!',U('index'));
  }

}