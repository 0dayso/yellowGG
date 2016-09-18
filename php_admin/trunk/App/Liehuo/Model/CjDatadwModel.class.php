<?php
namespace Liehuo\Model;
use Think\Model;

class CjDatadwModel extends PublicModel
{

  // 初见颜值版数据库配置
  protected $connection = 'conn_base';

  protected $subtable_is = 0;//是否分表
  protected $subtable_cardinal = 50000;//分表基数
  protected $subtable_readonly = 0;//只读子表 预发布开启


  /*
   * 根据UID和基数分表
   * */
  public function get_subtable_name($uid = true,$tab = true)
  {
    $tab === true && $tab = $this->getTableName();
    $this->subtable_is || $uid = -1;
    $uid === false && $uid = -1;//不分表
    $uid === true  && $uid = $this->uid;
    $uid = (int)$uid;
    if($uid >= 0)
    {
      $tab .= '_'.(int)($uid / $this->subtable_cardinal);
    }
    return $tab;
  }

}