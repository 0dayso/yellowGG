<?php
namespace Cms\Model;

class ScoreBaseModel extends CjDatadwModel
{

  // 打分类型
  public $score_types = array(
    0 => '打分团有效打分',
    1 => '打分团无效打分',
    2 => '普通用户打分',
    3 => '后台打分',
  );

}