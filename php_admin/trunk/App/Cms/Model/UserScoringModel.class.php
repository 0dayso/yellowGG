<?php
namespace Cms\Model;

class UserScoringModel extends PublicModel
{

  public function lists($map = array(),$ord = array())
  {
    if($map) $this->where($map);
    if($ord) $this->order($ord);
    $this->alias('u')->field(array('u.*','g.name' => 'group_name'))
         ->join('left join '.D('UserScoringGroup')->getTableName().' g on g.id = u.group_id');
    $arr = $this->select();
    return $arr;
  }

}