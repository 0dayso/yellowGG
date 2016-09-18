<?php
namespace Liehuo\Model;

class GlamourRecordModel extends LhWalletModel
{

  protected $subtable_is = 1;//是否分表

  const TYPE_OTHER              = 0;
  const TYPE_INCOME_SYSTEM      = 100;
  const TYPE_INCOME_LIKE        = 102;
  const TYPE_INCOME_REFUND      = 104;
  const TYPE_INCOME_SUPER_LIKE  = 105;
  const TYPE_INCOME_GOLD_LIKE   = 106;
  const TYPE_INCOME_WINNING     = 107;
  const TYPE_INCOME_GIFT        = 108;
  const TYPE_INCOME_LUCKY_BAG   = 109;
  const TYPE_INCOME_LIVE_GIFT   = 110;
  const TYPE_INCOME_LIVE_GAME   = 111;
  const TYPE_EXPENSE_SYSTEM     = 200;
  const TYPE_EXPENSE_CASH       = 201;
  const TYPE_EXPENSE_EXCHANGE   = 214;

  public $types = [
      self::TYPE_OTHER              => '其他',
      self::TYPE_INCOME_SYSTEM      => '系统赠送',
      self::TYPE_INCOME_LIKE        => '喜欢收入',
      self::TYPE_INCOME_REFUND      => '系统退款',
      self::TYPE_INCOME_SUPER_LIKE  => '超喜欢收入',
      self::TYPE_INCOME_GOLD_LIKE   => '一见倾心',
      self::TYPE_INCOME_WINNING     => '中奖祝福',
      self::TYPE_INCOME_GIFT        => '收取礼物',
      self::TYPE_INCOME_LUCKY_BAG   => '领取福袋',
      self::TYPE_INCOME_LIVE_GIFT   => '直播收礼',
      self::TYPE_INCOME_LIVE_GAME   => '游戏收入',
      self::TYPE_EXPENSE_SYSTEM     => '系统扣除',
      self::TYPE_EXPENSE_CASH       => '魅力提现',
      self::TYPE_EXPENSE_EXCHANGE   => '魅力兑换',
  ];


  public function set_uid($uid = 0)
  {
    $this->uid = (int)$uid;
    $this->set_type($this->type);
    return $this;
  }

  public function set_type($typ = self::TYPE_OTHER)
  {
    isset($this->default_subtable_is) || $this->default_subtable_is = $this->subtable_is;
    $this->type = (int)$typ;
    // 被赞收入分表
    if($this->type == self::TYPE_INCOME_LIKE)
    {
      $this->subtable_is = 1;
      $this->set_table($this->get_subtable_name($this->uid,'cj_glamour_record_like'));
    }
    // 直播收入分表
    elseif(in_array($this->type,[self::TYPE_INCOME_LIVE_GIFT,self::TYPE_INCOME_LIVE_GAME]))
    {
      $this->subtable_is = 1;
      $this->set_table($this->get_subtable_name($this->uid,'cj_glamour_record_live'));
    }
    else
    {
      $this->subtable_is = $this->default_subtable_is;
      $this->set_table($this->get_subtable_name($this->uid));
    }
    return $this;
  }


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: '';
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid'])  $map[$alias.'uid']  = $uid;
    if($oid = (int)$arr['oid'])  $map[$alias.'oid']  = $oid;
    if($typ = (int)$arr['type']) $map[$alias.'type'] = $typ;
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

  public function get_all($uid = 0,$map = [])
  {
    $map['uid'] || $map['uid'] = $uid;
    $map && $this->where($map);
    $sql = M('','',$this->connection)->table($this->get_subtable_name($uid,'cj_glamour_record_like'))->where($map)->buildSql();
    $this->union($sql);
    $sql = M('','',$this->connection)->table($this->get_subtable_name($uid,'cj_glamour_record_live'))->where($map)->buildSql();
    $this->union($sql);
    $this->set_table($this->buildSql().' tmp');
    //$map && $this->where($map);
    return $this;
  }

  public function add_record($data = [])
  {
    $data = array_merge([
      'create_time' => time(),
    ],$data ?: []);
    if(!isset($data['create_date'])) $data['create_date'] = date('Y-m-d',$data['create_time']);
    if($data['uid'])  $this->set_uid($data['uid']);
    if($data['type']) $this->set_type($data['type']);
    $w_old = false;//是否写主表
    $w_sub = false;//是否写子表
    if(!$this->subtable_is) $w_old = true;
    else
    {
      $w_sub = !$this->subtable_readonly;
      if($this->base_table_hold || $this->subtable_readonly) $w_old = true;
    }
    if($w_sub)
    {
      $ret = $this->add($data);
    }
    // 继续维护总表
    if($w_old)
    {
      $id = $this->getTable(false)->add($data);
    }
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