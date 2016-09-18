<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/6
 * Time: 13:46
 */
namespace Yanzhi\Controller;

use Think\Controller;
use Org\Util\Rbac;

class IndexController extends Controller
{

    public function index()
    {
        $this->redirect("Index:login");
    }

    public function login()
    {
        layout(false);
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
            $this->ajaxReturn(array('info'=>'验证码不能为空'));
        }

        $verify_code = $_POST['verify'] ;
        $verify = new \Think\Verify();
        if(!$verify->check($verify_code,''))
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
            }

            if(strpos($_SERVER['HTTP_REFERER'],$_SERVER['SERVER_NAME'])){
                $this->ajaxReturn(array('info'=>'登录成功','referer'=>$_POST['referer']));
            }else{
                $this->ajaxReturn(array('info'=>'登录成功'));
            }

            //$this->redirect('Common/index');
        }
    }

    // 
    public function notfind(){
        $this->display();
    }


}
