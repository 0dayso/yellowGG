<?php
namespace Liehuo\Model;

class OrderModel extends CjDatadwModel
{

  const STATE_CREATED  = 0;//订单已创建完成，未付款
  const STATE_PAID     = 1;//已付款
  const STATE_FINISHED = 2;//交易已完成
  const STATE_CLOSED   = 3;//交易已关闭

  const PAY_TYPE_BALANCE = 0;//余额
  const PAY_TYPE_ALIPAY  = 1;//支付宝
  const PAY_TYPE_WXPAY   = 2;//微信
  const PAY_TYPE_APPLE   = 3;//苹果
  const PAY_TYPE_DIAMOND = 4;//钻石

  public $states = [
    self::STATE_CREATED  => '未付款',
    self::STATE_PAID     => '已付款',
    self::STATE_FINISHED => '已完成',
    self::STATE_CLOSED   => '已关闭',
  ];

  public $pay_types = [
    self::PAY_TYPE_BALANCE => '烈火余额',
    self::PAY_TYPE_ALIPAY  => '支付宝',
    self::PAY_TYPE_WXPAY   => '微信',
    self::PAY_TYPE_APPLE   => '苹果',
    self::PAY_TYPE_DIAMOND => '钻石',
  ];


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid'])       $map[$alias.'uid']      = $uid;
    if($arr['filter'] == 'review')    $map[$alias.'pay_data'] = ['like','{%'];
    if($arr['state']    != '')        $map[$alias.'state']    = (int)$arr['state'];
    if($arr['pay_type'] != '')        $map[$alias.'pay_type'] = (int)$arr['pay_type'];
    if($arr['goods'] == 'diamond')    $map[$alias.'goods_id'] = ['in',[1001,1002,1003,1004,1005,1006,1007,1008]];
    if($arr['goods'] == 'vip')        $map[$alias.'goods_id'] = ['in',[1,2,3,901,902,903]];
    if($arr['goods'] == 'like')       $map[$alias.'goods_id'] = ['in',[101,102,103,701,702,703]];
    if($arr['goods'] == 'super_like') $map[$alias.'goods_id'] = ['in',[201,202,203,801,802,803]];
    if($arr['goods'] == 'all_like')   $map[$alias.'goods_id'] = ['in',[101,102,103,201,202,203,300,701,702,703,801,802,803]];
    if($gid = (int)$arr['goods_id'])  $map[$alias.'goods_id'] = $gid;
    if($arr['stime'] && $stime = strtotime($_REQUEST['stime'] = $_GET['stime'] = urldecode(urldecode($arr['stime']))))
    {
      is_array($map[$alias.'create_time']) || $map[$alias.'create_time'] = [];
      $map[$alias.'create_time'][] = ['egt',strtotime(date('Y-m-d H:i:s',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($_REQUEST['etime'] = $_GET['etime'] = urldecode(urldecode($arr['etime']))))
    {
      is_array($map[$alias.'create_time']) || $map[$alias.'create_time'] = [];
      $map[$alias.'create_time'][] = ['elt',strtotime(date('Y-m-d H:i:59',$etime))];
    }
    if($arr['sex'] != '')//性别筛选
    {
      $sex = (int)$arr['sex'] == 0 ? 0 : 1;
      $sql = D('UserBase')->table('__USER_BASE__')->field('uid')
        ->where(['uid' => ['exp',' = '.$alias.'uid'],'sex' => $sex])
        ->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($kwd = trim($arr['kwd']))
    {
      $map['_complex'] = [
          '_logic' => 'or',
          $alias.'remark' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd)) $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
    }
    return $map;
  }

}