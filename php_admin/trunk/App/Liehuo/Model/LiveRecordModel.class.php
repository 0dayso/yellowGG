<?php
namespace Liehuo\Model;

class LiveRecordModel extends LiveBaseModel
{

  const STATE_NONE   = 0;
  const STATE_LIVING = 1;
  const STATE_PAUSED = 2;
  const STATE_CLOSED = 3;

  public $states =
  [
    self::STATE_NONE   => '未直播',
    self::STATE_LIVING => '直播中',
    self::STATE_PAUSED => '暂停中',
    self::STATE_CLOSED => '已结束',
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
    $alias_sub = $alias ?: $this->getTableName().'.';
    $map = [];
    if($uid = (int)$arr['uid'])     $map[$alias.'uid']     = $uid;
    if(is_numeric($arr['live_id'])) $map[$alias.'live_id'] = $arr['live_id'];
    if($arr['room_id'] != '')       $map[$alias.'live_chatroomid'] = (int)$arr['room_id'];
    if($arr['filter'] == 'hot')
    {
      $arr['uids'] || $arr['uids'] = LiveHostModel::Instance()->get_hots() ?: [];
      $map[$alias.'uid']        = ['in',array_keys($arr['uids']) ?: [0]];
      $map[$alias.'live_state'] = self::STATE_LIVING;
    }
    $time_type = $alias.'time_begin';
    $arr['time_type'] == 'end' && $time_type = $alias.'time_end';
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
    if($arr['state'] == '-1');
    elseif($arr['state'] != '') $map[$alias.'live_state'] = (int)$arr['state'];
    else $map[$alias.'live_state'] = ['in',[self::STATE_LIVING,self::STATE_PAUSED]];
    if($arr['contract_type'] != '')
    {
      $ctp = (int)$arr['contract_type'];
      $whe =
      [
        'uid'           => ['exp','= '.$alias_sub.'uid'],
        'contract_type' => $ctp,
      ];
      $sql = D('LiveHost')->field('uid')->where($whe)->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $map['_complex'] =
      [
        '_logic' => 'or',
        $alias.'live_title' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'uid')     $map['_complex'][$alias.'uid']     = $kwd;
        if(!$field || $field == 'live_id') $map['_complex'][$alias.'live_id'] = ['like','%'.$kwd.'%'];
      }
    }
    return $map;
  }

  public function getCache($lid = '',$field = false)
  {
    $key = 'php_hash_liveroom_'.$lid;
    $rds = $this->get_redis();
    return $field ? $rds->hGet($key,$field) : $rds->hGetAll($key);
  }

  // 获取直播最终访客数
  public function getFinalVisitors($lid = '')
  {
    $key = 'php_set_liveroommember_'.$lid;
    $num = (int)$this->get_redis()->sCard($key);
    return $num >= 1 ? $num - 1 :  0;
  }

  public function getLiving($map = [])
  {
    static $cls = [];
    is_numeric($map) && $map = ['uid' => $map];
    $map['live_state'] = ['in',[self::STATE_LIVING,self::STATE_PAUSED]];
    $key = md5(json_encode($map));
    $cls[$key] || $cls[$key] = $this->where($map)
    ->order('id desc')
    ->find();
    return $cls[$key];
  }

}