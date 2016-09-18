<?php
namespace Liehuo\Model;

class UserScoreRecordModel extends CjAdminLogModel
{


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid']) $map[$alias.'uid']  = $uid;
    if($aid = (int)$arr['aid']) $map[$alias.'aid']  = $aid;
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'score_time']) || $map[$alias.'score_time'] = [];
      $map[$alias.'score_time'][] = ['egt',strtotime(date('Y-m-d',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'score_time']) || $map[$alias.'score_time'] = [];
      $map[$alias.'score_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($arr['sex'] != '')//性别筛选
    {
      $sex = (int)$arr['sex'] == 0 ? 0 : 1;
      $sql = D('UserBase')->table('chujiandw.__USER_BASE__')->field('uid')
        ->where(['uid' => ['exp',' = '.$alias.'uid'],'sex' => $sex])
        ->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $map['_complex'] = [
          '_logic' => 'or',
          //$alias.'remark' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd)) $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
      if(count($map['_complex']) <= 1) unset($map['_complex']);
    }
    return $map;
  }

  // 记录日志
  public function log($uid = 0,$sco = -1)
  {
    $dat = array_merge($dat ?: [],
    [
      'uid'   => (int)$uid,
      'score' => (float)$sco,
    ]);
    return $this->add_log($dat);
  }

  // 添加日志
  public function add_log($dat = [])
  {
    $dat = array_merge(
    [
      'uid'        => 0,
      'aid'        => (int)$_SESSION[C('USER_AUTH_KEY')],
      'score'      => -1,
      'score_time' => time(),
    ],$dat ?: []);
    $dat['id'] = $this->add($dat);
    $ret = $dat['id'] ? $dat : $dat['id'];
    return $ret;
  }

}