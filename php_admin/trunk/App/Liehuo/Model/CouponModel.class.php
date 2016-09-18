<?php
namespace Liehuo\Model;

class CouponModel extends CjDatadwModel
{

  const STATE_CREATED = 0;//红包已创建完成，未接收状态
  const STATE_TAKEN   = 1;//已接收
  const STATE_EXPIRED = 2;//已过期

  const TYPE_SMALL     = 0;//小红包
  const TYPE_LARGE     = 1;//大红包
  const TYPE_GOLD_LIKE = 2;//金星超赞
  const TYPE_GIFT      = 3;//礼物

  const SRC_TYPE_COMMON = 0;//普通用户红包
  const SRC_TYPE_SYSTEM = 1;//系统赠送红包

  public $states = [
    self::STATE_CREATED => '未接收',
    self::STATE_TAKEN   => '已接收',
    self::STATE_EXPIRED => '已过期',
  ];

  public $types = [
    self::TYPE_SMALL     => '小红包',
    self::TYPE_LARGE     => '大红包',
    self::TYPE_GOLD_LIKE => '金星超赞',
    self::TYPE_GIFT      => '礼物',
  ];

  public $src_types = [
    self::SRC_TYPE_COMMON => '普通用户红包',
    self::SRC_TYPE_SYSTEM => '系统赠送红包',
  ];

  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid'])  $map[$alias.'uid']   = $uid;
    if($oid = (int)$arr['oid'])  $map[$alias.'oid']   = $oid;
    if($_REQUEST['type']  != '') $map[$alias.'type']  = (int)$_REQUEST['type'];
    if($_REQUEST['state'] != '') $map[$alias.'state'] = (int)$_REQUEST['state'];
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
    if($arr['sex'] != '')//性别筛选
    {
      $sex = (int)$arr['sex'] == 0 ? 0 : 1;
      $sql = D('UserBase')->field('uid')
        ->where(['uid' => ['exp',' = '.$alias.'uid'],'sex' => $sex])
        ->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($kwd = trim($arr['kwd']))
    {
      $map['_complex'] = [
          '_logic' => 'or',
          //$alias.'reason' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd))
      {
        $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
        $map['_complex'][$alias.'oid'] = ['like','%'.$kwd.'%'];
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

}