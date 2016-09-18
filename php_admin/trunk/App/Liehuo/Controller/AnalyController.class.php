<?php
namespace Liehuo\Controller;

class AnalyController extends PublicController
{

  public function __construct()
  {
    parent::__construct();
    $this->is_offline = $_REQUEST['rdrs_type'] == 'dt';
  }


  public function daily_user()
  {
    $mod = D('Stat')->table($this->is_offline ? '__DT_ANALYSIS_DATA__' : '__DAILY_ANALYSIS_DATA__');
    $map = $mod->get_filters();
    $lst = $mod->plist($this->page_size,$map)->lists('','dtime desc,id desc');
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    foreach($lst ?: [] as $v)
    {
      $dat['list'][] =
          [
              '日期'     => $v['dtime'],
              '用户ID'   => $v['uid'],
              '设备'     => $dat['devices'][$v['device']],
              '渠道'     => $dat['channels'][$v['ch_id']]['ch_name'],
              '广告'     => $dat['advers'][$v['adver_id']]['ch_serial'],
              '性别'     => C('USER_SEX_IS.'.$v['gender']),
              '不喜欢'   => $v['day_nope_num'],
              '被不喜欢' => $v['day_been_nope_num'],
              '点赞'     => $v['day_free_thumb_num'],
              '被点赞'   => $v['day_been_thumb_num'],
              '超赞'     => $v['day_free_like_num'],
              '被超赞'   => $v['day_free_been_like_num'],
              '金赞'     => $v['day_pay_like_num'],
              '被金赞'   => $v['day_pay_been_like_num'],
              '免费匹配' => $v['day_free_match_num'],
              '付费匹配' => $v['day_pay_match_num'],
              '购买金额' => $v['day_buy_like_sums'],
              '提现金额' => $v['day_cash_sums'],
          ];
    }
    if(trim($_REQUEST['download'])) $dat['export'] = $dat['list'];
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->time_type = 'dtime';
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display('common');
  }

  public function user_stat()
  {
    $_REQUEST['time_type'] = $this->is_offline ? 'reg_time' : 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-1 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-1 days'));
    $mod = D('Stat')->set_table($this->is_offline ? '__DT_ANALYSIS_DATA__' : '__DAILY_ANALYSIS_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->limit(5000)->lists($map,'dtime desc,id desc');
    /*
        $arr = $mod->field(
        [
          'dtime','uid','ch_id','adver_id','pkg_id',
        ])->group('uid')->lists($map,'dtime desc,id desc');
    */
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      $key = $v['uid'];//$v['dtime'].'-'.
      $sex = (int)$v['gender'];
      $old = $lst[$key] ?: [];
      $lst[$key] = array_merge($old,
          [
              'dtime'          => $v['dtime'],
              'uid'            => $v['uid'],
              'ch_id'          => $v['ch_id'],
              'adver_id'       => $v['adver_id'],
              'pkg_id'         => $v['pkg_id'],
              'ad_serial'      => $dat['advers'][$v['adver_id']]['ch_serial'],
              'cnt_all_like'   => (int)$v['day_free_thumb_num']
                  + (int)$v['day_pay_thumb_num']
                  + (int)$v['day_free_like_num']
                  + (int)$v['day_pay_like_num']
                  + (int)$old['cnt_all_like'],
              'cnt_been_like'  => (int)$v['day_free_been_thumb_num']
                  + (int)$v['day_pay_been_thumb_num']
                  + (int)$v['day_free_been_like_num']
                  + (int)$v['day_pay_been_like_num']
                  + (int)$old['cnt_been_like'],
              'cnt_match'      => (int)$v['day_free_match_num']
                  + (int)$v['day_pay_match_num']
                  + (int)$old['cnt_match'],
              'cnt_buy_sums'   => (float)$v['day_buy_like_sums'] + (float)$v['day_buy_vip_sums'] + (float)$old['cnt_buy_sums'],
          ]);
      $lst[$key]['cnt_slide'] = (int)$v['day_nope_num'] + (int)$lst[$key]['cnt_all_like'] + (int)$old['cnt_slide'];
    }
    // 地推已注册无活跃用户
    if($this->is_offline)
    {
      $tab = $mod->set_table('__DT_USER_SOURCE__')->getTableName();
      $sql = $mod->set_table('__DT_ANALYSIS_DATA__')->field('uid')->where(array_merge($map,['uid' => ['exp','= '.$tab.'.uid']]))->limit(1)->buildSql();
      $nal = $mod->table($tab)->where('not exists '.$sql)->klist('uid',$map) ?: [];
      foreach($nal as $v)
      {
        $uid = (int)$v['uid'];
        if(isset($lst[$uid])) continue;
        $lst[$uid] =
            [
                'dtime'     => date('Y-m-d',$v['reg_time']),
                'uid'       => $v['uid'],
                'ch_id'     => $v['ch_id'],
                'adver_id'  => $v['adver_id'],
                'pkg_id'    => $v['pkg_id'],
                'ad_serial' => $dat['advers'][$v['adver_id']]['ch_serial'],
            ];
      }
      $dat['reg_citys'] = $mod->get_reg_city_by_list($lst);
      $dat['user_locs'] = D('LocationBase')->get_by_list($lst);
    }
    $umd = D('UserBase');
    $dat['users'] = $umd->get_by_list($lst) ?: [];
    $dls = [];
    foreach($dat['users'] as $v)
    {
      $dls[$v['device_id']][] = $v['uid'];
    }
    foreach($lst ?: [] as $k => $v)
    {
      $usr = $dat['users'][$v['uid']] ?: [];
      $usr['active_time'] = $umd->get_active_time($v['uid']);
      $pkg = $dat['packages'][$v['pkg_id']] ?: [];
      $row =
          [
            //'日期'     => $v['dtime'],
              '广告'     => $v['ad_serial'].'('.$dat['devices'][$pkg['pkg_device']].' '.$pkg['pkg_version'].')',
              '用户ID'   => '<a href="'.U('UserBase/view',['uid' => $v['uid']]).'" target="_blank">'.$v['uid'].'</a>',
              '昵称'     => $usr['nickname'],
              '性别'     => C('USER_SEX_IS.'.$usr['sex']),
              '手机号'   => $usr['phone'],
              '分数'     => $usr['score'],
              '设备'     => $usr['device'],
              '机型'     => $usr['device_model'],
              '设备ID'   => $usr['device_id'] && count($dls[$usr['device_id']] ?: []) >= 2 ? ('<b class="text-danger">'.$usr['device_id'].'</b>') : ($usr['device_id'] ?: '-'),
              '注册时间' => $usr['reg_time'] ? date('Y-m-d H:i:s',$usr['reg_time']) : '-',
              '最后活跃' => $usr['active_time'] ? date('Y-m-d H:i:s',$usr['active_time']) : '-',
              '送赞数'   => '<a href="'.U('UserBase/like_list',['uid' => $v['uid']]).'" target="_blank">'.($v['cnt_all_like'] ?: '-').'</a>',
              '被赞数'   => '<a href="'.U('UserBase/like_list',['oid' => $v['uid']]).'" target="_blank">'.($v['cnt_been_like'] ?: '-').'</a>',
              '匹配数'   => '<a href="'.U('UserBase/like_list',['uid' => $v['uid'],'matched' => 1]).'" target="_blank">'.($v['cnt_match'] ?: '-').'</a>',
              '滑动数'   => '<a href="'.U('UserBase/slide_list',['uid' => $v['uid']]).'" target="_blank">'.($v['cnt_slide'] ?: '-').'</a>',
          ];
      if($this->is_offline)
      {
        $row['注册地'] = $dat['reg_citys'][$v['uid']]['city'];
        $row['登陆地'] = $dat['user_locs'][$v['uid']]['city'];
      }
      else $row['购买金额'] = $v['cnt_buy_sums'] ?: '-';
      $dat['list'][] = $row;
      if(trim($_REQUEST['download'])) $dat['export'][] = array_map(function($v)
      {
        return preg_replace('/<[^>]+>/i','',$v);
      },$row);
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->is_offline && $this->all_params = ['rdrs_type' => 'dt'];
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }

  public function user_stat1()
  {
    $_REQUEST['time_type'] = $this->is_offline ? 'reg_time' : 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-1 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-1 days'));
    $mod = D('Stat')->set_table($this->is_offline ? '__DT_ANALYSIS_DATA__' : '__DAILY_ANALYSIS_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->limit(5000)->lists($map,'dtime desc,id desc');
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      $key = $v['uid'];//$v['dtime'].'-'.
      $sex = (int)$v['gender'];
      $old = $lst[$key] ?: [];
      $lst[$key] = array_merge($old,
          [
              'dtime'          => $v['dtime'],
              'uid'            => $v['uid'],
              'ch_id'          => $v['ch_id'],
              'adver_id'       => $v['adver_id'],
              'pkg_id'         => $v['pkg_id'],
              'ad_serial'      => $dat['advers'][$v['adver_id']]['ch_serial'],
              'cnt_all_like'   => (int)$v['day_free_thumb_num']
                  + (int)$v['day_pay_thumb_num']
                  + (int)$v['day_free_like_num']
                  + (int)$v['day_pay_like_num']
                  + (int)$old['cnt_all_like'],
              'cnt_been_like'  => (int)$v['day_free_been_thumb_num']
                  + (int)$v['day_pay_been_thumb_num']
                  + (int)$v['day_free_been_like_num']
                  + (int)$v['day_pay_been_like_num']
                  + (int)$old['cnt_been_like'],
              'cnt_match'      => (int)$v['day_free_match_num']
                  + (int)$v['day_pay_match_num']
                  + (int)$old['cnt_match'],
              'cnt_buy_sums'   => (float)$v['day_buy_like_sums'] + (float)$v['day_buy_vip_sums'] + (float)$old['cnt_buy_sums'],
          ]);
      $lst[$key]['cnt_slide'] = (int)$v['day_nope_num'] + (int)$lst[$key]['cnt_all_like'] + (int)$old['cnt_slide'];
    }
    // 地推已注册无活跃用户
    if($this->is_offline)
    {
      $tab = $mod->set_table('__DT_USER_SOURCE__')->getTableName();
      $sql = $mod->set_table('__DT_ANALYSIS_DATA__')->field('uid')->where(array_merge($map,['uid' => ['exp','= '.$tab.'.uid']]))->limit(1)->buildSql();
      $nal = $mod->table($tab)->where('not exists '.$sql)->klist('uid',$map) ?: [];
      foreach($nal as $v)
      {
        $uid = (int)$v['uid'];
        if(isset($lst[$uid])) continue;
        $lst[$uid] =
            [
                'dtime'     => date('Y-m-d',$v['reg_time']),
                'uid'       => $v['uid'],
                'ch_id'     => $v['ch_id'],
                'adver_id'  => $v['adver_id'],
                'pkg_id'    => $v['pkg_id'],
                'ad_serial' => $dat['advers'][$v['adver_id']]['ch_serial'],
            ];
      }
      $dat['reg_citys'] = $mod->get_reg_city_by_list($lst);
      $dat['user_locs'] = D('LocationBase')->get_by_list($lst);
    }
    $umd = D('UserBase');
    $dat['users'] = $umd->get_by_list($lst) ?: [];
    $dls = [];
    foreach($dat['users'] as $v)
    {
      $dls[$v['device_id']][] = $v['uid'];
    }
    foreach($lst ?: [] as $k => $v)
    {
      $usr = $dat['users'][$v['uid']] ?: [];
      $usr['active_time'] = $umd->get_active_time($v['uid']);
      $pkg = $dat['packages'][$v['pkg_id']] ?: [];
      $row =
          [
            //'日期'     => $v['dtime'],
              '广告'     => $v['ad_serial'].'('.$dat['devices'][$pkg['pkg_device']].' '.$pkg['pkg_version'].')',
              '用户ID'   => '<a href="'.U('UserBase/view',['uid' => $v['uid']]).'" target="_blank">'.$v['uid'].'</a>',
              '昵称'     => $usr['nickname'],
              '性别'     => C('USER_SEX_IS.'.$usr['sex']),
              '手机号'   => $usr['phone'],
              '分数'     => $usr['score'],
              '设备'     => $usr['device'],
              '机型'     => $usr['device_model'],
              '设备ID'   => $usr['device_id'] && count($dls[$usr['device_id']] ?: []) >= 2 ? ('<b class="text-danger">'.$usr['device_id'].'</b>') : ($usr['device_id'] ?: '-'),
              '注册时间' => $usr['reg_time'] ? date('Y-m-d H:i:s',$usr['reg_time']) : '-',
              '最后活跃' => $usr['active_time'] ? date('Y-m-d H:i:s',$usr['active_time']) : '-',
              '送赞数'   => '<a href="'.U('UserBase/like_list',['uid' => $v['uid']]).'" target="_blank">'.($v['cnt_all_like'] ?: '-').'</a>',
              '被赞数'   => '<a href="'.U('UserBase/like_list',['oid' => $v['uid']]).'" target="_blank">'.($v['cnt_been_like'] ?: '-').'</a>',
              '匹配数'   => '<a href="'.U('UserBase/like_list',['uid' => $v['uid'],'matched' => 1]).'" target="_blank">'.($v['cnt_match'] ?: '-').'</a>',
              '滑动数'   => '<a href="'.U('UserBase/slide_list',['uid' => $v['uid']]).'" target="_blank">'.($v['cnt_slide'] ?: '-').'</a>',
          ];
      if($this->is_offline)
      {
        $row['注册地'] = $dat['reg_citys'][$v['uid']]['city'];
        $row['登陆地'] = $dat['user_locs'][$v['uid']]['city'];
      }
      else $row['购买金额'] = $v['cnt_buy_sums'] ?: '-';
      $dat['list'][] = $row;
      if(trim($_REQUEST['download'])) $dat['export'][] = array_map(function($v)
      {
        return preg_replace('/<[^>]+>/i','',$v);
      },$row);
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->is_offline && $this->all_params = ['rdrs_type' => 'dt'];
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }


  // 总体每日行为数据
  public function daily_analy()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    $mod = D('Stat')->set_table('__STAT_REAL_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,id desc');
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $dat['pkg_channels'] = $mod->get_pkg_channels() ?: [];
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      $key = $v['dtime'];
      $sex = (int)$v['gender'];
      $old = $lst[$key] ?: [];
      $lst[$key] = array_merge($old,
          [
              'dtime'          => $v['dtime'],
              'ch_id'          => $v['ch_id'],
              'adver_id'       => $v['adver_id'],
              'ad_serial'      => $dat['advers'][$v['adver_id']]['ch_serial'],
              'cnt_reg'        => (int)$v['day_reg_user_num'] + (int)$old['cnt_reg'],
              'cnt_reg'.$sex   => (int)$v['day_reg_user_num'] + (int)$old['cnt_reg'.$sex],
              'cnt_dau'        => (int)$v['day_active_user_num'] + (int)$old['cnt_dau'],
              'cnt_1st'        => (int)$v['day_1st_user_num']  + (int)$old['cnt_1st'],
              'cnt_30th'       => (int)$v['day_30th_user_num'] + (int)$old['cnt_30th'],
              'cnt_slide_user' => (int)$v['day_slide_user_num'] + (int)$old['cnt_slide_user'],
              'cnt_slide'      => (int)$v['day_nope_total_num']
                  + (int)$v['day_free_thumb_total_num']
                  + (int)$v['day_pay_thumb_total_num']
                  + (int)$v['day_free_like_total_num']
                  + (int)$v['day_pay_like_total_num']
                  + (int)$old['cnt_slide'],
              'cnt_nope_user'  => (int)$v['day_nope_user_num'] + (int)$old['cnt_nope_user'],
              'cnt_nope'       => (int)$v['day_nope_total_num'] + (int)$old['cnt_nope'],
              'cnt_like_user'  => (int)$v['day_thumb_user_num'] + (int)$old['cnt_like_user'],
              'cnt_like'       => (int)$v['day_free_thumb_total_num']
                  + (int)$v['day_pay_thumb_total_num']
                  + (int)$old['cnt_like'],
              'cnt_slike_user' => (int)$v['day_like_user_num'] + (int)$old['cnt_slike_user'],
              'cnt_slike'      => (int)$v['day_free_like_total_num']
                  + (int)$v['day_pay_like_total_num']
                  + (int)$old['cnt_slike'],
              'cnt_all_like'   => (int)$v['day_free_thumb_total_num']
                  + (int)$v['day_pay_thumb_total_num']
                  + (int)$v['day_free_like_total_num']
                  + (int)$v['day_pay_like_total_num']
                  + (int)$old['cnt_all_like'],
              'cnt_match_user' => (int)$v['day_match_user_num'] + (int)$old['cnt_match_user'],
              'cnt_match'      => (int)$v['day_free_match_total_num']
                  + (int)$v['day_pay_match_total_num']
                  + (int)$old['cnt_match'],
              'cnt_buy_user'   => (int)$v['day_buy_thumb_user_num']
                  + (int)$v['day_buy_like_user_num']
                  + (int)$v['day_buy_vip_user_num']
                  + (int)$old['cnt_buy_user'],
              'cnt_buy_sums'   => (float)$v['day_buy_thumb_sums']
                  + (float)$v['day_buy_like_sums']
                  + (float)$v['day_buy_vip_sums']
                  + (float)$old['cnt_buy_sums'],
              'cnt_charge_user'=> (int)$v['day_charge_user_num'] + (int)$old['cnt_charge_user'],
              'cnt_charge_sums'=> (float)$v['day_charge_sums'] + (int)$old['cnt_charge_sums'],
              'cnt_vip_sums'   => (float)$v['day_buy_vip_sums'] + (int)$old['cnt_vip_sums'],
              'cnt_cash_user'  => (int)$v['day_cash_user_num'] + (int)$old['cnt_cash_user'],
              'cnt_cash_sums'  => (float)$v['day_cash_sums'] + (int)$old['cnt_cash_sums'],
          ]);
    }
    foreach($lst ?: [] as $k => $v)
    {
      $v['cnt_slide_rate']   = round($v['cnt_slide_user']  / $v['cnt_dau'] * 100,1).'%';
      $v['cnt_slide_avg']    = round($v['cnt_slide']       / $v['cnt_slide_user'],2);
      $v['cnt_nope_rate']    = round($v['cnt_nope_user']   / $v['cnt_dau'] * 100,1).'%';
      $v['cnt_nope_avg']     = round($v['cnt_nope']        / $v['cnt_nope_user'],2);
      $v['cnt_like_rate']    = round($v['cnt_like_user']   / $v['cnt_dau'] * 100,1).'%';
      $v['cnt_like_avg']     = round($v['cnt_like']        / $v['cnt_like_user'],2);
      $v['cnt_slike_rate']   = round($v['cnt_slike_user']  / $v['cnt_dau'] * 100,1).'%';
      $v['cnt_slike_avg']    = round($v['cnt_slike']       / $v['cnt_slike_user'],2);
      $v['cnt_all_like_avg'] = round($v['cnt_all_like']    / $v['cnt_dau'],1);
      $v['cnt_match_rate']   = round($v['cnt_match_user']  / $v['cnt_like_user'] * 100,1).'%';
      $v['cnt_match_avg']    = round($v['cnt_match']       / $v['cnt_match_user'],2);
      $v['cnt_buy_rate']     = round($v['cnt_buy_user']    / $v['cnt_dau'] * 100,1).'%';
      $v['cnt_buy_avg']      = round($v['cnt_buy_sums']    / $v['cnt_buy_user'],1);
      $v['cnt_charge_rate']  = round($v['cnt_charge_user'] / $v['cnt_dau'] * 100,1).'%';
      $v['cnt_charge_avg']   = round($v['cnt_charge_sums'] / $v['cnt_charge_user'],1);
      $v['cnt_cash_rate']    = round($v['cnt_cash_user']   / $v['cnt_dau'] * 100,1).'%';
      $v['cnt_cash_avg']     = round($v['cnt_cash_sums']   / $v['cnt_cash_user'],1);
      $row =
          [
              '日期'     => $v['dtime'],
              '日活'     => $v['cnt_dau'],
              '滑动人数' => '<span class="tip" data-original-title="'.$v['cnt_slide_user'].'">'.$v['cnt_slide_rate'].'</span>',
              '人均滑动' => '<span class="tip" data-original-title="'.$v['cnt_slide'].'">'.$v['cnt_slide_avg'].'</span>',
              '左滑人数' => '<span class="tip" data-original-title="'.$v['cnt_nope_user'].'">'.$v['cnt_nope_rate'].'</span>',
              '人均左滑' => '<span class="tip" data-original-title="'.$v['cnt_nope'].'">'.$v['cnt_nope_avg'].'</span>',
              '人均点赞' => '<span class="tip" data-original-title="'.$v['cnt_all_like'].'">'.$v['cnt_all_like_avg'].'</span>',
              '喜欢人数' => '<span class="tip" data-original-title="'.$v['cnt_like_user'].'">'.$v['cnt_like_rate'].'</span>',
              '人均喜欢' => '<span class="tip" data-original-title="'.$v['cnt_like'].'">'.$v['cnt_like_avg'].'</span>',
              '超赞人数' => '<span class="tip" data-original-title="'.$v['cnt_slike_user'].'">'.$v['cnt_slike_rate'].'</span>',
              '人均超赞' => '<span class="tip" data-original-title="'.$v['cnt_slike'].'">'.$v['cnt_slike_avg'].'</span>',
              '匹配人数' => '<span class="tip" data-original-title="'.$v['cnt_match_user'].'">'.$v['cnt_match_rate'].'</span>',
              '人均匹配' => '<span class="tip" data-original-title="'.$v['cnt_match'].'">'.$v['cnt_match_avg'].'</span>',
            //'购买人数' => $v['cnt_buy_user'],
            //'购买总额' => $v['cnt_buy_sums'],
              '充值人数' => $v['cnt_charge_user'],
              '充值总额' => $v['cnt_charge_sums'],
              '会员总额' => $v['cnt_vip_sums'],
              '提现人数' => $v['cnt_cash_user'],
              '提现金额' => $v['cnt_cash_sums'],
          ];
      $dat['list'][] = $row;
    }
    if(trim($_REQUEST['download'])) $dat['export'] = $dat['list'];
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->time_type = 'dtime';
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display('common');
  }


