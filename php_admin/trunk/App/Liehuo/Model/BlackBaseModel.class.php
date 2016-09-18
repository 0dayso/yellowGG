<?php
namespace Liehuo\Model;

class BlackBaseModel extends CjDatadwModel
{

  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid']) $map[$alias.'uid']   = $uid;
    if($oid = (int)$arr['oid']) $map[$alias.'oid']   = $oid;
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'add_time']) || $map[$alias.'add_time'] = [];
      $map[$alias.'add_time'][] = ['egt',strtotime(date('Y-m-d',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'add_time']) || $map[$alias.'add_time'] = [];
      $map[$alias.'add_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($arr['sex'] != '')//性别筛选
    {
      $sex = (int)$arr['sex'] == 0 ? 0 : 1;
      $sql = D('UserBase')->field('uid')
        ->where(['uid' => ['exp',' = '.$alias.'uid'],'sex' => $sex])
        ->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($kwd = trim($arr['kwd']))
    {
      $map['_complex'] = [
          '_logic' => 'or',
          $alias.'reason' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd))
      {
        $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
        $map['_complex'][$alias.'oid'] = ['like','%'.$kwd.'%'];
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

}