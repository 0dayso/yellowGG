<?php
namespace Common\Model;
use Think\Model;

class AdminAuthRuleModel extends CommonModel
{

  //

  public function getAllByGroup($map = [])
  {
    $arr = $this->lists($map) ?: [];
    $lst = [];
    foreach($arr as $v)
    {
      $lst[$v['group_name']][$v['id']] = $v;
    }
    return $lst;
  }

}