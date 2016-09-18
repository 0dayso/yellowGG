<?php
namespace Liehuo\Model;

class DailyCountModel extends CjAdminModel
{

  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($arr['sex'] != '')//性别筛选
    {
      $sex = (int)$arr['sex'] == 0 ? 0 : 1;
      $map[$alias.'sex'] = $sex;
    }
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'start_time']) || $map[$alias.'start_time'] = [];
      $map[$alias.'start_time'][] = array('egt',strtotime(date('Y-m-d',$stime)));
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'start_time']) || $map[$alias.'start_time'] = [];
      $map[$alias.'start_time'][] = array('elt',strtotime(date('Y-m-d 23:59:59',$etime)));
    }
    if($kwd = trim($arr['kwd']))
    {
      $map['_complex'] = [
        '_logic' => 'or',
        //$alias.'nickname' => ['like','%'.$kwd.'%'],
      ];
      //if(preg_match('/^\d+$/i',$kwd)) $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

  // 打分统计
  public function set_scoring($score = -1,$sex = 0)
  {
    is_array($score) || $score = [$score];
    $dat = ['sex' => (int)$sex];
    foreach($score as $sco)
    {
      $sco = (int)$sco;
      if($sco < 0) continue;
      $sco < 5 && $sco = 0;
      $sco > 9 && $sco = 9;
      $field = 'score'.$sco;
      $dat[$field] = ['exp',$field.' + 1'];
    }
    return $this->set_count($dat,
    [
      'date' => date('Y-m-d'),
      'sex'  => $dat['sex'],
    ]);
  }

  // 每日统计
  public function set_count($dat = [],$map = true)
  {
    $ret = false;
    $day = date('Y-m-d');
    $dat = array_merge($dat ?: [],
    [
      'date'      => $day,
      'date_unix' => strtotime($day),
    ]);
    $map = $map === true ? ['date' => $dat['date']] : $map;
    $old = $this->where($map)->find();
    if(!$old && ($dat['id'] = $this->add($dat)))
    {
      $ret = $dat['id'] ? $dat : $dat['id'];
    }
    elseif($ret = $this->where($map)->limit(1)->save($dat))
    {
      $ret = array_merge($old,$dat);
    }
    return $ret;
  }

}