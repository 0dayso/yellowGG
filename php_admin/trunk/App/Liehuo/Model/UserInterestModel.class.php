<?php
namespace Liehuo\Model;

class UserInterestModel extends CjDatadwModel
{

  public $types =
  [
    1 => '旅行',
    2 => '美食',
    3 => '宠物',
    4 => '运动',
    5 => '音乐',
    6 => '书籍',
    7 => '电影',
    8 => '品牌',
    9 => '游戏',
  ];

  public function get_by_ids($ids = [])
  {
    $dat = [];
    if($ids)
    {
      $arr = $this->where(['id' => ['in',$ids]])->select();
      foreach($arr ?: [] as $v)
      {
        $dat[$v['in_type']][$v['id']] = $v;
      }
    }
    return $dat;
  }

}