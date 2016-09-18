<?php
namespace Yanzhi\Model;

class AccusationBaseLogModel extends CjAdminLogModel
{

  // 封禁状态
  public $accusation_states  = array();//C('STATE_ACCUSATION_PROCESS_STATES')

  // 封禁理由
  public $accusation_reasons = array();//C('STATE_ACCUSATION_PROCESS_REASONS')

  protected function _initialize()
  {
    $this->accusation_states  = C('STATE_ACCUSATION_PROCESS_STATES') ?: array();
    $this->accusation_reasons = C('STATE_ACCUSATION_PROCESS_REASONS') ?: array();
  }

  // 获取列表中的管理员信息
  public function get_accusation_admins($arr = array(),$fields = false)
  {
    $dat = array();
    $ids = array_unique(array_column($arr,'aid')) ?: array();
    if($ids)
    {
      $mod = D('Admin');
      if($fields) $mod->field($fields);
      $dat = $mod->lists(array('aid' => array('in',$ids))) ?: array();
      if($dat) $dat = array_combine(array_column($dat,'aid'),$dat);
    }
    return $dat;
  }

}