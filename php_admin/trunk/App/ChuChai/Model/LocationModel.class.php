<?php
namespace ChuChai\Model;

class LocationModel extends PublicModel
{

  // 自动完成
  protected $_auto =
  [
    ['province','trim',self::MODEL_BOTH,'function'],
    ['city','trim',self::MODEL_BOTH,'function'],
    ['area','trim',self::MODEL_BOTH,'function'],
  ];

  public function __construct()
  {
    parent::__construct();
  }

  // 根据IDs获取列表
  public function get_by_ids($ids = [],$fields = false)
  {
    $dat = [];
    if(is_array($ids) && $ids)
    {
      if($fields) $this->field($fields);
      $dat = $this->klist('sid',['sid' => ['in',array_values($ids)]]) ?: [];
    }
    return $dat;
  }

  // 根据列表获取列表
  public function get_by_list($arr = [],$fields = false,$field_pk = 'sid')
  {
    $dat = [];
    if($ids = array_unique(array_column($arr ?: [],$field_pk)) ?: [])
    {
      $dat = $this->get_by_ids($ids,$fields);
    }
    return $dat;
  }

}