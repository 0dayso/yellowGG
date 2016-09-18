<?php
namespace Liehuo\Model;

class PreventionModel extends PublicModel
{

  protected $autoCheckFields   = false;
  //protected $connection        = 'conn_base';
  protected $redis_config      = 'redis_recommend';
  protected $rds_prev_list_key = 'go_zset_prevention';

  public $path_root_logs = '/opt/wwwroot/golang/src/prevent.rdrs/logs/';

  public $analy_types = [
    'time_sms_cmcc'    => '移动短信',
    'time_sms_ctcc'    => '电信短信',
    'time_sms_cucc'    => '联通短信',
    'time_reg_android' => '安卓注册',
    'time_reg_ios'     => 'IOS注册',
    'time_recm_empty'  => '空推荐',
    'time_match'       => '最后匹配',
    'time_im_notify'   => '离线消息',
  ];

  public $analy_logs = [];

  public $analy_cols = [
    'time_sms_cmcc'   => [1 => '时间',2 => '类型',3 => '手机号',4 => '验证码'],
    'time_sms_ctcc'   => [1 => '时间',2 => '类型',3 => '手机号',4 => '验证码'],
    'time_sms_cucc'   => [1 => '时间',2 => '类型',3 => '手机号',4 => '验证码'],
    'time_recm_empty' => [1 => '时间',2 => '类型',3 => '用户ID',4 => '推荐数'],
  ];

  public function __construct()
  {
    parent::__construct();
    $this->analy_logs['time_sms_cmcc'] = $this->path_root_logs.'cmcc/'.date('Ymd').'.log';
    $this->analy_logs['time_sms_ctcc'] = $this->path_root_logs.'ctcc/'.date('Ymd').'.log';
    $this->analy_logs['time_sms_cucc'] = $this->path_root_logs.'cucc/'.date('Ymd').'.log';
    $this->analy_logs['time_recm_empty'] = $this->path_root_logs.'recm/'.date('Ymd').'.log';
  }


  public function get_prev_list()
  {
    return $this->get_redis()->zRange($this->rds_prev_list_key,0,-1,true);
  }

  public function get_prev_analy()
  {
    $arr = $this->get_prev_list() ?: [];
    $dat = [
      'time_sms_cmcc'    => $this->analy_sms($arr,'cmcc'),
      'time_sms_ctcc'    => $this->analy_sms($arr,'ctcc'),
      'time_sms_cucc'    => $this->analy_sms($arr,'cucc'),
      'time_reg_android' => $this->analy_reg($arr,'android'),
      'time_reg_ios'     => $this->analy_reg($arr,'ios'),
      'time_recm_empty'  => $this->analy_timeout($arr,'time_list_not_empty'),
      'time_match'       => $this->analy_timeout($arr,'time_app_match_success'),
      //'time_im_notify'   => $this->analy_timeout($arr,'time_app_im_notification'),
    ];
    return $dat;
  }

  public function analy_sms($arr = [],$typ = 'cmcc')
  {
    if(!isset($arr['time_'.$typ.'_send_code'])) return 0;
    if(!isset($arr['time_'.$typ.'_use_code']))  return 0;
    $stm = (int)$arr['time_'.$typ.'_send_code'];
    $utm = (int)$arr['time_'.$typ.'_use_code'];
    return $stm - $utm;
  }

  public function analy_reg($arr = [],$typ = 'android')
  {
    if(!isset($arr['time_app_'.$typ.'_reg'])) return 0;
    $tim = (int)$arr['time_app_'.$typ.'_reg'];
    return time() - $tim;
  }

  public function analy_timeout($arr = [],$key)
  {
    if(!isset($arr[$key])) return 0;
    return time() - (int)$arr[$key];
  }

  public function get_logs_url()
  {
    $dat = [];
    foreach($this->analy_logs as $k => $v)
    {
      if($v) $dat[$k] = U('logs',['type' => $k]);
    }
    return $dat;
  }

  public function get_logs_list($typ = '')
  {
    $dat = [];
    $pat = $this->analy_logs[$typ];
    $str = file_get_contents($pat);
    foreach(explode("\n",$str) ?: [] as $row)
    {
      $v = explode('|',$row) ?: [];
      $arr = [];
      foreach($this->analy_cols[$typ] ?: [] as $idx => $val) $arr[$val] = $v[$idx];
      $dat[] = $arr;
    }
    return $dat;
  }

  public function test_data()
  {
    $keys = [
      'time_ctcc_send_code',
      'time_ctcc_use_code',
      'time_cmcc_send_code',
      'time_cmcc_use_code',
      'time_cucc_send_code',
      'time_cucc_use_code',
      'time_app_ios_reg',
      'time_app_android_reg',
      'time_list_not_empty',
      'time_app_match_success',
      'time_app_im_notification',
    ];
    $key = $keys[array_rand($keys)];
    $tim = time() - rand(100,60 * 60 * 3);
    $this->get_redis()->zAdd($this->rds_prev_list_key,$tim,$key);
  }


  /*
   * 获取短信平台剩余短信数
   * @param  get:
   *  POST /MWGate/wmgw.asmx/MongateQueryBalance HTTP/1.1
   *  userId=string&password=string
   * @return    HTTP/1.1 200 OK
   *  Content-Type: text/xml; charset=utf-8
   *  <?xml version="1.0" encoding="utf-8"?>
   *  <int xmlns="http://tempuri.org/">int</int>
   * */
  public function get_mongate_balance()
  {
    $val = 0;
    $url = C('WEB_SERVICE_COST_VIEW_HOST').C('WEB_SERVICE_COST_VIEW_URL');
    $dat = 'userId='.C('WEB_SERVICE_USER_ID').'&password='.C('WEB_SERVICE_PASSWORD');
    $xml = A('Public')->http($url,$dat);
    if(preg_match('/<int\b[^>]*>\s*(\d*)\s*<\/int>/i',$xml,$arr))
    {
      $val = $arr[1];
    }
    return $val;
  }


  // 获取数据库连接数
  public function get_mysql_processlist($cfg = '')
  {
    $cfg || $cfg = 'conn_readonly';
    //$read && $this->db(0,'conn_readonly');
    ob_start();
    print_r($this);
    $log = ob_get_contents();
    ob_end_clean();
    //trace($log);
    $sql = 'show processlist';
    trace($cfg,'conn');
    return M('','',$cfg)->query($sql);//
  }

  public function kill_mysql_processlist($id = 0,$cfg = '')
  {
    $cfg || $cfg = 'conn_readonly';
    $sql = 'kill '.(int)$id;
    return M('','',$cfg)->execute($sql);
  }


  // 获取Redis内存使用
  public function get_redis_memory($cfg = '')
  {
    $arr = $this->get_redis($cfg)->info('Memory');
    return $arr;//['used_memory'];
  }

}