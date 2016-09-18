<?php
namespace ChuChai\Controller;
use ChuChai\Model;

class SettingController extends PublicController
{

  public function __construct()
  {
    parent::__construct();

    $this->navs =
    [
      CONTROLLER_NAME.'/model_list' => '模型管理',
      CONTROLLER_NAME.'/field_list' => '属性管理',
    ];
  }

  // 模型设置
  public function model_list()
  {
    $id  = (int)$_REQUEST['id'];
    $mod = D('Model');
    $map = ['delete_time' => 0];
    $dat['list'] = $mod->klist(true,$map,'create_time,id');
    $id && $dat['item'] = $dat['list'][$id] ?: [];
    if($dat['item'])
    {
      unset($dat['list'][$id]);
      array_unshift($dat['list'],$dat['item']);
    }
    $this->data = $dat;
    $this->display();
  }

  public function model_save()
  {
    $id  = (int)$_REQUEST['id'];
    $mod = D('Model');
    $dat = $mod->create();
    $isadd = $id < 1;
    if    ($dat === false) $this->error($mod->getError() ?: '参数错误');
    elseif(!$dat['name'])  $this->error('名称不能为空');
    else
    {
      unset($dat['id']);
      if($isadd)
      {
        $dat['create_time'] = NOW_TIME;
        $id = (int)$mod->add($dat);
      }
      else
      {
        if(!$mod->where(['id' => $id])->save($dat)) $id = 0;
      }
      if($id < 1) $this->error('操作失败');
      else
      {
        $dat['id'] = $id;
      }
    }
    $this->data = ['item' => $dat];
    $this->success('操作成功',U('model_list'));
  }


  // 属性设置
  public function field_list()
  {
    $id  = (int)$_REQUEST['id'];
    $mod = D('Field');
    $map = ['delete_time' => 0];
    if($mid = (int)$_REQUEST['model_id']) $map['model_id'] = $mid;
    $dat['list'] = $mod->klist(true,$map,'sort,create_time,id');
    $dat['list'] = $mod->complete_all($dat['list']);
    $id && $dat['item'] = $dat['list'][$id] ?: [];
    if($dat['item'])
    {
      //unset($dat['list'][$id]);
      //array_unshift($dat['list'],$dat['item']);
    }
    $dat['types']  = $mod->types ?: [];
    $dat['models'] = D('Model')->klist(true,['delete_time' => 0]);
    $this->data = $dat;
    $this->display();
  }

  public function field_save()
  {
    $id  = (int)$_REQUEST['id'];
    $mod = D('Field');
    $dat = $mod->create();
    $isadd = $id < 1;
    if    ($dat === false) $this->error($mod->getError() ?: '参数错误');
    elseif(!$dat['name'])  $this->error('名称不能为空');
    elseif(!$dat['model_ids']) $this->error('请选择模型');
    else
    {
      unset($dat['id']);
      if($isadd)
      {
        $dat['create_time'] = NOW_TIME;
        $id = (int)$mod->add($dat);
      }
      else
      {
        if(!$mod->where(['id' => $id])->save($dat)) $id = 0;
      }
      if($id < 1) $this->error('操作失败');
      else
      {
        $dat['id'] = $id;
        $sort = $mod->auto_sort_set($id);//自动排序
        if($sort !== false) $dat['sort'] = $sort;
        $dat = $mod->attr2array_row($dat);
      }
    }
    $this->data = ['item' => $dat];
    $this->success('操作成功',U('field_list'));
  }

  public function field_del()
  {
    $id = (int)$_REQUEST['id'];
    if(!D('Field')->soft_del($id))
    {
      $this->error('操作失败');
    }
    $this->success('操作成功',U('field_list'));
  }

}