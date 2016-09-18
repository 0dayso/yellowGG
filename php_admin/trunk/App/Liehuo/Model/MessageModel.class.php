<?php
namespace Liehuo\Model;

class MessageModel extends CjImBaseModel
{

  protected $autoCheckFields = false;
  protected $redis_config    = 'redis_im_sysmsg';

  public $sender;
  public $recver;
  public $message = [];

  public $image_url_root = 'http://im-image.chujianapp.com/';

  const CHAT_TYPE_SYSTEM  = 1;
  const CHAT_TYPE_PRODUCT = 2;
  const CHAT_TYPE_NORMAL  = 3;
  const CHAT_TYPE_SNAP    = 6;

  public $chat_types =
  [
    self::CHAT_TYPE_SYSTEM  => '系统消息',
    self::CHAT_TYPE_PRODUCT => '产品消息',
    self::CHAT_TYPE_NORMAL  => '普通消息',
    self::CHAT_TYPE_SNAP    => '瞬间消息',
  ];

  const TEXT_TYPE_TEXT     = 1;
  const TEXT_TYPE_IMAGE    = 2;
  const TEXT_TYPE_TIETU    = 3;
  const TEXT_TYPE_VOICE    = 4;
  const TEXT_TYPE_LOCATION = 5;
  const TEXT_TYPE_COMPOUND = 100;

  public $text_types =
  [
    self::TEXT_TYPE_TEXT     => '文本',
    self::TEXT_TYPE_IMAGE    => '图片',
    self::TEXT_TYPE_TIETU    => '贴图',
    self::TEXT_TYPE_VOICE    => '语音',
    self::TEXT_TYPE_LOCATION => '位置',
    self::TEXT_TYPE_COMPOUND => '复合',
  ];

  const MAJOR_TYPE_NORMAL = 1;
  const MAJOR_TYPE_SYSTEM = 2;

  public $major_types =
  [
    self::MAJOR_TYPE_NORMAL => '普通消息',
    self::MAJOR_TYPE_SYSTEM => '系统消息',
  ];

  const MINOR_TYPE_TEXT     = 1;
  const MINOR_TYPE_EMOJI    = 2;
  const MINOR_TYPE_GIFT     = 3;
  const MINOR_TYPE_BARRAGE  = 4;
  const MINOR_TYPE_HORN     = 5;
  const MINOR_TYPE_COMPOUND = 100;

  public $minor_types =
  [
    self::MINOR_TYPE_TEXT     => '文本',
    self::MINOR_TYPE_EMOJI    => '表情',
    self::MINOR_TYPE_GIFT     => '礼物',
    self::MINOR_TYPE_BARRAGE  => '弹幕',
    self::MINOR_TYPE_HORN     => '喇叭',
    self::MINOR_TYPE_COMPOUND => '复合',
  ];


  public function __construct($recver = 0,$msg_type = 0,$opt = [])
  {
    parent::__construct();
    if(is_numeric($opt)) $opt = ['sender' => $opt];
    if(!is_array($opt))  $opt = [];
    $this->msg_data_type    = isset($opt['chat_type']) ? (int)$opt['chat_type'] : self::CHAT_TYPE_PRODUCT;//C('REDIS_DATA_JSON_TYPE') ?: 1;
    $this->txt_type         = isset($opt['text_type']) ? (int)$opt['text_type'] : self::TEXT_TYPE_TEXT;
    $this->msg_type         = (int)$msg_type;
    $this->account_system   = C('SYSTEM_ACCOUNT') ?: 10000;//系统账号
    $this->account_feedback = 10000;//意见反馈账号
    $this->sender           = (int)$opt['sender'] ?: $this->account_system;
    $this->recver           = (int)$recver;
    $this->offline          = isset($opt['offline']) && !$opt['offline'] ? 0 : 1;//发送离线消息
    $this->notification     = isset($opt['notification']) && !$opt['notification'] ? 0 : 1;//发送离线通知
    $this->options          = $opt;

    $this->redis_admin_list = C('REDIS_ADMIN_LIST_KEY');//消息队列

    // 模板自动替换 图片根路径
    $cfg = C('TMPL_PARSE_STRING') ?: [];
    if(is_array($cfg) && !isset($cfg['__IM_IMAGE_URL_ROOT__']))
    {
      $cfg['__IM_IMAGE_URL_ROOT__'] = $this->image_url_root;
      C('TMPL_PARSE_STRING',$cfg);
    }
  }


