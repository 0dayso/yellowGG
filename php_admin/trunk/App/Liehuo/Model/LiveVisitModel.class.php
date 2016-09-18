<?php
namespace Liehuo\Model;

class LiveVisitModel extends LhActionModel
{

  protected $redis_config = 'redis_live';

  public function __construct()
  {
    parent::__construct();
  }


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $alias_sub = $alias ?: $this->getTableName().'.';
    $map = [];
    if($uid = (int)$arr['uid'])      $map[$alias.'uid']     = $uid;
    if($lid = trim($arr['live_id'])) $map[$alias.'live_id'] = $lid;
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'visit_time']) || $map[$alias.'visit_time'] = [];
      $map[$alias.'visit_time'][] = ['egt',strtotime(date('Y-m-d 00:00:00',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'visit_time']) || $map[$alias.'visit_time'] = [];
      $map[$alias.'visit_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($arr['is_robot']   != '') $map[$alias.'is_robot']   = (int)$arr['is_robot'];
    if($arr['visit_type'] != '') $map[$alias.'visit_type'] = (int)$arr['visit_type'];
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $map['_complex'] =
      [
        '_logic' => 'or',
        $alias.'remark' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd)) $map['_complex'][$alias.'uid'] = $kwd;
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

}