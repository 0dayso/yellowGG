<?php
namespace Liehuo\Model;

class AccusationBaseLogModel extends CjAdminLogModel
{

  // 封禁状态
  public $accusation_states  = array();//C('STATE_ACCUSATION_PROCESS_STATES')

  // 封禁理由
  public $accusation_reasons = array('其他');//C('STATE_ACCUSATION_PROCESS_REASONS')

  protected function _initialize()
  {
    $this->accusation_states  = D('UserBase')->get_warning_status() ?: array();
    //$this->accusation_reasons = C('STATE_ACCUSATION_PROCESS_REASONS') ?: array();
  }


  public function log($dat = [])
  {
    $dat = array_merge([
      'report_id'   => 0,
      'uid'         => 0,
      'oid'         => 0,
      'aid'         => (int)$_SESSION['authId'],
      'status'      => 0,
      'reason'      => 0,
      'remark'      => '',
      'report_time' => '',
      'create_time' => time(),
    ],$dat ?: []);
    $dat['id'] = $this->add($dat);
    return $dat;
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