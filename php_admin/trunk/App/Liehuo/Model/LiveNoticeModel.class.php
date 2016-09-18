<?php
namespace Liehuo\Model;

class LiveNoticeModel extends LiveBaseModel
{

  protected $connection = 'conn_admin';

  const TYPE_NONE   = 0;
  const TYPE_PUBLIC = 101;
  const TYPE_ROOM   = 102;
  const TYPE_PACT   = 201;

  public $types =
  [
    self::TYPE_PUBLIC => '全服公告',
    self::TYPE_ROOM   => '房间公告',
    self::TYPE_PACT   => '循环公约',
  ];


  public $redis_pacts = 'php_live_pacts';//hash


  // 自动完成
  protected $_auto =
  [
    ['attrs','auto_attrs',self::MODEL_BOTH,'callback'],
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
    if($aid = (int)$arr['aid']) $map[$alias.'aid'] = $aid;
    if(is_numeric($arr['live_id'])) $map[$alias.'live_id'] = $arr['live_id'];
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
    $map[$alias.'delete_time'] = isset($arr['deleted']) && $arr['deleted'] ? ['egt',1] : 0;
    if($arr['type'] != '') $map[$alias.'type'] = (int)$arr['type'];
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $map['_complex'] =
      [
        '_logic' => 'or',
        $alias.'text' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'live_id') $map['_complex'][$alias.'live_id'] = ['like','%'.$kwd.'%'];
      }
    }
    return $map;
  }

  // 更新直播公约
  public function update_pacts()
  {
    $ret = false;
    $rds = $this->get_redis();
    $rds->del($this->redis_pacts);
    $arr = $this->lists(
    [
      'type'        => self::TYPE_PACT,
      'delete_time' => 0,
    ]) ?: [];
    foreach($arr as $v)
    {
      $row = $this->attr2array_row($v);
      $ret = $rds->hSet($this->redis_pacts,$row['id'],json_encode(
      [
        'id'          => (int)$row['id'],
        'text'        => (string)$row['text'],
        'color'       => preg_replace('/^#+/i','',(string)$row['attrs']['color']),
        'interval'    => (int)$row['attrs']['interval'],
        'is_first'    => (int)$row['attrs']['is_first'],
        'create_time' => (int)$row['create_time'],
      ])) || $ret;
    }
    return $ret;
  }

}