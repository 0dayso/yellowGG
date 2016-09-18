<?php
namespace Liehuo\Model;

class UserZanModel extends CjDatadwModel
{

  protected $redis_config = 'redis_user';
  //public $redis_user_zan  = 'user_zan_';
  public $redis_user_zan  = 'user_zan_v8_';

  // 每次送赞的数量
  public $free_zan_times = [
    0 => 80,
    1 => 1,
  ];

  // 赞类型
  public $zan_types = [
    0 => '免费普赞',
    1 => '免费超赞',
    2 => '付费超赞',
    3 => '付费普赞',
    4 => '返回机会',
  ];

  protected $zan_fields = [
    0 => 'zan',
    1 => 'super_zan',
  ];

  public function __construct()
  {
    parent::__construct();
  }


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid']) $map[$alias.'uid']  = $uid;
    if($arr['zan_type'] != '')           $map[$alias.'zan_type'] = (int)$arr['zan_type'];
    if($arr['zan_type'] == 'like')       $map[$alias.'zan_type'] = ['in',[0,3]];
    if($arr['zan_type'] == 'super_like') $map[$alias.'zan_type'] = ['in',[1,2]];
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'sub_zan_time']) || $map[$alias.'sub_zan_time'] = [];
      $map[$alias.'sub_zan_time'][] = ['egt',strtotime(date('Y-m-d 00:00:00',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'sub_zan_time']) || $map[$alias.'sub_zan_time'] = [];
      $map[$alias.'sub_zan_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $map['_complex'] =
      [
          '_logic' => 'or',
          //$alias.'remark' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd)) $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
      if(count($map['_complex']) <= 1) unset($map['_complex']);
    }
    return $map;
  }

  // 获取用户剩余赞
  public function get_zan($uid = 0,$type = 0)
  {
    $dat = $this->get_zan_all($uid);
    $field = $this->zan_fields[$type] ?: 'zan';
    $arr = $dat[$field] ?: [];
    return (int)$arr['free'] + (int)$arr['fees'];
  }

  public function get_zan_all($uid = 0)
  {
    $key = $this->redis_user_zan.$uid;
    $rds = $this->get_redis();
    $ret = [];
    foreach($this->zan_fields ?: [] as $type => $field)
    {
      $old = $rds->hGet($key,$field);
      list($free,$stm,$etm,$fees) = explode(':',$old);
      if(time() > (int)$etm) $free = $this->free_zan_times[$type];
      $ret[$field]['fees'] = (int)$fees;
      $ret[$field]['free'] = (int)$free;
      $ret[$field]['total'] = (int)$fees + (int)$free;
      $ret[$field]['stime'] = (int)$stm;
      $ret[$field]['etime'] = (int)$etm;
    }
    return $ret;
  }

  // 赠送赞v2.0
  public function set_like_times($uid = 0,$num = 0,$type = 3)
  {
    return D('RpcApi')->call('User/setLikeTimes',
    [
      'uid'  => (int)$uid,
      'num'  => (int)$num,
      'type' => (int)$type,
    ]);
  }

  // 赠送赞 v1.8
  public function set_zan($uid,$zan = 0,$type = 0)
  {
    $field = $this->zan_fields[$type] ?: 'zan';
    $key = $this->redis_user_zan.$uid;
    $ret = false;
    $rds = $this->get_redis();
    $old = $rds->hGet($key,$field);
    list($free,$stm,$etm,$fees) = explode(':',$old);
    $fees = (int)$fees + (int)$zan;
    $new = implode(':',[(int)$free,(int)$stm,(int)$etm,$fees]);
    $ret = $rds->hSet($key,$field,$new);
    return $ret;
  }

  // 赠送赞 v1.7
  public function set_zan_v7($uid,$zan = 0,$type = 0)
  {
    $ret = $this->add_zan(['uid' => $uid,'sub_zan' => $zan,'zan_type' => $type]);
    if($ret) $this->set_zan_surp_inc($uid,$zan,$type);
    return $ret;
  }

  // 获取剩余赞次数
  public function get_zan_surp($uid = 0,$type = 0)
  {
    $field = $this->zan_fields[$type] ?: 'zan';
    return (int)$this->get_redis()->hGet($this->redis_user_zan.$uid,$field);
  }

  // 设置剩余赞次数
  public function set_zan_surp($uid = 0,$zan = 0,$type = 0)
  {
    $field = $this->zan_fields[$type] ?: 'zan';
    $ret = $this->get_redis()->hSet($this->redis_user_zan.$uid,$field,$zan);
    return $ret ? $zan : false;
  }

  // 增加剩余赞次数
  public function set_zan_surp_inc($uid = 0,$zan = 0,$type = 0)
  {
    $sur = $this->get_zan_surp($uid,$type);
    $zan = $sur + (int)$zan;
    return $this->set_zan_surp($uid,$zan,$type);
  }

  // 增加赞
  public function add_zan($dat = [])
  {
    $dat = array_merge([
      'uid'           => 0,
      'sub_zan'       => 0,
      'sub_zan_time'  => time(),
      'surplus_zan'   => (int)$dat['sub_zan'],
      'use_last_time' => 0,
      'next_get_zan'  => 0,
      'zan_type'      => 0,
    ],$dat ?: []);
    $dat['id'] = $this->add($dat);
    return $dat['id'] ? $dat : false;
  }

  // 获取有效的赞
  public function get_zan_valid($uid = 0,$type = 0)
  {
    $map = [
      'uid'          => $uid,
      'zan_type'     => $type,
      'next_get_zan' => 0,
    ];
    return $this->where($map)->order('id')->find();
  }

}