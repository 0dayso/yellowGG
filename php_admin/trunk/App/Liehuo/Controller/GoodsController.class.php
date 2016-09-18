<?php
namespace Liehuo\Controller;
use \Liehuo\Model\GoodsModel;

class GoodsController extends PublicController
{

  public function discount_list()
  {
    $dat = [];
    $stime = $_REQUEST['stime'] ? strtotime($_REQUEST['stime']) : '-inf';//strtotime('-30 days')
    $etime = $_REQUEST['etime'] ? strtotime($_REQUEST['etime']) : '+inf';
    $mod = D(CONTROLLER_NAME);
    $rds = $mod->get_redis();
    $key = $mod->redis_goods_discount;
    $cnt = $rds->zCard($key);
    $pag = new \Think\Page($cnt,$this->page_size ?: 50);
    $arr = $rds->zRevRangeByScore($key,$etime,$stime,['limit' => [$pag->firstRow,$pag->listRows]]) ?: [];
    trace($stime,'stime','SQL');
    trace($etime,'etime','SQL');
    $dat['list'] = $mod->get_discounts($arr);
    $dat['goods_list'] = $mod->klist();
    $this->data = $dat;
    //die(json_encode(compact('dat','arr','pag','stime','etime')));
    $this->display();
  }

  public function discount_edit()
  {
    $mod = D(CONTROLLER_NAME);
    $id  = trim($_REQUEST['id']);
    $dat = $mod->get_discount($id) ?: [];
    $dat['goods_diamond']    = GoodsModel::$goods_diamond    ?: [];
    $dat['goods_vips']       = GoodsModel::$goods_vips       ?: [];
    $dat['goods_like']       = GoodsModel::$goods_like       ?: [];
    $dat['goods_super_like'] = GoodsModel::$goods_super_like ?: [];
    $dat['goods_gift']       = GoodsModel::$goods_gift       ?: [];
    $dat['goods_broadcast']  = GoodsModel::$goods_broadcast  ?: [];
    $dat['goods_list'] = $mod->klist();
    $this->data = $dat;
    //die(json_encode(compact('dat')));
    $this->display();
  }

  public function discount_save()
  {
    $key = trim($_REQUEST['id']);
    $key || $key = md5(uniqid(rand(),true).rand());
    $dat =
    [
      'id'             => $key,
      'goods_id'       => (int)$_REQUEST['goods_id'],
      'discount_stime' => is_numeric($_REQUEST['discount_stime']) ? (int)$_REQUEST['discount_stime'] : (int)strtotime($_REQUEST['discount_stime']),
      'discount_etime' => is_numeric($_REQUEST['discount_etime']) ? (int)$_REQUEST['discount_etime'] : (int)strtotime($_REQUEST['discount_etime']),
      'app_version'    => trim($_REQUEST['app_version']),
      'env_ip'         => trim($_REQUEST['env_ip']),
      'attrs'          => is_array($_REQUEST['attrs']) ? $_REQUEST['attrs'] : '',
      'remark'         => trim($_REQUEST['remark']),
      'update_time'    => time(),
    ];
    $mod = D(CONTROLLER_NAME);
    if(!$mod->set_discount($dat)) $this->error('操作失败');
    //die(json_encode(compact('dat')));
    $this->success('操作成功',U('discount_list'));
  }

  public function discount_del()
  {
    $key = trim($_REQUEST['id']);
    if(!D(CONTROLLER_NAME)->del_discount($key))
    {
      $this->error('操作失败');
    }
    $this->success('操作成功',U('discount_list'));
  }

}