<?php
namespace Cms\Model;

class UserScoringRangeModel extends PublicModel
{

  protected $_auto = array(
    array('work_start','auto_work_time',3,'callback'),
    array('work_end'  ,'auto_work_time',3,'callback'),
  );

  protected function auto_work_time($str)
  {
    $min = 0;
    $max = 60 * 60 * 24;
    $tim = is_numeric($str) ? (int)$str : strtotime($str) - strtotime(date('Y-m-d'));
    $tim < $min && $tim = $min;
    $tim > $max && $tim = $max;
    return $tim;
  }

}