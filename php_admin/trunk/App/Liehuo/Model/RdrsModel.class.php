<?php
namespace Liehuo\Model;

class RdrsModel extends CjDatadwModel
{

  protected $connection      = 'conn_rdrs';
  //protected $dbName          = 'cj_rdrs';
  protected $tablePrefix     = 'rdrs_';
  protected $autoCheckFields = false;

  public $devices =
  [
    0 => '安卓',
    1 => 'iOS',
  ];

  public function __construct()
  {
    parent::__construct();
  }


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: '';//$this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid      = (int)$arr['uid'])      $map[$alias.'uid']      = $uid;
    if($ch_id    = (int)$arr['ch_id'])    $map[$alias.'ch_id']    = $ch_id;
    if($pkg_id   = (int)$arr['pkg_id'])   $map[$alias.'pkg_id']   = $pkg_id;
    if($adver_id = (int)$arr['adver_id']) $map[$alias.'adver_id'] = $adver_id;
    if($arr['sex']       != '') $map[$alias.'gender']    = (int)$arr['sex'];
    if($arr['gender']    != '') $map[$alias.'gender']    = (int)$arr['gender'];
    if($arr['user_type'] != '') $map[$alias.'user_type'] = (int)$arr['user_type'];
    if($arr['device']    != '') $map[$alias.'device']    = (int)$arr['device'];
    if($arr['hour']      != '') $map[$alias.'hour']      = (int)$arr['hour'];
    if($arr['pay_freq_type'] != '') $map[$alias.'pay_freq_type'] = (int)$arr['pay_freq_type'];
    $time_type = $alias.'dtime';
    $arr['time_type'] == 'reg_time'   && $time_type = $alias.'reg_time';
    $arr['time_type'] == 'reg_date'   && $time_type = $alias.'reg_date';
    $arr['time_type'] == 'login_time' && $time_type = $alias.'day_login_time';
    $arr['time_type'] == 'login_date' && $time_type = $alias.'day_login_date';
    $arr['time_type'] == 'visit_date' && $time_type = $alias.'visit_date';
    $isdate = in_array($arr['time_type'],['dtime','reg_date','login_date','visit_date']);
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$time_type]) || $map[$time_type] = [];
      $map[$time_type][] = ['egt',$isdate ? date('Y-m-d',$stime) : strtotime(date('Y-m-d',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$time_type]) || $map[$time_type] = [];
      $map[$time_type][] = ['elt',$isdate ? date('Y-m-d',$etime) : strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($arr['ad_serial'] != '')
    {
      $sql = D('Stat')->table('__ADVER__')->field('id')
        ->where(['ch_serial' => $arr['ad_serial']])
        ->buildSql();
      $map[$alias.'adver_id'] = ['exp','in '.$sql];
    }
    if($arr['pkg_channel'] != '')
    {
      $sql = D('Stat')->table('__ADVER__')->field('id')
        ->where(['id' => ['exp',' = '.$alias.'adver_id'],'ch_serial' => $arr['pkg_channel']])
        ->limit(1)->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $field = trim($arr['search_field']);
      $map['_complex'] =
      [
        '_logic' => 'or',
      ];
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'uid') $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }


  // 获取渠道列表
  public function get_channel_list($map = [],$ord = [],$lmt = false)
  {
    $this->table('__CHANNEL__');
    $this->where(['ch_del' => 0]);
    $map && $this->where($map);
    $ord && $this->order($ord);
    $lmt && $this->limit($lmt);
    return $this->klist('id');
  }

  // 获取广告列表
  public function get_adver_list($map = [],$ord = [],$lmt = false)
  {
    $this->table('__ADVER__');
    $this->where(['del' => 0]);
    $map && $this->where($map);
    $ord && $this->order($ord);
    $lmt && $this->limit($lmt);
    return $this->klist('id');
  }

  // 获取广告列表
  public function get_pkg_channels($map = [],$ord = [],$lmt = false)
  {
    $this->table('__ADVER__');
    $this->where(['del' => 0]);
    $this->field('ch_serial')->group('ch_serial');
    $map && $this->where($map);
    $ord && $this->order($ord);
    $lmt && $this->limit($lmt);
    return $this->klist('ch_serial');
  }

  // 获取包版本列表
  public function get_package_list($map = [],$ord = [],$lmt = false)
  {
    $this->table('__PACKAGE__');
    $this->where(['pkg_del' => 0]);
    $map && $this->where($map);
    $ord && $this->order($ord);
    $lmt && $this->limit($lmt);
    return $this->klist('id');
  }

  // 获取投放金额纪录
  public function get_market_list($map = [],$ord = [],$lmt = false)
  {
    $this->table('__MARKET__');
    if($map)
    {
      if($map['stime'] && $stime = strtotime($map['stime']))
      {
        is_array($map['ma_date']) || $map['ma_date'] = [];
        $map['ma_date'][] = ['egt',date('Y-m-d',$stime)];
      }
      if($map['etime'] && $etime = strtotime($map['etime']))
      {
        is_array($map['ma_date']) || $map['ma_date'] = [];
        $map['ma_date'][] = ['elt',date('Y-m-d',$etime)];
      }
      unset($map['stime'],$map['etime']);
      $this->where($map);
    }
    $ord && $this->order($ord);
    $lmt && $this->limit($lmt);
    return $this->klist('id');
  }

  // 根据包、渠道序列号获取广告
  public function get_adver($pkg_id = 0,$ser = '')//形式参数
  {
    $ser = trim($ser);
    $this->table('__ADVER__');
    $this->where(
    [
      'pkg_id'    => $pkg_id,
      'ch_serial' => $ser,
      'ch_del'    => 0,
    ]);
    if(!$adv = $this->find())
    {
      $chn = $this->get_channel_by_serial($ser) ?: [];
      if(!$chn['id']) $this->error = '渠道不存在';
      elseif($pkg_id && $ser)
      {
        $adv = $this->add_adver(
        [
          'pkg_id'    => $pkg_id,
          'ch_id'     => $chn['id'],
          'ch_serial' => $ser,
        ]);
      }
    }
    return $adv;
  }

  // 添加广告
  public function add_adver($dat = [])
  {
    $this->table('__ADVER__');
    $dat = array_merge(
    [
      'pkg_id'    => 0,
      'ch_id'     => 0,
      'ch_serial' => '',
      'utime'     => time(),
      'del'       => 0,
    ],$dat ?: []);
    $dat['id'] = $this->add($dat);
    return $dat['id'] ? $dat : $dat['id'];
  }

  // 根据广告序列号获取对应渠道
  public function get_channel_by_serial($ser = '')
  {
    $ser = preg_replace('/[\d_-]+$/is','',$ser);
    if(!$ser)
    {
      $this->error = '广告序列号错误';
      return false;
    }
    $this->table('__CHANNEL__');
    $this->where(
    [
      'ch_serial' => $ser,
      'ch_del'    => 0,
    ]);
    return $this->find();
  }

  // 修改用户来源广告、渠道、包
  public function set_user_adver_channel($ids = [],$adv = [])
  {
    $map = ['uid' => ['in',$ids]];
    $dat = [
      'adver_id' => $adv['id'] ?: $adv['adver_id'],
      'ch_id'    => $adv['ch_id'],
      'pkg_id'   => $adv['pkg_id'],
    ];
    $ret = $this->table('__REAL_TIME_DATA__')->where($map)->save($dat);
    $ret = $this->table('__DAILY_COUNT_DATA__')->where($map)->save($dat) + $ret;
    return $ret;
  }


  // 获取用户数量统计
  public function get_user_counts($map = [],$gby = true)
  {
    $gby === true && $gby = 'dtime';
    $arr = $this->table('__RETENTION_REG_DATA__')->where($map)->select();
    $dat = [];
    foreach($arr ?: [] as $v)
    {
      $key = $v[$gby];
      $old = $dat[$key] ?: [];
      foreach(
      [
        'total_user_num',
        'pass_score_user_num',
      ] as $f)
      {
        if(!$gby) $dat[$f] = (int)$v[$f] + (int)$dat[$f];
        else $dat[$key][$f] = (int)$v[$f] + (int)$dat[$key][$f];
      }
    }
    return $dat;
  }


  // 获取用户首次登陆地
  public function get_reg_city_by_list($arr = [],$fields = 'uid,city',$field_pk = 'uid')
  {
    $dat = [];
    if($ids = array_unique(array_column($arr ?: [],$field_pk)) ?: [])
    {
      if($fields) $this->field($fields);
      $dat = $this->table('__USER_REG_CITY__')->klist('uid',
      [
        'uid'   => ['in',$ids],
        'utime' => ['egt',strtotime('2016-01-16 15:00')],
      ]) ?: [];
    }
    return $dat;
  }


  // 导入地推用户来源
  public function import_offline_source($ids = [],$dat = [])
  {
    $ret = false;
    $arr = array_values($ids) ?: [];
    if(is_array($arr[0]))
    {
      $uls = $ids ?: [];
      $ids = array_column($uls,'uid') ?: [];
    }
    else $uls = $this->table('__REAL_TIME_DATA__')->klist('uid',['uid' => ['in',$ids]]) ?: [];
    $els = $this->table('__DT_USER_SOURCE__')->klist('uid',['uid' => ['in',$ids]]) ?: [];
    $als = [];
    $now = time();
    foreach($ids ?: [] as $k)
    {
      if($k && !isset($els[$k]))
      {
        $usr = $uls[$k] ?: [];
        if(!$usr) continue;
        $als[] = $tmp =
        [
          'uid'      => $k,
          'reg_time' => $usr['reg_time'] ?: $now,
          'adver_id' => $dat['id'] ?: $dat['adver_id'],
          'ch_id'    => $dat['ch_id'],
          'pkg_id'   => $dat['pkg_id'],
          'device'   => $usr['device'],
          'gender'   => isset($usr['gender']) ? $usr['gender'] : $usr['sex'],
        ];
        $this->add_regdt_list($k,$tmp['reg_time']);//地推用户注册时间
        alog($tmp);
      }
    }
    if($als) $ret = $this->table('__DT_USER_SOURCE__')->addAll($als);
    return $ret;
  }

  // 导入地推用户每日行为数据
  public function import_offline_daily_user($ids = [],$dat = [])
  {
    $ret = false;
    $uls = $this->table('__REAL_TIME_DATA__')->klist('uid',['uid' => ['in',$ids]]) ?: [];
    $this->import_offline_source($uls,$dat);
    $arr = $this->table('__DAILY_ANALYSIS_DATA__')->where(['uid' => ['in',$ids]])->select() ?: [];
    $ols = $dts = [];
    foreach($arr as $v)
    {
      $ols[$v['dtime'].$v['uid']] = $v;
      $dts[] = $v['dtime'];
    }
    if(!$dts) return false;
    $arr = $this->table('__DT_ANALYSIS_DATA__')->where(
    [
      'uid'   => ['in',$ids],
      'dtime' => ['in',$dts],
    ])->select() ?: [];
    $els = [];
    foreach($arr as $v)
    {
      $els[$v['dtime'].$v['uid']] = $v;
    }
    $als = [];
    foreach($ols as $k => $v)
    {
      if(!isset($els[$k]))
      {
        $usr = $uls[$v['uid']] ?: [];
        $als[] = $tmp =
        [
          'uid'      => $v['uid'],
          'dtime'    => $v['dtime'],
          'reg_time' => $usr['reg_time'] ?: time(),
          'adver_id' => $dat['id'] ?: $dat['adver_id'],
          'ch_id'    => $dat['ch_id'],
          'pkg_id'   => $dat['pkg_id'],
          //'device'   => $usr['device'],
          //'gender'   => $usr['gender'],
        ];
        alog(array_merge(['import_offline_daily_user'],$tmp));
      }
    }
    if($als) $ret = $this->table('__DT_ANALYSIS_DATA__')->addAll($als);
    alog(['import_offline_daily_user',$this->getLastSql(),$ret]);
    return $ret;
  }

  // 更新地推用户每日行为数据
  public function update_offline_daily_user()
  {
    $this->set_table('__DT_ANALYSIS_DATA__');
    $dat = [];
    foreach($this->getDbFields() ?: [] as $v)
    {
      if(!in_array($v,['id','uid','dtime','ch_id','adver_id','pkg_id','reg_time']))
      {
        $dat['dt.'.$v] = ['exp','da.'.$v];
      }
    }
    $this->insert_offline_daily_user();//更新过去导入用户的新的活跃
    $ret = $this->table('__DT_ANALYSIS_DATA__ dt,__DAILY_ANALYSIS_DATA__ da')
      ->where(
      [
        'da.uid'   => ['exp','= dt.uid'],
        'da.dtime' => ['exp','= dt.dtime'],
      ])
      ->save($dat);
    //alog($dat);
    alog(['update_offline_daily_user',$this->getLastSql(),$ret]);
    return $ret;
  }

  // 导入地推用户最新行为数据
  /*
    SELECT da.uid,da.dtime,du.reg_time FROM rdrs_daily_analysis_data da,rdrs_dt_user_source du
    WHERE da.uid = du.uid
    AND NOT EXISTS
    (
      SELECT id FROM rdrs_dt_analysis_data WHERE uid = da.uid and dtime = da.dtime
    )
  */
  public function insert_offline_daily_user()
  {
    $sql = $this->table('__DT_ANALYSIS_DATA__')->where(
    [
      'uid'   => ['exp','= da.uid'],
      'dtime' => ['exp','= da.dtime'],
    ])->buildSql();
    $sql = $this->table('__DAILY_ANALYSIS_DATA__ da,__DT_USER_SOURCE__ du')
    ->field('da.uid,da.dtime,du.reg_time,du.ch_id,du.adver_id,du.pkg_id')
    ->where(
    [
      'da.uid'  => ['exp','= du.uid'],
      '_string' => 'not exists '.$sql,
    ])->fetchSql(true)->select();
    $sql = 'insert into __DT_ANALYSIS_DATA__ (uid,dtime,reg_time,ch_id,adver_id,pkg_id) '.$sql;
    $ret = $this->execute($sql);
    alog(['insert_offline_daily_user',$this->getLastSql(),$ret]);
    return $ret;
  }

  // 更新地推每日统计
  public function update_offline_daily()
  {
    $this->update_offline_daily_user();
    $this->import_offline_user_active();
    $this->export_offline_daily_cpp();
  }

  // 导出地推行为数据给C++服务器生成日统计数据
  public function export_offline_daily_cpp()
  {
    $sql = $this->table('__DT_STAT_DATA__')->field('id')->where(
    [
      'dtime' => ['exp','= dt.dtime'],
    ])->buildSql();
    $dls = $this->table('__DT_ANALYSIS_DATA__ dt')->where(
    [
      '_string' => 'not exists '.$sql,
      'dtime'   => [['elt',date('Y-m-d',strtotime('-1 days'))],['egt',date('Y-m-d',strtotime('-100 days'))]],
    ])->select() ?: [];
    alog(['export_offline_daily_cpp',$this->getLastSql(),count($dls)]);
    if($ids = array_unique(array_column($dls,'uid')))
    {
      $uls = $this->table('__DT_USER_SOURCE__')->klist('uid',['uid' => ['in',$ids]]);
      foreach($uls as $k => $v)
      {
        $v['uid'] && $this->add_regdt_list($v['uid'],$v['reg_time']);//地推用户注册时间
      }
    }
    $rds = $this->get_redis();
    foreach($dls as $v)
    {
      $tim = strtotime($v['dtime']);
      $key = 'daily_stats_'.date('Ymd',$tim);
      $row = ['uid' => (int)$v['uid']];
      $all = 0;
      foreach(
      [
       'day_nope_num',
       'day_free_thumb_num',
       'day_pay_thumb_num',
       'day_free_like_num',
       'day_pay_like_num',
       'day_free_match_num',
       'day_pay_match_num',
       'day_buy_thumb_sums',
       'day_buy_like_sums',
       'day_buy_vip_sums',
       'day_cash_sums',
       'day_recharge_sums',
      ] as $field)
      {
        $val = (float)$v[$field];
        $all += $val;
        if($val > 0)
        {
          $row[$field] = $val;
        }
        $this->add_activedt_list($v['uid'],$tim);
      }
      if($all > 0)
      {
        $rds->rPush($key,json_encode($row));
        alog(array_merge(['export_offline_daily_cpp'],$row));
      }
    }
    if($dls && !(int)@exec('ps -ef|grep dtHttpstats30Days|grep -v grep|wc -l'))
    {
      $shl = @exec('sh /opt/wwwroot/httpcodeframe/httpstats/dt_stats/dt.sh');
      alog(['shell',$shl]);
    }
  }


  // 导入老的用户行为数据 已废弃
  public function import_offline_daily_count($ids = [],$dat = [])
  {
    $ret = false;
    $arr = $this->table('__DAILY_COUNT_DATA__')->where(['uid' => ['in',$ids]])->select() ?: [];
    $ols = $dts = [];
    foreach($arr as $v)
    {
      $ols[$v['day_login_date'].$v['uid']] = $v;
      $dts[] = $v['day_login_date'];
    }
    $arr = $this->table('__DAILY_ANALYSIS_DATA__')->where(
    [
      'uid'   => ['in',$ids],
      'dtime' => ['in',$dts],
    ])->select() ?: [];
    $els = [];
    foreach($arr as $v)
    {
      $els[$v['dtime'].$v['uid']] = $v;
    }
    $als = [];
    foreach($ols as $k => $v)
    {
      if(!isset($els[$k]))
      {
        $als[] = $tmp =
        [
          'uid'                => $v['uid'],
          'dtime'              => $v['day_login_date'],
          'ch_id'              => $v['ch_id'],
          'adver_id'           => $v['adver_id'],
          'pkg_id'             => $v['pkg_id'],
          'device'             => $v['device'],
          'gender'             => $v['gender'],
          'day_nope_num'       => $v['day_nope_num'],
          'day_free_thumb_num' => $v['day_thumb_num'],
          'day_free_like_num'  => $v['day_free_like_num'],
          'day_pay_like_num'   => $v['day_pay_like_num'],
          'day_free_match_num' => $v['day_free_match_num'],
          'day_pay_match_num'  => $v['day_pay_match_num'],
          'day_buy_like_sums'  => $v['day_buy_like_sums'],
          'day_buy_vip_sums'   => $v['day_buy_vip_sums'],
          'day_cash_sums'      => $v['day_cash_sums'],
        ];
        alog(array_merge(['import_offline_daily_count'],$tmp));
      }
    }
    if($als) $ret = $this->table('__DAILY_ANALYSIS_DATA__')->addAll($als);
    return $ret;
  }

  // 导入地推用户每日活跃数据
  public function import_offline_user_active($tim = 0)
  {
    $day = date('Ymd',$tim);
    $rds = $this->get_redis();
    $als = $rds->zRange('php_active_'.$day) ?: [];
    $uls = $rds->sMembers('php_dt_users') ?: [];
    $lst = array_intersect($als,$uls) ?: [];
    foreach($lst as $uid)
    {
      $this->add_activedt_list($uid,$tim);
    }
    return $lst;
  }

  public function add_regdt_list($uid = 0,$tim = 0)
  {
    $uid = (int)$uid;
    $key = 'cpp_list_reg_'.date('Ymd',$tim);
    $rds = $this->get_redis();
    $ret = $rds->rPush($key,$uid);
    if($ret) $rds->expire($key,60 * 60 * 24 * 32);
    $rds->sAdd('php_dt_users',$uid);
    return $ret;
  }

  public function add_activedt_list($uid = 0,$tim = 0)
  {
    $key = 'cpp_list_pull_'.date('Ymd',$tim);
    $rds = $this->get_redis();
    $ret = $rds->rPush($key,(int)$uid);
    if($ret) $rds->expire($key,60 * 60 * 24 * 32);
    return $ret;
  }

}