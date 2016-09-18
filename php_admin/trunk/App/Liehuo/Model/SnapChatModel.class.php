<?php
namespace Liehuo\Model;

class SnapChatModel extends CjAdminModel
{

  protected $redis_config = 'redis_user';
  public $mq_snap_audit   = 'php_list_snap_audit';  //待审核队列
  public $mq_snap_result  = 'php_list_snap_result'; //审核结果
  //redis -h redisuser.chujianapp.com php_list_snap_audit
  //redis -h redisuser.chujianapp.com php_list_snap_result

  const STATE_CREATED  = 0;
  const STATE_APPROVED = 1;
  const STATE_REJECTED = 2;

  public $states =
  [
    self::STATE_CREATED  => '未审核',
    self::STATE_APPROVED => '已通过',
    self::STATE_REJECTED => '已拒绝',
  ];

  const SOURCE_USER  = 1;
  const SOURCE_ADMIN = 2;

  public $sources =
  [
    self::SOURCE_USER  => '用户',
    self::SOURCE_ADMIN => '运营',
  ];

  const SNAP_TEXT     = 0;//文本
  const SNAP_IMAGE    = 1;//图片
  const SNAP_COMPLEX  = 2;//图文

  const TARGET_UNKNOWN  = 0;//未知
  const TARGET_MATCHED  = 1;//匹配
  const TARGET_CHATTED  = 2;//聊过
  const TRAGET_SPECIFIC = 3;//指定

