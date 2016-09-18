<?php
namespace Liehuo\Model;

class TextRepeatLogModel extends CjAdminLogModel
{

  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    isset($arr['handled']) || $arr['handled'] = '0';
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid'])  $map[$alias.'uid'] = $uid;
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'update_time']) || $map[$alias.'update_time'] = [];
      $map[$alias.'update_time'][] = ['egt',strtotime(date('Y-m-d 00:00:00',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'update_time']) || $map[$alias.'update_time'] = [];
      $map[$alias.'update_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($arr['type']    != '') $map[$alias.'type']        = $arr['type'];
    if($arr['handled'] != '') $map[$alias.'handle_time'] = !(int)$arr['handled'] ? 0 : ['egt',1];
    if($kwd = trim($arr['kwd']))
    {
      $map['_complex'] =
      [
        '_logic'   => 'or',
        //$alias.'text' => [['like','%'.$kwd.'%']],
      ];
      foreach(preg_split('/[\s|;,]+/',$kwd) ?: [] as $v)
      {
        $map['_complex'][$alias.'text'][] = ['like','%'.$v.'%'];
      }
      if($map['_complex'][$alias.'text']) $map['_complex'][$alias.'text'][] = 'or';
      if(preg_match('/^\d+$/i',$kwd)) $map['_complex'][$alias.'uid'] = $kwd;
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }


  public function get_key($dat = [])
  {
    is_array($dat) || $dat = [];
    return md5(implode('-',[$dat['uid'],$dat['type'],$dat['text']]));
  }

  public function get_keys($arr = [])
  {
    $kys = array_map(function($v)
    {
      return $this->get_key($v);
    },$arr ?: []);
    return array_values($kys);
  }

  public function save_row($dat = [])
  {
    is_array($dat) || $dat = [];
    $now = time();
    $dat = array_merge(
    [
      'uid'         => 0,
      'aid'         => 0,
      'type'        => '',
      'text'        => '',
      'times'       => 0,
      'remark'      => '',
      'update_time' => 0,
      'handle_time' => 0,
    ],$dat ?: []);
    $ret = $this->where(
    [
      'key' => $this->get_key($dat),
      'uid' => $dat['uid'],
      'handle_time' => 0,
    ])->save(
    [
      'times'       => ['exp','times + '.(int)$dat['times']],
      'update_time' => (int)$dat['update_time'] ?: $now,
    ]);
    if(!$ret)
    {
      $dat['key'] = $this->get_key($dat);
      $dat['create_time'] = $now;
      $usr = UserBaseModel::Instance()->find($dat['uid']);
      if($usr['type'] == '2' && $usr['dblocking_time'] > $now) return false;
      $ret = $this->add($dat);
    }
    return $ret;
  }

  public function save_all($arr = [])
  {
    is_array($arr) || $arr = [];
    $ret = false;
    $now = time();
    $uls = [];
    if($ids = array_column($arr ?: [],'uid'))
    {
      $uls = UserBaseModel::Instance()->get_by_ids($ids);
    }
    $map =
    [
      'uid' => ['in',$ids],
      'key' => ['in',$this->get_keys($arr)],
      'handle_time' => 0,
    ];
    $els = $this->where($map)->select() ?: [];
    $edt = [];
    foreach($els as $v) $edt[$this->get_key($v)] = $v;
    $lst = $als = [];
    foreach($arr as $v)
    {
      $uid = (int)$v['uid'];
      $usr = $uls[$uid] ?: [];
      if(!$uid) continue;
      if(!$usr) continue;
      if($usr['type'] == '2' && $usr['dblocking_time'] > $now) continue;
      $key = $v['key'] ?: $this->get_key($v);
      $row = array_merge(
      [
        'uid'         => 0,
        'aid'         => 0,
        'type'        => '',
        'text'        => '',
        'times'       => 0,
        'remark'      => '',
        'update_time' => 0,
        'handle_time' => 0,
      ],$v ?: []);
      if(isset($edt[$key]))
      {
        $ret += (int)$this->where(
        [
          'key' => $key,
          'uid' => $uid,
          'handle_time' => 0,
        ])->save(
        [
          'times'       => ['exp','times + '.(int)$row['times']],
          'update_time' => (int)$row['update_time'] ?: $now,
        ]);
      }
      else
      {
        $row['key'] = $this->get_key($row);
        $row['create_time'] = $now;
        $als[] = $row;
      }
    }
    if($als) $ret = $this->addAll($als);
    return $ret;
  }

}