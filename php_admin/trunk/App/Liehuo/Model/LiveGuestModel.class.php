<?php
namespace Liehuo\Model;

class LiveGuestModel extends LiveBaseModel
{

  const MANAGE_NONE = 0;
  const MANAGE_ALL  = 63;

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
      is_array($map[$alias.'create_time']) || $map[$alias.'create_time'] = [];
      $map[$alias.'create_time'][] = ['egt',strtotime(date('Y-m-d 00:00:00',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'create_time']) || $map[$alias.'create_time'] = [];
      $map[$alias.'create_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($arr['is_robot'] != '') $map[$alias.'is_robot'] = (int)$arr['is_robot'];
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


  public function count_by_liveids($ids = [],$map = [])
  {
    return $this->field(
    [
      'live_id',
      'count(uid)' => 'cnt',
    ])
    ->group('live_id')
    ->klist('live_id',array_merge(
    [
      'live_id' => ['in',$ids],
    ],$map)) ?: [];
  }


  // 禁言
  // redis -h redislive.chujianapp.com set php_live_silent_12200022 time
  public function silence($uid = 0,$tim = 0)
  {
    $rds = $this->get_redis();
    $tim = (int)$tim;
    $ret = $rds->setEx('php_live_silent_'.$uid,$tim,NOW_TIME + $tim);
    if($ret)
    {
      MessageModel::Instance()->add_msg($uid,
      [
        'type' => 406,
        'text' => json_encode(
        [
          'silent_count' => $tim,
        ]),
      ]);
    }
    return $ret;
  }

  public function get_silence_ttl($uid = 0)
  {
    return (int)$this->get_redis()->ttl('php_live_silent_'.$uid);
  }


  // 添加场控
  // redis -h redislive.chujianapp.com zAdd $access $uid
  public function add_manager($uid = 0)
  {
    return $this->get_redis()->zAdd('php_live_managers',self::MANAGE_ALL,$uid);
  }

  public function del_manager($uid = 0)
  {
    return $this->get_redis()->zRem('php_live_managers',$uid);
  }

  public function is_manager($uid = 0)
  {
    return !!(int)$this->get_redis()->zScore('php_live_managers',$uid);
  }


/*
// 机器人配置
redis -h redislive.chujianapp.com set php_robot_config
{
  deadline  : 5400,//截至时间 秒
  robot_num : 2,//每进1个真人，进入假用户数
  rules :
  [
    {
      valid_begin    : 120,
      valid_end      : 599,
      amount_min     : 1,
      amount_max     : 5,
      popularity_min : 1,每次随机人气
      popularity_max : 1,
      interval       : 120//每隔多少秒随机
    },
    {...}
  ]
}
*/
  public function get_robot_config()
  {
    $dat = $this->get_redis()->get('php_robot_config');
    is_string($dat) && $dat = json_decode($dat,true);
    return $dat;
  }

  public function set_robot_config($dat = [])
  {
    is_string($dat) || $dat = json_encode($dat);
    return $this->get_redis()->set('php_robot_config',$dat);
  }


  // 机器人列表
  // redis -h redislive.chujianapp.com sAdd php_set_robot $uid
  // redis -h redislive.chujianapp.com sAdd php_set_robot_library $uid
  // redis -h redislive.chujianapp.com sAdd php_robot_library $uid          20160708 新增
  // redis -h redislive.chujianapp.com zAdd php_robot_assign $live_id $uid  20160708 新增
  public function add_robot($uls = [])
  {
    $ret = [];
    is_numeric($uls) && $uls = [$uls];
    $rds = $this->get_redis();
    foreach($uls as $v)
    {
      $ret[$v] = $rds->sAdd('php_robot_library',$v);
      $rds->sAdd('php_set_robot_library',$v);
      $rds->zAdd('php_set_robot_fans',rand(1,15),$v);
    }
    return $ret;
  }

  public function get_robot_count()
  {
    return $this->get_redis()->sCard('php_robot_library');
  }

  public function get_robot_assign_count()
  {
    return $this->get_redis()->zCard('php_robot_assign');
  }

  public function get_robot_lib_count()
  {
    return $this->get_redis()->sCard('php_set_robot_library');
  }

  // 机器人自动相互关注
  public function let_robot_auto_fans()
  {
    $ret = [];
    $key = 'php_set_robot_fans';
    $rds = $this->get_redis();
    for($i = 0;$i < 10;$i++)
    {
      $uls = $rds->zRevRangeByScore($key,'+inf',1,
      [
        'withscores' => true,
        'limit'      => [0,2],
      ]) ?: [];
      if(!$uls || count($uls) < 2) break;
      list($us1,$us2) = array_keys($uls);
      $dat = ['uid' => $us2,'oid' => $us1/*被关注人*/];
      $rrt = $rds->zIncrBy($key,-1,$us1);
      if($rrt !== false)
      {
        $ret[] = ['uid' => $us1,'score' => $rrt];
        RpcApiModel::Instance()->call('Live/follow',$dat);
      }
      rlog([date('H:i:s'),$dat,$uls],'let_robot_auto_fans',86400);
      cli_echo($us2.' follow '.$us1.' at '.date('Y-m-d H:i:s').PHP_EOL);
    }
    cli_echo(PHP_EOL);
    cli_echo(json_encode($ret).PHP_EOL);
    cli_echo(PHP_EOL);
    cli_echo('follow is ok. '.count($ret).' success'.PHP_EOL);
    return $ret;
  }

}