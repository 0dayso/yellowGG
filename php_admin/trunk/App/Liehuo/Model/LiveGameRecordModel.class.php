<?php
namespace Liehuo\Model;

class LiveGameRecordModel extends LiveBaseModel
{

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
    if($uid = (int)$arr['uid']) $map[$alias.'uid'] = $uid;
    if(is_numeric($arr['live_id'])) $map[$alias.'live_id'] = $arr['live_id'];
    if(is_numeric($arr['game_id'])) $map[$alias.'game_id'] = $arr['game_id'];
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'game_start']) || $map[$alias.'game_start'] = [];
      $map[$alias.'game_start'][] = ['egt',strtotime(date('Y-m-d 00:00:00',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'game_start']) || $map[$alias.'game_start'] = [];
      $map[$alias.'game_start'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($arr['game_result'] != '') $map[$alias.'game_result'] = (int)$arr['game_result'];
    if($arr['game_over'] != '')   $map[$alias.'game_over']   = (int)$arr['game_over'];
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $map['_complex'] =
      [
        '_logic' => 'or',
        //$alias.'text' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'uid')     $map['_complex'][$alias.'uid']     = $kwd;
        if(!$field || $field == 'live_id') $map['_complex'][$alias.'live_id'] = $kwd;
      }
    }
    return $map;
  }

}