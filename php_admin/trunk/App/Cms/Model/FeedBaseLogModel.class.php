<?php
namespace Cms\Model;
use Cms\Model;

class FeedBaseLogModel extends CjAdminLogModel
{

  public $log_types = array(
    0 => '删除',
    1 => '审核',
  );

  public function log($fed = array(),$dat = array())
  {
    if(!isset($fed['id']) || (int)$fed['id'] < 1) return false;
    if(!isset($dat['type']) || !isset($this->log_types[$dat['type']])) return false;
    $log = array(
      'type'        => $dat['type'],
      'feed_id'     => $fed['id'],
      'uid'         => $fed['uid'],
      'resource'    => $fed['resource'],
      'text'        => $fed['text'],
      'create_time' => $fed['create_time'],
      'aid'         => (int)$_SESSION['authId'],
      'remark'      => $dat['remark'] ?: '',
      'log_time'    => time(),
    );
    return $this->add($log);
  }

}