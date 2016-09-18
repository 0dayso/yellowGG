<?php
namespace Liehuo\Controller;

class PreventionController extends PublicController
{

  public function index()
  {
    $this->mongate_balance = D(CONTROLLER_NAME)->get_mongate_balance();
    $this->display();
  }

  public function query()
  {
    $mod = D(CONTROLLER_NAME);
    //$mod->test_data();
    $dat['data']  = $mod->get_prev_analy();
    $dat['types'] = $mod->analy_types;
    $dat['logs']  = $mod->get_logs_url();
    $dat['mysql'] = count($mod->get_mysql_processlist() ?: []);
    foreach(['redis_recommend','redis_user','redis_default'] as $v)
    {
      $dat['redis'][$v] = $mod->get_redis_memory($v);
    }
    die(json_encode($dat));
  }

  public function logs()
  {
    $mod = D(CONTROLLER_NAME);
    $typ = I('request.type');
    $dat = [];
    $fnm = $mod->analy_logs[$typ];
    if(!$typ)                  $this->error('参数错误');
    elseif(!$fnm)              $this->error('无Log');
    elseif(!file_exists($fnm)) $this->error('Log文件不存在<br>'.$fnm);
    else
    {
      $dat['cols'] = $mod->analy_cols[$typ];
      $dat['list'] = $mod->get_logs_list($typ);
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  // MySQL链接列表
  public function mysql_processlist()
  {
    $mod = D(CONTROLLER_NAME);
    $arr = $mod->get_mysql_processlist(trim($_REQUEST['conn'])) ?: [];
    if($db = trim($_REQUEST['db']))
    {
      foreach($arr as $v)
      {
        if($v['db'] == $db) $dat['list'][] = $v;
      }
    }
    else $dat['list'] = $arr;
    $dat['list'] = array_map(function($v)
    {
      $v['action'] = '<a href="'.U('kill_mysql_processlist',
      [
        'conn' => trim($_REQUEST['conn']),
        'id'   => $v['id'],
      ]).'">Kill</a>';
      return $v;
    },$dat['list'] ?: []);
    $dat['cols'] = ['ID','User','Host','DB','Command','Time','State','Info','Action'];
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display('logs');
  }

  public function kill_mysql_processlist()
  {
    $id = (int)$_REQUEST['id'];
    $ret = D(CONTROLLER_NAME)->kill_mysql_processlist($id,trim($_REQUEST['conn']));
    $this->success('操作成功');
  }

}