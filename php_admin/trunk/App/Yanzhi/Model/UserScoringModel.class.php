<?php
namespace Yanzhi\Model;

class UserScoringModel extends PublicModel
{

  public $redis_cache = 'php_feed_score_user8range';

  public function lists_with_range($map = array(),$ord = array())
  {
    if($map) $this->where($map);
    if($ord) $this->order($ord);
    $this->alias('us')->field(array('us.*','r.name' => 'range_name','r.work_start','r.work_end'))
         ->join('left join __USER_SCORING_RANGE__ r on r.id = us.range_id');
    $arr = $this->select();
    return $arr;
  }

  public function lists_with_group($map = array(),$ord = array())
  {
    if($map) $this->where($map);
    if($ord) $this->order($ord);
    $this->alias('u')->field(array('u.*','g.name' => 'group_name'))
         ->join('left join '.D('UserScoringGroup')->getTableName().' g on g.id = u.group_id');
    $arr = $this->select();
    return $arr;
  }

  // 获取符合当前班次的打分团用户
  public function get_user_by_range()
  {
    $this->redis || $this->new_redis();
    $key = $this->redis_cache;
    $arr = $this->redis->get($key) ?: array();
    if(!$arr)
    {
      $arr = $this->alias('us')->field('us.uid,r.work_start,r.work_end')
        ->join('left join __USER_SCORING_RANGE__ r on r.id = us.range_id')
        ->where(array('r.id' => array('egt',1)))
        ->select() ?: array();
      $this->redis->set($key,json_encode($arr));
    }
    $usr = array();
    $now = time() % strtotime(date('Y-m-d'));
    foreach($arr as $i => $v)
    {
      if($now >= $v['work_start'] && $now <= $v['work_end']) $usr[] = $v;
    }
    //$ids = array_unique(array_column($usr,'uid'));
    return $usr;
  }

  public function del_user_range_cache()
  {
    $this->redis || $this->new_redis();
    return $this->redis->del($this->redis_cache);
  }

  // 获取打分团用户所在班次
  public function get_user_scoring_range($uid = 0)
  {
    return $this->alias('us')->field('us.uid,r.work_start,r.work_end')
      ->join('left join __USER_SCORING_RANGE__ r on r.id = us.range_id')
      ->where(array('us.uid' => (int)$uid))
      ->find() ?: array();
  }

  public function new_redis($cfg = '')
  {
    $this->redis = D('PhpServerRedis')->new_redis($cfg);
    return $this->redis;
  }

}