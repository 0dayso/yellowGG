<?php
namespace Liehuo\Model;

class FeedbackNoteModel extends CjAdminModel
{

  // 状态
  public $status = [
    0 => '未处理',
    1 => '处理中',
    2 => '搁置中',
    3 => '已结束',
  ];

  public function __construct()
  {
    parent::__construct();
    $this->aid = (int)$_SESSION[C('USER_AUTH_KEY')];
  }


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid']) $map[$alias.'uid']     = $uid;
    if($arr['status'] != '')    $map[$alias.'status'] = (int)$arr['status'];
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
      $map['_complex'] =
      [
        '_logic' => 'or',
        $alias.'remark' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd)) $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

  // 新的意见反馈 批量
  public function start_all($arr = [])
  {
    $ids = array_column($arr ?: [],'uid') ?: [];
    $ols = $ids ? $this->where(['uid' => ['in',$ids]])->klist('uid') : [];
    $adt = $sdt = [];
    \Think\Log::write('意见反馈:批量开始:'.json_encode([$arr,$ids,$ols,time()])."\n\n");
    foreach($arr ?: [] as $v)
    {
      $uid = (int)$v['uid'];
      if($uid < 1) continue;
      $dat = array_merge([
        'status'     => 0,
        'aid'        => 0,
        'start_time' => time(),
        'reply_time' => 0,
        'over_time'  => 0,
        'remark'     => '',
      ],$v ?: [],['uid' => $uid]);
      if(!isset($ols[$uid]))              $adt[$uid] = $dat;
      elseif($ols[$uid]['status'] == '3') $sdt[$uid] = $dat;
      \Think\Log::write('意见反馈:开始:'.json_encode([$uid,$dat,time()])."\n\n");
    }
    $ret = $this->addAll(array_values($adt));
    foreach($sdt as $dat)
    {
      $uid = (int)$dat['uid'];
      unset($dat['uid']);
      $ret = $this->where(['uid' => $uid,'status' => 3])->limit(1)->save($dat) && $ret;
    }
    return $ret;
  }

  // 新的意见反馈
  public function start($dat = [])
  {
    $ret = false;
    $dat = array_merge(
    [
      'status'     => 0,
      'uid'        => $this->uid,
      'aid'        => 0,
      'start_time' => time(),
      'reply_time' => 0,
      'over_time'  => 0,
      'remark'     => '',
    ],$dat ?: []);
    if($dat['uid'])
    {
      $old = $this->where(['uid' => $dat['uid']])->find();
      if(!$old && ($dat['id'] = $this->add($dat)))
      {
        $ret = $dat;
      }
      elseif($old['status'] == '3' && $this->where(['uid' => $dat['uid'],'status' => 3])->limit(1)->save($dat))
      {
        $ret = array_merge($old,$dat);
      }
    }
    return $ret;
  }

  // 首次回复
  public function reply($dat = [])
  {
    $dat = array_merge([
      'status'     => 1,
      'aid'        => $this->aid,
      'reply_time' => time(),
    ],$dat ?: []);
    $ret = $this->where(
    [
      'uid'        => $this->uid,
      'reply_time' => ['elt',60 * 60 * 24],
      //'status'     => ['in',[0,2]],
    ])->limit(1)->save($dat);
    \Think\Log::write('意见反馈:响应:'.json_encode([$this->uid,$dat,time()])."\n\n");
    if($ret) $ret = $dat;
    return $ret;
  }

  // 搁置/暂停会话
  public function pause($dat = [])
  {
    $dat = array_merge([
      'status' => 2,
      'aid'    => $this->aid,
      'remark' => '',
    ],$dat ?: []);
    $ret = $this->where(['uid' => $this->uid])->limit(10)->save($dat);
    if($ret) $ret = $dat;
    return $ret;
  }

  // 结束会话
  public function over($dat = [])
  {
    $dat = array_merge([
      'status'    => 3,
      'aid'       => $this->aid,
      'over_time' => time(),
      'remark'    => '',
    ],$dat ?: []);
    $ret = $this->where(['uid' => $this->uid])->limit(10)->save($dat);
    if($ret) $ret = $dat;
    return $ret;
  }

  // 新的意见反馈
  public function add_row($dat = [])
  {
    $ret = false;
    $dat = array_merge(
    [
      'status'     => 0,
      'uid'        => $this->uid,
      'aid'        => $this->aid,
      'start_time' => time(),
      'reply_time' => 0,
      'over_time'  => 0,
      'remark'     => '',
    ],$dat ?: []);
    $dat['id'] = $this->add($dat);
    $ret = $dat['id'] ? $dat : $dat['id'];
    return $ret;
  }

}