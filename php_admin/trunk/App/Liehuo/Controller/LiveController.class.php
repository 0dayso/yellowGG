<?php
namespace Liehuo\Controller;
use Liehuo\Model;

class LiveController extends PublicController
{

  public function __construct()
  {
    parent::__construct();
  }

  public function index()
  {
    $_REQUEST['page_size'] || $this->page_size = 12;
    $mod = D('LiveRecord');
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','time_begin desc,id desc');
    $this->pager = $mod->pager;
    $dat = $this->handle_list($dat);
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function hots()
  {
    $mod = D('LiveRecord');
    $lhm = D('LiveHost');
    $_REQUEST['filter'] = 'hot';
    $dat['hots'] = $lhm->get_hots();
    $dat['hots_byTicket'] = $lhm->get_hots_byTicket();
    if($dat['hots_byTicket'])
    {
      $dat['hots'] = $dat['hots_byTicket'] + $dat['hots'];
      arsort($dat['hots']);
    }
    $_REQUEST['uids'] = $dat['hots'];
    $map = $mod->get_filters();
    $dat['list'] = $mod->klist('uid',$map,'id');
    $dat = $this->handle_list($dat);
    uasort($dat['list'],function($a,$b)
    {
      if($a['hot_sort'] == $b['hot_sort'])
      {
        if($a['total_income'] == $b['total_income']) return 0;
        return $a['total_income'] > $b['total_income'] ? -1 : 1;
      }
      return $a['hot_sort'] > $b['hot_sort'] ? -1 : 1;
    });
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display('index');
  }

  protected function handle_list($dat = [])
  {
    $mod = D('LiveRecord');
    $lhm = D('LiveHost');
    $dat['states'] = $mod->states ?: [];
    $dat['contract_types'] = D('LiveContractType')->get_all() ?: [];
    $dat['hosts'] = $lhm->get_by_list($dat['list']);
    $dat['users'] = D('UserBase')->get_users_account($dat['list']);
    isset($dat['hots']) || $dat['hots'] = $lhm->get_hots();
    if($ids = array_column($dat['list'] ?: [],'live_id'))
    {
      $dat['guest_counts'] = D('LiveGuest')->count_by_liveids($ids,['is_robot' => 0]);
      $dat['gift_records'] = D('LiveGiftRecord')->getByLiveIds($ids);
      $dat['reports']      = D('ReportBase')->getByLiveIds($ids);
    }
    $dat['list'] = array_map(function($v) use($mod,$dat)
    {
      $uid = $v['uid'];
      $lid = $v['live_id'];
      $cac = $mod->getCache($lid) ?: [];
      $v['hot_sort']         = $dat['hots'][$uid];
      $v['last_hot_at']      = $dat['hosts'][$uid]['last_hot_at'];
      $v['hot_ticket_ttl']   = $dat['hots_byTicket'][$uid] ? ($dat['hots_byTicket'][$uid] - NOW_TIME) : 0;
      $v['hot_ticket_ttl'] < 0 && $v['hot_ticket_ttl'] = 0;
      $v['final_visitors']   = $mod->getFinalVisitors($lid);
      $v['final_visitors_real'] = (int)$dat['guest_counts'][$lid]['cnt'];
      $v['current_visitors'] || $v['current_visitors'] = (int)$cac['current_visitors'];
      $v['current_visitors'] >= 2 && $v['current_visitors']--;
      $v['enter_time']       = (int)$cac['enter_time'];
      $v['live_duration']    = NOW_TIME - $v['enter_time'];
      $v['live_hot_duration']= $v['last_hot_at'] >= $v['enter_time'] ? (NOW_TIME - $v['last_hot_at']) : 0;
      $v['total_income']     = (int)$cac['total_income'];
      //$grs = $dat['gift_records'][$lid] ?: [];
      $v['gold_index'] = $v['total_income'] / $v['final_visitors_real'] * $v['live_duration'];
      return $v;
    },$dat['list'] ?: []);
    return $dat;
  }


  public function hot_set()
  {
    $ret = false;
    $uid = (int)$_REQUEST['uid'];
    $oid = (int)$_REQUEST['oid'];
    $top = (int)$_REQUEST['top'];
    $mod = D('LiveHost');
    if(!$uid || $uid == $oid) $this->error('参数错误');
    elseif(!$oid)
    {
      // 加入热门
      $ret = $mod->set_hot($uid,$top);
      D('OperLog')->log('live_hot_set',
      [
        $top ? '直播热门置顶' : '直播加入热门',
        '用户ID' => $uid,
      ],$uid);
    }
    else
    {
      // 热门排序
      $ret = $mod->sort_hot($uid,$oid);
    }
    if($ret === false) $this->error('操作失败');
    $dat['result'] = $ret;
    $dat['set_href'] = U('hot_del?uid='.$uid);
    $dat['set_text'] = '移出热门';
    $this->data = $dat;
    $this->success('操作成功',U('hots'));
  }

  public function hot_del()
  {
    $uid = (int)$_REQUEST['uid'];
    if(!D('LiveHost')->del_hot($uid))
    {
      $this->error('操作失败');
    }
    $dat['set_href'] = U('hot_set?uid='.$uid);
    $dat['set_text'] = '加入热门';
    D('OperLog')->log('live_hot_del',
    [
      '直播移出热门',
      '用户ID' => $uid,
    ],$uid);
    $this->data = $dat;
    $this->success('操作成功');
  }

  public function hot_reset()
  {
    D('LiveHost')->reset_hot() ? $this->success('操作成功',U('hots')) : $this->error('操作失败');
  }

  // 主播卡片信息
  public function info()
  {
    $uid = (int)$_REQUEST['uid'];
    $dat = $_SESSION['live-host-info'][$uid] ?: [];
    if((int)$dat['_update_at'] < (NOW_TIME - 60 * 10)/*Session缓存一段时间*/)
    {
      $dat['host'] = D('LiveHost')->find($uid);
      $dat['host'] = D('LiveHost')->attr2array_row($dat['host']);
      $dat['sqls'][] = D('LiveHost')->_sql();
      $dat['count'] = D('LiveRecord')->field(
      [
        'count(id)' => 'total_times',
        'sum(total_income)' => 'total_income',
        'sum(time_end - time_begin)' => 'total_duration',
      ])
      ->where(
      [
        'uid'        => $uid,
        'time_begin' => ['egt',1],
        'time_end'   => ['exp','>= time_begin'],
      ])
      ->find() ?: [];
      $dat['sqls'][]  = D('LiveRecord')->_sql();
      $dat['records'] = D('LiveRecord')
      ->where(
      [
        'uid'        => $uid,
        'time_begin' => ['egt',1],
        'time_end'   => ['exp','>= time_begin'],
        '_string'    => '(time_end - time_begin) >= 600',
      ])
      ->order('id desc')
      ->limit(2)
      ->select() ?: [];
      $dat['sqls'][] = D('LiveRecord')->_sql();
      $dat['dispose'] = D('OperLog')->field(
      [
        'type',
        'count(id)' => 'cnt',
      ])
      ->where(
      [
        'uid'  => $uid,
        'type' => ['in',['live_hot_set','live_stop','live_warn']],
      ])
      ->group('type')
      ->klist('type');
      $dat['sqls'][] = D('OperLog')->_sql();
      $dat['_update_at'] = NOW_TIME;
      $_SESSION['live-host-info'][$uid] = $dat;
    }
    $this->data = $dat;
    $this->success();
  }

  // 处置主播/观众
  public function dispose()
  {
    $ret = false;
    $act = trim($_REQUEST['act']);
    $uid = (int)$_REQUEST['uid'];
    $dur = (int)$_REQUEST['duration'];
    $msg = trim($_REQUEST['msg']);
    $rmk = trim($_REQUEST['remark']);
    if(!$uid) $this->error('UID错误');
    else
    {
      $mod = D('LiveRecord');
      $liv = $mod->getLiving($uid) ?: [];
      // 警告
      if($act == 'warn' && $msg)
      {
        if(!$liv['live_id']) $this->error('未直播');
        $ret = D('Message')->set_option(
        [
          'minor_type' => Model\MessageModel::TEXT_TYPE_COMPOUND,
          'room_id'    => (int)$liv['live_chatroomid'],
        ])->send_group_chat(
        [
          'type' => 302,
          'name' => '直播消息',
          'text' => $msg,
        ]);
        D('OperLog')->log('live_warn',
        [
          '警告主播',
          '用户ID' => $uid,
          '直播ID' => $liv['live_id'],
          '话术'   => $msg,
        ],$uid);
      }
      // 停播
      elseif($act == 'stop')
      {
        if(!$dur) $this->error('参数错误');
        //if(!$liv['live_id']) $this->error('未直播');
        $ret = D('RpcApi')->call('Live/close',
        [
          'uid'      => $uid,
          'live_id'  => $liv['live_id'],
          'duration' => $dur,
        ]);
        D('RpcApi')->handle();
        D('OperLog')->log('live_stop',
        [
          '主播停播',
          '用户ID'   => $uid,
          '直播ID'   => $liv['live_id'],
          '停播时长' => $dur.'s',
        ],$uid);
      }
      // 禁言
      elseif($act == 'silence')
      {
        $ret = D('LiveGuest')->silence($uid,$dur);
        D('OperLog')->log('live_silence',
        [
          '观众禁言',
          '用户ID'   => $uid,
          '禁言时长' => $dur.'s',
        ],$uid);
      }
    }
    $ret !== false ? $this->success('操作成功') : $this->error('操作失败');
  }

  public function manager_set()
  {
    $uid = (int)$_REQUEST['uid'];
    $isd = !!(int)$_REQUEST['del'];
    $mod = D('LiveGuest');
    $ret = !$isd ? $mod->add_manager($uid) : $mod->del_manager($uid);
    $ret !== false ? $this->success('操作成功') : $this->error('操作失败');
  }


  // 申请列表
  public function apply_list()
  {
    $mod = D('LiveSignUp');
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','sign_time desc,id desc');
    $this->pager = $mod->pager;
    $dat['types']  = $mod->types ?: [];
    $dat['styles'] = $mod->styles ?: [];
    $dat['users']  = D('UserBase')->get_users_account($dat['list']);
    $dat['avatar_url_root'] = D('UserBase')->avatar_url_root;
    $dat['list'] = array_map(function($v) use($dat)
    {
      $usr = $dat['users'][$v['uid']] ?: [];
      $row =
      [
        'ID'       => $v['id'],
        '申请人'   => '<a href="'.U('UserBase/view',['uid' => $v['uid']]).'" target="_blank" class="label label-default popover-avatar" data-original-title="'.$usr['nickname'].'">'.$v['uid'].'</a>
                       <b class="label label-danger">'.(boolval($usr['vip_level'] && $usr['vip_valid_end'] >= NOW_TIME) ? 'v' : '').$usr['glory_grade'].'</b>',
        '昵称'     => $usr['nickname'],
        '头像'     => $v['image_file'] ? ('<img src="'.$dat['avatar_url_root'].$v['image_file'].'" class="zoom" style="max-width:120px;">') : '',
        '手机'     => $v['phone'],
        'QQ'       => $v['qq'],
        '直播时间' => $v['live_time'],
        '其他平台' => $v['live_orth'],
        '直播类型' => $dat['types'][$v['live_type']] ?: $v['live_type'],
        '直播风格' => $dat['styles'][$v['live_style']] ?: $v['live_style'],
        '报名时间' => $v['sign_time'] ? date('Y-m-d H:i:s',$v['sign_time']) : '-',
        '操作'     => '<a href="'.U('edit',
        [
          'uid'        => $v['uid'],
          'live_type'  => $dat['types'][$v['live_type']],
          'live_style' => $dat['styles'][$v['live_style']],
        ]).'" target="_blank" class="btn btn-primary">签约</a>',
      ];
      return $row;
    },$dat['list'] ?: []);
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->display('Common/list-table');
  }

  // 主播库
  public function hosts()
  {
    $_REQUEST['page_size'] || $this->page_size = 60;
    $mod = D('LiveHost');
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','contract_time desc,uid desc');
    $this->pager = $mod->pager;
    $dat['contract_types'] = D('LiveContractType')->get_all() ?: [];
    $dat['propertys']      = $mod->propertys ?: [];
    $dat['users'] = D('UserBase')->get_users_account($dat['list']);
    if(trim($_REQUEST['download'])) foreach($dat['list'] ?: [] as $v)
    {
      $dat['export'][] =
      [
        '用户ID'   => $v['uid'],
      ];
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }


  // 签约主播
  public function edit()
  {
    $mod = D('LiveHost');
    $uid = (int)$_REQUEST['uid'];
    $dat['contract_types'] = D('LiveContractType')->get_all() ?: [];
    $dat['propertys']      = $mod->propertys ?: [];
    $dat['item'] = $mod->find($uid);
    $dat['item'] = $mod->attr2array_row($dat['item']);
    $dat['user'] = D('UserBase')->get_users_account_byids([$uid]);
    $dat['user'] = $dat['user'][$uid] ?: [];
    $dat['dispose'] = D('OperLog')->field(
    [
      'type',
      'count(id)' => 'cnt',
    ])
    ->where(
    [
      'uid'  => $uid,
      'type' => ['in',['live_hot_set','live_stop','live_warn']],
    ])
    ->group('type')
    ->klist('type');
    $this->data = $dat;
    $this->display();
  }

  public function save()
  {
    $mod = D('LiveHost');
    $uid = (int)$_REQUEST['uid'];
    $ids = preg_match_all('/\b(\d{4,11})\b/',$_REQUEST['uids'],$arr) ? $arr[1] : [];
    $ids = array_unique($ids ?: []);
    $dat = $mod->create();
    $ret = $mod->add_bat($uid ?: $ids,$dat);
    if(!$ret) $this->error('操作失败');
    $this->success('操作成功',U('hosts'));
  }

  // 批量更改用户签约类型
  public function contract_change()
  {
    $ids = (array)$_REQUEST['uids'];
    $dat['contract_type'] = (int)$_REQUEST['contract_type'];
    $ret = $ids && D('LiveHost')->add_bat($ids,$dat);
    if(!$ret) $this->error('操作失败');
    $this->success('操作成功',U('hosts'));
  }

  public function contract_del()
  {
    $mod = D('LiveHost');
    $uid = (int)$_REQUEST['uid'];
    $ret = $mod->where(['uid' => $uid])->save(['contract_type' => Model\LiveHostModel::CONTRACT_TYPE_NONE]);
    if(!$ret) $this->error('操作失败');
    $this->success('操作成功',U('hosts'));
  }


  // 签约类型配置
  public function contract_types()
  {
    $id  = trim($_REQUEST['id']);
    $mod = D('LiveContractType');
    $dat['list'] = $mod->order('sort,id')->get_all();
    $id && isset($dat['list'][$id]) && $dat['item'] = $dat['list'][$id] ?: [];
    $this->data = $dat;
    //die(json_encode(compact('dat','arr')));
    $this->display();
  }

  public function contract_type_save()
  {
    $id  = trim($_REQUEST['id']);
    $mod = D('LiveContractType');
    $dat = $mod->create();
    if($dat === false) $this->error($mod->getError() ?: '参数错误');
    elseif(!$mod->set($dat,$id)) $this->error('操作失败');
    $mod->update_cache();
    $this->success('操作成功',U('contract_types'));
  }

  public function contract_type_del()
  {
    $id  = (int)$_REQUEST['id'];
    $mod = D('LiveContractType');
    if((int)D('LiveHost')->where(['contract_type' => $id])->count('uid') >= 1)
    {
      $this->error('该类型下还有主播');
    }
    elseif(!$mod->soft_del($id))
    {
      $this->error('操作失败');
    }
    $mod->update_cache();
    $this->success('操作成功',U('contract_types'));
  }


  // 主播认证等级配置
  public function host_levels()
  {
    $id  = trim($_REQUEST['id']);
    $mod = D('LiveHostLevel');
    $dat['list'] = $mod->order('sort,id')->get_all();
    $id && isset($dat['list'][$id]) && $dat['item'] = $dat['list'][$id] ?: [];
    $this->data = $dat;
    //die(json_encode(compact('dat','arr')));
    $this->display();
  }

  public function host_level_save()
  {
    $id  = trim($_REQUEST['id']);
    $mod = D('LiveHostLevel');
    $dat = $mod->create();
    if($dat === false) $this->error($mod->getError() ?: '参数错误');
    elseif(!$mod->set($dat,$id)) $this->error('操作失败');
    $mod->update_cache();
    $this->success('操作成功',U('host_levels'));
  }

  public function host_level_del()
  {
    $id  = (int)$_REQUEST['id'];
    $mod = D('LiveHostLevel');
    if(!$mod->soft_del($id))
    {
      $this->error('操作失败');
    }
    $mod->update_cache();
    $this->success('操作成功',U('contract_types'));
  }


  // 礼物记录
  public function gift_records()
  {
    $mod = D('LiveGiftRecord');
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','create_time desc,id desc');
    $dat['page'] = $this->pager = $mod->pager;
    $dat['cnts'] = $mod->field(
    [
      'sum(goods_fee)'    => 'diamond',
      'sum(glamour_take)' => 'glamour',
    ])->where($map)->find();
    $dat['users'] = D('UserBase')->get_users_account($dat['list'],'uid,oid');
    $dat['goods'] = D('Goods')->klist();
    $dat['list'] = array_map(function($v) use($dat)
    {
      $usr = $dat['users'][$v['uid']] ?: [];
      $osr = $dat['users'][$v['oid']] ?: [];
      $row =
      [
        'ID'       => $v['id'],
        '送礼人'   => '<a href="'.U('UserBase/view',['uid' => $v['uid']]).'" target="_blank" class="label label-default popover-avatar" data-original-title="'.$usr['nickname'].'">'.$v['uid'].'</a>
                       <b class="label label-danger">'.(boolval($usr['vip_level'] && $usr['vip_valid_end'] >= NOW_TIME) ? 'v' : '').$usr['glory_grade'].'</b>',
        '送礼人昵称' => $usr['nickname'],
        '收礼人' => '<a href="'.U('UserBase/view',['uid' => $v['oid']]).'" target="_blank" class="label label-default popover-avatar" data-original-title="'.$osr['nickname'].'">'.$v['oid'].'</a>
                       <b class="label label-danger">'.(boolval($osr['vip_level'] && $osr['vip_valid_end'] >= NOW_TIME) ? 'v' : '').$osr['glory_grade'].'</b>',
        '收礼人昵称' => $osr['nickname'],
        '礼物'     => $dat['goods'][$v['goods_id']]['name'] ?: $v['goods_id'],
        '钻石'     => round($v['goods_fee']),
        '收到魅力' => $v['glamour_take'],
        '连击'     => $v['combo'],
        '直播ID'   => $v['live_id'],
        '送出时间' => $v['create_time'] ? date('Y-m-d H:i:s',$v['create_time']) : '-',
      ];
      return $row;
    },$dat['list'] ?: []);
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->has_filter = 1;
    $this->display();
  }

  // 直播聊天记录
  public function chat_logs()
  {
    $_REQUEST['day']   || $_REQUEST['day'] = 1;
    $_REQUEST['stime'] || $_REQUEST['stime'] = date('Y-m-d',strtotime('-'.((int)$_REQUEST['day'] - 1).' days'));
    $mod = D('LiveChatLog');
    $mod->sday = (int)$_REQUEST['day'];
    $mod->eday = 0;
    $map = $mod->get_filters();
    $dat = [];
    if($mod->sday - $mod->eday > 3) $this->error('查询时间范围不得超过3天');
    $dat['list'] = $mod->get_table_union($map,$mod->sday,$mod->eday)->plist(100)->select() ?: [];
    $dat['page'] = $this->pager = $mod->pager;
    $dat['_sql'] = $mod->_sql();
    //is_array($dat['list']) && $dat['list'] = array_reverse($dat['list']);
    is_array($dat['list']) && $dat['list'] = $mod->format_text_all($dat['list']);
    $dat['users'] = D('UserBase')->get_users_account($dat['list'],'sender');
    $gmd = D('LiveGuest');
    $dat['list'] = array_map(function($v) use(&$dat,$gmd)
    {
      $uid = $v['sender'];
      isset($dat['silences'][$uid]) || $dat['silences'][$uid] = $gmd->get_silence_ttl($uid);
      $v['silence_ttl'] = $dat['silences'][$uid];
      return $v;
    },$dat['list'] ?: []);
    //die(json_encode(compact('ids','dat')));
    $this->data = $dat;
    $this->show_inline = 1;
    $this->display();
  }


  // 直播游戏记录
  public function game_records()
  {
    $mod = D('LiveGameRecord');
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','id desc');
    $dat['page'] = $this->pager = $mod->pager;
    $dat['users'] = D('UserBase')->get_users_account($dat['list'],'uid');
    $dat['list'] = array_map(function($v) use($dat)
    {
      $usr = $dat['users'][$v['uid']] ?: [];
      $gdt = is_string($v['game_item_json']) ? json_decode($v['game_item_json'],true) : [];
      $row =
      [
        'ID'       => $v['id'],
        '发起者'   => '<a href="'.U('UserBase/view',['uid' => $v['uid']]).'" target="_blank" class="label label-default popover-avatar" data-original-title="'.$usr['nickname'].'">'.$v['uid'].'</a>
                       <b class="label label-danger">'.(boolval($usr['vip_level'] && $usr['vip_valid_end'] >= NOW_TIME) ? 'v' : '').$usr['glory_grade'].'</b>',
        '直播ID'   => $v['live_id'],
        '状态'     => $v['game_state'],
        '已筹集钻石' => $v['diamond'],
        '参与人数' => '<a href="'.U('game_users',['game_id' => $v['id']]).'" target="_blank" class="label label-default">'.$v['game_user'].'</a>',
        '结果'     => $gdt[$v['game_prize_id']]['name'] ?: $v['game_prize_id'] ?: '-',
        '游戏详情' => '<div class="td-content popover-hover">'
          .implode('',array_map(function($k,$v) use($dat)
          {
            if(!$v) return '';
            $arr = '';
            $v['type']        && ($arr[] = array_get(['','赢钱','表演'],$v['type']));
            $v['name']        && ($arr[] = $v['name']);
            $v['diamond']     && ($arr[] = $v['diamond'].'钻石');
            $v['probability'] && ($arr[] = $v['probability'].'%');
            return $k.'：'.implode("\t",$arr).'<br>';
          },array_keys($gdt),$gdt))
          .'</div>',
        //'是否结束' => $v['game_over'] ? '是' : '否',
        '开始时间' => $v['game_start'] ? date('Y-m-d H:i:s',$v['game_start']) : '-',
        '结束时间' => $v['game_end'] ? date('Y-m-d H:i:s',$v['game_end']) : '-',
      ];
      return $row;
    },$dat['list'] ?: []);
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->has_filter = 1;
    $this->has_filter_kwd = 1;
    $this->display('Common/list-table');
  }

  // 直播参与人
  public function game_users()
  {
    $mod = D('LiveGameRecord')->set_table('__LIVE_GAME_JOIN_USER__');
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','id');
    $dat['page'] = $this->pager = $mod->pager;
    $dat['users'] = D('UserBase')->get_users_account($dat['list'],'uid');
    $dat['list'] = array_map(function($v) use($dat)
    {
      $usr = $dat['users'][$v['uid']] ?: [];
      $row =
      [
        'ID'       => $v['id'],
        '参与者'   => '<a href="'.U('UserBase/view',['uid' => $v['uid']]).'" target="_blank" class="label label-default popover-avatar" data-original-title="'.$usr['nickname'].'">'.$v['uid'].'</a>
                       <b class="label label-danger">'.(boolval($usr['vip_level'] && $usr['vip_valid_end'] >= NOW_TIME) ? 'v' : '').$usr['glory_grade'].'</b>',
        '游戏ID'   => $v['game_id'],
        '状态'     => $v['game_state'],
        '押注钻石' => $v['diamond'],
        '参与人数' => $v['game_user'],
        '结果'     => $gdt[$v['game_prize_id']]['name'] ?: $v['game_prize_id'] ?: '-',
        '游戏详情' => '<div class="td-content popover-hover">'
          .implode('',array_map(function($k,$v) use($dat)
          {
            if(!$v) return '';
            $arr = '';
            $v['type']        && ($arr[] = array_get(['','赢钱','表演'],$v['type']));
            $v['name']        && ($arr[] = $v['name']);
            $v['diamond']     && ($arr[] = $v['diamond'].'钻石');
            $v['probability'] && ($arr[] = $v['probability'].'%');
            return $k.'：'.implode("\t",$arr).'<br>';
          },array_keys($gdt),$gdt))
          .'</div>',
        '加入时间' => $v['join_time'] ? date('Y-m-d H:i:s',$v['join_time']) : '-',
      ];
      return $row;
    },$dat['list'] ?: []);
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->has_filter = 1;
    $this->display('Common/list-table');
  }


  public function guests()
  {
    $mod = D('LiveGuest');
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','id desc');
    $dat['page'] = $this->pager = $mod->pager;
    $dat['users'] = D('UserBase')->get_users_account($dat['list']);
    $dat['list'] = array_map(function($v) use($dat)
    {
      $usr = $dat['users'][$v['uid']] ?: [];
      $row =
      [
        'ID'       => $v['id'],
        'UID'      => '<a href="'.U('UserBase/view',['uid' => $v['uid']]).'" target="_blank" class="label label-default popover-avatar" data-original-title="'.$usr['nickname'].'">'.$v['uid'].'</a>
                       <b class="label label-danger">'.(boolval($usr['vip_level'] && $usr['vip_valid_end'] >= NOW_TIME) ? 'v' : '').$usr['glory_grade'].'</b>',
        '昵称'     => $usr['nickname'],
        '直播ID'   => $v['live_id'],
        '在线时长' => $v['online_time'],
        '机器人'   => $v['is_robot'] ? '是' : '否',
      ];
      return $row;
    },$dat['list'] ?: []);
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->has_filter = 1;
    $this->display('Common/list-table');
  }

  public function follows()
  {
    $mod = D('LiveFollow');
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','follow_time desc,id desc');
    $dat['page'] = $this->pager = $mod->pager;
    $dat['users'] = D('UserBase')->get_users_account($dat['list'],'uid,oid');
    $dat['list'] = array_map(function($v) use($dat)
    {
      $usr = $dat['users'][$v['uid']] ?: [];
      $osr = $dat['users'][$v['oid']] ?: [];
      $row =
      [
        'ID'       => $v['id'],
        '关注人'   => '<a href="'.U('UserBase/view',['uid' => $v['uid']]).'" target="_blank" class="label label-default popover-avatar" data-original-title="'.$usr['nickname'].'">'.$v['uid'].'</a>
                       <b class="label label-danger">'.(boolval($usr['vip_level'] && $usr['vip_valid_end'] >= NOW_TIME) ? 'v' : '').$usr['glory_grade'].'</b>',
        '关注人昵称' => $usr['nickname'],
        '被关注人' => '<a href="'.U('UserBase/view',['uid' => $v['oid']]).'" target="_blank" class="label label-default popover-avatar" data-original-title="'.$osr['nickname'].'">'.$v['oid'].'</a>
                       <b class="label label-danger">'.(boolval($osr['vip_level'] && $osr['vip_valid_end'] >= NOW_TIME) ? 'v' : '').$osr['glory_grade'].'</b>',
        '被关注人昵称' => $osr['nickname'],
        '状态'     => $v['follow_type'] ? '关注中' : '未关注',
        '关注时间' => $v['follow_time'] ? date('Y-m-d H:i:s',$v['follow_time']) : '-',
      ];
      return $row;
    },$dat['list'] ?: []);
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->has_filter = 1;
    $this->display('Common/list-table');
  }


  // 完成直播任务记录
  public function task_records()
  {
    $mod = D('Task')->set_table('__TASK_SUB__');
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','create_time desc,id desc');
    $dat['list'] = $mod->attr2array_all($dat['list']);
    $this->pager = $mod->pager;
    $dat['tasks'] = $mod->table('__TASK_PUB__')->get_by_list($dat['list']);
    $dat['tasks'] = $mod->attr2array_all($dat['tasks']);
    $dat['types'] = $mod->types ?: [];
    $dat['users'] = D('UserBase')->get_users_account($dat['list']);
    $dat['list'] = array_map(function($v) use($dat)
    {
      $usr = $dat['users'][$v['uid']] ?: [];
      $tsk = $dat['tasks'][$v['task_id']] ?: [];
      $pro = $v['attrs']['progress'] ?: [];
      $row =
      [
        'ID'       => $v['id'],
        '用户ID'   => '<a href="'.U('UserBase/view',['uid' => $v['uid']]).'" target="_blank" class="label label-default popover-avatar" data-original-title="'.$usr['nickname'].'">'.$v['uid'].'</a>
                       <b class="label label-danger">'.(boolval($usr['vip_level'] && $usr['vip_valid_end'] >= NOW_TIME) ? 'v' : '').$usr['glory_grade'].'</b>',
        '昵称'     => $usr['nickname'],
        '任务'     => $tsk['title'],
        '奖励'     => $pro['reward_diamond'] ? ('获得'.$pro['reward_diamond'].'钻石') : $pro['reward_tip'],
        '完成时间' => $v['create_time'] ? date('Y-m-d H:i:s',$v['create_time']) : '-',
      ];
      return $row;
    },$dat['list'] ?: []);
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->has_filter = 1;
    $this->display('Common/list-table');
  }


  public function notices()
  {
    $mod = D('LiveNotice');
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','create_time desc,id desc');
    $this->pager = $mod->pager;
    $dat['list'] = $mod->attr2array_all($dat['list']);
    $dat['types'] = $mod->types ?: [];
    $dat['admins'] = D('Admin')->get_by_list($dat['list']);
    $this->data = $dat;
    $this->display();
  }

  public function notice_save()
  {
    $mod = D('LiveNotice');
    $dat = $mod->create();
    if($dat === false)    $this->error($mod->getError() ?: '参数错误');
    elseif(!$dat['text']) $this->error('内容不能为空');
    elseif($dat['live_id'] && !is_numeric($dat['live_id'])) $this->error('直播ID错误');
    else
    {
      $ret = true;
      if($dat['type'] == Model\LiveNoticeModel::TYPE_PUBLIC)
      {
        $ret = D('Message')->set_option(
        [
          'minor_type' => Model\MessageModel::TEXT_TYPE_COMPOUND,
          'all_room'   => true,
        ])->send_group_chat(
        [
          'type' => 306,
          'name' => '直播消息',
          'text' => $dat['text'],
        ]);
      }
      elseif($dat['type'] == Model\LiveNoticeModel::TYPE_ROOM)
      {
        $liv = D('LiveRecord')->getLiving(['live_id' => $dat['live_id']]) ?: [];
        $ret = D('Message')->set_option(
        [
          'minor_type' => Model\MessageModel::TEXT_TYPE_COMPOUND,
          'room_id'    => (int)$liv['live_chatroomid'],
        ])->send_group_chat(
        [
          'type' => 302,
          'name' => '直播消息',
          'text' => $dat['text'],
        ]);
      }
      if($ret !== false) $ret = $mod->add(array_merge($dat,
      [
        'aid' => (int)$this->aid,
      ]));
    }
    if(!$ret) $this->error('操作失败');
    else
    {
      $mod->update_pacts();
      D('OperLog')->log('live_notice',
      [
        '新增直播公告/公约',
        '类型'   => $mod->types[$dat['type']] ?: $dat['type'],
        '内容'   => $dat['text'],
        '直播ID' => $dat['live_id'],
      ]);
    }
    $this->success('操作成功');
  }

  public function notice_renew()
  {
    D('LiveNotice')->update_pacts();
    $this->success('操作成功');
  }

  public function notice_del()
  {
    $id = trim($_REQUEST['id']);
    $mod = D('LiveNotice');
    $mod->where(['id' => $id])->delete();
    $mod->update_pacts();
    $this->success('操作成功');
  }


  public function robot_config()
  {
    $id = trim($_REQUEST['id']);
    $dat = D('LiveGuest')->get_robot_config();
    $id && $dat['item'] = $dat['rules'][$id] ?: [];
    $this->data = $dat;
    $this->display();
  }

  public function robot_config_save()
  {
    $id = trim($_REQUEST['id']) ?: md5(uniqid(rand(),true).rand());
    if(!$_REQUEST['deadline'])       $this->error('参数错误');
    if(!$_REQUEST['robot_num'])      $this->error('参数错误');
    if((int)$_REQUEST['valid_begin']    < 0) $this->error('参数错误');
    if((int)$_REQUEST['amount_min']     < 0) $this->error('参数错误');
    if((int)$_REQUEST['popularity_min'] < 0) $this->error('参数错误');
    if(!$_REQUEST['interval'])       $this->error('参数错误');
    $dat = D('LiveGuest')->get_robot_config();
    isset($_REQUEST['deadline'])  && $dat['deadline']  = (int)$_REQUEST['deadline'] * 60;
    isset($_REQUEST['robot_num']) && $dat['robot_num'] = (int)$_REQUEST['robot_num'];
    $dat['rules'][$id] = array_merge($dat['rules'][$id] ?: [],
    [
      'valid_begin'    => (int)$_REQUEST['valid_begin'] * 60,
      'valid_end'      => (int)($_REQUEST['valid_end'] ?: $_REQUEST['valid_begin']) * 60,
      'amount_min'     => (int)$_REQUEST['amount_min'],
      'amount_max'     => (int)($_REQUEST['amount_max'] ?: $_REQUEST['amount_min']),
      'popularity_min' => (int)$_REQUEST['popularity_min'],
      'popularity_max' => (int)($_REQUEST['popularity_max'] ?: $_REQUEST['popularity_min']),
      'interval'       => (int)$_REQUEST['interval'] * 60,
    ]);
    $ret = D('LiveGuest')->set_robot_config($dat);
    $this->success('操作成功');
  }

  // 添加观众机器人
  public function add_robot()
  {
    $mod = D('LiveGuest');
    if(IS_POST)
    {
      $str = trim($_REQUEST['uids']);
      $ids = [];
      if(preg_match_all('/\b(\d{4,11})\b/',$str,$arr)) $ids = $arr[1];
      elseif($str) $ids = explode(',',$str);
      $ids = array_unique($ids ?: []);
      $uls = D('UserBase')->field('uid,phone,nickname,sex')->klist('uid',['uid' => ['in',array_values($ids)]]) ?: [];
      if(!$uls) $this->error('用户不存在');
      elseif(!$mod->add_robot(array_keys($uls)))
      {
        $this->error('操作失败');
      }
      $this->success('操作成功');
      die;
    }
    $dat['count'] = $mod->get_robot_count();
    $dat['count_assign'] = $mod->get_robot_assign_count();
    $dat['cntlib'] = $mod->get_robot_lib_count();
    $this->data = $dat;
    $this->display();
  }


  // 导出主播魅力信息 公会主播数据
  public function export_live_host_accounts()
  {
    $ids = trim($_POST['ids']);
    $ids = preg_split('/\s+/',$ids) ?: [];
    $ids = array_unique($ids);
    $mod = D('UserBase');
    $cnt = 0;
    $stm = strtotime(date('Y-m-d 00:00:00',strtotime($_REQUEST['stime'])));
    $etm = strtotime(date('Y-m-d 23:59:59',strtotime($_REQUEST['etime'])));
    $dat['list'] = [];
    if(!$ids) $this->display();
    foreach($ids as $uid)
    {
      $grm = D('GlamourRecord')->set_type(Model\GlamourRecordModel::TYPE_INCOME_LIVE_GIFT)->set_uid($uid);
      $tgl = (int)$grm->where(
      [
        'uid' => $uid,
        'create_time' => [['egt',$stm],['elt',$etm]],
      ])->sum('glamour');
      $tcs = (int)D('WithdrawCash')->where(
      [
        'uid'         => $uid,
        'create_time' => [['egt',$stm],['elt',$etm]],
        'state'       => ['neq',3],
      ])->sum('glamour');
      $tex = (int)D('OrderGlamour')->where(
      [
        'uid'         => $uid,
        'create_time' => [['egt',$stm],['elt',$etm]],
        'state'       => 2,
      ])->sum('fee');
      $cnt++;
      cli_echo("$uid\t$tgl\t$tex\t$tcs".PHP_EOL);
      $dat['list'][] =
      [
        'uid'            => $uid,
        'total_income'   => $tgl,
        'total_exchange' => $tex,
        'total_cash'     => $tcs,
      ];
    }
    if(trim($_REQUEST['download']))
    {
      $dat['export'][] =
      [
        '用户ID'   => '用户ID',
        '总魅力'   => '总魅力',
        '已兑换'   => '已兑换',
        '已提现'   => '已提现',
      ];
      foreach($dat['list'] ?: [] as $v)
      {
        $dat['export'][] =
        [
          '用户ID'   => $v['uid'],
          '总魅力'   => $v['total_income'],
          '已兑换'   => $v['total_exchange'],
          '已提现'   => $v['total_cash'],
        ];
      }
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }

  // 主播排期
  public function schedule()
  {
    $this->data = $dat;
    $this->display();
  }


  public function schedule_save()
  {
    //
//    echo 11;
  }




  public function fun_subs()
  {
//    var_dump($_GET);exit();
    $mod = D("ChestSub");
    $map['pub_id'] = ['eq',$_GET['id']];
    $count = $mod->where($map)->count('id');
    $page = new \Think\Page($count,20);
    $show = $page->show();
    $arr = $mod->where($map)->limit($page->firstRow.','.$page->listRow)->select();
    $this->assign('page',$show);
    $this->assign("data",$arr);
    $this->display();
  }

  public function fun_bagg()
  {
    $mod = D("ChestPub");
//    var_dump($mod);exit();
//    var_dump($_GET);exit();
    $map = [];
    $map['diamond'] = ['eq','500'];
    if(!empty($_GET['kwd'])){
      $map['uid'] = array('eq',$_GET['kwd']);
    }
    if(!empty($_GET['stime']) && !empty($_GET['etime'])){
      $map['create_time'] = array(array('gt',strtotime($_GET['stime'])),array('lt',strtotime($_GET['etime'])),'and');
    }
    $count = $mod->where($map)->count('id');//输出个数
    $page = new \Think\Page($count,20);//显示总数 个数
    $show = $page->show();//返回页码
    $list = $mod->where($map)->limit($page->firstRow.','.$page->listRows)->select();//实现搜索显示
    $this->assign('count',$count);//个数
    $this->assign("list",$list);
    $this->assign("page",$show);//页码显示
    $this->display();



//    $ord =
//        [
//            'p.create_time' => 'desc',
//            's.cnt_subs'    => 'desc',
//            'p.id'          => 'desc',
//        ];
//
//    $sql = $mod->table('__LUCKY_BAG_SUB__')->field(
//        [
//            'pub_id',
//            'count(id)'        => 'cnt_subs',
//            'sum(glamour)'     => 'cnt_glamour',
//            'max(create_time)' => 'last_time',
//        ])->group('pub_id')->buildSql();//字段切换 拼接sql

//    $map = $mod->set_table('__LUCKY_BAG_PUB__')->alias('p')->get_filters();

//    $dat['list'] = $mod->field('p.*,s.cnt_subs,s.cnt_glamour,s.last_time')
//        ->join('left join '.$sql.' s on s.pub_id = p.id')
//        ->plist($this->page_size,$map)
//        ->lists('',$ord);

//    $this->pager = $mod->pager;
//    $this->page = $dat['page_html'] = $this->pager->show();
//    $map = $mod->alias('s')->get_filters();
//    //$dat['cnt_subs'] = $mod->table('__LUCKY_BAG_SUB__')->where($map)->count('id');
//    $dat['cnt_user'] = $mod->where($map)->count('distinct uid');
//    $dat['users'] = D('UserBase')->get_users_account($dat['list']);
//    $this->data = $dat;
    //die(json_encode($dat));
//    $this->display();
  }

}