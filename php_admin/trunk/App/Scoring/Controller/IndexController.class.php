<?php
namespace Scoring\Controller;

class IndexController extends PublicController
{

  public function __construct()
  {
    parent::__construct();
  }

  public function index()
  {
    $this->redirect('ScoringBox9/index');
  }

  public function login()
  {
    if(IS_POST)
    {
      $dat = array();
      $dat['phone']    = trim($_REQUEST['phone']);
      $dat['password'] = $_REQUEST['password'];
      // 通过Api验证登录
      $jss = $this->http($this->api_root.'auth/login',$dat,'POST');
      $arr = json_decode($jss,true) ?: array();
      if($uid = (int)$arr['uid'])
      {
        $_SESSION[$this->auth_key] = $uid;
        $day = strtotime(date('Y-m-d'));
        $now = time() % $day;
        $rng = D('Yanzhi/UserScoring')->get_user_scoring_range($uid);
        if(!$rng) $this->error('该账号不是打分团账号');
        elseif($now < $rng['work_start'] - 60 * 5 || $now > $rng['work_end'])
        {
          $this->error('该账号的工作时段为'.date('H:i',$rng['work_start'] + $day).' - '.date('H:i',$rng['work_end'] + $day));
        }
        $_SESSION['user-scoring-range'] = $rng ?: array();
        $this->success('登录成功',U('ScoringBox9/index'));
        die;
      }
      $this->error($arr['error'] ?: '登录失败');
      die;
    }
    $this->display();
  }

  public function logout()
  {
    $_SESSION[$this->auth_key] = false;
    $this->redirect('Index/login');
  }

}