  public function daily_stat()
  {
    $mod = D('Stat')->table('__STAT_REAL_DATA__');
    $map = $mod->get_filters();
    $lst = $mod->plist($this->page_size,$map)->lists();
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    foreach($lst ?: [] as $v)
    {
      $row =
          [
              '日期'         => $v['dtime'],
              '设备'         => $dat['devices'][$v['device']],
              '渠道'         => $dat['channels'][$v['ch_id']]['ch_name'],
              '广告'         => $dat['advers'][$v['adver_id']]['ch_serial'],
              '性别'         => C('USER_SEX_IS.'.$v['gender']),
          ];
      if($_REQUEST['type'] == 'behavior') $row = array_merge($row,
          [
              '不喜欢次数'   => $v['day_nope_total_num'],
              '不喜欢人数'   => $v['day_nope_user_num'],
              '点赞次数'     => $v['day_free_thumb_total_num'],
              '点赞人数'     => $v['day_free_thumb_user_num'],
              '免费超赞次数' => $v['day_free_like_total_num'],
              '免费超赞人数' => $v['day_free_like_user_num'],
              '付费超赞次数' => $v['day_pay_like_total_num'],
              '付费超赞人数' => $v['day_pay_like_user_num'],
              '免费匹配次数' => $v['day_free_match_total_num'],
              '免费匹配人数' => $v['day_free_match_user_num'],
              '付费匹配次数' => $v['day_pay_match_total_num'],
              '付费匹配人数' => $v['day_pay_match_user_num'],
              '购买超赞金额' => $v['day_buy_like_sums'],
              '购买超赞人数' => $v['day_buy_like_user_num'],
              '提现金额'     => $v['day_cash_sums'],
              '提现人数'     => $v['day_cash_user_num'],
          ]);
      else $row = array_merge($row,
          [
              '注册人数'     => $v['day_reg_user_num'],
              '活跃人数'     => $v['day_active_user_num'],
              '合格'         => $v['day_pass_score_user_num'],
              '高分'         => $v['day_high_score_user_num'],
              '次留'         => $v['day_1st_user_num'],
              '3日留存'      => $v['day_3rd_user_num'],
              '7日留存'      => $v['day_7th_user_num'],
              '15日留存'     => $v['day_15th_user_num'],
              '30日留存'     => $v['day_30th_user_num'],
          ]);
      $dat['list'][] = $row;
    }
    if(trim($_REQUEST['download'])) $dat['export'] = $dat['list'];
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->time_type = 'dtime';
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display('common');
  }

