<?php
namespace ChuChai\Model;
use Common\Model\CommonModel;

class PublicModel extends CommonModel
{

  protected $connection  = 'conn_chuchai';
  protected $tablePrefix = 'bd_';


  // 自动设置排序
  public function auto_sort_set($id = 0,$field = 'sort')
  {
    if($id >= 1 && $field && trim($_REQUEST[$field]) === '')
    {
      $sort = $id * 10;
      $this->where(['id' => $id])->setField($field,$sort);
      return $sort;
    }
    else return false;
  }

}