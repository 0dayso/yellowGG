<?php
namespace Liehuo\Model;

class AppCfgModel extends CjAdminModel
{

  const TYPE_NONE        = 0;
  const TYPE_COMMON      = 1;
  const TYPE_LAUNCH      = 2;     //启动页
  const TYPE_VIP_BANNER  = 4;     //VIP Banner
  const TYPE_LIVE_BANNER = 8;     //直播 Banner
  const TYPE_LIVE_GAME   = 16;    //直播游戏
  const TYPE_CONTRACT    = 32;    //直播签约类型
  const TYPE_LIVE_LEVEL  = 64;    //主播认证等级
  const TYPE_ALL         = 30719;

  public $types =
  [
    self::TYPE_LAUNCH      => '启动页',
    self::TYPE_VIP_BANNER  => 'VIP Banner',
    self::TYPE_LIVE_BANNER => '直播Banner',
    self::TYPE_LIVE_GAME   => '直播游戏',
  ];

  protected $cfg_type     = self::TYPE_COMMON;
  protected $redis_config = 'redis_default';
  protected $redis_key    = 'php_app_cfgs';
  protected $redis_expire = 0;

  protected $connection = 'conn_admin';
  //protected $autoCheckFields = false;
  protected $tableName  = 'app_cfg';


  // 自动完成
  protected $_auto =
  [
    ['sort','auto_int',self::MODEL_BOTH,'callback'],
    ['attrs','auto_attrs',self::MODEL_BOTH,'callback'],
    ['start_time','auto_time',self::MODEL_BOTH,'callback'],
    ['end_time','auto_time',self::MODEL_BOTH,'callback'],
    ['create_time',NOW_TIME,self::MODEL_INSERT],
  ];

  public function __construct()
  {
    parent::__construct();
    $this->redis_expire = 60 * 60 * 24 * 365;
  }


  public function get_all($typ = null)
  {
    $lst = $this->klist(true,
    [
      'type'        => isset($typ) ? $typ : $this->cfg_type,
      'delete_time' => 0,
    ]);
    return $this->attr2array_all($lst) ?: [];
  }

  // 新增或修改数据
  public function set($dat = [],$id = true)
  {
    $id === true && $id = (int)$dat['id'];
    $dat = array_merge($dat ?: [],
    [
      'type' => $this->cfg_type,
    ]);
    unset($dat['id']);
    if($isa = $id < 1)
    {
      $dat['id'] = $this->add($dat);
    }
    else
    {
      $ret = $this->where(['id' => $id])->save($dat);
      $dat['id'] = $ret !== false ? $id : false;
      $sort = $this->auto_sort_set($id);//自动排序
      if($sort !== false) $dat['sort'] = $sort;
    }
    return $dat['id'] ? $dat : $dat['id'];
  }

  // 更新配置缓存
  public function update_cache($typ = null)
  {
    $ret = false;
    $rds = $this->get_redis();
    $rds->del($this->redis_key);
    $arr = $this->get_all($typ) ?: [];
    foreach($arr as $v)
    {
      $ret = $rds->hSet($this->redis_key,$v['id'],json_encode($v)) || $ret;
      $rds->expire($this->redis_key,$this->redis_expire);
    }
    return $ret;
  }


  // 纯缓存配置列表
  public function get_list($map = [],$page = false)
  {
    isset($map['stime']) || $map['stime'] = '-inf';
    isset($map['etime']) || $map['etime'] = '+inf';
    $rds = $this->get_redis();
    $key = $this->redis_key;
    $opt = [];
    if($page)
    {
      $cnt = $rds->zCard($key);
      $pag = new \Think\Page($cnt,$page);
      $opt['limit'] = [$pag->firstRow,$pag->listRows];
    }
    trace($this->redis_config,'redis_config','SQL');
    trace($key,'redis_key','SQL');
    trace(json_encode($map),'map','SQL');
    if($map['desc']) $arr = $rds->zRevRangeByScore($key,$map['etime'],$map['stime'],$opt) ?: [];
    else             $arr = $rds->zRangeByScore($key,$map['stime'],$map['etime'],$opt) ?: [];
    return $this->decode_list($arr);
  }

  public function decode_list($arr = [])
  {
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      $row = json_decode($v,true) ?: [];
      if(!$row) continue;
      if(!isset($row['id'])) $lst[] = $row;
      else $lst[$row['id']] = $row;
    }
    return $lst;
  }

  public function zSave($dat = [],$id = '',$sco = true)
  {
    $ret = false;
    $dat['update_time'] = time();
    $isadd = !$id;
    $sco === true && $sco = time();
    $rds = $this->get_redis();
    $key = $this->redis_key;
    $mem = '';
    if($isadd)
    {
      $dat['id'] = md5(uniqid(rand(),true).rand());
      $dat['create_time'] = time();
      $ret = $rds->zAdd($key,$sco,json_encode($dat));
    }
    elseif($old = $this->zGetByID($id))
    {
      $mem = $old['_member'] ?: '';
      $dat = array_merge($old,$dat);
      unset($dat['_member']);
      $rds->zRem($key,$mem);
      $ret = $rds->zAdd($key,$sco,json_encode($dat));
    }
    return $ret;
  }

  public function zRem($id = '')
  {
    $ret = false;
    if($old = $this->zGetByID($id))
    {
      $mem = $old['_member'] ?: '';
      $ret = $this->get_redis()->zRem($this->redis_key,$mem);
    }
    return $ret;
  }

  public function zGetByID($id = '')
  {
    $ret = false;
    $rds = $this->get_redis();
    $key = $this->redis_key;
    $arr = $rds->zRange($key,0,-1) ?: [];
    foreach($arr as $v)
    {
      $row = json_decode($v,true) ?: [];
      if(!$row) continue;
      if($row['id'] == $id)
      {
        $ret = $row;
        $ret['_member'] = $v;
        break;
      }
    }
    return $ret;
  }

}