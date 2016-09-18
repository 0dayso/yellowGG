<?php
namespace Cms\Model;
use Cms\Model;

class ScoreAssignLogModel extends CjAdminLogModel
{

  // 获取搜索筛选条件
  public function get_filters($arr = array())
  {
    $arr || $arr = $_REQUEST;
    $map = array();
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map['create_time']) || $map['create_time'] = array();
      $map['create_time'][] = array('egt',strtotime(date('Y-m-d',$stime)));
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map['create_time']) || $map['create_time'] = array();
      $map['create_time'][] = array('elt',strtotime(date('Y-m-d 23:59:59',$etime)));
    }
    if(($group_id = (int)$arr['group_id']) >= 1)
    {
      $map['group_id'] = $group_id;
    }
    if(($range_id = (int)$arr['range_id']) >= 1)
    {
      $map['range_id'] = $range_id;
    }
    return $map;
  }

}