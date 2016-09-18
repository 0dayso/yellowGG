<?php
namespace Liehuo\Controller;
use \Liehuo\Model;

class SnapChatController extends PublicController
{

  public function __construct()
  {
    parent::__construct();
    isset($_REQUEST['page_size']) || $this->page_size = 60;
  }

  public function index()
  {
    //isset($_REQUEST['state']) || $_REQUEST['state'] = '0';
    $mod = D(CONTROLLER_NAME);
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','create_time desc,id desc') ?: [];
    $this->pager = $mod->pager;
    $dat['states'] = $mod->states ?: [];
    $dat['users']  = D('UserBase')->get_users_account($dat['list']);
    $this->data = $dat;
    $this->display();
  }

  public function send()
  {
    $this->display();
  }

  public function logs()
  {
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-1 days'));
    $mod = D('ChatLogBase');
    $mod->sday = (int)$_REQUEST['day'];
    $mod->eday = 0;
    $map = $mod->get_filters();
    $map['chattype'] = 6;
    $map['texttype'] = 100;
    $mod->get_chat_log_union($map,$mod->sday,$mod->eday,true)
      ->field(['sender','smsid','text','time','count(recver)' => 'count','max(time)' => 'time_last','min(time)' => 'time_first'])
      ->group('sender,smsid')
      ->order('time desc');
    if($_REQUEST['target'] == Model\SnapChatModel::TARGET_MATCHED)  $mod->having('count >= 2');
    if($_REQUEST['target'] == Model\SnapChatModel::TRAGET_SPECIFIC) $mod->having('count = 1');
    $dat['list'] = $mod->plist($this->page_size)->lists() ?: [];
    $dat['list'] = D(CONTROLLER_NAME)->get_list_by_logs($dat['list']);
    $this->pager = $mod->pager;
    $dat['users'] = D('UserBase')->get_users_account($dat['list']);
    $dat['targets'] =
    [
      Model\SnapChatModel::TARGET_MATCHED  => '所有匹配',
      Model\SnapChatModel::TRAGET_SPECIFIC => '单聊',
    ];
    $this->data = $dat;
    $this->display('index');
  }

