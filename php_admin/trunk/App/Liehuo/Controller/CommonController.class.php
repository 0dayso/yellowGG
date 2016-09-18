<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/6
 * Time: 14:49
 * Description: 该文件暂时没有用上；
 */
namespace Liehuo\Controller;

class CommonController extends PublicController
{

    public function index()
    {
        if (!$_SESSION[C('USER_AUTH_KEY')]) {
            $this->redirect('Index/login');
        }
        else{
            $this->display();
        }
    }

    function logout()
    {
        if (!empty($_SESSION[C('USER_AUTH_KEY')])) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
            $_SESSION = array();
            session_destroy();
            //以下两种方式都可以
            //$this->success('登出成功');
            $this->redirect('Index/login');
        } else {
            $this->error('登出出错');
        }
    }
} 