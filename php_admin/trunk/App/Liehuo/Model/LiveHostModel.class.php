<?php
namespace Liehuo\Model;

class LiveHostModel extends LiveBaseModel
{

  const CONTRACT_TYPE_NONE = 0;
  const CONTRACT_TYPE_SIGN = 1;

  public $contract_types =
  [
    self::CONTRACT_TYPE_NONE => '未签约',
    self::CONTRACT_TYPE_SIGN => '签约中',
  ];

  const PROPERTY_NONE           = 0;
  const PROPERTY_HAS_ACTIVITY   = 1;
  const PROPERTY_HAS_OTHER_LIVE = 2;
  // 位运算 1 2 4 8 16 32 64 ...

  public $propertys =
  [
    self::PROPERTY_HAS_ACTIVITY   => '参与过官方活动',
    self::PROPERTY_HAS_OTHER_LIVE => '在其他平台直播',
  ];


  //redis redislive.chujianapp.com zAdd php_live_hots sort uid
  protected $redis_hots  = 'php_live_hots';
  protected $redis_hots2 = 'php_live_hots_bysex';


  public function __construct()
  {
    parent::__construct();
  }

  // 自动完成
  protected $_auto =
  [
    ['propertys','auto_propertys',self::MODEL_BOTH,'callback'],
    ['attrs','auto_attrs',self::MODEL_BOTH,'callback'],
  ];

  public function auto_propertys($dat = [])
  {
    $ret = 0;
    if(is_array($dat)) foreach($dat as $v)
    {
      $ret = $ret | (int)$v;
    }
    return $ret;
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
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'contract_time']) || $map[$alias.'contract_time'] = [];
      $map[$alias.'contract_time'][] = ['egt',strtotime(date('Y-m-d 00:00:00',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'contract_time']) || $map[$alias.'contract_time'] = [];
      $map[$alias.'contract_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($arr['contract_type'] != '') $map[$alias.'contract_type'] = (int)$arr['contract_type'];
    else $map[$alias.'contract_type'] = ['egt',1];
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

  public function get_by_list($arr = [],$fields = false,$field_pk = 'uid')
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
      $dat = $this->klist('uid',['uid' => ['in',array_values($ids)]]) ?: [];
    }
    return $dat;
  }

  public function add_bat($ids = [],$dat = [])
  {
    is_array($ids) || $ids = [(int)$ids];
    $els = $this->get_by_ids($ids);
    $als = $uls = [];
    $lmd = OperLogModel::Instance();
    $cts = LiveContractTypeModel::Instance()->get_all() ?: [];
    foreach($ids as $v)
    {
      $uid = (int)$v;
      if(isset($els[$uid]))
      {
        $uls[$uid] = $uid;
      }
      else
      {
        $als[] = array_merge($dat ?: [],
        [
          'uid'           => $uid,
          'contract_time' => NOW_TIME,
        ]);
      }
      $lmd->log('live_contract',
      [
        $dat['contract_type'] ? '签约主播' : '解约主播',
        '签约类型' => $cts[$dat['contract_type']]['attrs']['name'],
      ],$uid);
    }
    //var_dump(compact('ids','els','als','uls','dat'));die;
    $rt1 = $als && $this->addAll(array_values($als));
    $rt2 = $uls && $this->where(['uid' => ['in',array_values($uls)]])->save($dat);
    if($uls)
    {
      if($dat['contract_type']) $this->where(['uid' => ['in',array_values($uls)],'contract_time' => 0])->save(['contract_time' => NOW_TIME]);
      else $this->where(['uid' => ['in',array_values($uls)],'contract_time' => ['egt',1]])->save(['contract_time' => 0]);
    }
    return $rt1 || $rt2;
  }

  // 热门列表
  public function get_hots()
  {
    $rds = $this->get_redis();
    $hls = $rds->zRevRangeByScore($this->redis_hots,'+inf','-inf',['withscores' => true]) ?: [];
    return $hls;
  }

  // 热门列表
  public function get_hots_byTicket()
  {
    $rds = $this->get_redis();
    $hls = $rds->zRevRangeByScore('php_live_hots_byTicket','+inf',NOW_TIME,['withscores' => true]) ?: [];
    return $hls;
  }

  public function set_hot($uid = 0,$top = false)
  {
    $rds = $this->get_redis();
    $key = $this->redis_hots;
    if($top)
    {
      $sls = $rds->zRevRangeByScore($key,'+inf','-inf',
      [
        'withscores' => true,
        'limit'      => [0,2],
      ]);
      $sco = max($sls ?: [0]);
      $sco && $sco += 10;
    }
    elseif(!$sco = $rds->zScore($key,$uid))
    {
      $sls = $rds->zRangeByScore($key,'-inf','+inf',
      [
        'withscores' => true,
        'limit'      => [0,2],
      ]);
      $sco = min($sls ?: [0]);
      //$sco && $sco -= 10;
      $sco || $sco = 1444444444;
    }
    $ret = $rds->zAdd($key,$sco ?: NOW_TIME,$uid);
    rlog([date('H:i:s'),$uid,compact('key','sco','sls','ret','top')],'live_hot_set',86400);
    if(isset($sls) && !$sls) rlog([date('H:i:s'),$uid,[
        'bysex'   => $rds->zRange($this->redis_hots2,0,-1),
        'request' => $_REQUEST,
        'server'  => $_SERVER,
    ]],'live_hot_null',86400 * 3);
    if($ret)
    {
      $sex = (int)UserBaseModel::Instance()->get_user_cache($uid,'sex');
      $rds->zAdd($this->redis_hots2,$sex,$uid);
      $this->where(['uid' => $uid])->save(
      [
        'hot_times'   => ['exp','hot_times + 1'],
        'last_hot_at' => NOW_TIME,
      ]);
    }
    return $ret;
  }

  public function del_hot($uid = 0)
  {
    $rds = $this->get_redis();
    $ret = $rds->zRem($this->redis_hots,$uid);
    $rds->zRem($this->redis_hots2,$uid);
    $rds->zRem('php_live_hots_byTicket',$uid);
    $ret && rlog([date('H:i:s'),$uid,
    [
      'ret'     => $ret,
      'zCard'   => $rds->zCard($this->redis_hots),
      'zCard2'  => $rds->zCard($this->redis_hots2),
      'request' => $_REQUEST,
      'server'  => $_SERVER,
    ]],'live_hot_del',86400 * 2);
    return $ret;
  }

  public function sort_hot($uid = 0,$oid = 0)
  {
    $ret = false;
    $rds = $this->get_redis();
    $ust = (int)$rds->zScore($this->redis_hots,$uid);
    $ost = (int)$rds->zScore($this->redis_hots,$oid);
    if($ust && $ost)
    {
      $ret = [];
      $step = $ust >= $ost ? 1 : -1;
      $lst = $rds->zRangeByScore($this->redis_hots,min($ust,$ost),max($ust,$ost),['withscores' => true]) ?: [];
      unset($lst[$uid]);
      $rds->zAdd($this->redis_hots,$ost,$uid);
      $ret[$uid] = $ost;
      foreach($lst as $k => $v)
      {
        $ret[$k] = $rds->zIncrBy($this->redis_hots,$step,$k);
      }
    }
    return $ret;
  }

  public function reset_hot()
  {
    $rds = $this->get_redis();
    $arr = $rds->zRange($this->redis_hots,0,-1,true) ?: [];
    foreach($arr as $k => $v)
    {
      $rds->zAdd($this->redis_hots,1444444444,$k);
    }
    return $arr;
  }

}