  public function repeat_count()
  {
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-7 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime($_REQUEST['stime']) + 60 * 60 * 24 * 3);
    $_REQUEST['smsid'] = (string)(int)$_REQUEST['smsid'];
    $key = 'snapchat-repeat-count-'.$_REQUEST['sender'].'-'.$_REQUEST['smsid'];
    if(!($dat['count'] = session($key)))
    {
      $mod = D('ChatLogBase');
      $mod->sday = (int)$_REQUEST['day'];
      $mod->eday = 0;
      $map = $mod->get_filters();
      $map['chattype'] = 6;
      $map['texttype'] = 100;
      $uls = /*$dat['users'] = */$mod->get_chat_log_union($map,$mod->sday,$mod->eday,true)
        ->field(['sender','recver','time'])
        ->order('time,smsid')
        ->lists() ?: [];
      $ids = array_unique(array_column($uls,'recver')) ?: [];
      //$dat['sql-users']  = $mod->getLastSql();
      $dat['count_send'] = count($ids);
      if($ids)
      {
        $fst = $uls[0];
        $stm = $fst['time'];
        $etm = date('Y-m-d H:i:s',strtotime($fst['time']) + 60 * 60 * 24 * 3);
        $map = $mod->get_filters('',
        [
          'recver' => $fst['sender'],
          'stime'  => $stm,
          'etime'  => $etm,
        ]);
        $map['sender'] = ['in',array_values($ids)];
        $map['time'] =
        [
          ['egt',$stm],
          ['elt',$etm],
        ];
        $dat['where'] = $map;
        $dat['count'] = $mod->get_chat_log_union($map,$mod->sday,$mod->eday,false)->count('distinct sender');
        //$dat['sql-count'] = $mod->getLastSql();
        $dat['message'] = '发送'.$dat['count_send'].'人，回复'.$dat['count'].'人';
        session($key,$dat);
      }
    }
    $this->success($dat);
  }


  // 获取审核队列
  public function queue()
  {
    $mod = D(CONTROLLER_NAME);
    $dat['list'] = $mod->audit_pop();
    $dat['list'] || $dat['list'] =
    [
      [
        'chat_type' => 6,//瞬间消息固定为6
        'text_type' => 100,
        'text'      =>
        [
          'type'    => 0,
          'res'     => 'http://feed.chujianapp.com/20160419/76331ea37567d08ebb92694482166072.jpg',
          'txt'     => '瞬间消息...'.PHP_EOL.'瞬间消息瞬间消息...',
        ],
        'fromuid'   => 10000,
        'batched'   => true,
        'target'    =>
        [
          'type'    => 0,
          'userids' => [],
        ],
        'time'      => time(),
      ],
    ];
    $dat['queue_len'] = $mod->audit_len();
    $this->data = $dat;
    $this->success($dat);
  }

  // 瞬间审核处理
  public function handle()
  {
    $id  = (int)$_REQUEST['id'];
    $dat =
    [
      'state'       => (int)$_REQUEST['state'],
      'remark'      => trim($_REQUEST['remark']),
      'handle_time' => time(),
    ];
    $msg = trim($_REQUEST['msg']);
    $mat = 0;//匹配人数
    $srt = false;//是否发送
    $mod = D(CONTROLLER_NAME);
    if(!isset($mod->states[$dat['state']]))
    {
      $this->error('状态错误');
    }
    //elseif(!$dat['remark']) $this->error('备注不能为空');
    elseif(!$old = $mod->get_snap($id))
    {
      $this->error('对象不存在');
    }
    elseif(!$mod->set_snap($dat,$old))
    {
      $this->error('操作失败');
    }
    elseif($dat['state'] == Model\SnapChatModel::STATE_APPROVED/*通过*/)
    {
      $adt = $mod->get_attrs($old);
      if($adt['original'])
      {
        $srt = $mod->send_snap($adt['original']);//通过后发送
        $mat = D('Match')->get_count_byuid($old['uid']);
        $mod->set_attrs($id,['match_num' => $mat]);
      }
    }
    elseif($dat['state'] == Model\SnapChatModel::STATE_REJECTED/*拒绝*/)
    {
      $adt = $mod->get_attrs($old);
      if($adt['original'])
      {
        $mod->send_snap($adt['original'],false);
      }
    }
    if($msg) @D('Message')->add_msg_system($old['uid'],$msg);
    D('OperLog')->log('snap_handle',
    [
      '瞬间ID'   => $id,
      '状态'     => $mod->states[$dat['state']],
      '备注'     => $dat['remark'],
      '文本'     => $old['text'],
      //'图片'     => $old['image'],
      '创建时间' => $old['create_time'] ? date('Y-m-d H:i:s',$old['create_time']) : '',
      '匹配人数' => $mat,
      '群发结果' => $srt ? '已发' : '未发',
      '话术'     => $msg,
    ],$old['uid']);
    $this->success('操作成功');
  }

  // 发送瞬间
  public function send_snap()
  {
    isset($_REQUEST['timing']) || $_REQUEST['timing'] = date('Y-m-d H:i:s',time() + 10);
    $msg =
    [
      'fromuid' => (int)$_REQUEST['sender'],
      'batched' => true,
      'text'    =>
      [
        'type' => Model\SnapChatModel::SNAP_COMPLEX/*图文*/,
        'res'  => trim($_REQUEST['res']),
        'txt'  => trim($_REQUEST['txt']),
      ],
    ];
    if    (!$msg['fromuid'])     $this->error('发送人错误');
    elseif(!$msg['text']['res']) $this->error('图片不能为空');
    else
    {
      $uid = (int)$msg['fromuid'];
      $mod = D(CONTROLLER_NAME);
      // 定时
      if($tim = strtotime($_REQUEST['timing']))
      {
        $ret = $mod->send_timing_snap($tim,$msg);
      }
      // 及时
      else
      {
        $ret = $mod->send_snap($msg,true,Model\SnapChatModel::SOURCE_ADMIN/*运营*/);
      }
      if(!$ret) $this->error('发送失败');
      D('UserBase')->set_active($uid);//更新运营账号活跃时间
      $dat = $mod->add_snap(
      [
        'uid'         => $uid,
        'image'       => (string)$msg['text']['res'],
        'text'        => (string)$msg['text']['txt'],
        'attrs'       => ['original' => is_string($ret) ? $ret : json_encode($ret)],
        'state'       => Model\SnapChatModel::STATE_APPROVED,
        'remark'      => trim($_REQUEST['remark']),
        'create_time' => time(),
        'handle_time' => $tim ?: time(),
      ]);
      if($dat)
      {
        $mat = D('Match')->get_count_byuid($uid);
        D('OperLog')->log('snap_handle',
        [
          '瞬间ID'   => $$dat['id'],
          '状态'     => $mod->states[$dat['state']],
          '文本'     => $dat['text'],
          //'图片'     => $dat['image'],
          '创建时间' => $dat['create_time'] ? date('Y-m-d H:i:s',$dat['create_time']) : '',
          '定时发送' => $tim ? date('Y-m-d H:i:s',$tim) : '',
          '匹配人数' => $mat,
          '群发结果' => $ret ? '已发' : '未发',
        ],$uid);
      }
    }
    $this->success('操作成功');
  }

}