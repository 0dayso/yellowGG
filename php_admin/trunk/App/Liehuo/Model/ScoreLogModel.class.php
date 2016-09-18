<?php
namespace Liehuo\Model;

class ScoreLogModel extends CjAdminLogModel
{

  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid']) $map[$alias.'uid'] = $uid;
    if($aid = (int)$arr['aid']) $map[$alias.'aid'] = $aid;
    if($arr['stime'] && $stime = strtotime($_REQUEST['stime'] = $_GET['stime'] = urldecode(urldecode($arr['stime']))))
    {
      is_array($map[$alias.'score_time']) || $map[$alias.'score_time'] = [];
      $map[$alias.'score_time'][] = array('egt',strtotime(date('Y-m-d H:i:s',$stime)));
    }
    if($arr['etime'] && $etime = strtotime($_REQUEST['etime'] = $_GET['etime'] = urldecode(urldecode($arr['etime']))))
    {
      is_array($map[$alias.'score_time']) || $map[$alias.'score_time'] = [];
      $map[$alias.'score_time'][] = array('elt',strtotime(date('Y-m-d H:i:59',$etime)));
    }
    if($arr['sex'] != '')//性别筛选
    {
      $sex = (int)$arr['sex'] == 0 ? 0 : 1;
      $sql = D('UserBase')->table('chujiandw.__USER_BASE__')->field('uid')
        ->where(['uid' => ['exp',' = '.$alias.'uid'],'sex' => $sex])
        ->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($arr['score'] != '')
    {
      $sco = (int)$arr['score'];
      $exp = 'egt';
      if(0 - $sco > 0)
      {
        $exp = 'lt';
        $sco = 0 - $sco;
      }
      $map[$alias.'score'] = [$exp,$sco];
    }
    if($arr['score_range'] != '')
    {
      $sco = (int)$arr['score_range'];
      if($sco >= 9) $map[$alias.'score'] = ['egt',9];
      elseif($sco === 0)
      {
        $map[$alias.'score'] = [
          ['egt',0],
          ['elt',4.999],
        ];
      }
      else
      {
        $map[$alias.'score'] = [
          ['egt',$sco],
          ['elt',$sco + 0.999],
        ];
      }
    }
    if($arr['timeout'] != '')
    {
      list($min,$max) = preg_split('/[\s,_]+/i',$arr['timeout']) ?: [];
      $min = (int)$min ?: 0;
      $max = (int)$max ?: 999999;
      $map[$alias.'timeout'] =
      [
        ['egt',$min],
        ['elt',$max],
      ];
    }
    if($prov = trim(urldecode($arr['province'])))//省份筛选
    {
      $_REQUEST['province'] = $_GET['province'] = $prov;
      $whe = [
        'uid'      => ['exp',' = '.$alias.'uid'],
        '_complex' => [
          '_logic'   => 'or',
          'province' => ['like',$prov.'%'],
          'city'     => ['like',$prov.'%'],
          'area'     => ['like',$prov.'%'],
        ],
      ];
      $sql = D('LocationBase')->table('chujiandw.__LOCATION_BASE__')->field('uid')->where($whe)->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($arr['filter'] == 'score_changed')
    {
      $whe = [
        'resource'   => ['exp',' = '.$alias.'resource'],
        'uid'        => ['exp',' = '.$alias.'uid'],
        'score_time' => ['egt',1],
        'score'      => ['exp',' <> '.$alias.'score'],
      ];
      $sql = D('Avatar')->table('cj_admin.__AVATAR__')->field('id')->where($whe)->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $map['_complex'] = [
        '_logic' => 'or',
        //$alias.'nickname' => ['like','%'.$kwd.'%'],
      ];
      $sql = D('UserBase')->table('chujiandw.__USER_BASE__')->field('uid')
        ->where(['uid' => ['exp',' = '.$alias.'uid'],'pkg_channel' => $kwd])
        ->buildSql();
      $map['_complex']['_string'] .= ($map['_complex']['_string'] ? ' and ' : '').'exists '.$sql;
      if(preg_match('/^\d+$/i',$kwd)) $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

  // 添加日志
  public function add_log($dat = [])
  {
    $dat = array_merge(
    [
      'aid'        => (int)$_SESSION[C('USER_AUTH_KEY')],
      'uid'        => 0,
      'resource'   => '',
      'score'      => 0,
      'score_time' => time(),
      'timeout'    => 0,
    ],$dat ?: []);
    $dat['id'] = $this->add($dat);
    $ret = $dat['id'] ? $dat : $dat['id'];
    return $ret;
  }

}