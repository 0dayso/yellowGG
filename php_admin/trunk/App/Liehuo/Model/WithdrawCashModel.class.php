<?php
namespace Liehuo\Model;

class WithdrawCashModel extends CjDatadwModel
{

  protected $redis_config = 'redis_user';

  const STATE_CREATED = 0;//已提交申请，未处理
  const STATE_PROCESS = 1;//处理中
  const STATE_SUCCESS = 2;//已成功
  const STATE_FAILED  = 3;//已失败

  public $states = [
    self::STATE_CREATED => '未处理',
    self::STATE_PROCESS => '处理中',
    self::STATE_SUCCESS => '已成功',
    self::STATE_FAILED  => '已失败',
  ];


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid']) $map[$alias.'uid']      = $uid;
    if($arr['state']    != '')  $map[$alias.'state']    = (int)$arr['state'];
    if($arr['pay_type'] != '')  $map[$alias.'pay_type'] = (int)$arr['pay_type'];
    if($arr['pay_account'] != '') $map[$alias.'pay_account'] = trim($arr['pay_account']);
    $time_type = $arr['time_type'] == 'finish' ? 'finish' : 'create';
    if($arr['stime'] && $stime = strtotime($_REQUEST['stime'] = $_GET['stime'] = urldecode(urldecode($arr['stime']))))
    {
      is_array($map[$alias.$time_type.'_time']) || $map[$alias.$time_type.'_time'] = [];
      $map[$alias.$time_type.'_time'][] = ['egt',strtotime(date('Y-m-d H:i:s',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($_REQUEST['etime'] = $_GET['etime'] = urldecode(urldecode($arr['etime']))))
    {
      is_array($map[$alias.$time_type.'_time']) || $map[$alias.$time_type.'_time'] = [];
      $map[$alias.$time_type.'_time'][] = ['elt',strtotime(date('Y-m-d H:i:59',$etime))];
    }
    if($arr['sex'] != '')//性别筛选
    {
      $sex = (int)$arr['sex'] == 0 ? 0 : 1;
      $sql = D('UserBase')->table('__USER_BASE__')->field('uid')
        ->where(['uid' => ['exp',' = '.$alias.'uid'],'sex' => $sex])
        ->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($arr['kwd'] == 'idcard') $map[$alias.'pay_idcard'] = ['neq',''];
    elseif($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $map['_complex'] = [
          '_logic' => 'or',
          $alias.'pay_account' => ['exp','regexp \''.$kwd.'\''],
          $alias.'pay_name'    => ['like','%'.$kwd.'%'],
          $alias.'remark'      => ['like','%'.$kwd.'%'],
          $alias.'reason'      => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd)) $map['_complex'][$alias.'uid'] = ['exp','regexp \''.$kwd.'\''];
    }
    return $map;
  }

  // 获取重复的提现账号
  public function get_cash_repeat()
  {
    # code...
  }


  // 生成支付宝批量付款表单
  public function get_alipay_form($par = [])
  {
    $dir = APP_PATH.'Library/pay/alipay/';
    include_once $dir.'alipay.config.php';
    include_once $dir.'lib/alipay_submit.class.php';
    $alipay_config['private_key_path']    = $dir.'key/rsa_private_key.pem';
    $alipay_config['ali_public_key_path'] = $dir.'key/alipay_public_key.pem';
    $alipay_config['cacert']              = $dir.'cacert.pem';
    $par = array_merge([
      'service'        => 'batch_trans_notify',
      'partner'        => trim($alipay_config['partner']),
      'notify_url'     => C('api_root_app').'pay/cash_notify_alipay',
      'email'          => trim($alipay_config['seller_email']),
      'account_name'   => trim($alipay_config['seller_name']),
      'pay_date'       => date('Ymd'),
      'batch_no'       => '',
      'batch_fee'      => 0,
      'batch_num'      => 0,
      'detail_data'    => '',
      '_input_charset' => trim(strtolower($alipay_config['input_charset'])),
    ],$par ?: []);
    $par['batch_no'] || $par['batch_no'] = $this->get_serial_no();
    $alipaySubmit = new \AlipaySubmit($alipay_config);
    //die(json_encode(['data' => $par,$alipay_config,file_get_contents($alipay_config['private_key_path'])]));
    return $alipaySubmit->buildRequestForm($par,'POST','确认');
  }

  // 生成支付宝批量付款数据
  public function fomart_alipay_data($dat = [])
  {
    $ret = [
      'batch_no'  => $this->get_serial_no(),
      'batch_fee' => 0,
    ];
    $arr = [];
    foreach($dat ?: [] as $v)
    {
      $ret['batch_fee'] += $v['fee_cash'];
      $row = [$v['id'],$v['pay_account'],$v['pay_name'],$v['fee_cash'],'烈火余额提现'];
      $arr[] = implode('^',$row);
    }
    $ret['batch_num'] = count($arr);
    $ret['detail_data'] = implode('|',$arr);
    return $ret;
  }

  // 生成支付宝批量付款数据 csv
  public function get_alipay_csv($dat = [])
  {
    $dir = APP_PATH.'Library/pay/alipay/';
    include_once $dir.'alipay.config.php';
    $bno = $this->get_serial_no();
    $fei = 0;
    $arr = [];
    foreach($dat ?: [] as $v)
    {
      $fei += $v['fee_cash'];
      $row = [$v['id'],$v['pay_account'],$v['pay_name'],$v['fee_cash'],'烈火余额提现'];
      $arr[] = implode(',',$row);
    }
    $num = count($arr);
    array_unshift(
      $arr,
      '批次号,付款日期,付款人email,账户名称,总金额（元）,总笔数',
      implode(',',[
        $bno,
        date('Ymd'),
        trim($alipay_config['seller_email']),
        trim($alipay_config['seller_name']),
        $fei,
        $num,
      ]),
      '商户流水号,收款人email,收款人姓名,付款金额（元）,付款理由'
    );
    //die(json_encode(['data' => $arr]));
    return ['batch_no' => $bno,'data' => implode("\n",$arr)];
  }


  public function getAllQueue()
  {
    $lst = [];
    $arr = $this->get_redis()->lRange('php_cash_queue',0,-1) ?: [];
    foreach($arr as $v)
    {
      is_string($v) && ($v = json_decode($v,true) ?: []);
      $lst[$v['id']] = $v;
    }
    return $lst;
  }

  /*
   * 微信提现 加入队列
   * */
  public function cashByWeixin($dat = [],$openid = '')
  {
    $ret = false;
    is_numeric($dat) && $dat = $this->find($dat);
    if($dat && $dat['id'])
    {
      $ret = $this->get_redis()->lPush('php_cash_queue',json_encode([
        'id'  => (int)$dat['id'],
        'uid' => (int)$dat['uid'],
        'openid' => $openid ?: $dat['pay_account'] ?: '',
      ]));
    }
    return $ret;
  }

  /*
   * 生成流水号
   * 18位数字
   * */
  public function get_serial_no()
  {
    return date('YmdHis').rand(1000,9999);
  }

}