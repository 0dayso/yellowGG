<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/6
 * Time: 13:46
 */
namespace Liehuo\Controller;

use Think\Controller;
use Common\Controller\CommonController as CommonController;
use Org\Util\Rbac;

class IndexController extends Controller
{

    public function index()
    {
        $this->redirect("Index:login");//redirect重定向
    }

    public function login()
    {
        layout(false);
        $this->referer = session('login_request') ?: cookie('login_request');
        $this->display();
    }

    // 显示完整图片
    public function show_img_full()
    {
      die('<html><body><img src="'.I('request.src').'"></body></html>');
    }

    public function verify_code()
    {
        $Verify = new \Think\Verify();
        $Verify->codeSet = '0123456789';
        $Verify->fontSize = 30;
        $Verify->length   = 4;
        $Verify->useNoise = false;
        $Verify->expire   = 20;//验证码有效期20秒
        $Verify->entry();
    }

    public function check_login()
    {
        if(empty($_POST['name'])) {
            $this->ajaxReturn(array('info'=>'用户名不能为空'));
        }elseif (empty($_POST['pwd'])){
            $this->ajaxReturn(array('info'=>'密码不能为空'));
        }elseif (empty($_POST['verify'])){
            //$this->ajaxReturn(array('info'=>'验证码不能为空'));
        }

        $verify_code = $_POST['verify'] ;
        $verify = new \Think\Verify();
        if($verify_code && !$verify->check($verify_code,''))
        {
            $this->ajaxReturn(array('info'=>'验证码有误'));
        }


        $map             =   array();
        $map['nickname'] = $_POST['name'];
        $authInfo = Rbac::authenticate($map);

        //使用用户名、密码和状态 的方式进行认证
        if(false === $authInfo) {
            $this->ajaxReturn(array('info'=>'帐号不存在或被禁用'));
        }else {
            $pwd = md5($_POST['pwd']);
            if($authInfo['pwd'] != $pwd) {
                $this->ajaxReturn(array('info'=>'密码错误'));
            }

            $Init = new InitController();
            $initRet = $Init->init($authInfo);
            if($initRet === false)
                $this->ajaxReturn(array('info'=>'生成数据表失败'));

            // 最后登录时间
            if($aid = (int)$authInfo['aid'])
            {
              D('Admin')->where(array('aid' => $aid))->limit(1)->setField('last_login_time',date('Y-m-d H:i:s'));
              D('OperLog')->log('login',
              [
                '登陆后台',
                '管理员ID' => $aid,
                'IP' => $_SERVER['REMOTE_ADDR'],
              ]);
              //异地登陆IP白名单
              CommonController::sess_white($aid);
            }

            // 第三方打分团用户
            $is_open = D('Auth')->check('Scoring') && D('Auth')->check('Scoring/open');
            if($is_open && CONTROLLER_NAME != 'Scoring' && (int)$_SESSION[C('USER_AUTH_KEY')])
            {
              $url = U('Scoring/index?type=open');
              session('login_request',$url);
              $this->ajaxReturn(
              [
                'info'    => '登录成功',
                'referer' => $url,
              ]);
            }

            if(strpos($_SERVER['HTTP_REFERER'],$_SERVER['SERVER_NAME']))
            {
                $ref = $_POST['referer'];
                if(D('Auth')->check('Analy/adver_daily_open'))
                {
                  A('Common')->login_cookie();
                  $ref = U('Analy/adver_daily');
                }
                $this->ajaxReturn(array('info' => '登录成功','referer' => $ref));
            }
            else
            {
                $this->ajaxReturn(array('info' => '登录成功'));
            }

            //$this->redirect('Common/index');
        }
    }

    // 
    public function notfind(){
        $this->display();
    }


}
