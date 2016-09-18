<?php
namespace Liehuo\Controller;
use Common\Controller as Common;

class AdminController extends Common\AdminController
{

  public function csp_logs()
  {
    $key = 'php_csp_report_admin';
    $rds = D('PhpServerRedis')->new_redis();
    $cnt = $rds->zCard($key);
    $pag = new \Think\Page($cnt,$this->page_size ?: 50);
    $arr = $rds->zRevRange($key,$pag->firstRow,$pag->listRows,true) ?: [];
    foreach($arr as $k => $v)
    {
      $jss = preg_replace('/^\s*[\d-:]+|\s*$/','',$k);
      $csp = json_decode($jss,true) ?: [];
      $lst[] = array_merge($csp['csp-report'] ?: [],
      [
        'create_time' => $v,
        'create_date' => date('Y-m-d',$v),
        'admin_id'    => $csp['admin_id'],
      ]);
      $csp['admin_id'] && $dat['aids'][$csp['admin_id']] = $csp['admin_id'];
    }
    $dat['aids'] && $dat['admins'] = D('Admin')->klist('aid',['aid' => ['in',array_values($dat['aids'])]]);
    foreach($lst ?: [] as $v)
    {
      $dat['list'][] =
      [
        '时间' => date('Y-m-d H:i:s',$v['create_time']),
        '页面' => '<div class="td-content">'.$v['document-uri'].'</div>',
        'URL'  => '<div class="td-content">'.$v['blocked-uri'].'</div>',
        '描述' => $v['script-sample'],
        '管理员' => $dat['admins'][$v['admin_id']]['nickname'] ?: $v['admin_id'],
      ];
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    //die(json_encode(compact('dat')));
    $this->display('Common/list-table');
  }

}