  // 打分系统通知
  /*
    {
      type  : 7,
      satus : 1,
      hint  : '离线通知',
      text  :
      {
        comment              : '文本消息',
        sessionThumbImageUrl : '图片完整路径'
      }
    }
  */
  public function add_msg_scoring($uid = 0,$txt = [],$res = '')
  {
    is_string($txt) && $txt = ['comment' => $txt];
    $txt = array_merge(
    [
      'comment'              => '',
      'sessionThumbImageUrl' => $res,
      'intent_name'          => '10002',
    ],$txt);
    $msg =
    [
      'type' => 7,
      'hint' => $txt['comment'],
      'text' => array_to_json($txt),
    ];
    //$this->msg_data_type = 1;
    $this->txt_type      = 100;
    //rlog([$uid,$txt],'admin_msg_scoring');
    return $this->add_msg_system($uid,$msg);
  }

  // 自定义发送人
  public function add_msg_sender($sender = 0,$uid = 0,$msg = [])
  {
    is_array($msg) || $msg = array('text' => $msg);
    $this->sender = $sender;
    return $this->add_msg($uid,$msg);
  }

  // 系统通知
  public function add_msg_system($uid = 0,$msg = [])
  {
    is_array($msg) || $msg = array('text' => $msg);
    $this->sender = $this->account_system;
    return $this->add_msg($uid,$msg);
  }

  // 意见反馈
  public function add_msg_feedback($uid = 0,$msg = [])
  {
    is_array($msg) || $msg = array('text' => $msg);
    $this->set_feedback();
    return $this->add_msg($uid,$msg);
  }

  // 发送消息
  public function add_msg($uid = 0,$msg = [])
  {
    is_array($msg) && $msg = array_merge(array(
      'type'   => 7,
      'status' => 1,
      'text'   => '',
    ),$msg ?: []);
    is_string($msg) || $msg = array_to_json($msg);
    $this->recver = (int)$uid;
    $dat = ['message' => $msg];
    return $this->add_queue($dat);
  }

  // 发送图片消息
  public function add_image($uid = 0,$src = '',$thumb = '')
  {
    preg_match('/https?:/i',$src)   || $src   = $this->image_url_root.$src;
    preg_match('/https?:/i',$thumb) || $thumb = $this->image_url_root.$thumb;
    $msg = [
      'originPhotoUrl'    => $src,
      'thumbnailPhotoUrl' => $thumb ?: $src,
    ];
    is_string($msg) || $msg = array_to_json($msg);
    $this->recver = (int)$uid;
    $dat = [
      'txt_type' => 2,
      'message'  => $msg,
    ];
    $this->msg_data_type = 3;
    return $this->add_queue($dat);
  }

  // 添加消息到队列
  /*
    [
      uid      => 10000,
      receive  => 1000001,
      type     => 1:系统消息|2:意见反馈,
      txt_type => 0:未知|1:文本|2:图片|3:贴图|4:语音|5:地图|100:ETT_COMPOUND
      time     => NOW_TIME,
      message  =>
      {
        type  : 7,
        satus : 1,
        text  : ''
      },
      send_to_offline_user => 1:发送离线消息
    ]
   */
  public function add_queue($dat = [],$lst = '')
  {
    $opt = $this->options ?: [];
    if(is_array($dat))
    {
      if(isset($opt['target'])) $dat['target'] = (int)$opt['target'];//1:所有在线 2:指定UID 3:所有
      $dat = array_merge(array(
        'uid'      => $this->sender,
        'receive'  => $this->recver,
        'type'     => $this->msg_data_type,
        'txt_type' => $this->txt_type,
        'message'  => '',
        'time'     => NOW_TIME,
        'send_to_offline_user'      => $this->offline,
        'send_offline_notification' => $this->notification,
      ),$dat ?: []);
    }
    is_string($dat) || $dat = array_to_json($dat);
    $lst || $lst = $this->redis_admin_list;
    $ret = $this->get_redis()->rPush($lst,$dat);
    $this->message = $dat;
    alog($dat,'msg');
    //rlog($dat,'admin_msg_queue');
    $ret === false || $ret = $dat;
    return $ret;
  }


