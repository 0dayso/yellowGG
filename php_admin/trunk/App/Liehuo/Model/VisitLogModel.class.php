<?php
namespace Liehuo\Model;

class VisitLogModel extends CjDatadwModel
{

  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid'])  $map[$alias.'uid'] = $uid;
    if($oid = (int)$arr['oid'])  $map[$alias.'oid'] = $oid;
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'visit_time']) || $map[$alias.'visit_time'] = [];
      $map[$alias.'visit_time'][] = ['egt',strtotime(date('Y-m-d',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'visit_time']) || $map[$alias.'visit_time'] = [];
      $map[$alias.'visit_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $field = trim($arr['search_field']);
      $map['_complex'] =
      [
        '_logic' => 'or',
      ];
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'uid') $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
        if(!$field || $field == 'oid') $map['_complex'][$alias.'oid'] = ['like','%'.$kwd.'%'];
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

}