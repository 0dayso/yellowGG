<?php
namespace ChuChai\Controller;
use ChuChai\Model;

class ShopController extends PublicController
{

  public function __construct()
  {
    parent::__construct();

    $nls =
    [
      CONTROLLER_NAME.'/index' => '查看列表',
      //CONTROLLER_NAME.'/edit'  => '添加记录',
    ];
    $mls = D('Model')->get_models() ?: [];
    foreach($mls as $k => $v)
    {
      $nls[CONTROLLER_NAME.'/edit?model_id='.$k] = '添加'.$v['name'];
    }
    $this->navs = $nls;
  }

  public function index()
  {
    $mod = D(CONTROLLER_NAME);
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','create_time desc,sid desc');
    $dat['page'] = $this->pager = $mod->pager;
    $dat['locs'] = D('Location')->get_by_list($dat['list']);
    $dat['admins'] = D('Admin')->get_by_list($dat['list'],'aid,nickname','admin_id');
    $dat['models'] = D('Model')->get_models();
    $dat['fields'] = D('Field')->get_fields((int)$_REQUEST['model_id']);
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function edit()
  {
    $sid = (int)$_REQUEST['sid'];
    $mid = (int)$_REQUEST['model_id'];
    $mod = D(CONTROLLER_NAME);
    $dat = [];
    if($sid >= 1)
    {
      $dat['item']     = $mod->find($sid);
      $dat['location'] = D('Location')->find($sid);
      $dat['values']   = D('ShopField')->get_fields($sid);
    }
    elseif($mid >= 1) $dat['item']['model_id'] = $mid;
    else $this->error('请选择模型');
    $dat['models'] = D('Model')->get_models();
    $dat['fields'] = D('Field')->get_fields($dat['item']['model_id'] ?: $mid);
    if($_REQUEST['copy']) unset($dat['item']['sid']);
    $this->data = $dat;
    $this->display();
  }

  public function save()
  {
    $sid = (int)$_REQUEST['sid'];
    $mod = D(CONTROLLER_NAME);
    $lmd = D('Location');
    $dat = $mod->create();
    $ldt = $lmd->create();
    $isadd = $sid < 1;
    if    ($dat === false) $this->error($mod->getError() ?: '参数错误');
    elseif($ldt === false) $this->error($lmd->getError() ?: '参数错误');
    elseif(!$dat['name'])  $this->error('名称不能为空');
    else
    {
      $dat['admin_id'] = (int)$this->aid;
      unset($dat['sid'],$ldt['sid']);
      if($isadd)
      {
        $dat['create_time'] = time();
        $sid = (int)$mod->add($dat);
        if($sid >= 1)
        {
          $ldt['sid'] = $sid;
          $lmd->add($ldt);
          D('ShopField')->set_fields($sid,$_REQUEST['fields']);
        }
      }
      else
      {
        $ret = $mod->where(['sid' => $sid])->save($dat)             !== false;
        $ret = $lmd->where(['sid' => $sid])->save($ldt)             !== false && $ret;
        $ret = D('ShopField')->set_fields($sid,$_REQUEST['fields']) !== false && $ret;
        if(!$ret) $sid = 0;
      }
      if($sid < 1) $this->error('操作失败',U('index'));
      else
      {
        $dat['sid'] = $sid;
      }
    }
    rlog(
    [
      'sid' => $sid,
      //'dat' => $dat,
      //'ldt' => $ldt,
      //'Request' => $_REQUEST,
    ],'shop_save',86400);
    $this->data = ['item' => $dat];
    $this->success('操作成功',U('index'));
  }

  public function del()
  {
    $sid = (int)$_REQUEST['sid'];
    if(!D(CONTROLLER_NAME)->soft_del($sid))
    {
      $this->error('操作失败');
    }
    $this->success('操作成功',U('index'));
  }

}