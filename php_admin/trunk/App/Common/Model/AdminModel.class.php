<?php
namespace Common\Model;
use Think\Model;

class AdminModel extends CommonModel
{

  // 获取列表中的管理员信息
  public function get_by_list($arr = [],$fields = false,$field_aid = 'aid')
  {
    $dat = [];
    if($ids = array_unique(array_column($arr ?: [],$field_aid)) ?: [])
    {
      if($fields) $this->field($fields);
      $dat = $this->klist('aid',['aid' => ['in',$ids]]) ?: [];
    }
    return $dat;
  }

}