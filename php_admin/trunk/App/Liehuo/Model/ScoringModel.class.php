<?php
namespace Liehuo\Model;

class ScoringModel extends AvatarModel
{

  protected $autoCheckFields = false;

  protected $redis_config = 'redis_user';

  public $redis_list_key   = 'php_avatar_scoring'; // zSet 未打分用户列表
  public $redis_init_key   = 'php_zset_scoring';   // zSet 正在打分的队列
  public $redis_assign_key = 'php_scoring_assign'; // zSet 打分队列分配

  public function __construct()
  {
    parent::__construct();
  }


  // 删除队列
  public function del_byuid($uid = 0)
  {
    $rds = $this->get_redis();
    $ret = $rds->zRem($this->redis_list_key,$uid);
    $rds->zRem($this->redis_init_key,$uid);
    $rds->zRem($this->redis_assign_key,$uid);
    return $ret;
  }

  // 删除队列
  public function del_byuids($ids = [])
  {
    $ret = false;
    foreach($ids ?: [] as $uid)
    {
      $ret = $this->del_byuid($uid) || $ret;
    }
    return $ret;
  }

}