  // 发送群消息
  public function send_group_chat($msg = [])
  {
    $opt = $this->options ?: [];
    $dat =
    [
      'major_type' => isset($opt['major_type']) ? (int)$opt['major_type'] : self::MAJOR_TYPE_SYSTEM,//1:常规 2:系统
      'minor_type' => isset($opt['minor_type']) ? (int)$opt['minor_type'] : self::MINOR_TYPE_TEXT,//1:文本 2:表情 3:礼物 4:弹幕 5:喇叭 100:复合
      'sender'     => ['userid' => $this->sender],
      'live_chat_room_id'  => (int)$opt['room_id'],
      'all_live_chat_room' => !!$opt['all_room'],
      'dissolve_live_chat_room' => !!$opt['dissolve_room'],//关闭聊天室
      'content'    => is_string($msg) ? $msg : json_encode($msg,JSON_UNESCAPED_UNICODE),
      'time'       => NOW_TIME,
    ];
    $rds = $this->get_redis();
    $ret = $rds->lPush('cpp_live_chat_system_msg',json_encode($dat,JSON_UNESCAPED_UNICODE));
    $ret === false || $ret = $dat;
    rlog($dat,'admin_msg_group');
    return $ret;
  }


  // 发送定时消息
  public function send_timing_chat($tim = 0,$msg = [],$exclusive = false)
  {
    return $this->add_timing_queue($tim,
    [
      'uid'      => $this->sender,
      'receive'  => $this->recver,
      'type'     => $this->msg_data_type,
      'txt_type' => $this->txt_type,
      'time'     => NOW_TIME,
      'send_to_offline_user'      => $this->offline,
      'send_offline_notification' => $this->notification,
      'message'  => is_string($msg) ? $msg : json_encode($msg,JSON_UNESCAPED_UNICODE),
    ],$exclusive);
  }

  public function add_timing_queue($tim = 0,$dat = [],$exclusive = false)
  {
    $opt = $this->options ?: [];
    $dat =
    [
      'type'      => isset($opt['timing_type']) ? (int)$opt['timing_type'] : 1,//1:常规 2:瞬间
      'sender'    => $this->sender,
      'receiver'  => $this->recver,
      'time_send' => (int)$tim,
      'exclusive' => !!$exclusive,
      'content'   => is_string($dat) ? $dat : json_encode($dat,JSON_UNESCAPED_UNICODE),
    ];
    $rds = $this->new_redis('redis_im_timing');
    $ret = $rds->rPush('cpp_timing_msg_list',json_encode($dat,JSON_UNESCAPED_UNICODE));
    $ret === false || $ret = $dat;
    rlog($dat,'admin_msg_timing');
    return $ret;
  }


  public function set_feedback()
  {
    $this->msg_data_type = 2;
    $this->sender = $this->account_feedback;
    return $this;
  }

  public function set_offline($sto = 1)
  {
    $this->offline = $sto ? 1 : 0;
    return $this;
  }

  public function set_target($tar = 2)
  {
    $this->options['target'] = (int)$tar;
    return $this;
  }

  /*
  * 配置选项
  * 返回新的实例
  * */
  public function set_option($opt = [],$val = null)
  {
    is_string($opt) && $opt && $opt = [$opt => $val];
    $opt = array_merge($this->options ?: [],$opt ?: []);
    return self::Instance($this->uid,$this->source,$opt);
  }


  public function make_file_name($filename = '')
  {
    $ext = substr($filename,strrpos($filename,'.') + 1);
    $path = date('Ymd').'/';
    $path .= md5(uniqid(rand(),true)).'.'.$ext;
    return $path;
  }

  // 保存IM图片等上传记录，方便清理
  public function add_upload_histoty($fnm = '',$typ = 'im-image')
  {
    if(!$fnm) return false;
    $rds = $this->new_redis('redis_default');
    return $rds->zAdd('php_imupload_'.$typ,NOW_TIME,$fnm);
  }

}