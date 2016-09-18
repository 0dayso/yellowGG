<?php
namespace Liehuo\Model;

class DiamondRecordModel extends GlamourRecordModel
{

  protected $subtable_is = 1;//是否分表

  const TYPE_OTHER              = 0;
  const TYPE_INCOME_SYSTEM      = 100;
  const TYPE_INCOME_CHARGE      = 101;
  const TYPE_INCOME_REFUND      = 104;
  const TYPE_INCOME_WINNING     = 107;
  const TYPE_INCOME_LUCKY_BAG   = 109;
  const TYPE_INCOME_LIVE_GIFT   = 110;
  const TYPE_INCOME_LIVE_GAME   = 111;
  const TYPE_INCOME_TASK        = 112;
  const TYPE_INCOME_CHEST       = 113;
  const TYPE_INCOME_EXCHANGE    = 114;
  const TYPE_EXPENSE_SYSTEM     = 200;
  const TYPE_EXPENSE_LIKE       = 202;
  const TYPE_EXPENSE_ORDER      = 204;
  const TYPE_EXPENSE_SUPER_LIKE = 205;
  const TYPE_EXPENSE_GOLD_LIKE  = 206;
  const TYPE_EXPENSE_GIFT       = 208;
  const TYPE_EXPENSE_LUCKY_BAG  = 209;
  const TYPE_EXPENSE_LIVE_GIFT  = 210;
  const TYPE_EXPENSE_LIVE_GAME  = 211;
  const TYPE_EXPENSE_VIP        = 212;

  public $types = [
    self::TYPE_OTHER              => '其他',
    self::TYPE_INCOME_SYSTEM      => '系统赠送',
    self::TYPE_INCOME_CHARGE      => '余额充值',
    self::TYPE_INCOME_REFUND      => '系统退款',
    self::TYPE_INCOME_WINNING     => '中奖祝福',
    self::TYPE_INCOME_LUCKY_BAG   => '领取福袋',
    self::TYPE_INCOME_LIVE_GIFT   => '直播收礼',
    self::TYPE_INCOME_LIVE_GAME   => '游戏收入',
    self::TYPE_INCOME_TASK        => '任务奖励',
    self::TYPE_INCOME_CHEST       => '领取宝箱',
    self::TYPE_INCOME_EXCHANGE    => '兑换钻石',
    self::TYPE_EXPENSE_SYSTEM     => '系统扣账',
    self::TYPE_EXPENSE_LIKE       => '购买喜欢',
    self::TYPE_EXPENSE_ORDER      => '普通消费',
    self::TYPE_EXPENSE_SUPER_LIKE => '购买超喜欢',
    self::TYPE_EXPENSE_GIFT       => '送出礼物',
    self::TYPE_EXPENSE_LUCKY_BAG  => '送出福袋',
    self::TYPE_EXPENSE_LIVE_GIFT  => '直播送礼',
    self::TYPE_EXPENSE_LIVE_GAME  => '游戏支出',
    self::TYPE_EXPENSE_VIP        => '购买会员',
  ];


  public function set_type($typ = self::TYPE_OTHER)
  {
    isset($this->default_subtable_is) || $this->default_subtable_is = $this->subtable_is;
    $this->type = (int)$typ;
    if($this->type != -1)
    {
      $this->subtable_is = $this->default_subtable_is;
      $this->table($this->get_subtable_name($this->uid));
    }
    return $this;
  }

  public function get_all($uid = 0,$map = [])
  {
    $this->set_uid($uid);
    $map['uid'] || $map['uid'] = $uid;
    $map && $this->where($map);
    return $this;
  }

}