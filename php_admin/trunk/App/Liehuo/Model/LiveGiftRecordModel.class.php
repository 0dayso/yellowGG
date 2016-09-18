<?php
namespace Liehuo\Model;

class LiveGiftRecordModel extends LhWalletModel
{

  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid']) $map[$alias.'uid'] = $uid;
    if($oid = (int)$arr['oid']) $map[$alias.'oid'] = $oid;
    if($lid = trim($arr['live_id'])) $map[$alias.'live_id']  = $lid;
    if($gid = (int)$arr['goods_id']) $map[$alias.'goods_id'] = $gid;
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'create_time']) || $map[$alias.'create_time'] = [];
      $map[$alias.'create_time'][] = ['egt',strtotime(date('Y-m-d 00:00:00',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'create_time']) || $map[$alias.'create_time'] = [];
      $map[$alias.'create_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $field = trim($arr['search_field']);
      $map['_complex'] =
      [
        '_logic' => 'or',
      ];
      //if(!$field || $field == 'remark') $map['_complex'][$alias.'remark'] = ['like','%'.$kwd.'%'];
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'uid') $map['_complex'][$alias.'uid'] = $kwd;
        if(!$field || $field == 'oid') $map['_complex'][$alias.'oid'] = $kwd;
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

  public function getByLiveIds($ids = [])
  {
    $arr = $this->where(['live_id' => ['in',$ids]])->select() ?: [];
    $lst = [];
    foreach($arr as $v)
    {
      $lst[$v['live_id']][] = $v;
    }
    return $lst;
  }

}