<?php
namespace Cms\Controller;

class ScoreBaseController extends PublicController
{

  public function index()
  {
    $mod = D(CONTROLLER_NAME);
    $map = $dat = array();
    // 筛选搜索
    if($_REQUEST['uid'] != '')
    {
      $uid = (int)$_REQUEST['uid'];
      if($uid < 0) $map['uid'] = array('egt',1);
      else $map['uid'] = $uid;
    }
    if($_REQUEST['stime'] && $stime = strtotime($_REQUEST['stime']))
    {
      is_array($map['score_time']) || $map['score_time'] = array();
      $map['score_time'][] = array('egt',strtotime(date('Y-m-d',$stime)));
    }
    if($_REQUEST['etime'] && $etime = strtotime($_REQUEST['etime']))
    {
      is_array($map['score_time']) || $map['score_time'] = array();
      $map['score_time'][] = array('elt',strtotime(date('Y-m-d 23:59:59',$etime)));
    }
    if($_REQUEST['filter'] == 'virtual_user')//运营账号打分日志
    {
      $sql = D('UserBase')->field('uid')
        ->where(array('type' => 3,'uid' => array('exp',' = '.$mod->getTableName().'.uid')))
        ->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($kwd = trim($_REQUEST['kwd']))
    {
      $map['_complex'] = array(
        '_logic' => 'or',
        'uid' => $kwd,
        'oid' => $kwd,
      );
    }
    $dat['list'] = $mod->plist(100,$map)->lists('','score_time desc');//C('ITEMS_PER_PAGE')
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->pager = $mod->pager;
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

}