  public function daily_income()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-7 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    $mod = D('Stat')->set_table('__STAT_REAL_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,id desc');
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      $key = $v['dtime'];
      $sex = (int)$v['gender'];
      $old = $lst[$key] ?: [];
      $lst[$key] = array_merge($old,
          [
              'dtime'         => $v['dtime'],
              'cnt_vip_user'  => (int)$v['day_buy_vip_user_num'] + (int)$old['cnt_vip_user'],
              'cnt_vip_sums'  => (float)$v['day_buy_vip_sums'] + (float)$old['cnt_vip_sums'],
              'cnt_buy_user'  => (int)$v['day_buy_like_user_num'] + (int)$old['cnt_buy_user'],
              'cnt_buy_sums'  => (float)$v['day_buy_like_sums'] + (float)$old['cnt_buy_sums'],
              'cnt_cash_user' => (int)$v['day_cash_user_num'] + (int)$old['cnt_cash_user'],
              'cnt_cash_sums' => (float)$v['day_cash_sums'] + (float)$old['cnt_cash_sums'],
          ]);
    }
    $all = [];
    foreach($lst ?: [] as $k => $v)
    {
      $par =
          [
              'stime' => $v['dtime'],
              'etime' => $v['dtime'],
          ];
      $row =
          [
              '日期'       => $v['dtime'],
              'VIP人数'    => '<a href="'.U('UserBase/order_list?state=2&goods=vip',$par).'" target="_blank">'.$v['cnt_vip_user'].'</a>',
              'VIP金额'    => $v['cnt_vip_sums'],
              '购买赞人数' => '<a href="'.U('UserBase/order_list?state=2&goods=all_like',$par).'" target="_blank">'.$v['cnt_buy_user'].'</a>',
              '购买赞金额' => $v['cnt_buy_sums'],
              '提现人数'   => '<a href="'.U('UserBase/cash_list?state=2&time_type=finish',$par).'" target="_blank">'.$v['cnt_cash_user'].'</a>',
              '提现金额'   => $v['cnt_cash_sums'],
          ];
      foreach($row as $k => $v)
      {
        $all[$k] += (float)preg_replace('/<[^>]+>|\s+/i','',$v);
      }
      $dat['list'][] = $row;
      $all['日期'] = '合计';
    }
    array_unshift($dat['list'],$all);
    if(trim($_REQUEST['download'])) $dat['export'] = $dat['list'];
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->time_type = 'dtime';
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display('common');
  }

  public function adver_stat()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-1 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-1 days'));
    $days = (int)((strtotime($_REQUEST['etime']) - strtotime($_REQUEST['stime'])) / 60 / 60 / 24) + 1;
    $isad = !!(int)$_REQUEST['ch_id'];
    $mod = D('Stat')->set_table($this->is_offline ? '__DT_STAT_DATA__' : '__STAT_REAL_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,id desc');
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    $dat['markets']  = $mod->get_market_list(
        [
            'stime' => $_REQUEST['stime'],
            'etime' => $_REQUEST['etime'],
        ]) ?: [];
    $mls = [];
    foreach($dat['markets'] ?: [] as $v)
    {
      $mls[$v['ma_date']][$v['ch_id']][$v['adv_id']][0] = $v['android'];
      $mls[$v['ma_date']][$v['ch_id']][$v['adv_id']][1] = $v['ios'];
    }
    // 取当日注册用户总消费
    $sql = $mod->table('__REAL_TIME_DATA__')->field('uid')->where(
        [
            'reg_time' =>
                [
                    ['egt',strtotime(date('Y-m-d 00:00:00',strtotime($_REQUEST['stime'])))],
                    ['elt',strtotime(date('Y-m-d 23:59:59',strtotime($_REQUEST['etime'])))],
                ],
        ])->buildSql();
    if($days > 7) $als = [];
    else $als = $mod->table('__DAILY_ANALYSIS_DATA__')
        ->field(
            [
                'ch_id','adver_id',
                'count(distinct uid)'   => 'cnt_user',
                'sum(day_charge_sums)'  => 'cnt_charge_sums',
                'sum(day_buy_vip_sums)' => 'cnt_buyvip_sums',
                'sum(day_charge_sums + day_buy_vip_sums)' => 'cnt_pay_sums',
            ])
        ->where(
            [
                'dtime'    => ['egt',date('Y-m-d',strtotime($_REQUEST['stime']))],
                'uid'      => ['exp','in '.$sql],
                '_complex' =>
                    [
                        '_logic' => 'or',
                        'day_charge_sums'  => ['gt',0],
                        'day_buy_vip_sums' => ['gt',0],
                    ],
            ])
        ->group('ch_id,adver_id')
        //->fetchSql(true)
        ->select() ?: [];
    //header('debug-sql: '.json_encode($als));$als = [];
    foreach($als as $v)
    {
      $ads = $dat['advers'][$v['adver_id']]['ch_serial'];
      $key = $isad ? $ads : $v['ch_id'];
      $old = $dat['analy_data'][$key] ?: [];
      $dat['analy_data'][$key] = array_merge($old,
          [
              'ch_id'           => $v['ch_id'],
              'adver_id'        => $v['adver_id'],
              'ad_serial'       => $ads,
              'cnt_user'        => (int)$v['cnt_user']          + (int)$old['cnt_user'],
              'cnt_charge_sums' => (float)$v['cnt_charge_sums'] + (float)$old['cnt_charge_sums'],
              'cnt_buyvip_sums' => (float)$v['cnt_buyvip_sums'] + (float)$old['cnt_buyvip_sums'],
              'cnt_pay_sums'    => (float)$v['cnt_pay_sums']    + (float)$old['cnt_pay_sums'],
          ]);
    }
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      //if($v['dtime'] == '2016-01-12') continue;
      $ads = $dat['advers'][$v['adver_id']]['ch_serial'];
      $key = $isad ? $ads : $v['ch_id'];
      $sex = (int)$v['gender'];
      $fee = (float)$mls[$v['dtime']][$v['ch_id']][0][$v['device']];
      $mls[$v['dtime']][$v['ch_id']][0][$v['device']] = 0;
      $old = $lst[$key] ?: [];
      $lst[$key] = array_merge($old,
          [
              'ch_id'           => $v['ch_id'],
              'adver_id'        => $v['adver_id'],
              'ad_serial'       => $ads,
              'cnt_fee'         => $fee + (float)$old['cnt_fee'],
              'cnt_reg'         => (int)$v['day_reg_user_num'] + (int)$old['cnt_reg'],
              'cnt_reg'.$sex    => (int)$v['day_reg_user_num'] + (int)$old['cnt_reg'.$sex],
              'cnt_dau'         => (int)$v['day_active_user_num'] + (int)$old['cnt_dau'],
              'cnt_score_pass'  => (int)$v['day_pass_score_user_num'] + (int)$old['cnt_score_pass'],
              'cnt_score_high'  => (int)$v['day_high_score_user_num'] + (int)$old['cnt_score_high'],
              'cnt_1st'         => (int)$v['day_1st_user_num']  + (int)$old['cnt_1st'],
              'cnt_3rd'         => (int)$v['day_3rd_user_num']  + (int)$old['cnt_3rd'],
              'cnt_7th'         => (int)$v['day_7th_user_num']  + (int)$old['cnt_7th'],
              'cnt_15th'        => (int)$v['day_15th_user_num'] + (int)$old['cnt_15th'],
              'cnt_30th'        => (int)$v['day_30th_user_num'] + (int)$old['cnt_30th'],
              'cnt_buy_user'    => (int)$v['day_buy_like_user_num'] + (int)$v['day_buy_vip_user_num'] + (int)$old['cnt_buy_user'],
              'cnt_buy_sums'    => (float)$v['day_charge_sums'] + (float)$old['cnt_buy_sums'],
              'cnt_pay_user'    => (float)$dat['analy_data'][$key]['cnt_user'],
              'cnt_pay_sums'    => (float)$dat['analy_data'][$key]['cnt_pay_sums'],
          ]);
    }
    $all = [];
    foreach($lst ?: [] as $key => $row)
    {
      foreach($row ?: [] as $k => $v)
      {
        $all[$k] += (float)$v;
      }
      $all['isall'] = true;
    }
    $lst = array_merge([$all],$lst);
    foreach($lst ?: [] as $k => $v)
    {
      $v['cnt_1st_rate']  = round($v['cnt_1st'] / $v['cnt_reg'],3);
      $v['cnt_1st_rate']  = $v['cnt_1st_rate']  > 0 && $v['cnt_1st_rate']  <= 1 ? ($v['cnt_1st_rate']  * 100).'%' : '--';
      $v['cnt_1st_cost']  = round($v['cnt_fee'] / $v['cnt_1st'],3);
      $v['cnt_3rd_rate']  = round($v['cnt_3rd'] / $v['cnt_reg'],3);
      $v['cnt_3rd_rate']  = $v['cnt_3rd_rate']  > 0 && $v['cnt_3rd_rate']  <= 1 ? ($v['cnt_3rd_rate']  * 100).'%' : '--';
      $v['cnt_7th_rate']  = round($v['cnt_7th'] / $v['cnt_reg'],3);
      $v['cnt_7th_rate']  = $v['cnt_7th_rate']  > 0 && $v['cnt_7th_rate']  <= 1 ? ($v['cnt_7th_rate']  * 100).'%' : '--';
      $v['cnt_15th_rate'] = round($v['cnt_15th'] / $v['cnt_reg'],3);
      $v['cnt_15th_rate'] = $v['cnt_15th_rate'] > 0 && $v['cnt_15th_rate'] <= 1 ? ($v['cnt_15th_rate'] * 100).'%' : '--';
      $v['cnt_30th_rate'] = round($v['cnt_30th'] / $v['cnt_reg'],3);
      $v['cnt_30th_rate'] = $v['cnt_30th_rate'] > 0 && $v['cnt_30th_rate'] <= 1 ? ($v['cnt_30th_rate'] * 100).'%' : '--';
      $v['cnt_30th_cost'] = round($v['cnt_fee'] / $v['cnt_30th'],3);
      $v['cnt_reg_cost']  = round($v['cnt_fee'] / $v['cnt_reg'],3);
      $v['cnt_score_pass_cost'] = round($v['cnt_fee'] / $v['cnt_score_pass'],3);
      $v['cnt_score_high_cost'] = round($v['cnt_fee'] / $v['cnt_score_high'],3);
      $row =
          [
              '渠道'     => $dat['channels'][$v['ch_id']]['ch_name'] ?: $v['ch_id'],
              '单价'     => $v['cnt_reg_cost'] ?: '-',
              '注册'     => $v['cnt_reg'],
              '注册(男)' => $v['cnt_reg0'],
              '注册(女)' => $v['cnt_reg1'],
              '去新DAU'  => round(($v['cnt_dau'] - $v['cnt_reg']) / $days,1),
              '次留率'   => '<span class="tip" data-original-title="'.$v['cnt_1st'].'">'.$v['cnt_1st_rate'].'</span>',
              '3留率'    => '<span class="tip" data-original-title="'.$v['cnt_3rd'].'">'.$v['cnt_3rd_rate'].'</span>',
              '7留率'    => '<span class="tip" data-original-title="'.$v['cnt_7th'].'">'.$v['cnt_7th_rate'].'</span>',
              '15留率'   => '<span class="tip" data-original-title="'.$v['cnt_15th'].'">'.$v['cnt_15th_rate'].'</span>',
              '月留率'   => '<span class="tip" data-original-title="'.$v['cnt_30th'].'">'.$v['cnt_30th_rate'].'</span>',
              '次留成本' => $v['cnt_1st_cost'] ?: '-',
              '月留成本' => $v['cnt_30th_cost'] ?: '-',
              '合格'     => $v['cnt_score_pass'],
              '合格成本' => $v['cnt_score_pass_cost'] ?: '-',
              '优质'     => $v['cnt_score_high'],
              '优质成本' => $v['cnt_score_high_cost'] ?: '-',
              '付费用户' => $v['cnt_pay_user'] ?: '-',
              '付费总额' => $v['cnt_pay_sums'] ?: '-',
          ];
      $isad && $row['渠道'] = '<a href="'.U('user_stat',
              [
                  'rdrs_type' => $_REQUEST['rdrs_type'],
                  'ad_serial' => $v['ad_serial'],
                  'stime'     => $_REQUEST['stime'],
                  'etime'     => $_REQUEST['etime'],
              ]).'" target="_blank">'.$v['ad_serial'].'</a>';
      $v['isall'] && $row['渠道'] = '合计';
      $dat['list'][] = $row;
    }
    if(trim($_REQUEST['download'])) $dat['export'] = $dat['list'];
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->time_type = 'dtime';
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display('common');
  }


  // 手机版每日推广数据
  public function adver_daily()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    $mod = D('Stat')->set_table($this->is_offline ? '__DT_STAT_DATA__' : '__STAT_REAL_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,id desc');
    $dat['reg_total'] = D('UserBase')->where(
        [
            'reg_time' =>
                [
                    ['egt',strtotime('2015-12-09')],
                    ['elt',strtotime(date('Y-m-d 23:59:59',strtotime($_REQUEST['etime'])))],
                ],
        ])->count('uid');
    $dat['orders'] = D('OrderV2')
        ->field(
            [
                'from_unixtime(pay_time,\'%Y-%m-%d\')' => 'pay_date',
                'count(distinct uid)' => 'cnt_user',
                'sum(fee)' => 'cnt_fee',
            ])
        ->where(
            [
                'pay_time' =>
                    [
                        ['egt',strtotime('2015-12-09')],
                        ['elt',strtotime(date('Y-m-d 23:59:59',strtotime($_REQUEST['etime'])))],
                    ],
                'state' => 2,
                'pay_type' => ['in',[1,2]],
            ])
        ->group('pay_date')
        ->klist('pay_date');
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    $dat['markets']  = $mod->get_market_list(
        [
            'stime' => $_REQUEST['stime'],
            'etime' => $_REQUEST['etime'],
        ]) ?: [];
    $mls = [];
    foreach($dat['markets'] ?: [] as $v)
    {
      $mls[$v['ma_date']][$v['ch_id']][$v['adv_id']][0] = $v['android'];
      $mls[$v['ma_date']][$v['ch_id']][$v['adv_id']][1] = $v['ios'];
    }
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      $key = $v['dtime'];
      $sex = (int)$v['gender'];
      $fee = (float)$mls[$v['dtime']][$v['ch_id']][0][$v['device']];
      $mls[$v['dtime']][$v['ch_id']][0][$v['device']] = 0;
      $dat['reg_total'] = (int)$dat['reg_total'] - (int)$v['day_reg_user_num'];
      $old = $lst[$key] ?: [];
      $lst[$key] = array_merge($old,
          [
              'dtime'           => $v['dtime'],
              'ch_id'           => $v['ch_id'],
              'adver_id'        => $v['adver_id'],
              'ad_serial'       => $dat['advers'][$v['adver_id']]['ch_serial'],
              'cnt_fee'         => $fee + (float)$old['cnt_fee'],
              'cnt_reg'         => (int)$v['day_reg_user_num'] + (int)$old['cnt_reg'],
              'cnt_reg'.$sex    => (int)$v['day_reg_user_num'] + (int)$old['cnt_reg'.$sex],
              'cnt_dau'         => (int)$v['day_active_user_num'] + (int)$old['cnt_dau'],
              'cnt_slide_user'  => (int)$v['day_slide_user_num'] + (int)$old['cnt_slide_user'],
              'cnt_slide'       => (int)$v['day_nope_total_num']
                  + (int)$v['day_free_thumb_total_num']
                  + (int)$v['day_pay_thumb_total_num']
                  + (int)$v['day_free_like_total_num']
                  + (int)$v['day_pay_like_total_num']
                  + (int)$old['cnt_slide'],
              'cnt_match_user'  => (int)$v['day_match_user_num'] + (int)$old['cnt_match_user'],
              'cnt_match'       => (int)$v['day_free_match_total_num'] + (int)$v['day_pay_match_total_num'] + (int)$old['cnt_match'],
              'cnt_1st'         => (int)$v['day_1st_user_num']  + (int)$old['cnt_1st'],
              'cnt_30th'        => (int)$v['day_30th_user_num'] + (int)$old['cnt_30th'],
              'cnt_buy_user'    => (int)$v['day_buy_like_user_num'] + (int)$v['day_buy_vip_user_num'] + (int)$old['cnt_buy_user'],
              'cnt_buy_sums'    => (float)$v['day_buy_like_sums'] + (float)$v['day_buy_vip_sums'] + (float)$old['cnt_buy_sums'],
              'cnt_charge_user' => (int)$v['day_charge_user_num'] + (int)$old['cnt_charge_user'],
              'cnt_charge_sums' => (float)$v['day_charge_sums'] + (float)$old['cnt_charge_sums'],
          ]);
      $lst[$key]['cnt_pay_user'] = $dat['orders'][$key]['cnt_user'];
      $lst[$key]['cnt_pay_sums'] = $dat['orders'][$key]['cnt_fee'];
      $lst[$key]['cnt_pay_per']  = round($lst[$key]['cnt_pay_sums'] / $lst[$key]['cnt_pay_user'],2);
      $lst[$key]['cnt_reg_cost']   = round($lst[$key]['cnt_fee'] / $lst[$key]['cnt_reg'],2);
      $lst[$key]['cnt_buy_per']    = round($lst[$key]['cnt_buy_sums'] / $lst[$key]['cnt_buy_user'],2);
      $lst[$key]['cnt_charge_per'] = round($lst[$key]['cnt_charge_sums'] / $lst[$key]['cnt_charge_user'],2);
      $lst[$key]['cnt_1st_rate']   = round($lst[$key]['cnt_1st'] / $lst[$key]['cnt_reg'],2);
      $lst[$key]['cnt_reg_total']  = (int)$dat['reg_total'];
    }
    //unset($lst['2016-01-12']);
    0 && $lst['2016-01-07'] = array_merge($lst['2016-01-07'] ?: [],
        [
            'cnt_reg'  => 5071,
            'cnt_reg0' => 2305,
            'cnt_reg1' => 2766,
            'cnt_dau'  => 40000,
            'cnt_slide_user' => 29545,
            'cnt_match_user' => 12838,
            'cnt_match'      => 26348,
        ]);
    $dat['list'] = $lst;
    $this->time_type = 'dtime';
    $this->data = $dat;
    //die(json_encode($dat));
    $tpl = '';
    if(D('Auth')->check(CONTROLLER_NAME.'/adver_daily_open'))
    {
      $tpl = 'adver_daily_wap';
      layout(false);
      C('SHOW_PAGE_TRACE',false);
    }
    $this->display($tpl);
  }


  // 点赞分布
  public function daily_thumb()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-1 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-1 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__THUMB_ANALYSIS_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,id desc');
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    $dat['types'] =
        [
            '0'  => '0',
            '1'  => '1-5',
            '2'  => '6-10',
            '3'  => '11-20',
            '9'  => '21-40',
          //'4'  => '21-50',
            '10' => '41-100',
          //'5'  => '51-100',
            '6'  => '101-200',
            '7'  => '201-500',
            '8'  => '500以上',
        ];
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      if(!isset($dat['types'][$v['ntype']])) continue;
      $sex = (int)$v['gender'];
      $typ = (int)$v['ntype'];
      $old = $lst ?: [];
      foreach(['recv_user','recv_num','send_user','send_num'] as $f)
      {
        $lst[$f][$typ] = (int)$v[$f] + (int)$old[$f][$typ];
      }
    }
    $dat['list'] = $lst;
    $this->data = $dat;
    IS_AJAX && $this->ajaxReturn(
        [
            'ret'  => 0,
            'data' => $dat,
        ]);
    //die(json_encode($dat));
    $this->display();
  }


  // 曝光发布
  public function been_slide()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-1 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-1 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__USER_SLIDE_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,id desc');
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    $dat['counts']   = $mod->get_user_counts($map,false);
    $dat['types'] =
        [
            0 => '0',
            1 => '1-5',
            2 => '6-10',
            3 => '11-20',
            4 => '21-50',
            5 => '50以上',
        ];
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      $sex = (int)$v['gender'];
      $typ = (int)$v['been_slide_type'];
      $old = $lst ?: [];
      foreach(['total_user_num','been_slide_user_num','been_slide_total_num'] as $f)
      {
        $lst[$f][$typ] = (int)$v[$f] + (int)$old[$f][$typ];
      }
    }
    foreach($dat['types'] ?: [] as $typ => $type_name)
    {
      $lst['total_users'] = (int)$lst['been_slide_user_num'][$typ] + (int)$lst['total_users'];
      $lst['total_user2'] = (int)$lst['total_user_num'][$typ] + (int)$lst['total_user2'];
      $lst['total_times'] = (int)$lst['been_slide_total_num'][$typ] + (int)$lst['total_times'];
    }
    //header('debug: '.json_encode($lst['total_user_num']));
    $lst['been_slide_user_num'][0] += (int)$lst['total_user_num'][0];
    $lst['total_users'] += $lst['been_slide_user_num'][0];
    $dat['datas'] = $lst;
    foreach($dat['types'] ?: [] as $typ => $type_name)
    {
      $row =
          [
              '曝光次数' => $type_name,
              '曝光人数' => '<span class="tip" data-original-title="有过右滑且被左滑或右滑的用户数">'.(int)$lst['been_slide_user_num'][$typ].'</span>',
              '人数比例' => round((int)$lst['been_slide_user_num'][$typ] / (int)$lst['total_users'] * 100,2).'%',
            //'总人数'   => (int)$lst['total_user_num'][$typ],
            //'总人数比例' => round((int)$lst['total_user_num'][$typ] / (int)$lst['total_user2'] * 100,2).'%',
              '加权'     => (int)$lst['been_slide_total_num'][$typ],
              '占比'     => round((int)$lst['been_slide_total_num'][$typ] / (int)$lst['total_times'] * 100,2).'%',
          ];
      //$row['总人数'] = '<span class="tip" data-original-title="分数大于等于6分，且有过右滑">'.$row['总人数'].'</span>';
      $dat['list'][] = $row;
    }
    $dat['list'][] =
        [
            '曝光次数' => '合计',
            '曝光人数' => (int)$lst['total_users'],
            '人数比例' => '-',
          //'总人数' => (int)$lst['total_user2'],
          //'总人数比例' => '-',
            '加权'     => (int)$lst['total_times'],
            '占比'     => '-',
        ];
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    IS_AJAX && $this->ajaxReturn(
        [
            'ret'  => 0,
            'data' => $dat,
        ]);
    //die(json_encode($dat));
    $this->display('common');
  }


  // 被赞比率
  public function been_thumb()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-1 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-1 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__USER_THUMB_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,id desc');
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    $dat['types'] =
        [
            0 => '0',
            1 => '1-5',
            2 => '6-10',
            3 => '11-20',
            4 => '21-50',
            5 => '50以上',
        ];
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      $sex = (int)$v['gender'];
      $typ = (int)$v['been_thumb_type'];
      $old = $lst ?: [];
      foreach(['total_user_num','been_thumb_user_num','been_thumb_total_num'] as $f)
      {
        $lst[$f][$typ] = (int)$v[$f] + (int)$old[$f][$typ];
      }
    }
    foreach($dat['types'] ?: [] as $typ => $type_name)
    {
      $lst['total_users'] = (int)$lst['been_thumb_user_num'][$typ] + (int)$lst['total_users'];
      $lst['total_times'] = (int)$lst['been_thumb_total_num'][$typ] + (int)$lst['total_times'];
    }
    $lst['been_thumb_user_num'][0] += (int)$lst['total_user_num'][0];
    $lst['total_users'] += $lst['been_thumb_user_num'][0];
    $dat['datas'] = $lst;
    foreach($dat['types'] ?: [] as $typ => $type_name)
    {
      $row =
          [
              '被赞率(%)' => $type_name,
              '被赞人数' => '<span class="tip" data-original-title="有过任意滑动行为且被赞的人数">'.(int)$lst['been_thumb_user_num'][$typ].'</span>',
            //'总人数'   => (int)$lst['total_user_num'][$typ],
              '人数比例' => round((int)$lst['been_thumb_user_num'][$typ] / $lst['total_users'] * 100,2).'%',
              '被赞数'   => (int)$lst['been_thumb_total_num'][$typ],
              '占比'     => round((int)$lst['been_thumb_total_num'][$typ] / $lst['total_times'] * 100,2).'%',
          ];
      $dat['list'][] = $row;
    }
    $dat['list'][] =
        [
            '被赞率(%)' => '合计',
            '被赞人数'     => (int)$lst['total_users'],
            '人数比例' => '-',
            '被赞数'     => (int)$lst['total_times'],
            '占比'     => '-',
        ];
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    IS_AJAX && $this->ajaxReturn(
        [
            'ret'  => 0,
            'data' => $dat,
        ]);
    //die(json_encode($dat));
    $this->display('common');
  }


  // 匹配比率
  public function match_rate()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-1 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-1 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__USER_MATCH_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,id desc');
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    $dat['types'] =
        [
            0 => '0',
            1 => '1-5',
            2 => '6-10',
            3 => '11-20',
            4 => '21-50',
            5 => '50以上',
        ];
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      $sex = (int)$v['gender'];
      $typ = (int)$v['match_type'];
      $old = $lst ?: [];
      foreach(['total_user_num','match_user_num','match_total_num'] as $f)
      {
        $lst[$f][$typ] = (int)$v[$f] + (int)$old[$f][$typ];
      }
    }
    foreach($dat['types'] ?: [] as $typ => $type_name)
    {
      $lst['total_users'] = (int)$lst['match_user_num'][$typ] + (int)$lst['total_users'];
      $lst['total_times'] = (int)$lst['match_total_num'][$typ] + (int)$lst['total_times'];
    }
    $lst['match_user_num'][0] += (int)$lst['total_user_num'][0];
    $lst['total_users'] += $lst['match_user_num'][0];
    $dat['datas'] = $lst;
    foreach($dat['types'] ?: [] as $typ => $type_name)
    {
      $row =
          [
              '匹配率(%)' => $type_name,
              '匹配人数' => '<span class="tip" data-original-title="有过任意滑动行为的匹配人数">'.(int)$lst['match_user_num'][$typ].'</span>',
            //'总人数'   => (int)$lst['total_user_num'][$typ],
              '人数比例' => round((int)$lst['match_user_num'][$typ] / $lst['total_users'] * 100,2).'%',
              '匹配数'   => (int)$lst['match_total_num'][$typ],
              '占比'     => round((int)$lst['match_total_num'][$typ] / $lst['total_times'] * 100,2).'%',
          ];
      $dat['list'][] = $row;
    }
    $dat['list'][] =
        [
            '匹配率(%)' => '合计',
            '匹配人数' => (int)$lst['total_users'],
            '人数比例' => '-',
            '匹配数'   => (int)$lst['total_times'],
            '占比'     => '-',
        ];
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    IS_AJAX && $this->ajaxReturn(
        [
            'ret'  => 0,
            'data' => $dat,
        ]);
    //die(json_encode($dat));
    $this->display('common');
  }


  // 次留分析
  public function stat_retention()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-2 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-2 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__RETENTION_STAT_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,id desc');
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $dat['counts']   = $mod->get_user_counts($map,'user_type');
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    $dat['types'] =
        [
            0 => '0',
            1 => '1-5',
            2 => '6-10',
            3 => '11-20',
            4 => '21-50',
            5 => '50以上',
        ];
    $dat['user_types'] =
        [
            0 => '留存',
            1 => '流失',
        ];
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      $sex = (int)$v['gender'];
      $typ = (int)$v['user_type'];
      $old = $lst ?: [];
      foreach(
          [
              'total_user_num',
              'pass_score_user_num',
              'slide_user_num',
              'slide_total_num',
              'been_slide_user_num',
              'been_slide_total_num',
              'thumb_user_num',
              'thumb_total_num',
              'been_thumb_user_num',
              'been_thumb_total_num',
              'match_user_num',
              'match_total_num',
              'chat_user_num',
              'chat_total_num',
          ] as $f)
      {
        $lst[$f][$typ] = (int)$v[$f] + (int)$old[$f][$typ];
      }
    }
    foreach($dat['user_types'] ?: [] as $typ => $type_name)
    {
      $lst['total_users'] = (int)$dat['counts'][$typ]['total_user_num'] + (int)$lst['total_users'];
      $lst['total_passs'] = (int)$dat['counts'][$typ]['pass_score_user_num'] + (int)$lst['total_passs'];
    }
    $dat['datas'] = $lst;
    foreach($dat['user_types'] ?: [] as $typ => $type_name)
    {
      $row =
          [
              '类别' => $type_name,
              '人数' => //'<a href="'.U('stat_retention_detail?type=total&,$_GETuser_type='.$typ).'">'.
                  (int)$dat['counts'][$typ]['total_user_num']
                  .' / '.round((int)$dat['counts'][$typ]['total_user_num'] / (int)$lst['total_users'] * 100,2).'%'
            //.'</a>'
            ,
              '合格' => //'<a href="'.U('stat_retention_detail?type=pass_score&user_type='.$typ,$_GET).'">'.
                  (int)$dat['counts'][$typ]['pass_score_user_num']
                  .' / '.round((int)$dat['counts'][$typ]['pass_score_user_num'] / (int)$lst['total_passs'] * 100).'%'
            //.'</a>'
            ,
              '滑动' => '<a href="'.U('stat_retention_detail?type=slide&user_type='.$typ,$_GET).'">'.
                  (int)$lst['slide_user_num'][$typ]
                  .' / '.round((int)$lst['slide_total_num'][$typ] / (int)$lst['slide_user_num'][$typ],2)
                  .'</a>'
            ,
              '点赞' => '<a href="'.U('stat_retention_detail?type=thumb&user_type='.$typ,$_GET).'">'.
                  (int)$lst['thumb_user_num'][$typ]
                  .' / '.round((int)$lst['thumb_total_num'][$typ] / (int)$lst['thumb_user_num'][$typ],2)
                  .'</a>'
            ,
              '曝光' => '<a href="'.U('stat_retention_detail?type=been_slide&user_type='.$typ,$_GET).'">'.
                  (int)$lst['been_slide_user_num'][$typ]
                  .' / '.round((int)$lst['been_slide_total_num'][$typ] / (int)$lst['been_slide_user_num'][$typ],2)
                  .'</a>'
            ,
              '被赞' => '<a href="'.U('stat_retention_detail?type=been_thumb&user_type='.$typ,$_GET).'">'.
                  (int)$lst['been_thumb_user_num'][$typ]
                  .' / '.round((int)$lst['been_thumb_total_num'][$typ] / (int)$lst['been_thumb_user_num'][$typ],2)
                  .'</a>'
            ,
              '匹配' => '<a href="'.U('stat_retention_detail?type=match&user_type='.$typ,$_GET).'">'.
                  (int)$lst['match_user_num'][$typ]
                  .' / '.round((int)$lst['match_total_num'][$typ] / (int)$lst['match_user_num'][$typ],2)
                  .'</a>'
            ,
              '聊天' => '<a href="'.U('stat_retention_detail?type=chat&user_type='.$typ,$_GET).'">'.
                  (int)$lst['chat_user_num'][$typ]
                  .' / '.round((int)$lst['chat_total_num'][$typ] / (int)$lst['chat_user_num'][$typ],2)
                  .'</a>'
            ,
          ];
      $rat =
          [
              '类别' => $type_name.'率',
              '人数' => round((int)$lst['total_user_num'][$typ] / (int)$lst['total_users'] * 100,2).'%',
              '合格' => round((int)$lst['pass_score_user_num'][$typ] / (int)$lst['total_users'] * 100).'%',
              '滑动' => round((int)$lst['slide_user_num'][$typ] / (int)$lst['total_users'],2).'%',
              '点赞' => round((int)$lst['thumb_user_num'][$typ] / (int)$lst['total_users'],2).'%',
              '曝光' => round((int)$lst['been_slide_user_num'][$typ] / (int)$lst['total_users'],2).'%',
              '被赞' => round((int)$lst['been_thumb_user_num'][$typ] / (int)$lst['total_users'],2).'%',
              '匹配' => round((int)$lst['match_user_num'][$typ] / (int)$lst['total_users'],2).'%',
              '聊天' => round((int)$lst['chat_user_num'][$typ] / (int)$lst['total_users'],2).'%',
          ];
      $dat['list'][] = $row;
      //$dat['list'][] = $rat;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    IS_AJAX && $this->ajaxReturn(
        [
            'ret'  => 0,
            'data' => $dat,
        ]);
    //die(json_encode($dat));
    $this->display();
  }


  // 次留明细
  public function stat_retention_detail()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-2 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-2 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__RETENTION_STAT_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,id desc');
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    $dat['type'] = $type = $_REQUEST['type'];
    $dat['types'] =
        [
            0 => '0',
            1 => '1-5',
            2 => '6-10',
            3 => '11-20',
            4 => '21-50',
            5 => '50以上',
        ];
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      $sex = (int)$v['gender'];
      $typ = (int)$v['num_type'];
      $old = $lst ?: [];
      foreach(
          [
              'total_user_num',
              'pass_score_user_num',
              'slide_user_num',
              'slide_total_num',
              'been_slide_user_num',
              'been_slide_total_num',
              'thumb_user_num',
              'thumb_total_num',
              'been_thumb_user_num',
              'been_thumb_total_num',
              'match_user_num',
              'match_total_num',
              'chat_user_num',
              'chat_total_num',
          ] as $f)
      {
        $lst[$f][$typ] = (int)$v[$f] + (int)$old[$f][$typ];
      }
    }
    foreach($dat['types'] ?: [] as $typ => $type_name)
    {
      $lst['total_users_'.$type] = (int)$lst[$type.'_user_num'][$typ] + (int)$lst['total_users_'.$type];
      $lst['total_times_'.$type] = (int)$lst[$type.'_total_num'][$typ] + (int)$lst['total_times_'.$type];
    }
    //$lst[$type.'_user_num'][0] += (int)$lst['total_user_num'][0];
    //$lst['total_users_'.$type] += $lst[$type.'_user_num'][0];
    $dat['datas'] = $lst;
    foreach($dat['types'] ?: [] as $typ => $type_name)
    {
      $row =
          [
              '范围'     => $type_name,
              '人数'     => (int)$lst[$type.'_user_num'][$typ],
              '人数比例' => round((int)$lst[$type.'_user_num'][$typ] / $lst['total_users_'.$type] * 100,2).'%',
              '次数分布' => (int)$lst[$type.'_total_num'][$typ],
          ];
      $dat['list'][] = $row;
    }
    $dat['list'][] =
        [
            '范围'     => '合计',
            '人数'     => (int)$lst['total_users_'.$type],
            '人数比例' => '-',
            '次数分布' => (int)$lst['total_times_'.$type],
        ];
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    IS_AJAX && $this->ajaxReturn(
        [
            'ret'  => 0,
            'data' => $dat,
        ]);
    //die(json_encode($dat));
    $this->display('common');
  }


  // 每小时行为统计
  public function hourly_slide()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-0 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    $mod = D('Stat')->set_table('__SLIDE_HOUR_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->plist(48,$map)->lists('','dtime desc,hour,id desc');
    foreach($arr ?: [] as $v)
    {
      $row =
          [
              '时间'     => $v['dtime'].' '.$v['hour'].'点',
              '滑动人数' => $v['slide_user_num'] ?: '-',
              '人均滑动' => $v['avg_slide'] ?: '-',
              '人均点赞' => $v['avg_thumb'] ?: '-',
              '人均曝光' => $v['avg_been_slide'] ?: '-',
              '人均被赞' => $v['avg_been_thum'] ?: '-',
          ];
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display('common');
  }


  // 聊天分析
  public function daily_chat()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-2 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-2 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__TODAY_MATCH_CHAT__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,id desc');
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['advers']   = $mod->get_adver_list('','ch_serial') ?: [];
    //$dat['packages'] = $mod->get_package_list() ?: [];
    $dat['counts']   = $mod->get_user_counts($map,'user_type');
    $dat['sexs']     = C('USER_SEX_IS') ?: [];
    $dat['types'] =
        [
            0 => '0',
            1 => '1-5',
            2 => '6-10',
            3 => '11-20',
            4 => '21-50',
            5 => '50以上',
        ];
    $dat['user_types'] =
        [
            0 => '新用户',
            1 => '老用户',
        ];
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      $sex = (int)$v['gender'];
      $typ = (int)$v['match_chat_type'];
      foreach(
          [
              'greet_user_num',
              'greet_num',
              'chat_user_num',
              'chat_num',
          ] as $f)
      {
        $lst[$typ][$f]    = (int)$v[$f] + (int)$lst[$typ][$f];
        $dat['total'][$f] = (int)$v[$f] + (int)$dat['total'][$f];
      }
    }
    $dat['datas'] = $lst;
    foreach($dat['types'] ?: [] as $typ => $v)
    {
      $row =
          [
              '类别'     => $dat['types'][$typ],
              '聊天次数' => $lst[$typ]['chat_num'],
              '聊天人数' => $lst[$typ]['chat_user_num'],
              '人数比例' => round((int)$lst[$typ]['chat_user_num'] / (int)$dat['total']['chat_user_num'] * 100,2).'%',
              '打招呼次数' => $lst[$typ]['greet_num'],
              '打招呼人数' => $lst[$typ]['greet_user_num'],
              '打招呼比例' => round((int)$lst[$typ]['greet_user_num'] / (int)$dat['total']['greet_user_num'] * 100,2).'%',
          ];
      $dat['list'][] = $row;
    }
    $dat['list'][] =
        [
            '类别'     => '合计',
            '聊天次数' => $dat['total']['chat_num'],
            '聊天人数' => $dat['total']['chat_user_num'],
            '人数比例' => '-',
            '打招呼次数' => $dat['total']['greet_num'],
            '打招呼人数' => $dat['total']['greet_user_num'],
            '打招呼比例' => '-',
        ];
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display('common');
  }


  // 付费基础数据
  public function stat_pay_base()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__PAY_BASE_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->plist($this->page_size,$map)->lists('','dtime desc,id desc');
    foreach($arr as $v)
    {
      $par =
          [
              'stime' => $v['dtime'],
              'etime' => $v['dtime'],
          ];
      $par2 = array_merge($par,
          [
              'stime' => date('Y-m-d',strtotime($par['stime']) - 60 * 60 * 24 * 30),
          ]);
      $row =
          [
              '日期'     => $v['dtime'],
              '日活跃人' => $v['today_active_user_num'] ?: '-',
              '日付费人' => '<a href="'.U('UserBase/order_list?state=2',$par).'" target="_blank">'.$v['today_pay_user_num'].'</a>',
              '日付费率' => round((int)$v['today_pay_user_num'] / (int)$v['today_active_user_num'] * 100,2).'%',
              '日付费'   => $v['today_income'] ?: '-',
              '日ARUP'   => round((int)$v['today_income'] / (int)$v['today_pay_user_num'],2),
              '<b class="tip" data-original-title="倒退30天">月活跃人</b>' => $v['month_active_user_num'] ?: '-',
              '月付费人' => '<a href="'.U('UserBase/order_list?state=2',$par2).'" target="_blank">'.$v['month_pay_user_num'].'</a>',
              '月付费率' => round((int)$v['month_pay_user_num'] / (int)$v['month_active_user_num'] * 100,2).'%',
              '月付费'   => $v['month_income'] ?: '-',
              '月ARUP'   => round((int)$v['month_income'] / (int)$v['month_pay_user_num'],2),
              '二次付费率' => round((int)$v['second_pay_user_num'] / (int)$v['month_pay_user_num'] * 100,2).'%',
          ];
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    //die(json_encode(compact('dat','arr','map')));
    $this->display('common');
  }

  // 首付时间
  public function stat_pay_first()
  {
    $_REQUEST['time_type'] = 'reg_date';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__PAY_FREQUENCY__');
    $map = $mod->get_filters();
    $map['pay_freq_type'] = 1;
    $arr = $mod->lists($map,'reg_date desc,id desc');
    $dat['time_types'] =
        [
            1 => '当天',
            2 => '第2天',
            3 => '第3天',
            4 => '4-5天',
            5 => '6-13天',
            6 => '14-20天',
            7 => '21-25天',
            8 => '26及以上',
        ];
    $lst = [];
    foreach($arr as $v)
    {
      $key = $v['reg_date'];
      $typ = $v['pay_time_period'];
      $lst[$key] = array_merge($lst[$key] ?: [],
          [
              'reg_date'          => $v['reg_date'],
              'reg_user_num'      => $v['reg_user_num'],
              'pay_user_num'.$typ => (int)$v['pay_user_num'] + (int)$lst[$key]['pay_user_num'.$typ],
              'pay_user_num_all'  => (int)$v['pay_user_num'] + (int)$lst[$key]['pay_user_num_all'],
              'pay_fee_sum'.$typ  => (int)$v['pay_fee_sum']  + (int)$lst[$key]['pay_fee_sum'.$typ],
              'pay_fee_sum_all'   => (int)$v['pay_fee_sum']  + (int)$lst[$key]['pay_fee_sum_all'],
          ]);
    }
    foreach($lst as $k => $v)
    {
      $row =
          [
              '日期'     => $v['reg_date'],
              '注册人数' => $v['reg_user_num'],
              '付费人数' => $v['pay_user_num_all'],
              '类型'     => '首付',
          ];
      foreach($dat['time_types'] as $typ => $type_name)
      {
        $row[$type_name] = $v['pay_user_num'.$typ] ?: '-';
      }
      $dat['list'][] = $row;
      $row['类型'] = '占比';
      foreach($dat['time_types'] as $typ => $type_name)
      {
        $row[$type_name] = round((int)$v['pay_user_num'.$typ] / (int)$v['pay_user_num_all'] * 100,2).'%';
      }
      $dat['list'][] = $row;
      $row['类型'] = '人均';
      foreach($dat['time_types'] as $typ => $type_name)
      {
        $row[$type_name] = round((int)$v['pay_fee_sum'.$typ] / (int)$v['pay_user_num'.$typ],2) ?: '-';
      }
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->auto_rowspan = ['日期','注册人数','付费人数'];
    //die(json_encode(compact('dat','arr','map')));
    $this->display('common');
  }

  // 付费频率
  public function stat_pay_frequency1()
  {
    $_REQUEST['time_type'] = 'reg_date';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__PAY_FREQUENCY__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'reg_date desc,id desc');
    $dat['time_types'] =
        [
            1 => '当天',
            2 => '第2天',
            3 => '第3天',
            4 => '4-5天',
            5 => '6-13天',
            6 => '14-20天',
            7 => '21-25天',
            8 => '26及以上',
        ];
    $dat['freq_types'] =
        [
            1 => '1次',
            2 => '2次',
            3 => '3次',
            4 => '4次',
            5 => '5次',
            6 => '6次以上',
        ];
    $lst = [];
    foreach($arr as $v)
    {
      $key = $v['reg_date'];
      $typ = $v['pay_time_period'];
      $pft = $v['pay_freq_type'];
      $lst[$key] = array_merge($lst[$key] ?: [],
          [
              'reg_date'          => $v['reg_date'],
              'reg_user_num'      => $v['reg_user_num'],
              'pay_user_num'.$typ => (int)$v['pay_user_num'] + (int)$lst[$key]['pay_user_num'.$typ],
              'pay_user_num_all'  => (int)$v['pay_user_num'] + (int)$lst[$key]['pay_user_num_all'],
              'pay_fee_sum'.$typ  => (int)$v['pay_fee_sum']  + (int)$lst[$key]['pay_fee_sum'.$typ],
              'pay_fee_sum_all'   => (int)$v['pay_fee_sum']  + (int)$lst[$key]['pay_fee_sum_all'],
          ]);
    }
    foreach($lst as $k => $v)
    {
      $row =
          [
              '日期'     => $v['reg_date'],
              '注册人数' => $v['reg_user_num'],
              '付费人数' => $v['pay_user_num_all'],
          ];
      foreach($dat['time_types'] as $typ => $type_name)
      {
        $row[$type_name] = round((int)$v['pay_user_num'.$typ] / (int)$v['pay_user_num_all'] * 100,2).'%';//$v['pay_user_num'.$typ];
      }
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    //die(json_encode(compact('dat','arr','map')));
    $this->display('common');
  }

  // 付费频率
  public function stat_pay_frequency()
  {
    isset($_REQUEST['pay_freq_type']) || $_REQUEST['pay_freq_type'] = 1;
    $_REQUEST['time_type'] = 'reg_date';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__PAY_FREQUENCY__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'reg_date,id');
    $dat['time_types'] =
        [
            1 => '当天',
            2 => '第2天',
            3 => '第3天',
            4 => '4-5天',
            5 => '6-13天',
            6 => '14-20天',
            7 => '21-25天',
            8 => '26及以上',
        ];
    $dat['freq_types'] =
        [
            1 => '1次',
            2 => '2次',
            3 => '3次',
            4 => '4次',
            5 => '5次',
            6 => '6次以上',
        ];
    $lst = [];
    foreach($arr as $v)
    {
      $key = $v['pay_freq_type'];
      $typ = $v['pay_time_period'];
      $lst[$key] = array_merge($lst[$key] ?: [],
          [
              'reg_date'          => $v['reg_date'],
              'reg_user_num'      => $v['reg_user_num'],
              'pay_time_period'   => $v['pay_time_period'],
              'pay_user_num'.$typ => (int)$v['pay_user_num'] + (int)$lst[$key]['pay_user_num'.$typ],
              'pay_user_num_all'  => (int)$v['pay_user_num'] + (int)$lst[$key]['pay_user_num_all'],
              'pay_fee_sum'.$typ  => (int)$v['pay_fee_sum']  + (int)$lst[$key]['pay_fee_sum'.$typ],
              'pay_fee_sum_all'   => (int)$v['pay_fee_sum']  + (int)$lst[$key]['pay_fee_sum_all'],
          ]);
      //$dat[$key]['total_users'] += (int)$v['pay_range_'.$typ];
    }
    foreach($lst as $k => $v)
    {
      $row =
          [
              '频次' => $dat['freq_types'][$k],
              '合计' => $v['pay_user_num_all'],
          ];
      $row['类型'] = '人数';
      foreach($dat['time_types'] as $typ => $type_name)
      {
        $row[$type_name] = $v['pay_user_num'.$typ];
      }
      $dat['list'][] = $row;
      $row['类型'] = '占比';
      foreach($dat['time_types'] as $typ => $type_name)
      {
        $row[$type_name] = round((int)$v['pay_user_num'.$typ] / (int)$v['pay_user_num_all'] * 100,2).'%';
      }
      $dat['list'][] = $row;
      $row['类型'] = '人均';
      foreach($dat['time_types'] as $typ => $type_name)
      {
        $row[$type_name] = round((int)$v['pay_fee_sum'.$typ] / (int)$v['pay_user_num'.$typ],2);
      }
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->auto_rowspan = ['频次','合计'];
    //die(json_encode(compact('dat','arr','map')));
    $this->display('common');
  }

  // 付费追踪
  public function stat_pay_trace()
  {
    $_REQUEST['time_type'] = 'reg_date';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__PAY_TRACE__');
    $map = $mod->get_filters();
    $arr = $mod->plist($this->page_size,$map)->lists('','reg_date desc,id desc');
    $dat['range_types'] =
        [
            45    => '0-45',
            198   => '45-198',
            588   => '198-588',
            'max' => '588以上',
        ];
    foreach($arr as $v)
    {
      foreach($dat['range_types'] as $typ => $type_name)
      {
        $dat[$v['reg_date']]['total_users'] += (int)$v['pay_range_'.$typ];
      }
    }
    foreach($arr as $v)
    {
      $row =
          [
              '日期'     => $v['reg_date'],
              '注册人数' => $v['reg_user_num'],
              '类型'     => '人数',
          ];
      foreach($dat['range_types'] as $typ => $type_name)
      {
        $row[$type_name] = $v['pay_range_'.$typ] ?: '-';
      }
      $dat['list'][] = $row;
      $row['类型'] = '占比';
      foreach($dat['range_types'] as $typ => $type_name)
      {
        $row[$type_name] = round((int)$v['pay_range_'.$typ] / (int)$dat[$v['reg_date']]['total_users'] * 100,2).'%';
      }
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->auto_rowspan = ['日期','注册人数'];
    //die(json_encode(compact('dat','arr','map')));
    $this->display('common');
  }

  // 付费金额
  public function stat_pay_amount()
  {
    $_REQUEST['time_type'] = 'reg_date';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    //IS_AJAX || $this->display();
    $mod = D('OrderV2');
    $dat['range_types'] =
        [
            '0-29'    => 'total_fee <= 29',
            '30-44'   => 'total_fee >= 30  and total_fee <= 44',
            '45-198'  => 'total_fee >= 45  and total_fee <= 198',
            '199-588' => 'total_fee >= 199 and total_fee <= 588',
            '589以上' => 'total_fee >= 589',
        ];
    $arr = [];
    foreach($dat['range_types'] as $typ => $map)
    {
      $arr[$typ] = $mod->field(
          [
              'sum(fee)' => 'total_fee',
          ])->where(
          [
              'pay_time' =>
                  [
                      ['egt',strtotime(date('Y-m-d 00:00:00',strtotime($_REQUEST['stime'])))],
                      ['elt',strtotime(date('Y-m-d 23:59:59',strtotime($_REQUEST['etime'])))],
                  ],
              'state'    => 2,
              'pay_type' => 1,
          ])
          ->group('uid')
          ->having($map)
          ->select() ?: [];
      $arr[$typ] = ['cnt' => count($arr[$typ])];
    }
    $lst = [];
    foreach($arr as $typ => $v)
    {
      $lst[$typ] = $arr[$typ]['cnt'];
      $dat['totals'] += (int)$arr[$typ]['cnt'];
    }
    foreach([$lst] as $v)
    {
      $row = [];
      $row['类别'] = '人数';
      foreach($dat['range_types'] as $typ => $m)
      {
        $row[$typ] = $v[$typ] ?: '-';
      }
      $dat['list'][] = $row;
      $row['类别'] = '占比';
      foreach($dat['range_types'] as $typ => $m)
      {
        $row[$typ] = round((int)$v[$typ] / (int)$dat['totals'] * 100,2).'%';
      }
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys(reset($dat['list']) ?: []);
    $this->data = $dat;
    $this->auto_rowspan = ['日期'];
    //die(json_encode(compact('dat','arr','map')));
    $this->display('common');
  }

  // 付费金额 每日
  public function daily_pay_amount()
  {
    $_REQUEST['time_type'] = 'reg_date';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    //IS_AJAX || $this->display();
    $mod = D('OrderV2');
    $dat['range_types'] =
        [
            '0-29'    => [['elt',29]],
            '30-44'   => [['egt',30],['elt',44]],
            '45-198'  => [['egt',45],['elt',198]],
            '199-588' => [['egt',199],['elt',588]],
            '589以上' => [['egt',589]],
        ];
    $arr = [];
    foreach($dat['range_types'] as $typ => $map)
    {
      $arr[$typ] = $mod->field(
          [
              'from_unixtime(pay_time,\'%Y-%m-%d\')' => 'pay_date',
              'count(distinct uid)' => 'cnt',
          ])->where(
          [
              'pay_time' =>
                  [
                      ['egt',strtotime(date('Y-m-d 00:00:00',strtotime($_REQUEST['stime'])))],
                      ['elt',strtotime(date('Y-m-d 23:59:59',strtotime($_REQUEST['etime'])))],
                  ],
              'state'    => 2,
              'fee'      => $map ?: [],
          ])
          ->group('pay_date')
          ->klist('pay_date','','pay_date desc,cnt desc');
    }
    $lst = [];
    foreach($arr as $typ => $qls)
      foreach($qls ?: [] as $day => $v)
      {
        $lst[$day][$typ] = $arr[$typ][$day]['cnt'];
        $dat['totals'][$day] += (int)$arr[$typ][$day]['cnt'];
      }
    foreach($lst as $day => $v)
    {
      $row = ['日期' => $day];
      $row['类别'] = '人数';
      foreach($dat['range_types'] as $typ => $m)
      {
        $row[$typ] = $v[$typ] ?: '-';
      }
      $dat['list'][] = $row;
      $row['类别'] = '占比';
      foreach($dat['range_types'] as $typ => $m)
      {
        $row[$typ] = round((int)$v[$typ] / (int)$dat['totals'][$day] * 100,2).'%';
      }
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys(reset($dat['list']) ?: []);
    $this->data = $dat;
    $this->auto_rowspan = ['日期'];
    //die(json_encode(compact('dat','arr','map')));
    $this->display('common');
  }

  // 购买路径
  public function stat_pay_vip_path()
  {
    $_REQUEST['time_type'] = 'visit_date';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-3 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-1 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__PAY_VISIT__');
    $map = $mod->get_filters();
    $arr = $mod->plist($this->page_size,$map)->lists('','visit_date desc,id desc');
    $dat['active_list'] = $mod->table('__PAY_BASE_DATA__')->klist('dtime',
        [
            'dtime' =>
                [
                    ['egt',date('Y-m-d 00:00:00',strtotime($_REQUEST['stime']))],
                    ['elt',date('Y-m-d 23:59:59',strtotime($_REQUEST['etime']))],
                ],
        ]);
    $dat['field_types'] =
        [
            'visit_page_vip_total_user'     => '会员介绍人数',
          //'visit_page_vip_total_num'      => '会员介绍次数',
            'visit_total_user'              => '会员列表人数',
          //'visit_buy_success_total_user'  => '购买成功人数',
          //'visit_buy_success_total_num'   => '购买成功次数',
          //'visit_buy_failed_total_user'   => '购买失败人数',
          //'visit_buy_failed_total_num'    => '购买失败次数',
            'visit_page_diamond_total_user' => '充值列表人数',
          //'visit_page_diamond_total_num'  => '充值列表次数',
            'visit_buy_total_user'          => '购买人数',
        ];
    $lst = [];
    foreach($arr as $v)
    {
      $key = $v['visit_date'];
      $typ = $v['visit_type'];
      $lst[$key] = array_merge($lst[$key] ?: [],
          [
              'visit_date' => $v['visit_date'],
              'reg_user'   => $v['reg_user'],
          ]);
      foreach($dat['field_types'] as $f => $field_name)
      {
        $lst[$key][$f] = $v[$f];
        $lst[$key][$f.'_total'] += $v[$f];
      }
      //$dat[$key]['total_users_visit'] += (int)$v['visit_user'];
    }
    foreach($lst as $k => $v)
    {
      $v['active_user'] = $dat['active_list'][$v['visit_date']]['today_active_user_num'];
      $row =
          [
              '日期'     => $v['visit_date'],
            //'注册人数' => $v['reg_user'],
              '活跃人数' => $v['active_user'],
          ];
      $row['类型'] = '人数';
      foreach($dat['field_types'] as $typ => $type_name)
      {
        $row[$type_name] = $v[$typ] ?: '-';
      }
      $dat['list'][] = $row;
      $row['类型'] = '占比';
      $row['会员介绍人数'] = round((int)$v['visit_page_vip_total_user'] / (int)$v['active_user'] * 100,2).'%';
      $row['会员列表人数'] = round((int)$v['visit_total_user'] / (int)$v['visit_page_vip_total_user'] * 100,2).'%';
      $row['购买人数']     = round((int)$v['visit_buy_total_user'] / (int)$v['visit_total_user'] * 100,2).'%';
      //$row['购买成功人数'] = round((int)$v['visit_buy_success_total_user'] / (int)$v['visit_buy_total_user'] * 100,2).'%';
      $row['充值列表人数'] = round((int)$v['visit_page_diamond_total_user'] / (int)$v['active_user'] * 100,2).'%';
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->auto_rowspan = ['日期'];
    //die(json_encode(compact('dat','lst','map')));
    $this->display('common');
  }

  // 购买来源
  public function stat_pay_visit()
  {
    $_REQUEST['time_type'] = 'visit_date';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-3 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-1 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__PAY_VISIT__');
    $map = $mod->get_filters();
    $arr = $mod->plist($this->page_size,$map)->lists('','visit_date desc,id desc');
    $dat['visit_types'] = D('DefinedMap')->get_map(21000/*会员购买来源*/);
    $lst = [];
    foreach($arr as $v)
    {
      $key = $v['visit_date'];
      $typ = $v['visit_type'];
      $lst[$key] = array_merge($lst[$key] ?: [],
          [
              'visit_date'           => $v['visit_date'],
              'reg_user'             => $v['reg_user'],
              'visit_total_user'     => $v['visit_total_user'],
              'visit_buy_total_user' => $v['visit_buy_total_user'],
          ]);
      foreach(
          [
              'visit_user',
              'visit_num',
              'visit_buy_success_user',
              'visit_buy_success_num',
              'visit_buy_failed_user',
              'visit_buy_failed_num',
              'visit_buy_user',
          ] as $f) $lst[$key][$f.$typ] += (int)$v[$f];
      $dat[$key]['total_users_visit'] += (int)$v['visit_user'];
      $dat[$key]['total_users_buy']   += (int)$v['visit_buy_success_user'];
    }
    foreach($lst as $k => $v)
    {
      $row =
          [
              '日期'     => $v['visit_date'],
            //'注册人数' => $v['reg_user'],
          ];
      $row['类型'] = '购买列表';
      foreach($dat['visit_types'] as $typ => $type_name)
      {
        $row[$type_name] = $v['visit_user'.$typ] ?: '-';
      }
      $dat['list'][] = $row;
      $row['类型'] = '占比';
      foreach($dat['visit_types'] as $typ => $type_name)
      {
        $row[$type_name] = round((int)$v['visit_user'.$typ] / (int)$dat[$v['visit_date']]['total_users_visit'] * 100,2).'%';
      }
      $dat['list'][] = $row;
      $row['类型'] = '购买成功';
      foreach($dat['visit_types'] as $typ => $type_name)
      {
        $row[$type_name] = $v['visit_buy_success_user'.$typ] ?: '-';
      }
      $dat['list'][] = $row;
      $row['类型'] = '占比';
      foreach($dat['visit_types'] as $typ => $type_name)
      {
        $row[$type_name] = round((int)$v['visit_buy_success_user'.$typ] / (int)$dat[$v['visit_date']]['total_users_buy'] * 100,2).'%';
      }
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->auto_rowspan = ['日期','注册人数'];
    //die(json_encode(compact('dat','lst','map')));
    $this->display('common');
  }

  // 充值消耗
  public function stat_consume_frequency()
  {
    $_REQUEST['time_type'] = 'reg_date';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__CONSUME_FREQUENCY__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'reg_date,id');
    $dat['goods'] = D('Goods')->klist();
    $dat['field_types'] =
        [
            'consume_1st_user'  => '第1次',
            'consume_2nd_user'  => '第2次',
            'consume_3rd_user'  => '第3次',
            'consume_4th_user'  => '第4次',
            'consume_5th_user'  => '第5次',
            'consume_6th_user'  => '第6次',
            'consume_more_user' => '第7次+',
        ];
    $lst = [];
    foreach($arr as $v)
    {
      $key = $v['goods_id'];
      $lst[$key] = array_merge($lst[$key] ?: [],
          [
              'goods_id'     => $v['goods_id'],
              'reg_date'     => $v['reg_date'],
              'reg_user_num' => $v['reg_user_num'],
          ]);
      foreach($dat['field_types'] as $typ => $type_name)
      {
        $lst[$key][$typ] += (int)$v[$typ];
        $dat[$typ]['total_users'] += (int)$v[$typ];
      }
    }
    foreach($lst as $k => $v)
    {
      $row =
          [
              '消耗' => $dat['goods'][$v['goods_id']]['name'] ?: $v['goods_id'],
          ];
      $row['类型'] = '人数';
      foreach($dat['field_types'] as $typ => $type_name)
      {
        $row[$type_name] = $v[$typ] ?: '-';
      }
      $dat['list'][] = $row;
      $row['类型'] = '占比';
      foreach($dat['field_types'] as $typ => $type_name)
      {
        $row[$type_name] = round((int)$v[$typ] / (int)$dat[$typ]['total_users'] * 100,2).'%';
      }
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->auto_rowspan = ['消耗'];
    $this->table_class = 'table-column';
    //die(json_encode(compact('dat','arr','map')));
    $this->display('common');
  }


  // 用户资料完整度
  // /opt/wwwroot/adm.chujian.im/stand/stat_report_daily/
  public function daily_userinfo()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    $rds = D('UserBase')->get_redis();
    $arr = [];
    for($i = 0;$i < 7;$i++)
    {
      $tim = strtotime('-'.$i.' days');
      $day = date('Y-m-d',$tim);
      $arr[$day] = $rds->hGetAll('php_stat_report_daily_'.date('Ymd',$tim)) ?: [];
      $arr[$day]['dtime'] = $day;
    }
    foreach($arr ?: [] as $v)
    {
      $dau = (int)$v['cnt_active'] - (int)$v['cnt_reg'];
      $row =
          [
              '日期'     => $v['dtime'],
              '人数'     => $v['cnt_userinfo'] ?: '-',
              '活跃人数' => $dau ?: '-',
              '占比'     => round((int)$v['cnt_userinfo'] / (int)$dau * 100,2).'%',
          ];
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->hide_filter = true;
    //die(json_encode($dat));
    $this->display('common');
  }


  // 用户资料修改行为
  public function daily_userinfo_modify()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    $mod = D('Stat')->set_table('__STAT_REAL_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->field(
        [
            'dtime',
            'sum(day_reg_user_num)'    => 'total_reg',
            'sum(day_active_user_num)' => 'total_active',
        ])
        ->group('dtime')
        ->klist('dtime',$map,'dtime desc');
    $rds = D('UserBase')->get_redis();
    foreach($arr ?: [] as $k => $v)
    {
      $day = $v['dtime'];
      $arr[$k]['count'] = $rds->zCard('user_visitor_up_'.date('Ymd',strtotime($day)));
    }
    foreach($arr ?: [] as $v)
    {
      $dau = (int)$v['total_active'] - (int)$v['total_reg'];
      $row =
          [
              '日期'     => $v['dtime'],
              '修改资料人数' => $v['count'] ?: '-',
              '活跃人数' => $dau ?: '-',
              '占比'     => round((int)$v['count'] / (int)$dau * 100,2).'%',
          ];
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->hide_filter = true;
    //die(json_encode($dat));
    $this->display('common');
  }


  // 用户查看他人资料分析
  public function visit_log()
  {
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-2 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    $mod = D('VisitLog');
    $map = $mod->get_filters();
    $arr = $mod->field(
        [
            'from_unixtime(visit_time,\'%Y-%m-%d\')' => 'dtime',
            'video_num',
            'count(id)'           => 'cnt',
            'count(distinct oid)' => 'cnt_oid',
        ])
        ->group('dtime,video_num')
        ->lists($map,'id desc');
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      $key = $v['dtime'];
      $has = $v['video_num'] ? 1 : 0;
      $lst[$key] = array_merge($lst[$key] ?: [],
          [
              'dtime'         => $v['dtime'],
              'cnt_all'       => (int)$v['cnt'] + (int)$lst[$key]['cnt_all'],
              'cnt_'.$has     => (int)$v['cnt'] + (int)$lst[$key]['cnt_'.$has],
              'cnt_oid'       => (int)$v['cnt_oid'] + (int)$lst[$key]['cnt_oid'],
              'cnt_oid_'.$has => (int)$v['cnt_oid'] + (int)$lst[$key]['cnt_oid_'.$has],
          ]);
    }
    foreach($lst ?: [] as $k => $v)
    {
      $row =
          [
              '日期'     => $v['dtime'],
              '查看次数' => $v['cnt_all'] ?: '-',
              '被查看人数' => $v['cnt_oid'] ?: '-',
              '有视频人数' => $v['cnt_oid_1'] ?: '-',
              '人均查看' => round((int)$v['cnt_all'] / (int)$v['cnt_oid'],2),
              '有视频人均' => round((int)$v['cnt_1'] / (int)$v['cnt_oid_1'],2),
              '有视频'   => $v['cnt_1'] ?: '-',
              '有视频%'  => round((int)$v['cnt_1'] / (int)$v['cnt_all'] * 100,2).'%',
              '无视频'   => $v['cnt_0'] ?: '-',
              '无视频%'  => round((int)$v['cnt_0'] / (int)$v['cnt_all'] * 100,2).'%',
          ];
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->hide_filter = true;
    //die(json_encode($dat));
    $this->display('common');
  }


  // 用户查看他人资料分析
  public function stat_users_visit()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__VIDEO_USERS_VISIT_OR_NOT__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,id desc');
    $dat['user_types'] =
        [
            0 => '没视频',
            1 => '有视频',
        ];
    $lst = [];
    foreach($arr ?: [] as $v)
    {
      $key = $v['dtime'];
      $typ = (int)$v['user_type'];
      foreach(
          [
              'visited_user_num',
              'visit_user_num',
              'visit_num',
          ] as $f)
      {
        $lst[$key] = array_merge($lst[$key] ?: [],
            [
                'dtime' => $v['dtime'],
                $f      => (int)$v[$f] + (int)$lst[$key][$f],
                $f.$typ => (int)$v[$f] + (int)$lst[$key][$f.$typ],
            ]);
      }
    }
    foreach($lst ?: [] as $k => $v)
    {
      $row =
          [
              '日期'     => $v['dtime'],
              '查看次数' => $v['visit_num'] ?: '-',
              '被查看人数' => $v['visited_user_num'] ?: '-',
              '有视频人数' => $v['visited_user_num1'] ?: '-',
              '人均查看' => round((int)$v['visit_num'] / (int)$v['visit_user_num'],2),
              '有视频人均' => round((int)$v['visit_num1'] / (int)$v['visit_user_num1'],2),
              '有视频'   => $v['visit_num1'] ?: '-',
              '有视频%'  => round((int)$v['visit_num1'] / (int)$v['visit_num'] * 100,2).'%',
              '无视频'   => $v['visit_num0'] ?: '-',
              '无视频%'  => round((int)$v['visit_num0'] / (int)$v['visit_num'] * 100,2).'%',
          ];
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display('common');
  }


  // 打分耗时分析
  public function stat_scoring_time()
  {
    $typ = 'ResultTimeRange';
    $dat['types'] =
        [
            0 => '0-4点',
            1 => '4-8点',
            2 => '8-12点',
            3 => '12-16点',
            4 => '16-20点',
            5 => '20-24点',
            6 => '全天',
        ];
    $lst = [];
    $max = 7;
    for($i = 0;$i < $max;$i++)
    {
      $day = date('Y-m-d',strtotime('-'.$i.' days'));
      $jss = @file_get_contents(DATA_PATH.'stat/'.$typ.'/'.$day.'.log');
      if($jso = json_decode($jss,true))
      {
        foreach($jso ?: [] as $t => $row)
        {
          $key = $day.'-'.$t;
          foreach($row ?: [] as $f => $val)
            $lst[$key] = array_merge($lst[$key] ?: [],
                [
                    'dtime' => $day,
                    'type'  => $t,
                    $f      => (int)$val + (int)$lst[$key][$f],
                  //$f.$t   => (int)$val + (int)$lst[$key][$f.$t],
                ]);
        }
      }
      elseif($max < 15) $max++;
    }
    foreach($lst ?: [] as $k => $v)
    {
      $row =
          [
              '日期'     => $v['dtime'],
              '时间'     => $dat['types'][$v['type']] ?: '-',
              '平均耗时(秒)' => $v['ave'] ?: '-',
              '平均耗时(分)' => round($v['ave'] / 60,2) ?: '-',
              '平均耗时(时)' => round($v['ave'] / 60 / 60,2) ?: '-',
          ];
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->hide_filter  = true;
    $this->auto_rowspan = ['日期'];
    //die(json_encode(compact('lst','dat')));
    $this->display('common');
  }

  // 打分耗时分析
  public function stat_scoring_count()
  {
    $typ = 'ResultScoringTimeRange';
    $dat['types'] =
        [
            0 => '0-10分',
            1 => '10-30分',
            2 => '30-60分',
            3 => '1-2小时',
            4 => '2-4小时',
            5 => '4-8小时',
            6 => '8小时以上',
        ];
    $lst = [];
    $max = 7;
    for($i = 0;$i < $max;$i++)
    {
      $day = date('Y-m-d',strtotime('-'.$i.' days'));
      $jss = @file_get_contents(DATA_PATH.'stat/'.$typ.'/'.$day.'.log');
      if($jso = json_decode($jss,true))
      {
        foreach($jso ?: [] as $t => $row)
        {
          $key = $day.'-'.$t;
          $t == -1 && $key = $day;
          foreach($row ?: [] as $f => $val)
            $lst[$key] = array_merge($lst[$key] ?: [],
                [
                    'dtime' => $day,
                    'type'  => $t,
                    $f      => (int)$val + (int)$lst[$key][$f],
                  //$f.$t   => (int)$val + (int)$lst[$key][$f.$t],
                ]);
          $dat['total_count'][$day] += (int)$row['count'];
        }
      }
      elseif($max < 15) $max++;
    }
    foreach($lst ?: [] as $k => $v)
    {
      $day = $v['dtime'];
      $row =
          [
              '日期'     => $v['dtime'],
              '耗时'     => $dat['types'][$v['type']] ?: '-',
              '数量'     => $v['count'] ?: '-',
              '占比'     => round((int)$v['count'] / (int)$dat['total_count'][$day] * 100,2).'%',
          ];
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    $this->hide_filter  = true;
    $this->auto_rowspan = ['日期'];
    //die(json_encode(compact('lst','dat')));
    $this->display('common');
  }


  // 会员边框统计
  public function stat_border_visit()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-3 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    //IS_AJAX || $this->display();
    $mod = D('Stat')->set_table('__BORDER_VISIT__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,id desc');
    if($ids = array_unique(array_column($arr ?: [],'border_id')))
    {
      $dat['borders'] = D('UserBase')->set_table('__VIP_BORDER__')->klist('id',['id' => ['in',$ids]]);
    }
    foreach($arr as $k => $v)
    {
      if(!$v['user_num']) continue;
      $row =
          [
              '日期'     => $v['dtime'],
              '日活'     => $v['day_active_user_num'],
              '边框'     => $dat['borders'][$v['border_id']]['name'] ?: $v['border_id'],
              '使用人数' => $v['user_num'] ?: '-',
              '占比'     => round((int)$v['user_num'] / (int)$v['day_active_user_num'] * 100,2).'%',
          ];
      $key = $v['dtime'].'-total';
      $dat['list'][$key] = array_merge($row,
          [
              '边框'     => '<b>合计</b>',
              '使用人数' => (int)$v['user_num'] + (int)$dat['list'][$key]['使用人数'],
              '占比'     => round((int)$dat['list'][$key]['使用人数'] / (int)$v['day_active_user_num'] * 100,2).'%',
          ]);
      $key = $v['dtime'].'-'.$v['border_id'];
      $dat['list'][$key] = $row;
    }
    $dat['cols'] = array_keys(reset($dat['list']) ?: []);
    $this->data = $dat;
    $this->auto_rowspan = ['日期','日活'];
    //die(json_encode(compact('dat','arr','map')));
    $this->display('common');
  }

  // 群发活动参与人数统计
  public function stat_im_active()
  {
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d 00:00:00',strtotime('-6 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d 23:59:59',strtotime('-0 days'));
    //IS_AJAX || $this->display();
    $mod = D('UserBase')->set_table('__IM_ACTIVE__');
    $map =
        [
            'ac_time' =>
                [
                    ['egt',strtotime($_REQUEST['stime'])],
                    ['elt',strtotime($_REQUEST['etime'])],
                ],
        ];
    $arr = $mod
        ->field(
            [
                'from_unixtime(ac_time,\'%Y-%m-%d\')' => 'dtime',
                'count(uid)' => 'cnt',
            ])
        ->group('dtime')
        ->lists($map,'ac_time desc,id desc');
    foreach($arr as $k => $v)
    {
      $row =
          [
              '日期'     => $v['dtime'],
              '人数'     => $v['cnt'],
          ];
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys($dat['list'][0] ?: []);
    $this->data = $dat;
    //die(json_encode(compact('dat','arr','map')));
    $this->display('common');
  }


  public function update_offline_daily_user()
  {
    D('Stat')->update_offline_daily_user();
    $this->success('操作成功');
  }

  public function update_offline_daily()
  {
    D('Stat')->update_offline_daily();
    $this->success('操作成功');
  }



  /***************************************
   * date_begin:开始日期，2014-12-23
   * date_end  :结束日期，2015-08-12
   * device    :设备，0为安卓，1为ios，2为所有
   * channel   :渠道id，0为所有
   ****************************************/
  public function account()
  {
    $par = [
      'date_begin' => trim($_REQUEST['stime']) ?: date('Y-m-d',strtotime('-7 days')),
      'date_end'   => trim($_REQUEST['etime']) ?: date('Y-m-d'),
      'device'     => (int)$_REQUEST['device'] ?: 2,
      'channel'    => (int)$_REQUEST['channel'],
    ];
    $ret = $this->get_stat('stat/backend',$par);
    //die(json_encode($ret));
    $dat['list'] = $ret['list'];
    usort($dat['list'],function($a,$b)
    {
      return $a > $b ? -1 : 1;
    });
    $this->data = $dat;
    $this->display();
  }

  public function operation()
  {
    $mod = D('OperLog');
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-0 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    $map = [
      'type' => ['in',['scoring','avatar_audit','text_audit','feedback','report_handle']],
    ];
    if($_REQUEST['stime'] && $stime = strtotime($_REQUEST['stime']))
    {
      is_array($map['create_time']) || $map['create_time'] = [];
      $map['create_time'][] = ['egt',strtotime(date('Y-m-d',$stime))];
    }
    if($_REQUEST['etime'] && $etime = strtotime($_REQUEST['etime']))
    {
      is_array($map['create_time']) || $map['create_time'] = [];
      $map['create_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($_REQUEST['show_type'] == 'admin')
    {
      $dat['list'] = $mod->list_analy_byaid($map,$this->page_size)->select();
      $dat['list'] = $mod->analy_byaid($dat['list']);
      $dat['admins'] = D('Admin')->get_by_list($dat['list'],'aid,nickname');
    }
    else
    {
      $dat['list'] = $mod->list_analy($map,$this->page_size)->select();
      $dat['list'] = $mod->analy_bytype($dat['list']);
    }
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function user_quality()
  {
    $mod = D('UserBase');
    $map = $mod->get_filters(true);
    if($_REQUEST['stime'] && $stime = strtotime($_REQUEST['stime']))
    {
      is_array($map['reg_time']) || $map['reg_time'] = [];
      $map['reg_time'][] = ['egt',strtotime(date('Y-m-d',$stime))];
    }
    if($_REQUEST['etime'] && $etime = strtotime($_REQUEST['etime']))
    {
      is_array($map['reg_time']) || $map['reg_time'] = [];
      $map['reg_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    $map['score'] = ['egt',0];
    $dat['list'] = $mod->analy_quality($map);
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function avatar_quality()
  {
    $mod = D('Avatar');
    $map = $mod->get_filters(true);
    $_REQUEST['stime'] || $_REQUEST['stime'] = date('Y-m-d',strtotime('-6 days'));
    if($_REQUEST['stime'] && $stime = strtotime($_REQUEST['stime']))
    {
      is_array($map['score_time']) || $map['score_time'] = [];
      $map['score_time'][] = ['egt',strtotime(date('Y-m-d',$stime))];
    }
    if($_REQUEST['etime'] && $etime = strtotime($_REQUEST['etime']))
    {
      is_array($map['score_time']) || $map['score_time'] = [];
      $map['score_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    $map['score'] = ['egt',0];
    $dat['list'] = $mod->analy_quality($map);
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display('user_quality');
  }

  public function score_quality()
  {
    $mod = D('DailyCount');
    $map = $mod->get_filters();
    $dat['list'] = $mod->field(
    [
      'date',
      'date_unix',
      'sum(score0)' => 'score0',
      'sum(score5)' => 'score5',
      'sum(score6)' => 'score6',
      'sum(score7)' => 'score7',
      'sum(score8)' => 'score8',
      'sum(score9)' => 'score9',
      '(sum(score0) + sum(score5) + sum(score6) + sum(score7) + sum(score8) + sum(score9))' => 'cnt_all',
    ])
      ->group('date')
      ->plist($this->page_size,$map)
      ->lists('','date desc,id desc');
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $this->pager = $mod->pager;
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function score_record()
  {
    $mod = D('UserScoreRecord');
    $map = [];//$mod->get_filters();
    $sql = $mod->field(
    [
      'id',
      'uid',
      'min(id)' => 'id_min',
      'max(id)' => 'id_max',
      'count(id)' => 'cnt',
      'max(score_time)' => 'score_time',
    ])
    ->group('uid')
    ->buildSql();
    $dat['list'] = $mod->table($sql)->alias('g')
      ->field('g.*,min.score as score_min,max.score as score_max')
      ->join('left join __USER_SCORE_RECORD__ min on min.id = g.id_min')
      ->join('left join __USER_SCORE_RECORD__ max on max.id = g.id_max')
      ->where('min.score != max.score')
      ->plist($this->page_size,$map)->lists('','g.score_time desc,g.uid desc') ?: [];
    $ids = array_merge(
      array_column($dat['list'],'id_min') ?: [],
      array_column($dat['list'],'id_max') ?: []
    );
    //$dat['scores'] = $mod->klist('id',['id' => ['in',$ids]]);
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $this->pager = $mod->pager;
    $this->data = $dat;
    if(trim($_REQUEST['download'])) foreach($dat['list'] ?: [] as $v)
    {
      $dat['export'][] =
      [
        '用户ID'   => $v['uid'],
        '首次评分' => $v['score_min'],
        '最后评分' => $v['score_max'],
        '评分次数' => $v['cnt'],
        '时间'     => $v['score_time'] ? date('Y-m-d H:i:s',$v['score_time']) : '',
      ];
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->export();
    $this->display();
  }


  // 主播每日统计数据
  public function live_host_daily()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-1 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime($_REQUEST['stime']));
    $mod = D('Stat')->set_table('__LIVE_HOST_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->plist($this->page_size,$map)->lists('','dtime desc,live_income desc,live_time desc,id desc') ?: [];
    $dat['page'] = $this->pager = $mod->pager;
    $dat['users'] = D('UserBase')->get_users_account($arr,'uid');
    $dat['hosts'] = D('LiveHost')->get_by_list($arr);
    $dat['contract_types'] = D('LiveContractType')->get_all() ?: [];
    foreach($arr as $k => $v)
    {
      $usr = $dat['users'][$v['uid']] ?: [];
      $row =
      [
        '日期'     => $v['dtime'],
        '主播ID'   => '<a href="'.U('UserBase/view',['uid' => $v['uid']]).'" target="_blank" class="label label-default popover-avatar" data-original-title="'.$usr['nickname'].'">'.$v['uid'].'</a>
                       <b class="label label-danger">'.implode(' ',array_filter([
                          boolval($usr['vip_level'] && $usr['vip_valid_end'] >= NOW_TIME) ? 'v' : '',
                          $usr['glory_grade'],
                          $dat['contract_types'][$dat['hosts'][$v['uid']]['contract_type']]['attrs']['name'],
                        ])).'</b>',
        '昵称'     => $usr['nickname'] ?: '-',
        '收礼金额' => $v['live_income'] ?: '-',
        '直播时长' => $v['live_time'] ?: '-',
        '关注数'   => $v['live_followers'] ?: '-',
        '人气'     => $v['live_thumbs'] ?: '-',
      ];
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys(reset($dat['list']) ?: []);
    $this->data = $dat;
    $this->display('common');
  }

  // 直播访客每日统计
  public function live_guest_daily()
  {
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-1 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-1 days'));
    isset($_REQUEST['is_robot'])   || $_REQUEST['is_robot']   = '0';
    isset($_REQUEST['visit_type']) || $_REQUEST['visit_type'] = '0';
    $mod = D('LiveVisit');
    $map = $mod->get_filters();
    $arr = $mod->field(
    [
      'from_unixtime(visit_time,\'%Y-%m-%d\')' => 'dtime',
      'count(id)'           => 'cnt',
      'count(distinct uid)' => 'cnt_user',
    ])
    ->group('dtime')
    ->plist($this->page_size)
    ->lists($map,'dtime desc,cnt desc');
    foreach($arr as $v)
    {
      $usr = $dat['users'][$v['uid']] ?: [];
      $row =
      [
        '日期' => $v['dtime'],
        '人数' => $v['cnt_user'] ?: '-',
        '次数' => $v['cnt'] ?: '-',
      ];
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys(reset($dat['list']) ?: []);
    $this->data = $dat;
    $this->display('common');
  }

  // 每日直播礼物数据
  public function live_gift_daily()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-3 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    $mod = D('Stat')->set_table('__LIVE_GIFT_DATA__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,used_cnt desc,id desc');
    $dat['goods'] = D('Goods')->klist();
    foreach($arr as $v)
    {
      $row =
      [
        '日期' => $v['dtime'],
        '礼物' => $dat['goods'][$v['goods_id']]['name'] ?: $v['goods_id'],
        '次数' => $v['used_cnt'],
      ];
      $dat['list'][] = $row;
    }
    $dat['cols'] = array_keys(reset($dat['list']) ?: []);
    $this->auto_rowspan = ['日期'];
    $this->data = $dat;
    $this->display('common');
  }

  // 直播访客来源统计
  public function live_guest_source()
  {
    $_REQUEST['time_type'] = 'dtime';
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-3 days'));
    isset($_REQUEST['etime']) || $_REQUEST['etime'] = date('Y-m-d',strtotime('-0 days'));
    $mod = D('Stat')->set_table('__LIVE_GUEST_SOURCE__');
    $map = $mod->get_filters();
    $arr = $mod->lists($map,'dtime desc,id desc');
    $dat['sources'] =
    [
      0 => '热门列表',
      1 => '最新直播',
      2 => '关注列表',
      3 => '主播资料',
      4 => '推送关注',
      5 => '查找',
      6 => '礼物',
      7 => '聊天',
      8 => '个人资料',
    ];
    foreach($arr as $k => $v)
    {
      $row =
      [
        '日期'     => $v['dtime'],
        '来源'     => $dat['sources'][$v['visit_source']] ?: $v['border_id'],
        '人数'     => $v['visit_user_num'] ?: '-',
        '次数'     => $v['visit_enter_num'] ?: '-',
        '人均次数' => round((int)$v['visit_enter_num'] / (int)$v['visit_user_num'],2),
      ];
      $key = 'total';
      $dat['list'][$key] = array_merge($row,
      [
        '日期'     => '<b>合计</b>',
        '来源'     => '<b>合计</b>',
        '人数'     => (int)$v['visit_user_num'] + (int)$dat['list'][$key]['人数'],
        '次数'     => (int)$v['visit_enter_num'] + (int)$dat['list'][$key]['次数'],
        '人均次数' => round((int)$dat['list'][$key]['次数'] / (int)$dat['list'][$key]['人数'],2),
      ]);
      $key = $v['dtime'].'-total';
      $dat['list'][$key] = array_merge($row,
      [
        '来源'     => '<b>合计</b>',
        '人数'     => (int)$v['visit_user_num'] + (int)$dat['list'][$key]['人数'],
        '次数'     => (int)$v['visit_enter_num'] + (int)$dat['list'][$key]['次数'],
        '人均次数' => round((int)$dat['list'][$key]['次数'] / (int)$dat['list'][$key]['人数'],2),
      ]);
      $key = $v['dtime'].'-'.$v['visit_source'];
      $dat['list'][$key] = $row;
    }
    $dat['cols'] = array_keys(reset($dat['list']) ?: []);
    $this->data = $dat;
    $this->auto_rowspan = ['日期'];
    //die(json_encode(compact('dat','arr','map')));
    $this->display('common');
  }

//自己写的方法
  public function get_ndata()//方法定义没错
  {
    $mod = D('Stat');
    $map = [];
//    var_dump($_REQUEST);exit();
    if(!empty($_GET['stime']) && !empty($_GET['etime'])){
      $map['dtime'] = array(array('egt',$_GET['stime']),array('elt',$_GET['etime']));
    }
    $count = $mod->table('__PLATFORM_INFO__')->where($map)->count();
    $page = new \Think\Page($count,$this->page_size ?: 5);
    $show = $page->show();
    $list = $mod ->table('__PLATFORM_INFO__')->where($map)->order('dtime desc')->limit($page->firstRow.','.$page->listRows)->select();
    $this->assign('page',$show);
    $this->assign('list',$list);
    $this->assign('count',$count);
    $this->display();

  }

  public function get_stat($api = '',$dat = [])
  {
    preg_match('/https?:/i',$api) || $api = C('api_root_statistic').$api;
    is_array($dat) && $dat = json_encode($dat);
    $ret = $this->http($api,$dat,'POST');
    $ret = json_decode($ret,true) ?: [];
    return $ret;
  }

}