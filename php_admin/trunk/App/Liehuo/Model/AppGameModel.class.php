<?php
namespace Liehuo\Model;

class AppGameModel extends PublicModel
{

  protected $redis_config  = 'redis_live';
  public    $redis_key     = 'php_zset_games';//redis redisuser.chujianapp.com zAdd php_zset_games end_time {"id":1,"attrs":{"list":[]}

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
    if($uid = (int)$arr['uid']) $map[$alias.'uid'] = $uid;
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
        $alias.'remark' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd))
      {
        //if(!$field || $field == 'uid') $map['_complex'][$alias.'uid'] = $kwd;
      }
    }
    return $map;
  }

  // 更新缓存
  public function update_cache()
  {
    $ret = false;
    $rds = $this->get_redis();
    $rds->del($this->redis_key);
    $arr = $this->lists(
    [
      'end_time'    => ['egt',time()],
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