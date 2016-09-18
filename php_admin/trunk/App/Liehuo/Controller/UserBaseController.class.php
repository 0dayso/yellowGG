<?php
namespace Liehuo\Controller;
use Liehuo\Model;

class UserBaseController extends PublicController
{

  public $uid;
  public $ret = array('ret' => 0,'msg' => '','data' => array());

  public function __construct()
  {
    parent::__construct();
    $this->uid = (int)$_REQUEST['uid'];
  }

  public function index()
  {
    $mod = D(CONTROLLER_NAME)->alias('u');
    $dat = array();
    //isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    $map = $mod->get_filters(true/*表别名*/);//筛选搜索
    $ods =
    [
      'uid'        => 'u.uid desc,u.reg_time desc',
      'reg_time'   => 'u.reg_time desc,u.uid desc',
      'login_time' => 'l.update_time desc,u.reg_time desc,u.uid desc',
      'score'      => 'u.score desc,u.reg_time desc,u.uid desc',
      'expense'    => 'a.total_expense desc,u.reg_time desc,u.uid desc',
      'balance'    => 'a.balance desc,u.reg_time desc,u.uid desc',
    ];
    $fds = ['u.*','l.update_time','a.balance','a.diamond','a.glamour','a.vip_level','a.vip_valid_end','a.glory_grade','a.total_charge','a.total_expense'];
    if(isset($map['l.update_time']))
    {
      $mod->field($fds)
        ->join('left join '.D('LocationBase')->getTableName().' l on l.uid = u.uid')
        ->join('left join '.D('AccountBase')->getTableName().' a on a.uid = u.uid')
        ->plist($this->page_size,$map);
    }
    else
    {
      $mod->plist($this->page_size,$map)
        ->field($fds)
        ->join('left join '.D('LocationBase')->getTableName().' l on l.uid = u.uid')
        ->join('left join '.D('AccountBase')->getTableName().' a on a.uid = u.uid');
    }
    // 获取列表
    $dat['list'] = $mod->lists('',$ods[trim($_REQUEST['order'])] ?: $ods['reg_time']);
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['list'] = $mod->format_nickname_all($dat['list']);
    $dat['list'] = array_map(function($v) use($mod)
    {
      $v['active_time'] = $mod->get_active_time($v['uid']);
      return $v;
    },$dat['list'] ?: []);
    $dat['user_types'] = $mod->user_types ?: array();
    //$stm = strtotime(date('Y-m-d 00:00:00',$_REQUEST['stime'] ? strtotime($_REQUEST['stime']) : time()));
    //$etm = strtotime(date('Y-m-d 23:59:59',$_REQUEST['etime'] ? strtotime($_REQUEST['etime']) : time()));
    $dat['cnt_active'] = $mod->get_redis()->zCount('php_active',strtotime(date('Y-m-d')),time() + 1);
    if($_REQUEST['display'] == 'uids')
    {
      $ids = array_column($dat['list'],'uid');
      die(implode(',',$ids));
    }
    if(trim($_REQUEST['download'])) foreach($dat['list'] ?: [] as $v)
    {
      $dat['export'][] =
      [
        '用户ID'   => $v['uid'],
        '用户昵称' => $v['nickname'],
        '电话号码' => $v['phone'],
        '性别'     => C('USER_SEX_IS.'.$v['sex']),
        '用户类型' => $data['user_types'][$v['type']],
        '照片数'   => count(json_decode($v['album'],true) ?: []),
        '分数'     => $v['score'] >= 5 ? (int)$v['score'] : '0',
        '消费金额' => $v['total_expense'],
        '最后活跃' => $v['active_time'] ? date('Y-m-d H:i:s',$v['active_time']) : '',
        '注册时间' => $v['reg_time'] ? date('Y-m-d H:i:s',$v['reg_time']) : '',
      ];
    }
    $this->data = $dat;
    $this->ResourceModel = D('Resource');
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }

