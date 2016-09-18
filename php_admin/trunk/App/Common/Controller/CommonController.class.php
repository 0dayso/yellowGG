<?php
namespace Common\Controller;
use Think\Controller;

class CommonController extends Controller
{

  // 无需登录的操作
  public $guest_actions = [];
  public $guest_actions_global =
  [
    'csp_report',
  ];

  public function __construct()
  {
    $aid = (int)$_SESSION[C('USER_AUTH_KEY')];
    //跨站脚本报告
    $csp = 'script-src \'self\' *.chujianapp.com *.chujian.im cdn.bootcss.com *.qq.com *.baidu.com *.bdimg.com fullcalendar.io \'unsafe-inline\' \'unsafe-eval\'; report-uri '.U('Common/csp_report?aid='.$aid);
    @header('Content-Security-Policy-Report-Only: '.$csp);
    @header('X-Content-Security-Policy-Report-Only: '.$csp);//for ie
    parent::__construct();
    if(!$aid) $this->login_cookie_check();//cookie登陆
    $gas = array_merge($this->guest_actions_global ?: [],$this->guest_actions ?: []) ?: [];
    if(!in_array(ACTION_NAME,$gas))
    {
      $this->aid = (int)$_SESSION[C('USER_AUTH_KEY')];
      if(!$this->aid)
      {
        if(!IS_AJAX)
        {
          session('login_request',$_SERVER['REQUEST_URI']);
          cookie('login_request',$_SERVER['REQUEST_URI'],60 * 60);
        }
        $this->redirect('Index/login',array());
      }
      $this->sess_check();//异地登陆验证
      $auth = D('Auth');
      if(!$auth->check(CONTROLLER_NAME) || !$auth->check(CONTROLLER_NAME.'/'.ACTION_NAME))
      {
        $this->error('没有权限...');
      }
    }
  }

  // 权限验证
  public function auth_check($name)
  {
    $ret = D('Auth')/*->reset()*/->check($name);
    if(!$ret) $this->error('没有权限...');
    return $ret;
  }

  // 异地登陆验证
  //   $sip 白名单IP
  public function sess_check($sip = false)
  {
    $aid = $this->aid;
    if(!$aid) return false;
    $rds = D('PhpServerRedis')->new_redis();
    $key = 'php_session_check_admin';
    if($sip) $rds->hSet($key,$aid,$sip);
    $oip = $rds->hGet($key,$aid);
    $cip = trim($_SERVER['REMOTE_ADDR']);
    if($cip != $oip)
    {
      $rds->hSet($key,$aid,$cip);
      $rds->expire($key,60 * 60 * 24 * 7);
      if($oip && array_slice(explode('.',$cip),0,3) != array_slice(explode('.',$oip),0,3))
      {
        D('OperLog')->log('system',
        [
          '管理员异地登陆',
          'SessionID' => session_id(),
          'IP'        => $oip.' -> '.$cip,
        ]);
        session(null);
        session_destroy();
        cookie('admin_token','');
        $rds->zRemRangeByScore('php_admin_tokens',$aid,$aid);
        $this->error('您的账号已在其他地方登陆！',U('Index/login'));
      }
    }
  }

  // 设置白名单IP
  public static function sess_white($aid = true,$sip = true)
  {
    $aid === true && $aid = (int)$_SESSION[C('USER_AUTH_KEY')];
    $sip === true && $sip = trim($_SERVER['REMOTE_ADDR']);
    if(!$aid) return false;
    $rds = D('PhpServerRedis')->new_redis();
    $key = 'php_session_check_admin';
    return $rds->hSet($key,$aid,$sip);
  }


  // cookie登陆
  public function login_cookie()
  {
    $aid = $this->aid;
    if(!$aid) return false;
    $rds = D('PhpServerRedis')->new_redis();
    $key = 'php_admin_tokens';
    $tok = md5($aid.'admin_token'.time()).'|'.time();
    $exp = 60 * 60 * 24 * 7;
    $rds->zRemRangeByScore($key,$aid,$aid);
    $rds->zAdd($key,$aid,$tok);
    $rds->expire($key,$exp);
    cookie('admin_token',$tok,$exp);
    return $tok;
  }

  public function login_cookie_check()
  {
    $ret = false;
    $rds = D('PhpServerRedis')->new_redis();
    $key = 'php_admin_tokens';
    $tok = cookie('admin_token');
    $exp = 60 * 60 * 24 * 7;
    if($tok)
    {
      $aid = (int)$rds->zScore($key,$tok);
    }
    if($aid)
    {
      list($tmp,$tim) = explode('|',$tok);
      if(time() - (int)$tim < $exp)
      {
        $dat = \Org\Util\Rbac::authenticate(['aid' => $aid]);
        $this->init_auth($dat);
        $this->login_cookie();
        $ret = $dat;
      }
    }
    return $ret;
  }

  protected function init_auth($dat = [])
  {
    $aid = (int)$dat['aid'];
    $_SESSION[C('USER_AUTH_KEY')] = $aid;
    $_SESSION['email']            = $dat['email'];
    $_SESSION['nickname']         = $dat['nickname'];
    return $aid;
  }

  // CSP跨域脚本报告
  public function csp_report()
  {
    $ret = false;
    $jso = file_get_contents('php://input');
    $csp = json_decode($jso,true);
    if($csp && $csp['csp-report']['blocked-uri'])
    {
      $csp['admin_id'] = $this->aid ?: (int)$_SESSION[C('USER_AUTH_KEY')] ?: (int)$_REQUEST['aid'];
      $rds = D('PhpServerRedis')->new_redis();
      $key = 'php_csp_report_admin';
      $ret = $rds->zAdd($key,time(),date('Y-m-d').':'.json_encode($csp));
      if($ret !== false)
      {
        $rds->zRemRangeByScore($key,'-inf',strtotime('-60 days'));
        $rds->expire($key,60 * 60 * 24 * 60);
      }
    }
    //die(json_encode(['data' => [$csp,$this->aid,$_SESSION,$_COOKIE]]));
    die($ret !== false ? '1' : '0');
  }


  /*
   * 模板显示 兼容Ajax
   */
  protected function display($templateFile = '',$charset = '',$contentType = '',$content = '',$prefix = '')
  {
    if(trim($_REQUEST['ajax']) && $this->data)
    {
      $dat = $this->data;
      isset($this->navs) && $dat['navs'] = $this->navs;
      //$dat = array_merge((array)$dat,get_object_vars($this) ?: []);
      $this->success($dat);
    }
    else parent::display($templateFile,$charset,$contentType,$content,$prefix);
  }

  /*
   * 操作成功跳转的快捷方法 高级
   * $this->success('成功',U('index'));
   * $this->success(['key' => $val]);
   * $this->data = ['key' => $val];$this->success('成功');
   */
  protected function success($message = '',$jumpUrl = '',$ajax = false)
  {
    if(!is_string($message))
    {
      $dat = $message ?: [];
      unset($dat['_message']);
      if($ajax || IS_AJAX) $ajax = ['data' => $dat];
      $message = $message['_message'] ?: '';
    }
    elseif($this->data)
    {
      if($ajax || IS_AJAX) $ajax = ['data' => $this->data];
    }
    parent::success($message,$jumpUrl,$ajax);
  }

}