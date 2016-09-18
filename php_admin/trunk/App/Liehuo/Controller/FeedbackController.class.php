<?php
namespace Liehuo\Controller;

class FeedbackController extends PublicController
{

  public $ret = ['ret' => 0,'msg' => '','data' => []];
  public $account_feedback  = 10000;//意见反馈账号
  public $chat_history_days = 7;
  public $chat_assign_max   = 20;//最多分配数量

  public $redis_feedback_list   = 'customer_service_10000';//实时意见反馈队列 lPush rPop
  public $redis_feedback_closed = 'php_feedback_closed';//结束会话列表
  public $redis_feedback_assign = 'php_feedback_assign';//会话分配列表

  public function test()
  {
    D('Message')->add_msg_sender((int)$_REQUEST['uid'] ?: 10000,(int)$_REQUEST['oid'] ?: 12200022,'Hi');
  }

  public function index()
  {
    isset($_SESSION['chat_assign_stop']) || session('chat_assign_stop',1);//默认不接受新会话
    $this->chat_assign_stop = session('chat_assign_stop');
    $this->display();
  }

  // 历史记录
  public function notes()
  {
    $mod = D('FeedbackNote');
    $dat = [];
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','start_time desc,id desc');
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['status'] = $mod->status;
    //$dat['users'] = D(CONTROLLER_NAME)->get_users_account($dat['list']);
    $dat['admins'] = D('Admin')->get_by_list($dat['list'],'aid,nickname');
    if(trim($_REQUEST['download'])) foreach($dat['list'] as $v)
    {
      $dat['export'][] =
      [
        '用户ID'   => $v['uid'],
        '咨询时间' => $v['start_time'] ? date('Y-m-d H:i:s',$v['start_time']) : '',
        '响应时间' => $v['reply_time'] ? date('Y-m-d H:i:s',$v['reply_time']) : '',
        '管理员'   => $dat['admins'][$v['aid']]['nickname'] ?: $v['aid'],
        '状态'     => $dat['status'][$v['status']] ?: $v['status'],
        '备注'     => $v['remark'],
      ];
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }

  // 未读消息库
  public function unreads()
  {
    $kwd = $_REQUEST['kwd'] = $_GET['kwd'] = urldecode(trim(I('request.kwd')));
    $stm = strtotime($_REQUEST['stime']);
    $etm = strtotime($_REQUEST['etime']);
    $mod = D('ChatLogBase');
    $rds = $mod->get_redis();
    $arr = $rds->lRange($this->redis_feedback_list,0,10000) ?: [];
    $lst = [];
    $hls = [];
    if($arr) foreach($arr ?: [] as $v)
    {
      $str = $v;
      is_string($v) && $v = json_decode($v,true);
      $tim = is_numeric($v['Time']) ? $v['Time'] : strtotime($v['Time']);
      $row = [
        'id'        => $v['Index'],
        'sender'    => $v['FromID'],
        'recver'    => $v['ToID'],
        'smsid'     => $v['MsgKey'],
        'text'      => $v['Text'],
        'texttype'  => $v['TextType'],
        'chattype'  => $v['ChatType'],
        'time'      => date('Y-m-d H:i:s',$tim),
        'time_unix' => $tim,
        'list_key'  => $str,
      ];
      $ist = true;
      $uid = $row['sender'];
      if($kwd && !preg_match('/'.$kwd.'/isu',$row['text'])) $ist = false;
      if($stm && $row['time_unix'] < strtotime(date('Y-m-d 00:00:00',$stm))) $ist = false;
      if($etm && $row['time_unix'] > strtotime(date('Y-m-d 23:59:59',$etm))) $ist = false;
      if($ist)
      {
        $lst[$uid] = $row;
        $hls[$uid] || $hls[$uid] = [];
        array_unshift($hls[$uid],$row);
      }
    }
    $cnt = count($lst);
    $pag = new \Think\Page($cnt,100);
    $lst = array_slice($lst,$pag->firstRow,$pag->listRows) ?: [];
    $dat['list'] = $lst;
    is_array($dat['list']) && $dat['list'] = $mod->format_text_all($dat['list']);
    $this->pager = $pag;
    $this->page  = $dat['page_html'] = $this->pager->show();
    $dat['history'] = $hls;
    $this->data  = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function unreads_bat()
  {
    $ids = (array)$_REQUEST['ids'];
    $rmk = trim(I('request.remark'));
    $msg = I('request.msg');
    if(!$ids)     $this->error('请选择记录');
    elseif(!$rmk) $this->error('备注不能为空');
    //elseif(!$msg) $this->error('话术不能为空');
    $rds = D('ChatLogBase')->get_redis();
    $mod = D('FeedbackNote');
    $log_model = D('OperLog');
    $msg_model = D('Message');
    $ols = $ids ? $mod->where(['uid' => ['in',array_keys($ids)]])->klist('uid') : [];
    foreach($ids ?: [] as $uid => $tim)
    {
      $uid = (int)$uid;
      $mod->set_user($uid);
      $tim = is_numeric($tim) ? (int)$tim : strtotime($tim);
      if($uid < 1) continue;
      $dat =
      [
        'status'     => 3,
        'uid'        => $uid,
        'aid'        => (int)$_SESSION[C('USER_AUTH_KEY')],
        'start_time' => $tim,
        'reply_time' => time(),
        'over_time'  => time(),
        'remark'     => $rmk ?: '批量已完成',
      ];
      $ret = false;
      if(!isset($ols[$uid]))                                   $ret = $mod->add_row($dat);
      elseif($ols[$uid]['status'] == '3')                      $ret = $mod->over($dat);
      elseif(!$rds->zScore($this->redis_feedback_assign,$uid)) $ret = $mod->over($dat);
      if($ret)
      {
        $this->clear_unreads($uid);
        $log_model->log('feedback',['结束会话','备注' => $rmk],$uid);
        if($msg) $msg_model->add_msg_feedback($uid,$msg);
      }
    }
    //die(json_encode(compact('ids','ols','dat','ret')));
    $this->success('操作成功');
  }

  public function get_users()
  {
    $day = (int)$_REQUEST['day'] ?: $this->chat_history_days;
    $map = ['recver' => $this->account_feedback];
    $dat = [];
    $mod = D('ChatLogBase');
    $uls = $mod->get_chat_log_union($map,$day)
      ->field(['sender','count(smsid)' => 'count','max(time)' => 'time_last','min(time)' => 'time_first'])
      ->group('sender')
      ->order('time_last desc')
      ->limit(1000)
      ->klist('sender') ?: [];
    $dat['user_count'] = count($uls);
    $uls = $this->assign_users($uls);//分配
    $usr = D('UserBase');
    $rds = $this->get_redis();
    $sls = [];
    foreach($uls as $k => $v)
    {
      $uid = (int)$v['sender'];
      if(!$uid) continue;
      $tim = is_numeric($v['time_last']) ? (int)$v['time_last'] : strtotime($v['time_last']);
      $ctm = (int)$rds->hGet($this->redis_feedback_closed,$uid);
      if($tim >= $ctm)
      {
        $v['user'] = $usr->get_user_cache($uid);
        $dat['list'][] = $v;
        $sls[$uid] =
        [
          'uid'        => $uid,
          'start_time' => $tim,
        ];
        $rds->hDel($this->redis_feedback_closed,$uid);
      }
      else $rds->zRem($this->redis_feedback_assign,$uid);
    }
    if($sls) D('FeedbackNote')->start_all($sls);
    $this->data = $dat;
    $this->ret['data'] = $dat;
    //die(json_encode(compact('uls','dat')));
    $this->ajaxReturn($this->ret);
  }

  public function get_chats()
  {
    $day = (int)$_REQUEST['day'] ?: $this->chat_history_days;
    $uid = (int)$_REQUEST['uid'];
    $dat = $this->get_feedback_list($day,$uid) ?: [];
    $dat['list'] = $this->get_feedback_admins($uid,$dat['list']);
    $this->data = $dat;
    $this->ret['data'] = $dat;
    $this->ajaxReturn($this->ret);
  }

  // 获取实时消息
  public function get_chats_rtime()
  {
    $mod = D('ChatLogBase');
    $rds = $mod->get_redis();
    $arr = $rds->lRange($this->redis_feedback_list,-10,-1) ?: [];
    $dat = [];
    $dat['list'] = [];
    foreach($arr as $v)
    {
      $str = $v;
      is_string($v) && $v = json_decode($v,true);
      $tim = is_numeric($v['Time']) ? $v['Time'] : strtotime($v['Time']);
      $row = [
        'id'        => $v['Index'],
        'sender'    => $v['FromID'],
        'recver'    => $v['ToID'],
        'smsid'     => $v['MsgKey'],
        'text'      => $v['Text'],
        'texttype'  => $v['TextType'],
        'chattype'  => $v['ChatType'],
        'time'      => date('Y-m-d H:i:s',$tim),
        'time_unix' => $tim,
        'list_key'  => $str,
      ];
      $dat['list'][] = $row;
    }
    $dat['list'] = $this->assign_users($dat['list']);//分配
    $dat['count_all'] = $rds->lSize($this->redis_feedback_list);
    $sls = [];
    foreach($dat['list'] ?: [] as $row)
    {
      $rds->lRem($this->redis_feedback_list,$row['list_key']);
      $sls[$row['sender']] =
      [
        'uid'        => $row['sender'],
        'start_time' => (int)$row['time_unix'] ?: time(),
      ];
    }
    if($sls) D('FeedbackNote')->start_all($sls);
    is_array($dat['list']) && $dat['list'] = $mod->format_text_all($dat['list']);
    $this->data = $dat;
    $this->ret['data'] = $dat;
    //die(json_encode(compact('arr','dat')));
    $this->ajaxReturn($this->ret);
  }

  // 意见反馈 回复
  public function send()
  {
    $uid = (int)$_REQUEST['uid'];
    $msg = $_REQUEST['msg'];
    if($uid < 1)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = 'UID错误';
    }
    elseif(!trim($msg))
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '消息内容不能为空';
    }
    elseif(!D('Message')->add_msg_feedback($uid,$msg))
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '发送失败';
    }
    else
    {
      $this->ret['msg'] = '发送成功';
      D('FeedbackNote')->set_user($uid)->reply();
    }
    $this->ajaxReturn($this->ret);
  }

  // 发送图片消息
  public function send_image()
  {
    $uid = (int)$_REQUEST['uid'];
    if($uid < 1)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = 'UID错误';
    }
    elseif(!$img = $_FILES['image'] ?: [])
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '请选择图片';
    }
    else
    {
      $fnm = $this->make_file_name($img['name']);
      //$ret = $this->aliyup('im-image',$fnm,$img['tmp_name']);
      $ret = D('Resource')->oss_upload('im-image',$img['tmp_name'],$fnm);
      if(!$ret)
      {
        $this->ret['ret'] = 1;
        $this->ret['msg'] = '上传失败';
      }
      else
      {
        $msg = D('Message');
        $msg->add_upload_histoty($fnm);
        $src = $msg->image_url_root.$fnm;
        $thu = $src.'@640w';
        if(!$msg->set_feedback()->add_image($uid,$src,$thu))
        {
          $this->ret['ret'] = 1;
          $this->ret['msg'] = '发送失败';
        }
        else
        {
          $this->ret['msg']  = '发送成功';
          $this->ret['data'] =
          [
            'smsid'     => 0,
            'sender'    => $this->account_feedback,
            'recver'    => $uid,
            'texttype'  => 2,
            'text'      => '',
            'text_json' =>
            [
              'originPhotoUrl'    => $src,
              'thumbnailPhotoUrl' => $thu,
            ],
            'time'      => date('Y-m-d H:i:s'),
            'is_self'   => true,
          ];
          D('FeedbackNote')->set_user($uid)->reply();
        }
      }
    }
    $this->ajaxReturn($this->ret);
  }

  public function close_chat()
  {
    $uid = (int)$_REQUEST['uid'];
    $rmk = trim(I('request.remark'));
    $pause = (int)$_REQUEST['pause'] == 1;
    if($uid < 1)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = 'UID错误';
    }
    elseif(!$rmk)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '备注不能为空';
    }
    else
    {
      $this->ret['msg'] = '操作成功';
      $fbn = D('FeedbackNote')->set_user($uid);
      $dat = ['remark' => $rmk];
      $pause ? $fbn->pause($dat) : $fbn->over($dat);
      $this->get_redis()->hSet($this->redis_feedback_closed,$uid,time());
      $this->get_redis()->zRem($this->redis_feedback_assign,$uid);
      $this->clear_unreads($uid);
      D('OperLog')->log('feedback',[$pause ? '暂停会话' : '结束会话','备注' => $rmk],$uid);
    }
    $this->ajaxReturn($this->ret);
  }

  // 停止分配到当前客服
  public function stop_assign()
  {
    $stop = (int)$_REQUEST['stop'];
    session('chat_assign_stop',$stop);
    $this->ajaxReturn($this->ret);
  }

  // 转移分配客服
  public function change_assign()
  {
    $uid = (int)$_REQUEST['uid'];
    $aid = (int)$_REQUEST['aid'];
    if($uid && $aid)
    {
      $ret = $this->get_redis()->zAdd($this->redis_feedback_assign,$aid,$uid);
      if($ret === false)
      {
        $this->ret['ret'] = 1;
        $this->ret['msg'] = '操作失败';
      }
      else
      {
        $oid = $_SESSION[C('USER_AUTH_KEY')];
        $jss = json_encode(
        [
          'ChatType' => 1,
          'FromID'   => $uid,
          'Index'    => uniqid('system-'),
          'MsgKey'   => 0,
          'Text'     => '客服【'.$oid.'】转移会话给您！',
          'TextType' => 1,
          'Time'     => time(),
          'ToID'     => $this->account_feedback,
        ]);
        D('ChatLogBase')->get_redis()->rPush($this->redis_feedback_list,$jss);
        D('OperLog')->log('feedback',['转移会话','客服' => $oid.' -> '.$aid],$uid);
      }
    }
    $this->ajaxReturn($this->ret);
  }

  public function get_users_typeahead()
  {
    $mod = D('UserBase');
    $map = $mod->get_filters(true);
    $dat['list'] = $mod->field('uid,nickname,phone,sex')->limit(10)->lists($map);
    $this->data = $dat;
    $this->ret['data'] = $dat;
    $this->ajaxReturn($this->ret);
  }

  public function reset_closed()
  {
    $this->get_redis()->del($this->redis_feedback_closed);
    $arr = D('FeedbackNote')
      ->field('uid,over_time')->where(['status' => 3,'over_time' => ['egt',60 * 60 * 24 * 60]])
      ->order('over_time desc')->limit(10000)
      ->select() ?: [];
    $dat = [];
    foreach($arr as $v)
    {
      $dat[$v['uid']] = $v['over_time'];
    }
    $this->get_redis()->hMSet($this->redis_feedback_closed,$dat);
    $this->ret['msg'] = '操作成功';
    $this->ajaxReturn($this->ret);
  }

  public function clear_cloud_test_chat()
  {
    $arr = [];
    for($i = 12200049; $i <= 12200659; $i++)
    {
      $arr[$i] = time() + 60 * 60 * 24 * 10;
    }
    $this->get_redis()->hMSet($this->redis_feedback_closed,$arr);
  }

  // 获取聊天记录 array
  protected function get_feedback_list($day = 0,$uid = 0)
  {
    $uid = (int)$uid;
    $map = [];
    if($uid)
    {
      $map = '(sender = '.$uid.' and recver = '.$this->account_feedback.') or (sender = '.$this->account_feedback.' and recver = '.$uid.')';
    }
    else $map = [
      '_complex' => [
        '_logic' => 'or',
        'sender' => $this->account_feedback,
        'recver' => $this->account_feedback,
      ],
    ];
    $dat = [];
    $mod = D('ChatLogBase');
    $dat['list'] = $mod->get_chat_log_union($map,$day,0,true/*包含客服消息*/)->order('time')->limit(500)->select() ?: [];
    //$dat['debug'] = $mod->get_chat_log_union($map,$day,0,true/*包含客服消息*/)->order('time')->limit(500)->select(false);
    is_array($dat['list']) && $dat['list'] = $mod->format_text_all($dat['list']);
    $this->data = $dat;
    $this->ret['data'] = $dat;
    return $dat;
  }

  // 获取客服操作历史
  protected function get_feedback_admins($uid = 0,$arr = [])
  {
    $lst = D('OperLog')->limit(5)->lists(
    [
      'uid'         => $uid,
      'type'        => 'feedback',
      'create_time' => ['egt',strtotime('-7 days')],
    ],'create_time desc,id desc');
    $lst = array_reverse($lst);
    $als = D('Admin')->get_by_list($lst,'aid,nickname');
    $tpl =
    [
      'id'        => 0,
      'sender'    => 0,
      'recver'    => 0,
      'smsid'     => 0,
      'text'      => '',
      'texttype'  => 0,
      'chattype'  => 0,
      'text_json' => [],
      'text_html' => '',
      'time'      => '',
      'time_unix' => 0,
    ];
    $dat = [];
    foreach($arr ?: [] as $v)
    {
      $v['time_unix'] = is_numeric($v['time']) ? $v['time'] : strtotime($v['time']);
      if($lst[0] && $v['time_unix'] > $lst[0]['create_time'])
      {
        $log = array_shift($lst) ?: [];
        $dat[] = array_merge($tpl,
        [
          'texttype'  => -1,
          'text_json' =>
          [
            'admin_id'   => $log['aid'],
            'admin_name' => $als[$log['aid']]['nickname'],
            'remark'     => $log['remark'],
          ],
          'time'      => date('Y-m-d H:i:s',$log['create_time']),
          'time_unix' => $log['create_time'],
        ]);
      }
      $dat[] = $v;
    }
    foreach($lst as $log)
    {
      $dat[] = array_merge($tpl,
      [
        'texttype'  => -1,
        'text_json' =>
        [
          'admin_id'   => $log['aid'],
          'admin_name' => $als[$log['aid']]['nickname'],
          'remark'     => $log['remark'],
        ],
        'time'      => date('Y-m-d H:i:s',$log['create_time']),
        'time_unix' => $log['create_time'],
      ]);
    }
    //die(json_encode(compact('dat','arr','lst','als')));
    return $dat;
  }

  // 分配会话给不同的管理员
  protected function assign_users($arr = [])
  {
    $aid = (int)$_SESSION[C('USER_AUTH_KEY')];
    $key = $this->redis_feedback_assign;
    $rds = $this->get_redis();
    $cnt = (int)$rds->zCount($key,$aid,$aid);
    $dat = [];
    foreach($arr ?: [] as $k => $v)
    {
      $uid = (int)$v['sender'] ?: (int)$v['uid'];
      $oid = $rds->zScore($key,$uid);
      if($oid && $oid != $aid) continue;
      elseif(!$oid)
      {
        if(session('chat_assign_stop'))    continue;//停止分配
        if($cnt >= $this->chat_assign_max) continue;
        $rds->zAdd($key,$aid,$uid);
        $cnt++;
      }
      $dat[$k] = $v;
    }
    return $dat;
  }

  protected function clear_unreads($uid = 0)
  {
    $cnt = 0;
    $rds = D('ChatLogBase')->get_redis();
    $arr = $rds->lRange($this->redis_feedback_list,0,-1) ?: [];
    if($arr) foreach($arr ?: [] as $v)
    {
      $key = $v;
      is_string($v) && $v = json_decode($v,true);
      if($v['FromID'] == $uid)
      {
        $rds->lRem($this->redis_feedback_list,$key);
        $cnt++;
      }
    }
    return $cnt;
  }

  protected function make_file_name($filename = '')
  {
    return D('Message')->make_file_name($filename);
  }

  public function get_redis()
  {
    return D('PhpServerRedis')->new_redis();
  }

}