  // 用户详情
  public function view()
  {
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    $phone = trim($_REQUEST['phone']);
    $mod = D(CONTROLLER_NAME);
    if($phone)
    {
      $usr = $mod->where(['phone' => $phone])->find();
      $uid = (int)$usr['uid'];
    }
    $dat = array();
    if(!$usr = $mod->find($uid))
    {
      $this->error('对象不存在!');
    }
    else
    {
      //$dat['item'] = $mod->attr2array_row($dat['item']);
      $usr['active_time']     = $mod->get_redis()->zScore('php_active',$uid);//最后活跃时间
      $usr['surp_like_times'] = D('UserZan')->get_zan_all($uid) ?: [];
      if($usr['device_id']) $usr['device_count'] = $mod->where(['device_id' => $usr['device_id']])->count('uid');
      $dat['item'] = $usr ?: [];
      $dat['auth_data']       = $mod->get_auth_data($uid);
      $dat['user_types']    = $mod->user_types ?: [];
      $dat['live_manager']  = D('LiveGuest')->is_manager($uid);
      $dat['album']         = json_decode(trim($usr['album']),true) ?: [];
      $dat['user_account']  = D('AccountBase')->where(['uid' => $uid])->find();
      $dat['user_location'] = D('LocationBase')->where(['uid' => $uid])->find();
      $dat['home_city']     = D('UserCity')->get_by_user($usr['home']);
      $dat['interest']      = D('UserInterest')->get_by_ids($usr['interest']);
      $dat['interest_types']= D('UserInterest')->types ?: [];
      $dat['job_haunt']     = D('UserJobsHaunt')->get_job_haunt_by_user($usr['job_haunt']);
      $dat['character']     = D('UserJobsHaunt')->get_character_by_ids($usr['character']);
      $dat['remark_scoring'] = Model\UserAttrsModel::Instance($uid)->get_attr('remark_scoring');
      // 历史照片
      $dat['avatars']  = D('Avatar')->klist('resource',['uid' => $uid]) ?: [];
      $alb = D('Resource')->fmtNewAlbum($dat['album'],true);
      foreach($dat['avatars'] as $k => $v)
      {
        $v['in_album'] = array_key_exists($v['resource'],$alb);
        $dat['avatars'][$k] = $v;
        if(!$v['in_album']) $dat['avatar_history'][$k] = $v;
      }
      // 封禁历史
      $acc = D('AccusationBaseLog');
      $dat['accusation_last'] = $acc->where(
      [
        'oid'    => $uid,
        'status' => ['in',[1,2,3,4,5,6]],
      ])->order('create_time desc,id desc')->find();
      $dat['accusation_logs']    = $acc->lists(['oid' => $uid],'create_time desc');
      $dat['accusation_states']  = $acc->accusation_states;
      $dat['accusation_reasons'] = $acc->accusation_reasons;
      $dat['accusation_admins']  = $acc->get_accusation_admins($dat['accusation_logs'],'aid,nickname');
      $dat['hosts'] = D('LiveHost')->get_by_ids([$uid]) ?: [];
      $dat['live_host'] = reset($dat['hosts']) ?: [];
      $dat['contract_types'] = D('LiveContractType')->get_all() ?: [];
      $dat['goods'] = D('Goods')->klist() ?: [];
      $dat['props'] = D('PropStore')->setUser($uid)->getByUser();
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }


  // 显示用户头像
  public function avatar()
  {
    $uid = (int)$_REQUEST['uid'];
    $src = D(CONTROLLER_NAME)->get_avatar($uid);
    $src || $src = C('TMPL_PARSE_STRING.__PUBLIC__').'/img/0.png';
    header('Location: '.$src);
    die;
  }

  // 头像列表
  public function avatar_list()
  {
    isset($_REQUEST['page_size']) || $this->page_size = 120;
    $isv = $_REQUEST['type'] == '3';
    $mod = D('Avatar');
    $dat = [];
    isset($_REQUEST['type'])    || $_REQUEST['type']    = 1;
    isset($_REQUEST['deleted']) || $_REQUEST['deleted'] = 0;
    $map = $mod->get_filters(true) ?: [];
    $map['delete_time'] = 0;
    $ord = $_REQUEST['filter'] == 'scored' ? 'score_time desc,id desc' : 'create_time desc,id desc';
    $dat['list'] = $mod->plist($isv ? 12 : $this->page_size,$map)->lists('',$ord);//C('ITEMS_PER_PAGE')
    $dat['state_audit']  = $mod->state_audit;
    $dat['users']        = D(CONTROLLER_NAME)->get_users_account($dat['list']);
    $dat['audit_admins'] = D('Admin')->get_by_list($dat['list'],'aid,nickname','audit_aid');
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->pager = $mod->pager;
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display($isv ? 'video_list' : '');
  }

  // 照片审核
  public function avatar_audit()
  {
    $id  = (int)$_REQUEST['id'];
    $aud = isset($_REQUEST['audited']) ? (int)$_REQUEST['audited'] : 1;
    $mod = D('Avatar');
    if($id < 1) $this->error('ID错误');
    elseif(!$old = $mod->find($id))
    {
      $this->error('对象不存在('.$id.')');
    }
    elseif($old['audited'] == '1')
    {
      $this->error('已被审核('.$id.')');
    }
    elseif(!$mod->audit($id,['audited' => $aud]))
    {
      $this->error('操作失败('.$id.')');
    }
    else
    {
      // 视频加权
      if($aud == 2 && $old['type'] == '3')
      {
        $rmd = D('Resource')->set_user($old['uid']);
        $usr = D(CONTROLLER_NAME)->find($old['uid']);
        $alb = $rmd->fmtNewAlbum(json_decode($usr['album'],true),true);
        if($alb)
        {
          $rmd->scoring_video(0,$rmd->get_video_num($alb));
          $mod->recommend_video($old['resource']);//推荐
          D('AccountBase')->set_field($old['uid'],'glamour_frozen',0);//解除冻结魅力值
        }
      }
      D('OperLog')->log('avatar_audit',
      [
        '资源ID' => $id,
        '类型'   => $rmd->types[$old['type']]['type'],
        '状态'   => $mod->state_audit[$aud],
      ],$old['uid']);
    }
    $this->success('操作成功('.$id.')');
  }

  // 照片删除
  public function avatar_del()
  {
    $uid = (int)$_REQUEST['uid'];
    $res = $_REQUEST['resource'];
    $rmk = trim(I('request.remark'));
    $msg = trim(I('request.msg'));
    $mod = D(CONTROLLER_NAME);
    $rmd = D('Resource')->set_user($uid);
    !is_array($res) && $res && $res = [$res];
    if($uid < 1)  $this->error('UID错误');
    elseif(!$res) $this->error('请选择照片');
    elseif(!$rmk) $this->error('备注不能为空');
    elseif(!$msg) $this->error('话术不能为空');
    elseif(!$old = $mod->find($uid))
    {
      $this->error('对象不存在('.$uid.')');
    }
    else
    {
      $alb = $rmd->fmtNewAlbum(json_decode(trim($old['album']),true),true) ?: [];
      $old_count = $rmd->get_video_num($alb);
      foreach($res ?: [] as $img)
      {
        if(!trim($img)) continue;
        if(isset($alb[$img]))
        {
          unset($alb[$img]);
          $this->aliydel('feed',$img);
        }
        $rmd->where(['uid' => $uid,'resource' => $img])->limit(1)->delete();
        D('Avatar')->where(['uid' => $uid,'resource' => $img])->limit(1)->delete();
        D('OperLog')->log('avatar_audit',[
          '删除照片',
          '备注' => $rmk,
          '话术' => $msg,
          '照片' => $img,
        ],$uid);
      }
      $album = json_encode(array_values($alb) ?: []);
      $mod->where(['uid' => $uid])->limit(1)->save(['album' => $album]);
      $mod->del_user_cache($uid);
      // 视频加权
      $new_count = $rmd->get_video_num($alb);
      if($new_count != $old_count) $rmd->scoring_video(0,$new_count);
      // 发送系统消息
      if($msg) D('Message')->add_msg_system($uid,$msg);
    }
    $this->success('操作成功');
  }

  // 清除用户历史照片
  public function avatar_clear()
  {
    $this->auth_check(CONTROLLER_NAME.'/avatar_del');
    $uid = (int)$_REQUEST['uid'];
    $mod = D(CONTROLLER_NAME);
    if($uid < 1) $this->error('UID错误');
    elseif(!$old = $mod->find($uid))
    {
      $this->error('对象不存在('.$uid.')');
    }
    else
    {
      $ava = D('Avatar');
      $log = D('OperLog');
      $alb = json_decode($old['album'],true) ?: [];
      $alb = D('Resource')->fmtNewAlbum($alb,true);
      $als = $ava->where(['uid' => $uid])->select() ?: [];
      foreach($als as $v)
      {
        $res = $v['resource'];
        if(in_array($res,$alb) || array_key_exists($res,$alb)) continue;
        $ava->where(['id' => $v['id']])->limit(1)->delete();
        $this->aliydel('feed',$res);
        $log->log('avatar_audit',[
          '删除照片',
          '备注' => '历史照片',
          '照片' => $res,
        ],$v['uid']);
      }
    }
    $this->success('操作成功');
  }

  // 修改分值
  public function set_score()
  {
    $uid = (int)$_REQUEST['uid'];
    $sco = round($_REQUEST['score'],2);
    $sco < 0  && $sco = 0;
    $sco > 10 && $sco = 10;
    $rmk = trim(I('request.remark'));
    $msg = trim(I('request.msg'));
    $mod = D(CONTROLLER_NAME);
    if(!is_numeric($_REQUEST['score'])) $this->error('分值错误');
    elseif(!$rmk)                       $this->error('备注不能为空');
    elseif(!$old = $mod->find($uid))
    {
      $this->error('对象不存在');
    }
    elseif(false === $mod->scoring($uid,$sco))
    {
      $this->error('保存失败');
    }
    else
    {
      $alb = json_decode($old['album'],true) ?: [];
      $res = trim(is_array($alb[0]) ? $alb[0]['resource'] : $alb[0]);
      $res && D('Avatar')->where(['uid' => $uid,'resource' => $res])->limit(1)->save(['score' => $sco,'score_time' => time()]);
      if(D('Avatar')->where(['uid' => $uid,'score_time' => ['egt',1]])->count('id') == 0)
      {
        // 首次打分强制曝光
        D('RpcUser')->add_go_list('force',[
          'uid'        => (int)$uid,
          'gender'     => (int)$usr['sex'],
          'force_type' => 0,
          'num'        => 0,
        ]);
      }
      D('OperLog')->log('score_modify',[
        '分值' => $old['score'].' -> '.$sco,
        '备注' => $rmk,
        '打分团备注' => trim($_REQUEST['remark_scoring']),
        '话术' => $msg,
      ],$uid);
      // 发送系统消息
      if($msg)
      {
        // 发送系统消息
        D('Message')->add_msg_scoring($uid,
        [
          'comment'  => $msg,
          'show_tip' => $sco < 6 ? 1 : 0,
        ],$mod->avatar_url_root.$res);
        //D('Message')->add_msg_system($uid,$msg);
      }
      A('Scoring')->del_run($uid);
      Model\UserAttrsModel::Instance($uid)->set_attr('remark_scoring',trim($_REQUEST['remark_scoring']));
    }
    $this->success('操作成功');
  }


  // 获取所有聊天记录 html
  public function chat_logs()
  {
    $_REQUEST['day'] || $_REQUEST['day'] = 1;
    $_REQUEST['stime'] || $_REQUEST['stime'] = date('Y-m-d',strtotime('-'.((int)$_REQUEST['day'] - 1).' days'));
    $dat = $this->get_chat_log_data();
    $ids = array_unique(array_merge(
      array_column($dat['list'] ?: [],'sender') ?: [],
      array_column($dat['list'] ?: [],'recver') ?: []
    ));
    if($ids) $dat['users'] = D(CONTROLLER_NAME)->get_users_account_byids($ids);
    //die(json_encode(compact('ids','dat')));
    $this->data = $dat;
    $this->display();
  }

  // 获取所有聊天记录 json
  public function get_chat_log_all()
  {
    $dat = $this->get_chat_log_data();
    $this->ajaxReturn($this->ret);
  }

  // 获取用户聊天记录 json
  public function get_chat_log()
  {
    $dat = $this->get_chat_log_data();
    $this->ajaxReturn($this->ret);
  }

  // 获取聊天记录 array
  protected function get_chat_log_data($where = [])
  {
    isset($_REQUEST['page_size']) || $this->page_size = 100;
    isset($_REQUEST['nosys'])     || $_REQUEST['nosys'] = 0;
    $mod = D('ChatLogBase');
    $mod->sday = (int)$_REQUEST['day'];
    $mod->eday = 0;
    $map = $mod->get_filters();
    $map = array_merge($map,$where);
    $dat = [];
    if($mod->sday - $mod->eday > 3) $this->error('查询时间范围不得超过3天');
    $dat['list'] = $mod->get_chat_log_union($map,$mod->sday,$mod->eday,$_REQUEST['nosys'] ? false : true)->plist($this->page_size)->select() ?: [];
    $dat['_sql'] = $mod->_sql();
    $this->pager = $mod->pager;
    //is_array($dat['list']) && $dat['list'] = array_reverse($dat['list']);
    is_array($dat['list']) && $dat['list'] = $mod->format_text_all($dat['list']);
    $this->data = $dat;
    $this->show_inline = !$uid;
    $this->ret['data'] = $dat;
    return $dat;
  }

  // 单个字段清空
  public function del_field()
  {
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    $key = I('request.field') ?: I('request.name');
    $rmk = trim(I('request.remark'));
    $msg = trim(I('request.msg'));
    $mod = D(CONTROLLER_NAME);
    $fds = D('UserInfoModifyRequest')->user_fields ?: [];
    $fds = array_merge($fds,
    [
      'qq_open_id' => 'QQ OpenID',
      'wx_open_id' => '微信OpenID',
    ]);
    if(!array_key_exists($key,$fds)) $this->error('参数错误');
    elseif(!$rmk) $this->error('备注不能为空');
    elseif(!$msg) $this->error('话术不能为空');
    elseif(!$old = $mod->find($uid))
    {
      $this->error('对象不存在');
    }
    elseif($key == 'qq_open_id' && !($old['wx_open_id'] || $old['phone'])) $this->error('该用户不可解绑QQ');
    elseif($key == 'wx_open_id' && !($old['qq_open_id'] || $old['phone'])) $this->error('该用户不可解绑微信');
    elseif(!$mod->where(['uid' => $uid])->limit(1)->setField($key,''))
    {
      $this->error('操作失败');
    }
    else
    {
      $mod->del_user_cache($uid);
      D('UserInfoModifyRequest')->handle($uid,$key,$rmk);
      D('OperLog')->log('text_audit',[
        '字段清空',
        '字段' => $fds[$key] ?: $key,
        '原值' => $old[$key],
        '备注' => $rmk,
        '话术' => $msg,
      ],$uid);
      // 发送系统消息
      if($msg) D('Message')->add_msg_system($uid,$msg);
    }
    $this->success('操作成功');
  }

  // 字段批量清空
  public function del_fields()
  {
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    $arr = (array)$_REQUEST['clear_info'];
    $rmk = trim(I('request.remark'));
    $msg = trim(I('request.msg'));
    $mod = D(CONTROLLER_NAME);
    $fds = D('UserInfoModifyRequest')->user_fields ?: [];
    $dat = $kys = $lgs = [];
    if(!$rmk)     $this->error('备注不能为空');
    elseif(!$msg) $this->error('话术不能为空');
    elseif(!$old = $mod->find($uid))
    {
      $this->error('对象不存在');
    }
    else
    {
      foreach(['nickname','description'] as $key) if($arr[$key])
      {
        $dat[$key] = '';
        $kys[$key] = 1;
        $lgs[] =
        [
          '字段清空',
          '字段' => $fds[$key] ?: $key,
          '原值' => $old[$key],
          '备注' => $rmk,
          '话术' => $msg,
        ];
      }
      if($hid = (int)$arr['haunt'])
      {
        $dat['job_haunt'] = preg_replace('/\b(h:)\d*/i','$1',$old['job_haunt']);
        $kys['job_haunt_character'] = 1;
        $lgs[] =
        [
          '字段清空',
          '字段' => '出没地',
          '备注' => $rmk,
          '话术' => $msg,
        ];
      }
      if($jid = (int)$arr['job'])
      {
        $dat['job_haunt'] = preg_replace('/\b(j:)\d*/i','$1',$old['job_haunt']);
        $kys['job_haunt_character'] = 1;
        $lgs[] =
        [
          '字段清空',
          '字段' => '职业',
          '备注' => $rmk,
          '话术' => $msg,
        ];
      }
      if($cls = array_keys((array)$arr['character']))
      {
        $lst = explode(',',$old['character']) ?: [];
        $dat['character'] = implode(',',array_diff($lst,$cls));
        $kys['job_haunt_character'] = 1;
        $lgs[] =
        [
          '字段清空',
          '字段' => '性格',
          '数量' => count($cls),
          '备注' => $rmk,
          '话术' => $msg,
        ];
      }
      if($ils = array_keys((array)$arr['interest']))
      {
        $lst = explode(',',$old['interest']) ?: [];
        $dat['interest'] = implode(',',array_diff($lst,$ils));
        $kys['interest'] = 1;
        $lgs[] =
        [
          '字段清空',
          '字段' => '兴趣',
          '数量' => count($ils),
          '备注' => $rmk,
          '话术' => $msg,
        ];
      }
      if(!$dat);
      elseif(!$mod->where(['uid' => $uid])->limit(1)->save($dat))
      {
        $this->error('操作失败');
      }
      else
      {
        $mod->del_user_cache($uid);
        D('UserInfoModifyRequest')->handle($uid,['in',array_keys($kys)],$rmk);
        foreach($lgs ?: [] as $v) D('OperLog')->log('text_audit',$v,$uid);
        // 发送系统消息
        if($msg) D('Message')->add_msg_system($uid,$msg);
      }
    }
    $this->success('操作成功');
  }

  // 单个字段修改
  public function set_field()
  {
    $this->auth_check(CONTROLLER_NAME.'/userinfo_update');
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'] ?: (int)$_REQUEST['pk'];
    $key = I('request.name');
    $val = I('request.value');
    $map = array('uid' => $id);
    $mod = D(CONTROLLER_NAME);
    if($id < 1)
    {
      $this->error('ID错误');
    }
    elseif(!$key)
    {
      $this->error('参数错误');
    }
    elseif(!$old = $mod->find($id))
    {
      $this->error('对象不存在');
    }

    // 设置昵称
    elseif($key == 'nickname')
    {
      if(!$val)
      {
        $this->error('昵称不能为空');
      }
      elseif(false === $mod->where($map)->limit(1)->setField($key,$val))
      {
        $this->error('保存失败');
        D('OperLog')->log('user_info_set',['修改昵称'],$uid);
      }
      else
      {
        // im...
      }
    }

    // 设置手机号
    elseif($key == 'phone')
    {
      if(!($val && preg_match('/^[12][34578]\d{9}$/i',$val)))
      {
        $this->error('手机号格式错误');
      }
      elseif($val == $old['phone'])
      {
        // 未改动
      }
      elseif($val != $old['phone'] && $mod->where(array('phone' => $val))->count('uid') > 0)
      {
        $this->error('手机号已存在');
      }
      elseif(false === $mod->where($map)->limit(1)->setField($key,$val))
      {
        $this->error('保存失败');
      }
      else
      {
        // im...
        D('OperLog')->log('user_info_set',
        [
          '修改手机号',
          '手机号' => $old['phone'].' -> '.$val,
        ],$uid);
      }
    }

    // 设置性别
    elseif($key == 'sex')
    {
      $val = (int)$val;
      if(false === $mod->auto_field($key,$val))
      {
        $this->error($mod->getError() ?: '数据错误');
      }
      elseif(false === $mod->where($map)->limit(1)->setField($key,$val))
      {
        $this->error('保存失败');
      }
      else
      {
        $sexs = C('USER_SEX_IS') ?: [];
        D('LocationBase')->where($map)->limit(1)->setField($key,$val);
        D('Rdrs')->set_table('__REAL_TIME_DATA__')->where($map)->limit(1)->setField('gender',$val);
        D('Rdrs')->set_table('__REAL_TIME_TEMP__')->where($map)->limit(1)->setField('gender',$val);
        D('OperLog')->log('user_info_set',
        [
          '修改性别',
          '性别' => ($sexs[$old['sex']] ?: '未知').' -> '.$sexs[$val],
        ],$uid);
      }
    }

    // 设置密码
    elseif($key == 'password')
    {
      $this->auth_check(CONTROLLER_NAME.'/set_password');
      if(!$val)
      {
        $this->error('密码不能为空');
      }
      elseif(false === $mod->where($map)->limit(1)->setField($key,$mod->password_encrypt($val)))
      {
        $this->error('保存失败');
      }
      else
      {
        $mod->del_user_token($id);
        D('OperLog')->log('user_info_set',['修改密码'],$uid);
      }
    }
    // 删除用户缓存
    $mod->del_user_cache($id);
    $this->success('保存成功');
  }

  // 设置特权用户
  public function set_super_user()
  {
    $uid = (int)$_REQUEST['uid'];
    $power = (int)$_REQUEST['power'] ? 1 : 0;
    $mod = D(CONTROLLER_NAME);
    if(!$mod->where(['uid' => $uid])->limit(1)->save(['power' => $power]))
    {
      $this->error('操作失败');
    }
    else
    {
      D('Message')->set_offline()->add_msg_system($uid,[
        'type' => 213,//开通特权
        'text' => '您已成为特权用户！',
      ]);
      $mod->del_user_cache($uid);
      //$mod->del_user_token($uid);
      D('OperLog')->log($power ? 'add_power' : 'del_power',[],$uid);
    }
    $this->success('操作成功');
  }

  // 赠送赞、VIP
  public function give()
  {
    $this->auth_check('pay');
    $uid = (int)$_REQUEST['uid'];
    $ids = trim($_POST['ids']);
    $is_bat = !!$ids;
    $typ = trim($_REQUEST['type']);
    $gid = (int)$_REQUEST['goods_id'];
    $num = (int)$_REQUEST['num'];
    $rmk = trim($_REQUEST['remark']);
    if($num < 1 && !in_array($typ,['diamond','glamour'])) $this->error('参数错误');
    if($ids && preg_match_all('/\b(\d{4,11})\b/',$ids,$arr))
    {
      $ids = $arr[1] ?: [];
    }
    is_array($ids) || $ids = [];
    $uid && array_push($ids,$uid);
    $uls = D('UserBase')->lists(['uid' => ['in',$ids]]) ?: [];
    $acc = D('AccountBase');
    $zan = D('UserZan');
    $log = D('OperLog');
    $cnt = 0;
    foreach($uls as $v)
    {
      $uid = (int)$v['uid'];
      if($uid < 1) continue;
      $log_typ = 'give';
      $log_nam = '';
      if($typ == 'like')
      {
        $log_nam = '赠送普赞';
        $zan->set_like_times($uid,$num,3);
        $log->log($log_typ,[$log_nam,'数量' => $num],$uid);
        $cnt++;
      }
      elseif($typ == 'super_like')
      {
        $log_nam = '赠送超赞';
        $zan->set_like_times($uid,$num,2);
        $log->log($log_typ,[$log_nam,'数量' => $num],$uid);
        $cnt++;
      }
      elseif($typ == 'vip')
      {
        $log_nam = '赠送会员';
        $acc->set_vip_days($uid,$num);
        $log->log($log_typ,[$log_nam,'天数' => $num],$uid);
        $cnt++;
      }
      elseif($typ == 'diamond')
      {
        $this->auth_check('UserBase/give_diamond');
        $log_nam = $num >= 0 ? '赠送钻石' : '扣除钻石';
        //$acc->set_diamond_inc($uid,$num);
        D('RpcApi')->call('Account/setDiamond',
        [
          'uid'     => $uid,
          'diamond' => $num,
          'remark'  => $rmk,
          'with_glory' => (int)$_REQUEST['with_glory'] ? 1 : 0,
        ]);
        $log->log($log_typ,
        [
          $log_nam,
          '数量' => $num,
          '备注' => $rmk,
          '同时增加荣耀值' => (int)$_REQUEST['with_glory'] ? '是' : '',
        ],$uid);
        $cnt++;
      }
      elseif($typ == 'glamour')
      {
        $log_nam = $num >= 0 ? '赠送魅力' : '扣除魅力';
        D('RpcApi')->call('Account/setGlamour',
        [
          'uid' => $uid,
          'num' => $num,
        ]);
        $log->log($log_typ,[$log_nam,'数量' => $num],$uid);
        $cnt++;
      }
      elseif($typ == 'prop')
      {
        $log_nam = '赠送道具';
        D('RpcApi')->call('PropStore/addProp',
        [
          'uid'      => $uid,
          'goods_id' => $gid,
          'num'      => $num,
        ]);
        $log->log($log_typ,[$log_nam,'道具ID' => $gid,'数量' => $num],$uid);
        $cnt++;
      }
      elseif($typ == 'live_effect')
      {
        $log_nam = '赠送进场特效';
        D('RpcApi')->call('PropStore/addProp',
        [
          'uid'      => (int)$uid,
          'goods_id' => (int)$gid,
          'num'      => 1,
          'days'     => (int)$num,
        ]);
        $log->log($log_typ,[$log_nam,'特效ID' => $gid,'天数' => $num],$uid);
        $cnt++;
      }
      else $this->error('请选择赠送类型');
    }
    if($log_nam && $is_bat) $log->log($log_typ,
    [
      $log_nam,
      '批量赠送',
      '导入数' => count($ids),
      '成功数' => $cnt,
    ]);
    $this->success('操作成功');
  }

  public function give_bat()
  {
    //$this->auth_check(CONTROLLER_NAME.'/give');
    $dat['count'] = D('RpcApi')->length();
    $this->data = $dat;
    $this->display();
  }


  // 订单列表
  public function order_list()
  {
    isset($_REQUEST['state']) || $_REQUEST['state'] = 2;
    $mod = D($_REQUEST['ver'] == '1' ? 'Order' : 'OrderV2')->alias('o');
    $dat = array();
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','create_time desc');
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['order_total']      = (float)$mod->alias('o')->where($map)->sum('fee');
    $dat['order_count_user'] = (int)$mod->alias('o')->where($map)->count('distinct uid');
    $dat['order_states'] = $mod->states;
    $dat['pay_types'] = $mod->pay_types;
    $dat['users'] = D(CONTROLLER_NAME)->get_users_account($dat['list']);
    if($ids = array_unique(array_column($dat['list'] ?: [],'goods_id')))
    {
      $dat['goods'] = D('Goods')->klist('id',['id' => ['in',$ids]]) ?: [];
      if($dat['goods']) $dat['list'] = array_map(function($v) use($dat)
      {
        $v['goods_name'] = $dat['goods'][$v['goods_id']]['name'];
        return $v;
      },$dat['list']);
    }
    if(trim($_REQUEST['download'])) foreach($dat['list'] ?: [] as $v)
    {
      $dat['export'][] =
      [
        '订单号'   => $v['id'],
        '用户ID'   => $v['uid'],
        '性别'     => C('USER_SEX_IS.'.$dat['users'][$v['uid']]['sex']),
        '商品名称' => $v['goods_name'] ?: $v['goods_id'] ?: '',
        '交易金额' => $v['fee'],
        '支付方式' => $dat['pay_types'][$v['pay_type']],
        '交易状态' => $dat['order_states'][$v['state']],
        '下单时间' => $v['create_time'] ? date('Y-m-d H:i:s',$v['create_time']) : '',
        '付款时间' => $v['pay_time'] ? date('Y-m-d H:i:s',$v['pay_time']) : '',
        '备注'     => $v['remark'],
      ];
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }

  public function order_top()
  {
    $mod = D($_REQUEST['ver'] == '1' ? 'Order' : 'OrderV2');
    $dat = [];
    $map = $mod->get_filters();
    $dat['list'] = $mod
      ->field(
      [
        'uid',
        'count(id)'        => 'cnt',
        'sum(fee)'         => 'fee',
        'max(create_time)' => 'last_time',
      ])
      ->group('uid')
      ->plist($this->page_size,$map)
      ->lists('','fee desc,cnt desc');
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['order_states'] = $mod->states;
    $dat['users'] = D(CONTROLLER_NAME)->get_users_account($dat['list']);
    if(trim($_REQUEST['download'])) foreach($dat['list'] ?: [] as $v)
    {
      $dat['export'][] =
      [
        '用户ID' => $v['uid'],
        '性别'   => C('USER_SEX_IS.'.$dat['users'][$v['uid']]['sex']),
        '总金额' => $v['fee'],
        '订单数' => $v['cnt'],
        '最后下单时间' => $v['cnt'],
      ];
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }

  public function order_del()
  {
  }


  // 钻石订单列表
  public function order_diamond_list()
  {
    $mod = D('OrderDiamond')->alias('o');
    $dat = array();
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','create_time desc');
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['order_total']      = (float)$mod->alias('o')->where($map)->sum('fee');
    $dat['order_count_user'] = (int)$mod->alias('o')->where($map)->count('distinct uid');
    $dat['order_states'] = $mod->states;
    $dat['pay_types'] = $mod->pay_types;
    $dat['users'] = D(CONTROLLER_NAME)->get_users_account($dat['list']);
    if($ids = array_unique(array_column($dat['list'] ?: [],'goods_id')))
    {
      $dat['goods'] = D('Goods')->klist('id',['id' => ['in',$ids]]) ?: [];
      if($dat['goods']) $dat['list'] = array_map(function($v) use($dat)
      {
        $v['goods_name'] = $dat['goods'][$v['goods_id']]['name'];
        return $v;
      },$dat['list']);
    }
    if(trim($_REQUEST['download'])) foreach($dat['list'] ?: [] as $v)
    {
      $dat['export'][] =
      [
        '订单号'   => $v['id'],
        '用户ID'   => $v['uid'],
        '性别'     => C('USER_SEX_IS.'.$dat['users'][$v['uid']]['sex']),
        '商品名称' => $v['goods_name'] ?: $v['goods_id'] ?: '',
        '交易金额' => $v['fee'],
        '支付方式' => $dat['pay_types'][$v['pay_type']],
        '交易状态' => $dat['order_states'][$v['state']],
        '下单时间' => $v['create_time'] ? date('Y-m-d H:i:s',$v['create_time']) : '',
        '付款时间' => $v['pay_time'] ? date('Y-m-d H:i:s',$v['pay_time']) : '',
        '备注'     => $v['remark'],
      ];
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display('order_list');
  }


  // 红包、金赞列表
  public function coupon_list()
  {
    $mod = D('Coupon');
    $dat = [];
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','create_time desc');
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['states'] = $mod->states;
    $dat['types']  = $mod->types;
    $uis = array_unique(array_column($dat['list'],'uid') ?: []);
    $ois = array_unique(array_column($dat['list'],'oid') ?: []);
    $ids = array_values(array_unique(array_merge($uis,$ois)) ?: []);
    $dat['cnt_user'] = $mod->where($map)->count('distinct uid');
    $dat['users'] = D(CONTROLLER_NAME)->get_users_account_byids($ids);
    $dat['gifts'] = Model\GoodsModel::$goods_gift ?: [];
    $dat['goods'] = D('Goods')->klist() ?: [];
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }


  // 余额明细
  public function fee_record()
  {
    $mod = D('FeeRecord')->alias('f');
    $dat = array();
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)
      ->field(array('f.*','o.pay_type'))
      ->join('left join __ORDER__ o on o.id = f.order_id')
      ->lists('','f.create_time desc,f.id desc');
    $dat['fee_record_types'] = $mod->types;
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->pager = $mod->pager;
    $dat['fee_total'] = (float)$mod->alias('f')->where($map)->sum('fee');
    if(trim($_REQUEST['download'])) foreach($dat['list'] ?: [] as $v)
    {
      $dat['export'][] =
      [
        '流水号'   => $v['serial_no'],
        '用户ID'   => $v['uid'],
        '收支类型' => $dat['fee_record_types'][$v['pay_type']],
        '金额'     => $v['fee'],
        '余额'     => $v['balance'],
        '对方用户' => $v['oid'],
        '记录时间' => $v['create_time'] ? date('Y-m-d H:i:s',$v['create_time']) : '',
        '备注'     => $v['remark'],
      ];
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }


  // 魅力明细
  public function glamour_record()
  {
    $mod = D('GlamourRecord');
    $dat = [];
    $map = $mod->get_filters();
    $dat['list'] = $mod->get_all((int)$_REQUEST['uid'],$map)
      ->plist($this->page_size)
      ->lists('','create_time desc,id desc');
    $dat['types'] = $mod->types;
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->pager = $mod->pager;
    $dat['fee_total'] = (float)$mod->where($map)->sum('glamour');
    $dat['users'] = D(CONTROLLER_NAME)->get_users_account($dat['list'],'uid,oid');
    if(trim($_REQUEST['download'])) foreach($dat['list'] ?: [] as $v)
    {
      $dat['export'][] =
      [
        '用户ID'   => $v['uid'],
        '收支类型' => $dat['types'][$v['type']],
        '金额'     => $v['glamour'],
        '余额'     => $v['balance'],
        '对方用户' => $v['oid'],
        '记录时间' => $v['create_time'] ? date('Y-m-d H:i:s',$v['create_time']) : '',
        '备注'     => $v['remark'],
      ];
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }


  // 钻石明细
  public function diamond_record()
  {
    $mod = D('DiamondRecord');
    $dat = [];
    $map = $mod->get_filters();
    $dat['list'] = $mod->get_all((int)$_REQUEST['uid'],$map)
      ->plist($this->page_size)
      ->lists('','create_time desc,id desc');
    $dat['types'] = $mod->types;
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->pager = $mod->pager;
    $dat['fee_total'] = (float)$mod->where($map)->sum('diamond');
    $dat['users'] = D(CONTROLLER_NAME)->get_users_account($dat['list'],'uid,oid');
    if(trim($_REQUEST['download'])) foreach($dat['list'] ?: [] as $v)
    {
      $dat['export'][] =
      [
        '用户ID'   => $v['uid'],
        '收支类型' => $dat['types'][$v['type']],
        '金额'     => $v['diamond'],
        '余额'     => $v['balance'],
        '对方用户' => $v['oid'],
        '记录时间' => $v['create_time'] ? date('Y-m-d H:i:s',$v['create_time']) : '',
        '备注'     => $v['remark'],
      ];
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }


  // 赞、超赞记录
  public function like_list()
  {
    $mod = D('Like');
    $dat = [];
    $map = $mod->get_filters();
    $dat['list'] = $mod->field('id,uid,oid,like_type,like_time,matched')
      ->plist($this->page_size,$map)
      ->lists('','like_time desc,id desc') ?: [];
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['like_types'] = $mod->types;
    $uis = array_unique(array_column($dat['list'],'uid') ?: []);
    $ois = array_unique(array_column($dat['list'],'oid') ?: []);
    $ids = array_values(array_unique(array_merge($uis,$ois)) ?: []);
    if($ids)
    {
      $dat['matchs'] = D('Match')->get_list_byuoids($uis,$ois);
      $dat['users']  = D(CONTROLLER_NAME)->get_users_account_byids($ids);
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }


  // 匹配记录
  public function match_list()
  {
    $mod = D('Match');
    $dat = [];
    $map = $mod->get_filters();
    $this->uid && $mod->set_user($this->uid);
    $dat['list'] = $mod->field('id,uid,oid,match_type,create_time')
      ->plist($this->page_size,$map)
      ->lists('','create_time desc,id desc');
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['types'] = $mod->types;
    $ids = array_merge(
      array_column($dat['list'] ?: [],'uid') ?: [],
      array_column($dat['list'] ?: [],'oid') ?: []
    );
    $ids = array_values(array_unique($ids) ?: []);
    if($ids) $dat['users'] = D(CONTROLLER_NAME)->get_users_account_byids($ids);
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }


  // 不喜欢我的列表
  public function dislikeme_list()
  {
    $uid = (int)$_REQUEST['uid'];
    $mod = D(CONTROLLER_NAME);
    $ids = D('Like')->set_user($uid)->get_dislikeme_uids() ?: [];
    if($ids)
    {
      $dat['list'] = $mod->field('uid,nickname,phone,sex,score,reg_time')
        ->plist($this->page_size,['uid' => ['in',$ids]])
        ->lists('','reg_time desc,uid desc');
      $this->pager = $mod->pager;
      $this->page  = $dat['page_html'] = $mod->pager->show();
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }


  // 用户推荐列表
  public function recommend_list()
  {
    $uid = (int)$_REQUEST['uid'];
    $mod = D(CONTROLLER_NAME);
    $lst = D('Like')->set_user($uid)->get_recommend_list() ?: [];
    if($ids = array_keys($lst))
    {
      $dat['list'] = $mod->field('uid,nickname,phone,sex,score,reg_time')
        ->plist($this->page_size,['uid' => ['in',$ids]])
        ->lists('','reg_time desc,uid desc');
      $this->pager = $mod->pager;
      $this->page  = $dat['page_html'] = $mod->pager->show();
    }
    $this->data = $dat;
    //die(json_encode(compact('lst','ids','dat')));
    $this->display('dislikeme_list');
  }


  // 用户滑动记录
  public function slide_list()
  {
    $uid = (int)$_REQUEST['uid'];
    $mod = D('Like');
    $dat['list'] = $mod->set_user($uid)->get_slide_list($this->page_size) ?: [];
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $this->data = $dat;
    //die(json_encode(compact('dat')));
    $this->display('dislikeme_list');
  }


  // 解除匹配记录
  public function black_list()
  {
    $mod = D('BlackBase');
    $dat = [];
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','add_time desc,id desc');
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $uids = array_column($dat['list'] ?: [],'uid') ?: [];
    $oids = array_column($dat['list'] ?: [],'oid') ?: [];
    $ids = array_merge($uids,$oids);
    $ids = array_values(array_unique($ids) ?: []);
    if($ids)
    {
      $dat['users']  = D(CONTROLLER_NAME)->get_users_account_byids($ids);
      $arr = D('Match')->where(['uid' => ['in',$uids],'oid' => ['in',$oids]])->select() ?: [];
      foreach($arr as $v)
      {
        $dat['matchs'][$v['uid']][$v['oid']] = $v;
      }
    }
    if(trim($_REQUEST['download'])) foreach($dat['list'] ?: [] as $v)
    {
      $dat['export'][] =
      [
        'ID'       => $v['id'],
        '用户ID'   => $v['uid'],
        '昵称'     => $data['users'][$v['uid']]['nickname'],
        '对方ID'   => $v['oid'],
        '时间'     => $v['add_time'] ? date('Y-m-d H:i:s',$v['add_time']) : '',
        '原因'     => $v['reason'],
      ];
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }


  // 提现记录
  public function cash_list()
  {
    $mod = D('WithdrawCash');
    $dat = array();
    $map = $mod->get_filters();
    $ord = $_REQUEST['time_type'] == 'finish' ? 'finish_time desc,id desc' : 'create_time desc,id desc';
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('',$ord) ?: [];
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['cash_total']  = (float)$mod->where($map)->sum('fee_cash');
    $dat['cash_states'] = $mod->states;
    $dat['pay_types']   = D('Order')->pay_types;
    $dat['cash_queues'] = $mod->getAllQueue();
    if($ids = array_unique(array_column($dat['list'],'uid')))
    {
      $dat['users'] = D(CONTROLLER_NAME)->get_users_account($dat['list']);
      $sql = $mod
        ->field(['max(id)' => 'id'])
        ->where(['uid' => ['in',$ids],'state' => Model\WithdrawCashModel::STATE_FAILED/*失败*/])
        ->group('uid')
        ->buildSql();
      $dat['prev_list'] = $mod->klist('uid',['id' => ['exp','in '.$sql]]);
      $dat['hosts'] = D('LiveHost')->get_by_list($dat['list']);
      $dat['contract_types'] = D('LiveContractType')->get_all() ?: [];
    }
    if($als = array_unique(array_column($dat['list'],'pay_account')))
    {
      $dat['account_users'] = $mod->field(
      [
        'pay_account',
        'count(id)'           => 'cnt',
        'count(distinct uid)' => 'cnt_user',
      ])
      ->group('pay_account')
      ->klist('pay_account',['pay_account' => ['in',$als]]);
    }
    if(trim($_REQUEST['download'])) foreach($dat['list'] ?: [] as $v)
    {
      $dat['export'][] =
      [
        'ID'       => $v['id'],
        '用户ID'   => $v['uid'],
        '性别'     => C('USER_SEX_IS.'.$dat['users'][$v['uid']]['sex']),
        '提现金额' => $v['fee_cash'],
        '支付方式' => $dat['pay_types'][$v['pay_type']],
        '提现账号' => $v['pay_account'],
        '真实姓名' => $v['pay_name'],
        '状态'     => $dat['cash_states'][$v['state']],
        '申请时间' => $v['create_time'] ? date('Y-m-d H:i:s',$v['create_time']) : '',
        '完成时间' => $v['finish_time'] ? date('Y-m-d H:i:s',$v['finish_time']) : '',
        '失败原因' => $v['reason'],
      ];
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }

  public function cash_top()
  {
    $mod = D('WithdrawCash');
    $dat = [];
    $map = $mod->get_filters();
    $ord = ['cnt' => 'desc','fee' => 'desc'];
    $_REQUEST['order'] == 'fee' && $ord = array_reverse($ord);
    $dat['list'] = $mod
      ->field(
      [
        'uid',
        'count(id)'     => 'cnt',
        'sum(fee_cash)' => 'fee',
      ])
      ->group('uid')
      ->having('cnt >= 2')
      ->plist($this->page_size,$map)
      ->lists('',$ord);
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['cash_states'] = $mod->states;
    $dat['users'] = D(CONTROLLER_NAME)->get_by_list($dat['list'],'uid,nickname,phone,sex');
    if(trim($_REQUEST['download'])) foreach($dat['list'] ?: [] as $v)
    {
      $dat['export'][] =
      [
        '用户ID'     => $v['uid'],
        '性别'       => C('USER_SEX_IS.'.$dat['users'][$v['uid']]['sex']),
        '提现次数'   => $v['cnt'],
        '提现总金额' => $v['fee'],
      ];
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }

  // 提现付款确认
  public function cash_confirm()
  {
    $this->auth_check('cash');
    $mod = D('WithdrawCash');
    $ids = (array)$_REQUEST['ids'];
    if(!$ids) $this->error('请选择记录');
    elseif(!($arr = $mod->where(['id' => ['in',$ids],'state' => 0,'pay_type' => 1])->select())) $this->error('未找到记录');
    else
    {
      $dat = ['list' => $arr];
      $dat['params'] = $mod->fomart_alipay_data($arr);
      $dat['cash_states'] = $mod->states;
      $dat['pay_types'] = D('Order')->pay_types;
      $this->data = $dat;
    }
    $this->display();
  }

  // 导出批量提现数据
  public function cash_download()
  {
    $this->auth_check('cash');
    $mod = D('WithdrawCash');
    $ids = (array)$_REQUEST['ids'];
    if(!$ids) $this->error('请选择记录');
    elseif(!($arr = $mod->where(['id' => ['in',$ids],'state' => 0,'pay_type' => 1])->select())) $this->error('未找到记录');
    else
    {
      $csv = $mod->get_alipay_csv($arr);
      if($csv['data'])
      {
        //$mod->where(['id' => ['in',$ids],'state' => 0])->save(['state' => 1]);//处理中
        layout(false);
        header('Content-Disposition: attachment; filename=cash.alipay.'.$csv['batch_no'].'.csv');
        $this->show($csv['data'],'utf-8','text/csv');
        die;
      }
      $this->data = $dat;
    }
    $this->error('操作失败');
  }

  // 提现付款提交
  public function cash_submit()
  {
    $this->auth_check('pay');
    $this->auth_check('cash');
    $mod = D('WithdrawCash');
    $ids = (array)$_REQUEST['ids'];
    if(!$ids) $this->error('请选择记录');
    elseif(!($arr = $mod->where(['id' => ['in',$ids],'state' => 0])->select())) $this->error('未找到记录');
    else
    {
      $dat = ['list' => $arr];
      $dat['params'] = $mod->fomart_alipay_data($arr);
      if($dat['params'])
      {
        $htm = $mod->get_alipay_form($dat['params']);
        $dat['html_form'] = $htm;
        $mod->where(['id' => ['in',$ids],'state' => 0])->save([
          'state'    => 1,//处理中
          'batch_no' => $dat['params']['batch_no'],
        ]);
        D('OperLog')->log('cash_submit',[
          '批次号' => $dat['params']['batch_no'],
          '总金额' => $dat['params']['batch_fee'],
          '总笔数' => $dat['params']['batch_num'],
        ]);
      }
      $this->data = $dat;
    }
    $htm = $dat['html_form'] ? ('<html><body>'.$dat['html_form'].'</body></html>') : '意外错误';
    //die(json_encode($dat));
    layout(false);
    $this->show($htm,'utf-8','text/html');die;
    $this->display();
  }

  // 处理提现
  public function cash_handle()
  {
    $this->auth_check('pay');
    $this->auth_check('cash');
    $id  = (int)$_REQUEST['id'];
    $mod = D('WithdrawCash');
    if(!$old = $mod->find($id))            $this->error('对象不存在');
    elseif(!in_array($old['state'],[0,1])) $this->error('不可操作');
    elseif(!$mod->cashByWeixin($old))
    {
      $this->error('操作失败');
    }
    $this->success('操作成功','',0);
  }

  // 设置提现状态
  public function cash_set_state()
  {
    $this->auth_check('pay');
    $this->auth_check('cash');
    $id  = (int)$_REQUEST['id'];
    $dat = [
      'state'       => (int)$_REQUEST['state'],
      'reason'      => I('request.reason'),
      'finish_time' => NOW_TIME,
    ];
    $msg = I('request.msg');
    $mod = D('WithdrawCash');
    if(!$dat['reason'])             $this->error('原因不能为空');
    elseif(!$msg)                   $this->error('话术不能为空');
    elseif(!$old = $mod->find($id)) $this->error('对象不存在');
    elseif(in_array($dat['state'],$mod->states)) $this->error('状态错误');
    elseif(!in_array($old['state'],[0,1]))       $this->error('不可操作');
    elseif(!$mod->where(['id' => $id])->limit(1)->save($dat))
    {
      $this->error('操作失败');
    }
    else
    {
      $uid = (int)$old['uid'];
      // 提现不通过
      if($dat['state'] == 3)
      {
        \Think\Log::write('提现审核:不通过:'.json_encode([$uid,$dat,$old,NOW_TIME])."\n\n");
        $acc = D('AccountBase');
        // 魅力兑换
        if($old['glamour'])
        {
          $ret = $acc->set_glamour_inc($uid,$old['glamour'],[
            'type'   => Model\GlamourRecordModel::TYPE_INCOME_REFUND,//系统退款
            'remark' => '提现失败退还',
          ]);
        }
        // 余额提现 老版本
        else
        {
          $ret = $acc->set_balance_inc($uid,$old['fee'],[
            'type'   => Model\FeeRecordModel::TYPE_INCOME_REFUND,//系统退款
            'remark' => '提现失败退款',
          ]);
        }
        if($ret === false) $this->error($acc->getError() ?: '操作失败');
        $acc->set_dec($uid,'total_cash',$old['fee_cash']);
        if($old['glamour']) $acc->set_dec($uid,'total_cash_glamour',$old['glamour']);
      }
      // 提现已完成
      elseif($dat['state'] == 2)
      {
        \Think\Log::write('提现审核:已完成:'.json_encode([$uid,$dat,$old,NOW_TIME])."\n\n");
        @D('Rpc')->add_go_list('cash',
        [
          'items'       => [['uid' => $uid,'sum' => (float)$old['fee_cash']]],
          'update_time' => time(),
        ]);
      }
      D('OperLog')->log('cash_set_state',
      [
        '记录ID' => $id,
        '状态'   => $mod->states[$old['state']].' -> '.$mod->states[$dat['state']],
        '原因'   => $dat['reason'],
        '话术'   => $msg,
      ],$old['uid']);
      if($msg) @D('Message')->add_msg_system($uid,$msg);
      ob_start();
      var_dump(Model\RpcModel::$redis_instances);
      $obc = ob_get_contents();
      ob_end_clean();
      \Think\Log::write("\n".$obc."\n\n");
    }
    $this->success('操作成功','',0);
  }


/*

redis -h redisuser.chujianapp.com -p 6379 -a c30690277da3464f:Lhapp123
list lPop php_list_illegal_text
[
  {
    "type" : 'msg',//nickname|description|interest|job_haunt_character|msg|msg_repeat|msg_warn
    "uid"  : 12200022,
    "text" : '文本',
    "times": 2,
    "time" : 1444444444
  },
  {
    "type" : 'nickname',
    "uid"  : 12200022,
    "text" : '文本2',
    "time" : 1444444444
  }
]

*/
  public function illegal_text_queue()
  {
    $mod = D('UserInfoModifyRequest');
    $trm = D('TextRepeatLog');
    $swm = D('SensitiveWords');
    $clm = D('ChatLogBase');
    $umd = D(CONTROLLER_NAME);
    $rds = $mod->get_redis();
    $key = 'php_list_illegal_text';
    $dat['count'] = $rds->lLen($key);
    $lst = $als = [];
    $max = 20;
    for($i = 0;$i < $max;$i++)
    {
      $jss = $rds->lPop($key);
      $row = json_decode($jss,true) ?: [];
      $txt = $row['text'];
      $typ = $row['type'];
      $uid = (int)$row['uid'];
      $dat['count-'.$typ]++;
      if(in_array($typ,['msg','msg_repeat','msg_warn']))
      {
        $txt = $clm->get_msg_text($txt);
        $ret = !!$txt;
        if($typ == 'msg') $ret = false;//取消聊天关键词匹配
        if($row['time'] <= strtotime('-7 days')) $ret = false;
        if(!$ret)
        {
          if($max < 1000) $max++;
          continue;
        }
        // 重复聊天内容
        if($typ == 'msg_repeat')
        {
          $ret = $trm->save_row(
          [
            'uid'   => $uid,
            'type'  => $typ,
            'text'  => $txt,
            'times' => (int)$row['times'],
            'update_time' => (int)$row['time'],
          ]);
          if($ret) $lst[] = array_merge($row,
          [
            'text_checked' => $txt.'【'.$row['times'].'次】',
            'avatar'       => U('avatar?uid='.$uid),
          ]);
          continue;
        }
        // 聊天内容含数字
        elseif($typ == 'msg_warn')
        {
          $ret = $trm->save_row(
          [
            'uid'   => $uid,
            'type'  => $typ,
            'text'  => '连续多条聊天包含5位或以上数字！',
            'times' => (int)$row['times'],
          ]);
          if($ret) $lst[] = array_merge($row,
          [
            'text_checked' => $txt.'【'.$row['times'].'次】',
            'avatar'       => U('avatar?uid='.$uid),
          ]);
          $usr = $umd->get_user_cache($uid);
          $als[] =
          [
            'uid'         => $uid,
            'sex'         => (int)$usr['sex'],
            'field_name'  => $typ,
            'field_value' => htmlspecialchars($txt),
            'has_illegal' => 1,
            'sub_time'    => date('Y-m-d H:i:s',$row['time'] ?: time()),
          ];
          continue;
        }
      }
      $ret = !!$txt;
      if($ret)
      {
        $usr = $umd->get_user_cache($uid);
        $adt =
        [
          'uid'         => $uid,
          'sex'         => (int)$usr['sex'],
          'field_name'  => $typ,
          'field_value' => htmlspecialchars($txt),
          'has_illegal' => 0,
          'sub_time'    => date('Y-m-d H:i:s',$row['time'] ?: time()),
        ];
        $cdt = $swm->check_text($txt,'txt');
        $ret = !$cdt['ret'];
        if($ret)
        {
          $adt['has_illegal']  = 1;
          $row['text']         = $txt;
          $row['text_checked'] = $cdt['checked'];
          $row['avatar']       = U('avatar?uid='.$uid);
          $lst[] = $row;
        }
        $als[] = $adt;
        //@header('debug-illegal: '.json_encode(compact('txt','row','cdt')));
      }
      if(!$ret)
      {
        if($max < 100) $max++;
        continue;
      }
    }
    $mod->add_all($als);
    $dat['list'] = $lst;
    $this->data = $dat;
    //die(json_encode($dat));
    $this->success($dat);
  }

  // 文字审核
  public function text_modify_request()
  {
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    $mod = D('UserInfoModifyRequest');
    $dat = array();
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','sub_time desc,id desc');
    $dat['list'] = $mod->format_fields_all('field_value',$dat['list']);
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $mod->pager->show();
    $dat['user_fields'] = $mod->user_fields;
    $dat['operation_status'] = $mod->operation_status;
    $dat['users'] = D(CONTROLLER_NAME)->get_users_account($dat['list']);
    $dat['hosts'] = D('LiveHost')->get_by_list($dat['list']);
    $dat['contract_types'] = D('LiveContractType')->get_all() ?: [];
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  // 文字审核通过
  public function text_modify_pass()
  {
    $id  = (int)$_REQUEST['id'];
    $uid = (int)$_REQUEST['uid'];
    $dat = [
      'operation' => 1,
      'pass_time' => date('Y-m-d H:i:s'),
      'result'    => '审核通过',
    ];
    $mod = D('UserInfoModifyRequest');
    if(!$old = $mod->find($id))
    {
      $this->error('对象不存在');
    }
    if(!$mod->where(['id' => $id])->limit(1)->save($dat))
    {
      $this->error('操作失败');
    }
    else
    {
      D('OperLog')->log('text_audit',[$mod->user_fields[$old['field_name']] => $old['field_value']],$old['uid']);
    }
    $this->success('操作成功');
  }

  // 历史文字清除
  public function text_modify_clear()
  {
    $mod = D('UserInfoModifyRequest');
    $sql = $mod->field(['max(id)' => 'id'])->where(['operation' => 0])->group('uid,field_name')->buildSql();
    /* You can't specify target table for update in FROM clause */
    $sql = M()->table($sql.' tmp')->field('id')->buildSql();
    $map =
    [
      'operation' => 0,
      'id'        => ['exp','not in '.$sql],
    ];
    $num = $mod->where($map)->save(
    [
      'operation' => 1,
      'pass_time' => date('Y-m-d H:i:s'),
      'result'    => '审核通过',
    ]);
    $num && D('OperLog')->log('text_audit',
    [
      '批量清理历史数据',
      '影响条数' => $num,
    ]);
    $this->success('操作成功，影响'.$num.'条记录',U('text_modify_request?operation=0'));
  }

  // 字段批量清空
  public function text_modify_clear_bat()
  {
    $ids = array_keys((array)$_REQUEST['ids']) ?: [];
    $rmk = trim(I('request.remark'));
    $msg = trim(I('request.msg'));
    $mod = D(CONTROLLER_NAME);
    $umr = D('UserInfoModifyRequest');
    $fds = $umr->user_fields ?: [];
    if(!$rmk)     $this->error('备注不能为空');
    elseif(!$msg) $this->error('话术不能为空');
    elseif(!$rls = $umr->lists(['id' => ['in',$ids]]))
    {
      $this->error('对象不存在');
    }
    elseif(!$uls = $mod->get_by_list($rls))
    {
      $this->error('用户不存在');
    }
    else
    {
      foreach($rls as $v)
      {
        $uid = (int)$v['uid'];
        $usr = $uls[$uid] ?: [];
        $key = $v['field_name'];
        if(!$usr) continue;
        if(!$mod->where(['uid' => $uid])->limit(1)->setField($key,'')) continue;
        $mod->del_user_cache($uid);
        $umr->where(['uid' => $uid,'field_name' => $key,'operation' => 0])->save([
          'operation' => 2,
          'aid'       => (int)$_SESSION[C('USER_AUTH_KEY')],
          'result'    => $rmk,
          'pass_time' => date('Y-m-d H:i:s'),
        ]);
        D('OperLog')->log('text_audit',
        [
          '字段清空',
          '字段' => $fds[$key] ?: $key,
          '原值' => $usr[$key],
          '备注' => $rmk,
          '话术' => $msg,
        ],$uid);
        // 发送系统消息
        if($msg) D('Message')->add_msg_system($uid,$msg);
      }
      D('OperLog')->log('text_audit',
      [
        '字段批量清空',
        '总数量' => count($rls),
        '用户数' => count($uls),
        '备注' => $rmk,
        '话术' => $msg,
      ]);
    }
    $this->success('操作成功');
  }

  // 聊天内容重复
  public function text_repeat()
  {
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    $mod = D('TextRepeatLog');
    $dat = [];
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','times desc,update_time,id desc');
    $dat['list'] = D('UserInfoModifyRequest')->format_fields_all('text',$dat['list']);
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $mod->pager->show();
    $dat['users'] = D(CONTROLLER_NAME)->get_users_account($dat['list']);
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function text_repeat_pass()
  {
    $id  = (int)$_REQUEST['id'];
    $dat =
    [
      'handle_time' => time(),
      'remark'      => '审核通过',
    ];
    $mod = D('TextRepeatLog');
    if(!$old = $mod->find($id))
    {
      $this->error('对象不存在');
    }
    if(!$mod->where(['id' => $id])->limit(1)->save($dat))
    {
      $this->error('操作失败');
    }
    else
    {
      D('OperLog')->log('text_audit',
      [
        '重复聊天内容审核',
        '内容' => $old['text'],
        '次数' => $old['times'],
      ],$old['uid']);
    }
    $this->success('操作成功');
  }


  // 处罚中的用户列表
  public function punished()
  {
    $mod = D(CONTROLLER_NAME);
    $acc = D('AccusationBaseLog');
    $map = $mod->get_filters(true);
    $map = array_merge($map,[
      'dblocking_time' => ['egt',time()],
      'type'           => ['in',[2,3]],
    ]);
    $dat['list'] = $mod->field('uid,nickname,sex,phone,reg_time')
      ->plist($this->page_size,$map)
      ->lists('','reg_time desc,uid desc');
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['states'] = $acc->accusation_states;
    if($ids = array_unique(array_column($dat['list'],'uid')))
    {
      $sql = $acc->field('max(id) as id_max')->where(
      [
        'oid'    => ['in',$ids],
        'status' => ['in',[1,2,3,4,5,6]],
      ])->group('oid')->buildSql();
      $dat['logs'] = $acc->table($sql)->alias('g')->field('a.*')
        ->join('left join __ACCUSATION_BASE_LOG__ a on a.id = g.id_max')
        ->klist('oid');
      $dat['admins'] = D('Admin')->get_by_list($dat['logs'],'aid,nickname');
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  // 封禁日志
  public function accusation_logs()
  {
    $mod = D('AccusationBaseLog');
    $dat['list'] = $mod->plist(C('ITEMS_PER_PAGE'),$map)->lists('','create_time desc');//C('ITEMS_PER_PAGE')
    $this->page = $dat['page_html'] = $mod->pager->show();
    $dat['accusation_states']  = $mod->accusation_states;
    $dat['accusation_reasons'] = $mod->accusation_reasons;
    $dat['accusation_admins']  = $mod->get_accusation_admins($dat['list'],'aid,nickname');
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  // 警告、封禁用户
  public function closure()
  {
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    $rid = (int)$_REQUEST['report_id'];
    $status = (int)$_REQUEST['status'];
    $reason = trim($_REQUEST['reason']);
    $remark = I('request.remark');
    $msg = I('request.msg');
    $m2r = (int)$_REQUEST['msg2reporter'];
    $usr = D(CONTROLLER_NAME);
    $acc = D('AccusationBaseLog');
    $dat = array();
    if($rid >= 1)
    {
      $rep = D('ReportBase');
      $dat['report'] = $rep->find($rid) ?: array();
      $rid = (int)$dat['report']['id'];
      if($rid) $uid = (int)$dat['report']['offender_uid'];
    }
    $log = array(
      'report_id'   => $rid,
      'uid'         => (int)$dat['report']['uid'],
      'oid'         => $uid,//被封禁人ID
      'status'      => $status,//封禁状态
      'reason'      => $reason,
      'remark'      => $remark,
      'msg'         => $msg,
      'report_time' => (int)$dat['report']['dtime'],
    );
    $rpt = array(
      'status' => 3,//举报已处理并警告
      'reason' => $reason,
      'remark' => $remark,
      'atime'  => time(),
    );
    if($uid < 1)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '用户ID错误';
    }
    elseif(trim($_REQUEST['status']) == '')
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '请选择处理类型';
    }
    // 处罚中不处理
    elseif($status == -2)
    {
      $rpt['status'] = 5;
      $rpt['remark'] = $log['remark'] = $remark ?: '已处罚不再处罚';
    }
    elseif(!$remark)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '备注不能为空';
    }
    // 拒绝受理
    elseif($status == 0)
    {
      $rpt['status'] = 4;
    }
    // 已做其他处理
    elseif($status == -3)
    {
      $rpt['status'] = 1;
    }
    // 解除惩罚
    elseif($status == -1)
    {
      $this->ret = array_merge($this->ret,$usr->closure($uid,$status) ?: []);
    }
    // 永久封禁
    elseif($status == 5)
    {
      $rpt['status'] = 2;
      $this->ret = array_merge($this->ret,$usr->closure($uid,$status) ?: []);
    }
    // 同时封禁设备
    elseif($status == 6)
    {
      $rpt['status'] = 2;
      $did = $usr->where(['uid' => $uid])->getField('device_id');
      $did && $usr->closure_device($did);
      $this->ret = array_merge($this->ret,$usr->closure($uid,5) ?: []);
      D('OperLog')->log('closure',
      [
        '封禁设备',
        '设备ID'   => $did,
      ],$uid);
    }
    elseif(!$msg)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '话术不能为空';
    }
    else
    {
      $this->ret = array_merge($this->ret,$usr->closure($uid,$status) ?: []);
      //die(json_encode($this->ret));
    }
    if(!$this->ret['ret'])
    {
      if($msg) D('Message')->add_msg_system($uid,$msg);
      if($rid >= 1 && $dat['report']['status'] == 0)
      {
        // 举报处理
        $ret = $rep->where(['id' => $rid])->limit(1)->save($rpt);
        if($m2r)
        {
          $unm = $usr->where(['uid' => $dat['report']['offender_uid']])->getField('nickname');
          $msg2 = $rep->get_report_msg($rpt['status'],$unm);
          if($msg2) D('Message')->add_msg_system($dat['report']['uid'],$msg2);//反馈消息给举报人
        }
        D('OperLog')->log('report_handle',
        [
          '状态' => $acc->accusation_states[$status],
          '备注' => $remark,
          '话术' => $msg ?: $msg2,
        ],$uid);
        alog(array_merge(['report_handle',$rid,$ret,$msg,$msg2],$rpt));
      }
      else
      {
        D('OperLog')->log($status == -1 ? 'unclosure' : 'closure',
        [
          '状态' => $acc->accusation_states[$status],
          '备注' => $remark,
          '话术' => $msg,
        ],$uid);
      }
      // 文字审核处理
      D('UserInfoModifyRequest')->handle($uid,'msg',$remark);
      $acc->log($log);
    }
    $this->ret['ret'] ? $this->error($this->ret['msg'] ?: '操作失败') : $this->success($this->ret['msg'] ?: '操作成功');
  }

  // 批量封禁
  public function closure_bat()
  {
    $this->auth_check(CONTROLLER_NAME.'/closure');
    $ids = array_keys((array)$_REQUEST['ids']) ?: [];
    $rmk = I('request.remark');
    $has_device = !!(int)$_REQUEST['has_device'];
    $mod = D(CONTROLLER_NAME);
    if(!$ids)     $this->error('请选择对象');
    elseif(!$rmk) $this->error('备注不能为空');
    elseif(!$uls = $mod->lists(['uid' => ['in',$ids]]))
    {
      $this->error('对象不存在');
    }
    else
    {
      $acc = D('AccusationBaseLog');
      $sta = 5;
      $rmk || $rmk = '批量封禁';
      foreach($uls as $v)
      {
        $uid = (int)$v['uid'];
        $mod->closure($uid,$sta);
        $acc->log(
        [
          'oid'    => $uid,//被封禁人ID
          'status' => $sta,//封禁状态
          'remark' => $rmk,
        ]);
        D('OperLog')->log('closure',
        [
          '状态' => $acc->accusation_states[$sta],
          '备注' => $rmk,
        ],$uid);
        // 同时封禁设备
        if($has_device && $v['device_id'])
        {
          $mod->closure_device($v['device_id']);
          D('OperLog')->log('closure',
          [
            '封禁设备',
            '设备ID' => $v['device_id'],
          ],$uid);
        }
        // 文字审核处理
        D('UserInfoModifyRequest')->handle($uid,'msg',$rmk);
      }
      D('OperLog')->log('closure',
      [
        '批量封禁',
        '封禁人数' => count($uls),
      ]);
    }
    $this->success('操作成功');
  }

  // 封禁解除
  public function unclosure()
  {
    $this->auth_check(CONTROLLER_NAME.'/closure');
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    if($uid < 1)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '用户ID错误';
    }
    else
    {
      $usr = D(CONTROLLER_NAME);
      $this->ret = array_merge($this->ret,$usr->closure($uid,-1) ?: []);
    }
    if(!$this->ret['ret'])
    {
      $sta = $acc->accusation_states[-1] ?: '解除惩罚';
      D('AccusationBaseLog')->log([
        'oid'    => $uid,//被封禁人ID
        'status' => -1,
        'remark' => $sta,
      ]);
      D('OperLog')->log('unclosure',[],$uid);
    }
    $this->ret['ret'] ? $this->error($this->ret['msg'] ?: '操作失败') : $this->success($this->ret['msg'] ?: '操作成功');
  }

  // 多账号设备类别
  public function device_list()
  {
    $mod = D(CONTROLLER_NAME);
    $dat = [];
    $map = ['device_id' => [['neq','0'],['neq','']]];
    if($did = trim($_REQUEST['device_id'])) $map['_complex'] = ['device_id' => $did];
    if($kwd = trim($_REQUEST['kwd']))       $map['_complex'] = ['device_id' => ['like','%'.$kwd.'%']];
    if($_REQUEST['closured'] != '') $map['device_id'][] = [(int)$_REQUEST['closured'] ? 'in' : 'not in',$mod->get_devices_closured(false)];
    $dat['list'] = $mod->get_device_list($this->page_size,$map) ?: [];
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['cnt_closured'] = (int)D('UserBase')->new_redis('redis_default')->zCard('php_device_disabled');
    if($did = trim($_REQUEST['device_id']))
    {
      $dat['list'][$did] = $dat['list'][$did] ?: ['device_id' => $did];
    }
    $dat['list'] = array_map(function($v) use($mod)
    {
      $v['closure_time'] = $mod->get_device_ctime($v['device_id']);
      return $v;
    },$dat['list']);
    $dat['list'] = array_filter($dat['list'],function($v)
    {
      $ret = true;
      if($_REQUEST['closured'] != '') $ret = (int)$_REQUEST['closured'] ? !!$v['closure_time'] : !$v['closure_time'];
      return $ret;
    });
    $dat['rmks'] = $mod->get_devices_remark(array_column($dat['list'],'device_id'));
    $dat['white_list'] = $mod->get_devices_whitelist();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  // 设备封禁
  public function device_closure()
  {
    $this->auth_check(CONTROLLER_NAME.'/closure');
    $mod = D(CONTROLLER_NAME);
    $did = trim(I('request.device_id'));
    $rmk = trim(I('request.remark'));
    if(!$did) $this->error('设备ID错误');
    else
    {
      $mod->closure_device($did);
      $acc = D('AccusationBaseLog');
      $uls = $mod->lists(['device_id' => $did]) ?: [];
      $sta = 5;
      foreach($uls as $v)
      {
        $mod->closure($v['uid'],$sta);
        $acc->log(
        [
          'oid'    => $v['uid'],//被封禁人ID
          'status' => $sta,//封禁状态
          'remark' => $rmk ?: '封禁设备',
        ]);
        D('OperLog')->log('closure',
        [
          '状态' => $acc->accusation_states[$sta],
          '备注' => $rmk ?: '封禁设备',
        ],$v['uid']);
      }
      D('OperLog')->log('closure',
      [
        '封禁设备',
        '设备ID'   => $did,
        '封禁人数' => count($uls),
      ]);
    }
    $this->success('操作成功');
  }

  // 设备解封
  public function device_unclosure()
  {
    $this->auth_check(CONTROLLER_NAME.'/closure');
    $mod = D(CONTROLLER_NAME);
    $did = trim(I('request.device_id'));
    if(!$did) $this->error('设备ID错误');
    else
    {
      $mod->unclosure_device($did);
      D('OperLog')->log('unclosure',
      [
        '解封设备',
        '设备ID'   => $did,
      ]);
    }
    $this->success('操作成功');
  }

  // 设备备注
  public function device_remark()
  {
    //$this->auth_check(CONTROLLER_NAME.'/closure');
    $mod = D(CONTROLLER_NAME);
    $did = trim(I('request.device_id'));
    $rmk = trim(I('request.remark'));
    $iwl = (int)$_REQUEST['white_list'];
    if(!$did) $this->error('设备ID错误');
    elseif($rmk)
    {
      $mod->set_devices_remark([$did => $rmk]);
    }
    else
    {
      $mod->del_device_remark($did);
    }
    //设置白名单
    $iwl ? $mod->set_device_whitelist($did) : $mod->del_device_whitelist($did);
    $this->success('操作成功');
  }


  // 获得赞次数记录
  public function zan_logs()
  {
    $uid = (int)$_REQUEST['uid'];
    $mod = D('UserZan');
    $map = $mod->get_filters();
    $lst = $mod->plist($this->page_size,$map)->lists('','id desc') ?: [];
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['types'] = $mod->zan_types ?: [];
    foreach($lst as $v)
    {
      $row =
      [
        'ID'       => $v['id'],
        '用户ID'   => $v['uid'],
        '类型'     => $dat['types'][$v['zan_type']] ?: $v['zan_type'],
        '数量'     => $v['sub_zan'],
        '时间'     => $v['sub_zan_time'] ? date('Y-m-d H:i:s',$v['sub_zan_time']) : '-',
        '用完时间' => $v['use_last_time'] ? date('Y-m-d H:i:s',$v['use_last_time']) : '-',
      ];
      $dat['list'][] = $row;
    }
    $dat['export'] = $dat['list'] ?: [];
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->display('Common/list-table');
  }


  // 操作日志
  public function oper_logs()
  {
    $uid = (int)$_REQUEST['uid'];
    $ope = D('OperLog');
    $dat = [];
    $map = $ope->get_filters(true);
    $dat['list'] = $ope->plist($this->page_size,$map)->lists('','create_time desc,id desc') ?: [];
    $this->pager = $ope->pager;
    $this->page  = $dat['page_html'] = $ope->pager->show();
    $dat['oper_types']  = $ope->types;
    $dat['oper_users']  = D(CONTROLLER_NAME)->get_by_list($dat['list'],'uid,nickname,phone,sex');
    $dat['oper_admins'] = D('Admin')->get_by_list($dat['list'],'aid,nickname');
    $dat['admins'] = D('Admin')->klist('aid');
    if(trim($_REQUEST['download'])) foreach($dat['list'] ?: [] as $v)
    {
      $row =
      [
        'ID'     => $v['id'],
        '管理员' => $dat['oper_admins'][$v['aid']]['nickname'] ?: $v['aid'],
        '时间'   => $v['create_time'] ? date('Y-m-d H:i:s',$v['create_time']) : '',
        '操作'   => $dat['oper_types'][$v['type']] ?: $v['type'],
        '用户ID' => $v['uid'],
        '性别'   => C('USER_SEX_IS.'.$dat['oper_users'][$v['uid']]['sex']),
      ];
      if($_REQUEST['type'] == 'scoring')
      {
        preg_match('/分[值数][:：][^;]*?([\d.]+)\s*(?:;|$)/iu',$v['remark'],$arr);
        $sco = (float)$arr[1];
        $row['分值'] = $sco;
      }
      $row['详细'] = $v['remark'];
      $dat['export'][] = $row;
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }


  // 上传相册资源
  public function upload_album()
  {
    $typ = trim($_REQUEST['type']);
    $res = $_FILES['file'];
    if(!$res) $this->error('请选择文件');
    else
    {
      $mod = D('Resource');
      $fnm = $mod->make_file_name($res['name']);
      //$ret = $this->aliyup('cjfeed',$fnm,$res['tmp_name']);
      $ret = $mod->oss_upload('cjfeed',$res['tmp_name'],$fnm);
      if($ret)
      {
        $this->success(
        [
          'filename' => $fnm,
          'resource' => D(CONTROLLER_NAME)->avatar_url_root.$fnm,
          'result'   => is_object($ret) ? get_object_vars($ret) : $ret,
        ]);
      }
    }
    $this->error('操作失败');
  }


  // 运营账号
  public function virtual_users()
  {
    $uid = (int)$_REQUEST['uid'];
    $mod = D(CONTROLLER_NAME);
    $map = $mod->get_filters(true);
    $map['type'] = 1;
    if(0)
    {
      $dat['list'] = $mod->plist($this->page_size,$map)->klist('uid','','reg_time desc,uid desc');
      $dat['list'] = $mod->format_nickname_all($dat['list'] ?: []);
      $this->pager = $mod->pager;
      $this->page  = $dat['page_html'] = $mod->pager->show();
    }
    $uid && isset($dat['list'][$uid]) && $dat['item'] = $dat['list'][$uid] ?: [];
    $dat['user_types'] = $mod->user_types ?: array();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  // 运营账号 注册
  public function virtual_reg()
  {
    $mod = D(CONTROLLER_NAME);
    $dat = $mod->create();
    $dat['type'] = (int)$_REQUEST['type'];
    if(!$dat)          $this->error($mod->getError() ?: '操作失败');
    if(!$dat['album']) $this->error('头像不能为空');
    if(!isset($mod->user_types[$dat['type']])) $this->error('用户类型错误');
    $cnt = $mod->where(['phone' => $dat['phone']])->count('uid');
    if($cnt > 0) $this->error('手机号已存在');
    if($dat['type'] == Model\UserBaseModel::TYPE_VIRTUAL/*运营*/)
    {
      $dat['uid_min'] = 12121000;
      $dat['uid_max'] = 12121999;
    }
    elseif($dat['type'] == Model\UserBaseModel::TYPE_ROBOT/*机器人*/)
    {
      $dat['uid_min'] = 12122000;
      $dat['uid_max'] = 12122999;
    }
    D('RpcApi')->call('User/Reg',$dat);
    //die(json_encode(compact('dat')));
    $this->success('操作成功');
  }

  // 运营账号 刷新缓存
  public function virtual_renew()
  {
    $mod = D(CONTROLLER_NAME);
    $rds = $mod->get_redis();
    $key = 'php_virtual_users';
    $ky2 = $key.'_'.date('Ymd');
    if($rds->rename($key,$ky2)) $rds->expire($ky2,60 * 60 * 24 * 7);
    $uls = $mod->field('uid,sex,phone,nickname')->klist('uid',['type' => Model\UserBaseModel::TYPE_VIRTUAL]) ?: [];
    foreach($uls as $v)
    {
      $rds->zAdd($key,$v['sex'],$v['uid']);
    }
    $this->success('操作成功');
  }


  // 保存用户附加属性
  public function save_attrs()
  {
    $this->auth_check(CONTROLLER_NAME.'/userinfo_update');
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    $mod = D(CONTROLLER_NAME);
    $dat = $mod->auto_field('attrs',(array)$_REQUEST['attrs']);
    if($dat === false)
    {
      $this->error($mod->getError() ?: '数据错误');
    }
    elseif(false === $mod->where(array('uid' => $uid))->setField('attrs',$dat))
    {
      $this->error('保存失败');
    }
    $this->success('操作成功');
  }


  public function get_user_token()
  {
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    die('Token:'.D(CONTROLLER_NAME)->get_user_token($uid));
  }

  public function del_user_cache()
  {
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    $mod = D(CONTROLLER_NAME);
    $mod->del_user_cache($uid);
    $rds = $mod->new_redis();
    //$rds->del('php_user_daily_'.$uid);
    for($i = 0;$i <= 8;$i++)
    {
      $day = date('Ymd',strtotime('-'.$i.' days'));
      $rds->hDel('php_income_daily_'.$day,$uid);
      if($i <= 1)
      {
        $mon = date('Ym',strtotime('-'.$i.' month'));
        $rds->hDel('php_income_month_'.$mon,$uid);
      }
    }
    $this->success('操作成功');
  }

}