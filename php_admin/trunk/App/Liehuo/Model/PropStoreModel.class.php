<?php
namespace Liehuo\Model;

class PropStoreModel extends CjDatadwModel
{

  protected $redis_config = 'redis_user';

  public function __construct($uid = 0)
  {
    parent::__construct();
    $this->uid          = (int)$uid;
    $this->redis_key    = 'php_prop_store_'.$this->uid;
    $this->redis_expire = 60 * 60 * 24 * 7;
  }

  public function setUser($uid = 0)
  {
    return self::Instance($uid);
  }

  public function getByUser()
  {
    $rds = $this->get_redis();
    $dat = $rds->hGetAll($this->redis_key) ?: [];
    if(!$dat)
    {
      $arr = $this->lists(['uid' => $this->uid,'expire_time' => ['egt',NOW_TIME + 1],'balance' => ['egt',1]]) ?: [];
      if($arr)
      {
        foreach($arr as $v)
        {
          $gid = (int)$v['goods_id'];
          $etm = (int)$v['expire_time'];
          if($etm <= NOW_TIME) continue;
          $etk = $gid.'_expire_time';
          $tim = (int)$dat[$etk];
          if($tim <= 0 || $etm < $tim) $tim = $etm;
          $dat[$gid] = (int)$v['balance'] + (int)$dat[$gid];
          $dat[$etk] = $tim;
        }
        //$rds->hMSet($this->redis_key,$dat) && $rds->expire($this->redis_key,$this->redis_expire);
      }
    }
    return $dat;
  }

}