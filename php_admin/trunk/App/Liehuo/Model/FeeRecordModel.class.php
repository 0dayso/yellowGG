<?php
namespace Liehuo\Model;

class FeeRecordModel extends CjDatadwModel
{

  const TYPE_OTHER              = 0;
  const TYPE_INCOME_SYSTEM      = 100;
  const TYPE_INCOME_CHARGE      = 101;
  const TYPE_INCOME_LIKE        = 102;
  const TYPE_INCOME_COUPON      = 103;
  const TYPE_INCOME_REFUND      = 104;
  const TYPE_INCOME_SUPER_LIKE  = 105;
  const TYPE_INCOME_GOLD_LIKE   = 106;
  const TYPE_INCOME_WINNING     = 107;
  const TYPE_EXPENSE_SYSTEM     = 200;
  const TYPE_EXPENSE_CASH       = 201;
  const TYPE_EXPENSE_LIKE       = 202;
  const TYPE_EXPENSE_COUPON     = 203;
  const TYPE_EXPENSE_ORDER      = 204;
  const TYPE_EXPENSE_SUPER_LIKE = 205;
  const TYPE_EXPENSE_GOLD_LIKE  = 206;
  const TYPE_EXPENSE_PUNISH     = 207;

  public $types = [
    self::TYPE_OTHER              => '其他',
    self::TYPE_INCOME_SYSTEM      => '系统入账',
    self::TYPE_INCOME_CHARGE      => '普通充值',
    self::TYPE_INCOME_LIKE        => '被赞收入',
    self::TYPE_INCOME_COUPON      => '红包收入',
    self::TYPE_INCOME_REFUND      => '系统退款',
    self::TYPE_INCOME_SUPER_LIKE  => '超赞收入',
    self::TYPE_INCOME_GOLD_LIKE   => '金星超赞收入',
    self::TYPE_INCOME_WINNING     => '活动中奖',
    self::TYPE_EXPENSE_SYSTEM     => '系统扣账',
    self::TYPE_EXPENSE_CASH       => '用户提现',
    self::TYPE_EXPENSE_LIKE       => '购买点赞',
    self::TYPE_EXPENSE_COUPON     => '红包支出',
    self::TYPE_EXPENSE_ORDER      => '普通消费',
    self::TYPE_EXPENSE_SUPER_LIKE => '购买超赞',
    self::TYPE_EXPENSE_GOLD_LIKE  => '支付金星超赞',
    self::TYPE_EXPENSE_PUNISH     => '违规处罚',
  ];


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid'])  $map[$alias.'uid']     = $uid;
    if($oid = (int)$arr['oid'])  $map[$alias.'oid']     = $oid;
    if($typ = (int)$arr['type']) $map[$alias.'type']    = $typ;
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'create_time']) || $map[$alias.'create_time'] = [];
      $map[$alias.'create_time'][] = ['egt',strtotime(date('Y-m-d',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'create_time']) || $map[$alias.'create_time'] = [];
      $map[$alias.'create_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $field = trim($arr['search_field']);
      $map['_complex'] =
      [
        '_logic' => 'or',
      ];
      if(!$field || $field == 'remark') $map['_complex'][$alias.'remark'] = ['like','%'.$kwd.'%'];
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'uid') $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
        if(!$field || $field == 'oid') $map['_complex'][$alias.'oid'] = ['like','%'.$kwd.'%'];
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }


  public function add_record($data = [])
  {
    $data = array_merge([
        'create_time' => time(),
    ],$data ?: []);
    if(!isset($data['serial_no'])) $data['serial_no'] = $this->get_serial_no();
    return $this->add($data);
  }

  /*
   * 生成流水号
   * 18位数字
   * */
  public function get_serial_no()
  {
    return date('YmdHis').rand(1000,9999);
  }

}