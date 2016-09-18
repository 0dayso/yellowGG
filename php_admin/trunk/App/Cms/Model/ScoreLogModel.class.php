<?php
namespace Cms\Model;
use Cms\Model;

class ScoreLogModel extends CjAdminLogModel
{

  public function lists_join($map = array(),$ord = array())
  {
    $this->order('s.score_time desc,s.id desc');
    if($map) $this->where($map);
    if($ord) $this->order($ord);
    $this->alias('s')
         ->field(array('s.*','a.resource','a.create_time','a.group_id','a.range_id'))
         ->join('left join '.D('ScoreAssignLog')->getTableName().' a on a.feed_id = s.feed_id');
    $arr = $this->select();
    return $arr;
  }

  // 保存打分日志
  public function scoring_log($dat = array())
  {
    $dat = array_merge(array(
      'feed_id'     => 0,
      'type'        => 3,//后台打分
      'uid'         => 0,
      'score'       => 0,
      'score_time'  => time(),
      'timeout'     => -1,
    ),$dat);
    $fid = (int)$dat['feed_id'];
    if(!$fid) return false;
    return $this->add($dat);
  }

}