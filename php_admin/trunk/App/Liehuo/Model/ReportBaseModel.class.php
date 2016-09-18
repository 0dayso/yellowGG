<?php
namespace Liehuo\Model;

class ReportBaseModel extends CjDatadwModel
{

  // 举报类型
  public $report_types = array(
    0 => '-',
    //1 => '假照片、资料',
    //2 => '淫秽色情',
    //3 => '辱骂、恶意、不文明语言',
    //4 => '微商（广告、兜售、钓鱼）',
    //5 => '违法（违禁、托、政治敏感）',
    6 => '虚假（假头像、照片）',
    7 => '反感（微商、广告）',
    8 => '骚扰（色情、辱骂、不文明言语）',
    100 => '其他',
    200 => '直播',
  );

  // 举报状态
  public $report_status = array(
    0 => '未处理',
    1 => '已处理',
    2 => '已处理并封禁',
    3 => '已处理并警告',
    4 => '拒绝处理',
    5 => '处罚中不处理',
  );

  // 举报理由
  public $report_reasons = array();//C('STATE_ACCUSATION_TYPE')

  protected function _initialize()
  {
    $this->report_reasons = C('STATE_ACCUSATION_TYPE') ?: array();
  }


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($arr['uid'] != '') $map[$alias.'uid']          = (int)$arr['uid'];
    if($arr['oid'] != '') $map[$alias.'offender_uid'] = (int)$arr['oid'];
    if(is_numeric($arr['live_id'])) $map[$alias.'live_id']   = $arr['live_id'];
    if($arr['status'] != '')      $map[$alias.'status']      = (int)$arr['status'];
    if($arr['report_type'] != '') $map[$alias.'report_type'] = (int)$arr['report_type'];
    $time_type = $alias.'dtime';
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$time_type]) || $map[$time_type] = [];
      $map[$time_type][] = ['egt',strtotime(date('Y-m-d',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$time_type]) || $map[$time_type] = [];
      $map[$time_type][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $field = trim($arr['search_field']);
      $map['_complex'] =
      [
        '_logic' => 'or',
      ];
      if(!$field || $field == 'remark') $map['_complex'][$alias.'remark'] = ['like','%'.$kwd.'%'];
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'uid')          $map['_complex'][$alias.'uid']          = ['like','%'.$kwd.'%'];
        if(!$field || $field == 'offender_uid') $map['_complex'][$alias.'offender_uid'] = ['like','%'.$kwd.'%'];
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

  // 设置举报状态
  public function set_status($id = 0,$sta = 0,$dat = [])
  {
    is_string($dat) && $dat = ['remark' => '$dat'];
    $dat = array_merge([
      'reason' => 0,
      'remark' => '',
      'atime'  => time(),
    ],$dat ?: [],[
      'status' => (int)$sta,
    ]);
    $ret = $this->where(['id' => $id])->limit(1)->save($dat);
    if($ret) $ret = $dat;
    return $ret;
  }

  // 获取给举报人的反馈消息
  public function get_report_msg($status = 0,$name = '')
  {
    $msg = '';
    //已做其他处理
    if(in_array($status,[1,2,3])) $msg = '系统提醒：您对用户（'.$name.'）的举报我们已做出处理，感谢您对我们工作的支持和帮助！';
    elseif($status == 4)          $msg = '系统提醒：感谢您的举报，我们已对该用户（'.$name.'）进行相关警告，会持续关注后续行为，如再次涉及违规我们将暂停TA使用相关功能！';
    //elseif($status == 4)          $msg = '系统提醒：感谢您的举报，我们正持续关注该用户（'.$name.'）后续行为，如涉及明显违规我们将暂停TA使用相关功能！';
    //elseif($status == 4)          $msg = '系统提醒：感谢您的举报，我们正持续关注该用户（'.$name.'）后续行为，如果已对您造成财产损失请立即报案。';
    //已处罚不再处罚
    elseif($status == 5)          $msg = '系统提醒：感谢您的举报，该用户（'.$name.'）已被处理！请继续关注和支持我们的工作！';
    return $msg;
  }

  function getByLiveIds($ids = [],$map = [])
  {
    $map = array_merge(
    [
      'live_id'     => ['in',$ids],
      //'report_type' => 200,
    ],$map ?: []);
    $arr = $this->where($map)->select() ?: [];
    $lst = [];
    foreach($arr as $v)
    {
      $lst[$v['live_id']][] = $v;
    }
    return $lst;
  }

}