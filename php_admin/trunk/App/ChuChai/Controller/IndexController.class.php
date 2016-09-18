<?php
namespace ChuChai\Controller;
use Common\Controller\CommonController as CommonController;

class IndexController extends PublicController
{

  // 无需登录的操作
  public $guest_actions =
  [
    'index',
    'login',
    'show_img_full',
  ];

  public function __construct()
  {
    parent::__construct();
  }

  public function index()
  {
    $this->redirect('Shop/index');
  }

  public function login()
  {
    if(IS_POST)
    {
      $mod = D('Admin');
      $name = trim($_REQUEST['name']);
      $pass = $_REQUEST['pass'];
      $map = ['nickname' => $name];
      $dat = $mod->where($map)->find() ?: [];
      //$dat = \Org\Util\Rbac::authenticate($map);
      if(!$dat) $this->error('管理员不存在');
      elseif($dat['pwd'] != md5($pass)) $this->error('密码错误');
      elseif($aid = (int)$dat['aid'])
      {
        $_SESSION[C('USER_AUTH_KEY')] = $aid;
        $_SESSION['email']            = $dat['email'];
        $_SESSION['nickname']         = $dat['nickname'];
        //异地登陆IP白名单
        CommonController::sess_white($aid);
        A('Common')->login_cookie();
        $mod->where(['aid' => $aid])->limit(1)->setField('last_login_time',date('Y-m-d H:i:s'));
        D('Liehuo/OperLog')->log('login',
        [
          '登陆爱出差后台',
          '管理员ID' => $aid,
          'IP' => $_SERVER['REMOTE_ADDR'],
        ]);
        $this->success('登录成功',U('Shop/index'));
        die;
      }
      $this->error('登录失败');
      die;
    }
    $this->display();
  }

  // 显示完整图片
  public function show_img_full()
  {
    die('<html><body><img src="'.I('request.src').'"></body></html>');
  }

}