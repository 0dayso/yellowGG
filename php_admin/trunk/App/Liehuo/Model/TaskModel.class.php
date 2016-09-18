<?php
namespace Liehuo\Model;

class TaskModel extends CjDatadwModel
{

  protected $tableName    = 'task_pub';
  protected $redis_config = 'redis_user';

  public $redis_key = 'php_zset_tasks';

  const TYPE_LIVE_WATCH     = 101;
  const TYPE_LIVE_SHOW      = 102;
  const TYPE_LIVE_TIMES     = 103;
  const TYPE_LIVE_GIFT_RECV = 104;
  const TYPE_LIVE_GIFT_SUM  = 105;
  const TYPE_LIVE_FOLLOW    = 106;
  const TYPE_LIVE_SHARE     = 107;
  const TYPE_LIVE_GIFT_SEND = 108;
  const TYPE_GLORY_GRADE    = 99;

  public $types =
  [
    self::TYPE_LIVE_WATCH     => '观看时长',
    self::TYPE_LIVE_SHOW      => '直播时长',
    //self::TYPE_LIVE_TIMES     => '直播次数',
    //self::TYPE_LIVE_GIFT_RECV => '收礼次数',
    self::TYPE_LIVE_GIFT_SUM  => '收礼金额',
    //self::TYPE_LIVE_FOLLOW    => '关注人数',
    self::TYPE_LIVE_SHARE     => '分享次数',
    self::TYPE_LIVE_GIFT_SEND => '送礼金额',
    self::TYPE_GLORY_GRADE    => '荣耀等级',
  ];

  // 自动完成
  protected $_auto =
  [
    ['attrs','auto_attrs',self::MODEL_BOTH,'callback'],
    ['start_time','auto_time',self::MODEL_BOTH,'callback'],
    ['end_time','auto_time',self::MODEL_BOTH,'callback'],
    ['create_time','time',self::MODEL_INSERT,'function'],
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
    if($uid = (int)$arr['uid'])     $map[$alias.'uid']     = $uid;
    if($tid = (int)$arr['task_id']) $map[$alias.'task_id'] = $tid;
    $time_type = $alias.'create_time';
    $arr['time_type'] == 'start' && $time_type = $alias.'start_time';
    $arr['time_type'] == 'end'   && $time_type = $alias.'end_time';
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
    $map[$alias.'delete_time'] = isset($arr['deleted']) && $arr['deleted'] ? ['egt',1] : 0;
    if($arr['type'] != '') $map[$alias.'type'] = (int)$arr['type'];
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $map['_complex'] =
      [
        '_logic' => 'or',
        $alias.'title' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'uid') $map['_complex'][$alias.'uid'] = $kwd;
      }
    }
    return $map;
  }

  public function get_by_list($arr = [],$fields = false,$field_pk = 'task_id')
  {
    $dat = [];
    if($ids = array_unique(array_column($arr ?: [],$field_pk)) ?: [])
    {
      $dat = $this->get_by_ids($ids,$fields);
    }
    return $dat;
  }

  public function get_by_ids($ids = [],$fields = false)
  {
    $dat = [];
    if(is_array($ids) && $ids)
    {
      if($fields) $this->field($fields);
      $dat = $this->klist(true,['id' => ['in',array_values($ids)]]) ?: [];
    }
    return $dat;
  }

  // 更新缓存
  public function update_cache()
  {
    $ret = false;
    $rds = $this->get_redis();
    $rds->del($this->redis_key);
    $arr = $this->lists(
    [
      'end_time'    => ['egt',NOW_TIME],
      'delete_time' => 0,
    ]) ?: [];
    foreach($arr as $v)
    {
      $row = $this->attr2array_row($v);
      $ret = $rds->zAdd($this->redis_key,$row['end_time'],json_encode($row)) || $ret;
    }
    return $ret;
  }

}