<?php
namespace Yanzhi\Controller;

class FeedbackController extends PublicController
{

  public $ret = array('ret' => 0,'msg' => '','data' => array());
  public $account_feedback  = 10001;//意见反馈账号
  public $chat_history_days = 7;

  public $redis_feedback_list   = 'customer_service_10001';//实时意见反馈队列
  public $redis_feedback_closed = 'php_feedback_closed';//结束会话列表

  public function index()
  {
    $this->display();
  }

  public function get_users()
  {
    $day = (int)$_REQUEST['day'] ?: $this->chat_history_days;
    $map = array('recver' => $this->account_feedback);
    $dat = array();
    $mod = D('ChatLogBase');
    $uls = $mod->get_chat_log_union($map,$day)
      ->field(array('sender','count(smsid)' => 'count','max(time)' => 'time_last'))->group('sender')
      ->limit(100)->klist('sender') ?: array();
    $usr = D('UserBase');
    $rds = D('PhpServerRedis')->new_redis();
    foreach($uls as $k => $v)
    {
      $uid = (int)$v['sender'];
      if(!$uid) continue;
      $tim = is_numeric($v['time_last']) ? (int)$v['time_last'] : strtotime($v['time_last']);
      $ctm = $rds->hGet($this->redis_feedback_closed,$uid);
      if($tim >= (int)$ctm)
      {
        $v['user'] = $usr->get_user_cache($uid);
        $dat['list'][$uid] = $v;
      }
    }
    $this->data = $dat;
    $this->ret['data'] = $dat;
    //die(json_encode(compact('uls','dat')));
    $this->ajaxReturn($this->ret);
  }

  public function get_chats()
  {
    $day = (int)$_REQUEST['day'] ?: $this->chat_history_days;
    $uid = (int)$_REQUEST['uid'];
    $dat = $this->get_feedback_list($day,$uid) ?: array();
    $this->data = $dat;
    $this->ret['data'] = $dat;
    $this->ajaxReturn($this->ret);
  }

  // 获取实时消息
  public function get_chats_rtime()
  {
    $mod = D('ChatLogBase');
    $rds = $mod->get_redis();
    $arr = $dat = array();
    for($i = 0;$i < 10;$i++)
    {
      $row = $rds->lPop($this->redis_feedback_list) ?: array();
      if($row) $arr[] = $row;
      elseif($rds->lLen($this->redis_feedback_list) < 1) break;
    }
    $dat['list'] = array_map(function($v)
    {
      is_string($v) && $v = json_decode($v,true);
      return array(
        'id'       => $v['Index'],
        'sender'   => $v['FromID'],
        'recver'   => $v['ToID'],
        'smsid'    => $v['MsgKey'],
        'text'     => $v['Text'],
        'texttype' => $v['TextType'],
        'chattype' => $v['ChatType'],
        'time'     => date('Y-m-d H:i:s',is_numeric($v['Time']) ? $v['Time'] : strtotime($v['Time'])),
      );
    },$arr);
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
    }
    $this->ajaxReturn($this->ret);
  }

  public function close_chat()
  {
    $uid = (int)$_REQUEST['uid'];
    if($uid < 1)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = 'UID错误';
    }
    else
    {
      D('PhpServerRedis')->new_redis()->hSet($this->redis_feedback_closed,$uid,time());
      $this->ret['msg'] = '发送成功';
    }
    $this->ajaxReturn($this->ret);
  }

  // 获取聊天记录 array
  protected function get_feedback_list($day = 0,$uid = 0)
  {
    $uid = (int)$uid;
    $map = array();
    if($uid)
    {
      $map = '(sender = '.$uid.' and recver = '.$this->account_feedback.') or (sender = '.$this->account_feedback.' and recver = '.$uid.')';
    }
    else $map = array(
      '_complex' => array(
        '_logic' => 'or',
        'sender' => $this->account_feedback,
        'recver' => $this->account_feedback,
      ),
    );
    $dat = array();
    $mod = D('ChatLogBase');
    $dat['list'] = $mod->get_chat_log_union($map,$day,0,true/*包含客服消息*/)->order('time')->limit(500)->select() ?: array();
    //$dat['debug'] = $mod->get_chat_log_union($map,$day,0,true/*包含客服消息*/)->order('time')->limit(500)->select(false);
    is_array($dat['list']) && $dat['list'] = $mod->format_text_all($dat['list']);
    $this->data = $dat;
    $this->ret['data'] = $dat;
    return $dat;
  }

}