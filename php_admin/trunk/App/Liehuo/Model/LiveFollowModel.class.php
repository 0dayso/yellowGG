<?php
namespace Liehuo\Model;

class LiveFollowModel extends LiveBaseModel
{

  const STATE_NONE      = 0;
  const STATE_FOLLOWING = 1;

  public $states =
  [
    self::STATE_NONE      => '未关注',
    self::STATE_FOLLOWING => '关注中',
  ];

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
    $map = [];
    if($uid = (int)$arr['uid']) $map[$alias.'uid'] = $uid;
    if($oid = (int)$arr['oid']) $map[$alias.'oid'] = $oid;
    $time_type = $alias.'follow_time';
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$time_type]) || $map[$time_type] = [];
      $map[$time_type][] = ['egt',strtotime(date('Y-m-d 00:00:00',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$time_type]) || $map[$time_type] = [];
      $map[$time_type][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($arr['follow_type'] != '') $map[$alias.'follow_type'] = (int)$arr['follow_type'];
    else $map[$alias.'follow_type'] = self::STATE_FOLLOWING;
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $map['_complex'] =
      [
        '_logic' => 'or',
      ];
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'uid') $map['_complex'][$alias.'uid'] = $kwd;
        if(!$field || $field == 'oid') $map['_complex'][$alias.'oid'] = $kwd;
      }
    }
    return $map;
  }

}