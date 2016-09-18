<?php
namespace Liehuo\Model;

class LuckyBagModel extends LhWalletModel
{

  protected $tableName    = 'lucky_bag_pub';
  protected $redis_config = 'redis_default';

  public $states =
  [
    0 => '已创建',
    1 => '已领完',
    2 => '已过期',
  ];
  
  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: '';
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid']) $map[$alias.'uid']   = $uid;
    if($arr['pub_id']  != '') $map[$alias.'pub_id']  = (int)$arr['pub_id'];
    if($arr['pub_key'] != '') $map[$alias.'pub_key'] = $arr['pub_key'];
    if($arr['state']   != '') $map[$alias.'state']   = (int)$arr['state'];
    $time_type = $arr['time_type'] == 'finish' ? 'finish' : 'create';
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.$time_type.'_time']) || $map[$alias.$time_type.'_time'] = [];
      $map[$alias.$time_type.'_time'][] = ['egt',strtotime(date('Y-m-d',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.$time_type.'_time']) || $map[$alias.$time_type.'_time'] = [];
      $map[$alias.$time_type.'_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($kwd = trim($arr['kwd']))
    {
      $map['_complex'] =
      [
          '_logic' => 'or',
          //$alias.'pub_key' => ['like','%'.$kwd.'%'],
          //$alias.'remark'  => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd))//数字搜索
      {
        $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;//拼接where条件
  }

}