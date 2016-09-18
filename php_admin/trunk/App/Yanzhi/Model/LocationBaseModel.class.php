<?php
namespace Yanzhi\Model;

class LocationBaseModel extends CjDatadwModel
{

  public function location_update($uid = 0,$loc = array())
  {
    if(!$uid) return false;
    $loc = array_merge(array(),$loc);
    return $this->where(array('uid' => $uid))->save($loc);
  }

}