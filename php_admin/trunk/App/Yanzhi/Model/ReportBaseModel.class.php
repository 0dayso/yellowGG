<?php
namespace Yanzhi\Model;

class ReportBaseModel extends CjDatadwModel
{

  // 举报类型
  public $report_types = array(
    0 => '-',
    1 => '色情低俗',
    2 => '欺诈广告',
    3 => '虚假照片',
  );

  // 举报状态
  public $report_status = array(
    0 => '未处理',
    1 => '已处理',
    2 => '已处理并封禁',
    3 => '已处理并删除动态',
    4 => '拒绝处理',
  );

  // 举报理由
  public $report_reasons = array();//C('STATE_ACCUSATION_TYPE')

  protected function _initialize()
  {
    $this->report_reasons = C('STATE_ACCUSATION_TYPE') ?: array();
  }

}