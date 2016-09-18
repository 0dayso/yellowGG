<?php
namespace Liehuo\Model;

class OperLogModel extends CjAdminLogModel
{

  public $types = [
    'login'          => '后台登陆',
    'system'         => '系统日志',
    'scoring'        => '用户评级',
    'score_modify'   => '更改评级',
    'avatar_audit'   => '照片审核',
    'text_audit'     => '文字审核',
    'user_info_set'  => '资料修改',
    'feedback'       => '意见反馈',
    'msg_send_bat'   => '消息群发',
    'report_handle'  => '举报处理',
    'closure'        => '封禁降权',
    'unclosure'      => '用户解封',
    'add_power'      => '开通特权',
    'del_power'      => '解除特权',
    'give'           => '赠送礼包',
    'add_like'       => '赠送普赞',
    'add_super_like' => '赠送超赞',
    'add_vip_days'   => '赠送会员',
    'cash_submit'    => '提现提交',
    'cash_set_state' => '提现审核',
    'promo_offline'  => '地推导入',
    'promo_snap'     => '街拍群发',
    'article'        => '文章管理',
  ];


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid']) $map[$alias.'uid']  = $uid;
    if($aid = (int)$arr['aid']) $map[$alias.'aid']  = $aid;
    if($arr['type'] != '')      $map[$alias.'type'] = mysql_escape_string($arr['type']);
    if($arr['type'] == 'give')  $map[$alias.'type'] = ['in',['give','add_like','add_super_like','add_vip_days']];
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
      $sql = D('UserBase')->table('chujiandw.__USER_BASE__')->field('uid')
        ->where(['uid' => ['exp',' = '.$alias.'uid'],'sex' => $sex])
        ->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $map['_complex'] = [
          '_logic' => 'or',
          $alias.'remark' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd)) $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
    }
    return $map;
  }

  // 记录日志
  public function log($type = '',$remark = [],$dat = [])
  {
    if(is_numeric($dat)) $dat = ['uid' => $dat];
    $dat = array_merge($dat ?: [],[
      'type'   => $type,
      'remark' => $remark,
    ]);
    return $this->add_log($dat);
  }

  // 添加日志
  public function add_log($dat = [])
  {
    $dat = array_merge([
      'type'        => '',
      'aid'         => (int)$_SESSION[C('USER_AUTH_KEY')],
      'uid'         => 0,
      'ip'          => trim($_SERVER['REMOTE_ADDR']),
      'remark'      => '',
      'create_time' => time(),
    ],$dat ?: []);
    if(!$dat['type']) return false;
    //if(!isset($this->types[$dat['type']])) return false;
    if(is_array($dat['remark']))
    {
      $arr = [];
      foreach($dat['remark'] as $k => $v)
      {
        if($v) $arr[] = (is_numeric($k) ? '' : ($k.':')).$v;
      }
      $dat['remark'] = implode(";\n",$arr);
    }
    $dat['id'] = $this->add($dat);
    $ret = $dat['id'] ? $dat : $dat['id'];
    return $ret;
  }

  // 获取客服操作统计数据
  public function list_analy($map = [],$page = 20)
  {
    $this->field([
      'from_unixtime(create_time,\'%Y-%m-%d\')' => 'create_date',
      'type',
      'count(id)' => 'count',
    ]);
    if($map) $this->where($map);
    $this->group('create_date,type');
    $this->plist($page,$map);
    $this->order('create_date desc');
    return $this;
  }

  public function analy_bytype($arr = [])
  {
    $dat = [];
    foreach($arr ?: [] as $v)
    {
      $key   = $v['create_date'];
      $field = 'cnt_'.$v['type'];
      $dat[$key] = array_merge($dat[$key] ?: [],[
        'date'        => $v['create_date'],
        'create_date' => $v['create_date'],
        'cnt_all'     => (int)$v['count'] + (int)$dat[$key]['cnt_all'],
        $field        => (int)$v['count'] + (int)$dat[$key][$field],
      ]);
    }
    return $dat;
  }

  // 获取客服操作统计数据
  public function list_analy_byaid($map = [],$page = 20)
  {
    $this->field([
      'from_unixtime(create_time,\'%Y-%m-%d\')' => 'create_date',
      'aid',
      'type',
      'count(id)' => 'count',
    ]);
    if($map) $this->where($map);
    $this->group('create_date,aid,type');
    $this->plist($page,$map);
    $this->order('create_date desc');
    return $this;
  }

  public function analy_byaid($arr = [])
  {
    $dat = [];
    foreach($arr ?: [] as $v)
    {
      $key   = $v['create_date'].':'.$v['aid'];
      $field = 'cnt_'.$v['type'];
      $dat[$key] = array_merge($dat[$key] ?: [],[
        'date'        => $v['create_date'],
        'create_date' => $v['create_date'],
        'aid'         => $v['aid'],
        'cnt_all'     => (int)$v['count'] + (int)$dat[$key]['cnt_all'],
        $field        => (int)$v['count'] + (int)$dat[$key][$field],
      ]);
    }
    return $dat;
  }

}