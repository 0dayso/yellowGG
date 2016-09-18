<?php
namespace Yanzhi\Model;

class MessageModel extends CjImBaseModel
{

  protected $autoCheckFields = false;
  protected $redis_config    = 'redis_im_sysmsg';

  public $sender;
  public $recver;
  public $message = array();

  public function __construct()
  {
    parent::__construct();
    $this->msg_data_type    = C('REDIS_DATA_JSON_TYPE') ?: 1;
    $this->account_system   = C('SYSTEM_ACCOUNT') ?: 10000;//系统账号
    $this->account_feedback = 10001;//意见反馈账号
    $this->sender           = $this->account_system;

    $this->redis_admin_list = C('REDIS_ADMIN_LIST_KEY');//消息队列
  }


  // 打分系统通知
  public function add_msg_scoring($uid = 0,$fed = array())
  {
    is_string($fed) || $fed = array_to_json($fed);
    $msg = array(
      'type' => 202,
      'text' => $fed,
    );
    return $this->add_msg_system($uid,$msg);
  }

  // 自定义发送人
  public function add_msg_sender($sender = 0,$uid = 0,$msg = array())
  {
    is_array($msg) || $msg = array('text' => $msg);
    $this->sender = $sender;
    return $this->add_msg($uid,$msg);
  }

  // 系统通知
  public function add_msg_system($uid = 0,$msg = array())
  {
    is_array($msg) || $msg = array('text' => $msg);
    $this->sender = $this->account_system;
    return $this->add_msg($uid,$msg);
  }

  // 意见反馈
  public function add_msg_feedback($uid = 0,$msg = array())
  {
    is_array($msg) || $msg = array('text' => $msg);
    $this->msg_data_type = 2;
    $this->sender = $this->account_feedback;
    return $this->add_msg($uid,$msg);
  }

  // 发送消息
  public function add_msg($uid = 0,$msg = array())
  {
    is_array($msg) && $msg = array_merge(array(
      'type'   => 7,
      'status' => 1,
      'text'   => '',
    ),$msg ?: array());
    is_string($msg) || $msg = array_to_json($msg);
    $this->recver = (int)$uid;
    $dat = array('message' => $msg);
    return $this->add_queue($dat);
  }

  // 添加消息到队列
  /*
    [
      uid     => 10000,
      receive => 1000001,
      type    => 1:系统消息|2:意见反馈,
      time    => time(),
      message =>
      {
        type  : 7,
        satus : 1,
        text  : ''
      }
    ]
   */
  public function add_queue($dat = array(),$lst = '')
  {
    is_array($dat) && $dat = array_merge(array(
      'uid'     => $this->sender,
      'receive' => $this->recver,
      'type'    => $this->msg_data_type,
      'message' => '',
      'time'    => time(),
    ),$dat ?: array());
    is_string($dat) || $dat = array_to_json($dat);
    $lst || $lst = $this->redis_admin_list;
    return $this->get_redis()->rPush($lst,$dat);
  }


  public function get_redis()
  {
    $this->redis || $this->new_redis();
    return $this->redis;
  }

  public function new_redis($cfg = '')
  {
    $cfg || $cfg = $this->redis_config;
    $this->redis = D('PhpServerRedis')->new_redis($cfg);
    return $this->redis;
  }

}