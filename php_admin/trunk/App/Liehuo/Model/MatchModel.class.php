<?php
namespace Liehuo\Model;

class MatchModel extends CjDatadwModel
{

  protected $redis_config = 'redis_recommend';
  protected $subtable_is  = 1;//是否分表

  // 赞类型
  public $types =
  [
    0 => '普通赞',
    1 => '超级赞',
    2 => '金星赞',
  ];

  // 设置用户ID
  public function set_user($uid = 0)
  {
    $this->uid = (int)$uid;
    $this->set_table($this->get_subtable_name($this->uid));
  }


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: '';
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid'])  $map[$alias.'uid']        = $uid;
    if($oid = (int)$arr['oid'])  $map[$alias.'oid']        = $oid;
    if($arr['match_type'] != '') $map[$alias.'match_type'] = (int)$arr['match_type'];
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
    $map[$alias.'delete_time'] = 0;
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
          //$alias.'remark' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd))
      {
        $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
        $map['_complex'][$alias.'oid'] = ['like','%'.$kwd.'%'];
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    if($arr['deleted'] != '') $map[$alias.'delete_time'] = $arr['deleted'] ? ['egt',1] : 0;
    return $map;
  }

  public function get_count_byuid($uid = 0)
  {
    $this->set_user($uid);
    $this->where(
    [
      'uid'         => $uid,
      'delete_time' => 0,
    ]);
    return (int)$this->count('id');
  }

  public function get_list_byuoids($uis = [],$ois = [])
  {
    $uis && $this->where(['uid' => ['in',$uis]]);
    $ois && $this->where(['oid' => ['in',$ois]]);
    $arr = $this->select() ?: [];
    $dat = [];
    foreach($arr as $v)
    {
      $dat[$v['uid']][$v['oid']] = $v;
    }
    return $dat;
  }

}