  public static $datas = [];

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
    $time_type = $arr['time_type'] == 'handle' ? 'handle_time' : ($alias.'create_time');
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$time_type]) || $map[$time_type] = [];
      $map[$time_type][] = ['egt',strtotime(date('Y-m-d 00:00:00',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$time_type]) || $map[$time_type] = [];
      $map[$time_type][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($arr['uid']   != '') $map[$alias.'uid']   = (int)$arr['uid'];
    if($arr['oid']   != '') $map[$alias.'oid']   = (int)$arr['oid'];
    if($arr['state'] != '') $map[$alias.'state'] = (int)$arr['state'];
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $field = trim($arr['search_field']);
      $map['_complex'] =
      [
        '_logic' => 'or',
      ];
      if(!$field || $field == 'text')   $map['_complex'][$alias.'text']   = ['like','%'.$kwd.'%'];
      if(!$field || $field == 'remark') $map['_complex'][$alias.'remark'] = ['like','%'.$kwd.'%'];
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'uid') $map['_complex'][$alias.'uid'] = $kwd;
        if(!$field || $field == 'oid') $map['_complex'][$alias.'oid'] = $kwd;
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

  public function get_snap($id = 0)
  {
    $dat = self::$datas[$id];
    if(!$dat)
    {
      $dat = $this->find($id);
      is_string($dat['attrs']) && $dat['attrs'] = json_decode($dat['attrs'],true);
      $dat && self::$datas[$dat['id']] = $dat;
    }
    return $dat;
  }

  public function set_snap($dat = [],$id = true)
  {
    $ret = false;
    $id === true && $id = (int)$dat['id'];
    $old = is_array($id) ? $id : $this->get_snap($id);
    $id = (int)$old['id'];
    if(!$old) $this->error = '对象不存在';
    else
    {
      if(isset($old['attrs']) && isset($dat['attrs']))
      {
        $old['attrs'] = $this->get_attrs($old) ?: [];
        $dat['attrs'] = $this->get_attrs($dat) ?: [];
        $dat['attrs'] = array_merge($old['attrs'],$dat['attrs']);
      }
      $dat = array_merge($old,$dat ?: []);
      $dat && self::$datas[$id] = $dat;
      $dat['attrs'] = $this->auto_attrs($dat['attrs']);
      unset($dat['id']);
      if($this->where(['id' => $id])->save($dat) !== false)
      {
        $ret = self::$datas[$id];
      }
    }
    return $ret;
  }

  public function get_attrs($id = 0)
  {
    $dat = is_array($id) ? $id : ($this->get_snap($id) ?: []);
    is_string($dat['attrs']) && $dat['attrs'] = (json_decode($dat['attrs'],true) ?: []);
    return $dat['attrs'];
  }

  public function set_attrs($id = 0,$dat = [],$old = true)
  {
    $ret = false;
    $old === true && $old = $this->get_attrs($id);
    is_array($old) || $old = [];
    is_array($dat) || $dat = [];
    if($dat)
    {
      $dat = array_merge($old,$dat);
      if($this->where(['id' => $id])->save(['attrs' => $this->auto_attrs($dat)]) !== false)
      {
        $ret = $dat;
      }
    }
    return $ret;
  }

  public function add_snap($dat = [])
  {
    $dat = array_merge(
    [
      'uid'         => 0,
      'oid'         => 0,
      'image'       => '',
      'text'        => '',
      'attrs'       => '',
      'state'       => self::STATE_CREATED,
      'source'      => self::SOURCE_USER,
      'create_time' => time(),
    ],$dat ?: []);
    $dat['attrs'] = $this->auto_attrs($dat['attrs']);
    $dat['id'] = $this->add($dat);
    return $dat['id'] ? $dat : $dat['id'];
  }

  // 弹出审核队列
  public function audit_pop($len = 1)
  {
    $rds = $this->get_redis();
    $max = (int)$len;
    $lst = [];
    for($i = 0;$i < $max;$i++)
    {
      $jss = $rds->lPop($this->mq_snap_audit);
      $row = json_decode($jss,true) ?: [];
      if(!$row) continue;
      $msg = is_array($row['text']) ? $row['text'] : json_decode($row['text'],true);
      if($msg && $msg['res'])
      {
        $row['text']     = $msg;
        $row['original'] = $jss;
        $lst[] = $row;
        $this->add_snap(
        [
          'uid'         => (int)$row['fromuid'],
          'image'       => (string)$msg['res'],
          'text'        => (string)$msg['txt'],
          'attrs'       => ['original' => $jss],
          'create_time' => (int)$row['time'] ?: time(),
        ]);
        rds_log($row,'admin_msg_snap_chat_pop');
      }
      $this->send_snap($jss);
    }
    return $lst;
  }

  // 队列剩余数
  public function audit_len()
  {
    return (int)$this->get_redis()->lLen($this->mq_snap_audit);
  }

  // 发送/审核通过瞬间消息
  public function send_snap($msg = [],$pass = true,$src = self::SOURCE_USER)
  {
    is_string($msg) && $msg = json_decode($msg,true);
    $msg = array_merge(
    [
      'chat_type' => 6,//瞬间消息固定为6
      'text_type' => 100,
      'text'      => '',
      'text_css'  => '',
      'smskey'    => 0,
      'batched'   => true,
      'fromuid'   => 10000,
      'touid'     => 0,
      'index'     => '',
      'group_id'  => 0,
      'target'    =>
      [
        'type'    => self::TARGET_MATCHED,
        'userids' => [],
      ],
      'time'      => time(),
    ],$msg ?: []);
    is_array($msg['text']) && $msg['text'] = json_encode($msg['text'],JSON_UNESCAPED_UNICODE);
    $rds = $this->get_redis();
    $ret = $rds->lPush($this->mq_snap_result,json_encode(
    [
     'pass'   => !!$pass,
     'source' => (int)$src,// 1:用户 2:后台
     'msg'    => $msg,
    ],JSON_UNESCAPED_UNICODE));
    if($ret) $ret = $msg;
    rlog($msg,'admin_msg_snap_chat_send');
    return $ret;
  }

  // 发送定时瞬间
  public function send_timing_snap($tim = 0,$msg = [],$exclusive = false)
  {
    is_string($msg) && $msg = json_decode($msg,true);
    $msg = array_merge(
    [
      'chat_type' => MessageModel::CHAT_TYPE_SNAP,//瞬间消息固定为6
      'text_type' => MessageModel::TEXT_TYPE_COMPOUND,
      'text'      => '',
      'text_css'  => '',
      'smskey'    => 0,
      'batched'   => true,
      'fromuid'   => 10000,
      'touid'     => 0,
      'index'     => '',
      'group_id'  => 0,
      'target'    =>
      [
        'type'    => self::TARGET_MATCHED,
        'userids' => [],
      ],
      'time'      => time(),
    ],$msg ?: []);
    is_array($msg['text']) && $msg['text'] = json_encode($msg['text'],JSON_UNESCAPED_UNICODE);
    return MessageModel::Instance($msg['touid'],0,
    [
      'sender'      => (int)$msg['fromuid'],
      'chat_type'   => MessageModel::CHAT_TYPE_SNAP,
      'text_type'   => MessageModel::TEXT_TYPE_COMPOUND,
      'timing_type' => 2,
    ])->add_timing_queue($tim,
    [
     'pass'   => true,
     'source' => self::SOURCE_ADMIN,// 1:用户 2:后台
     'msg'    => $msg,
    ],$exclusive);
  }


  // 获取历史记录
  public function get_list_by_logs($arr = [])
  {
    $lst = [];
    foreach($arr ?: [] as $k => $v)
    {
      $msg = json_decode($v['text'],true);
      if(!$msg) continue;
      $lst[$k] =
      [
        'id'          => 0,
        'uid'         => $v['sender'],
        'oid'         => $v['sender'],
        'image'       => $msg['res'],
        'text'        => $msg['txt'],
        'state'       => self::STATE_APPROVED,
        'source'      => self::SOURCE_USER,
        'create_time' => strtotime($v['time']),
        'handle_time' => strtotime($v['time']),
        'smsid'       => $v['smsid'],
        'cnt_send'    => $v['count'],
      ];
    }
    return $lst;
  }

}