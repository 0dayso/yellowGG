<?php
namespace Liehuo\Controller;
use Liehuo\Model;

class CliController extends \Think\Controller
{

  // 无需登录的操作
  public $guest_actions = [];

  public function __construct()
  {
    if(PHP_SAPI !== 'cli') die('not cli !');
    parent::__construct();
  }


  // 创建分表
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/create_subtables
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/create_subtables/is_first/1
  public function create_subtables()
  {
    $ret = [];
    $tbs =
    [
      'cj_fee_record'          => 'FeeRecord',
      'cj_diamond_record'      => 'GlamourRecord',
      'cj_glamour_record_like' => 'GlamourRecord',
      'cj_glamour_record_live' => 'GlamourRecord',
      'cj_match'               => 'Match',
      'cj_like'                => ['LikeMe',10000],
      'cj_like_me'             => ['LikeMe',10000],
    ];
    $min = D('UserBase')->where(['reg_time' => ['elt',strtotime('-1 days')]])->max('uid');
    $max = D('UserBase')->max('uid') + 200000;
    trim($_REQUEST['is_first']) && $min = 12200000;
    //$min = 12120000;$max = 12129999;//运营账号区间
    //cli_die("$min $max");
    foreach($tbs as $tnm => $cfg)
    for($uid = $min;$uid <= $max;$uid++)
    {
      $mod = $cfg;
      $avg = 50000;
      if(is_array($cfg))
      {
        $mod = $cfg[0] ?: 'UserBase';
        $avg = (int)$cfg[1] ?: $avg;
      }
      $num = (int)($uid / $avg);
      $tab = $tnm.'_'.$num;
      if(!$ret[$tab])
      {
        //echo $num."<br>";
        $ret[$tab] = D($mod)->execute('create table if not exists '.$tab.' like '.$tnm);
        $ret[$tab] = 1;
        cli_echo($tab.' is ok'."\n");
      }
    }
    rlog($ret,'create_subtables');
    die(json_encode(compact('min','max','ret')));
  }


  // 更新趣看直播Token
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/update_quklive_token
  // php /opt/wwwroot/admin.chujian.im/liehuo/index.php cli/update_quklive_token
  public function update_quklive_token()
  {
    $ret = D('QukLive')->user_refresh_token();
    die(json_encode(compact('ret')));
  }


  // 处理微信提现
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/handle_cash_weixin_queue
  public function handle_cash_weixin_queue()
  {
    $ret = false;
    $rds = D('WithdrawCash')->get_redis();
    $dat = $rds->rPop('php_cash_queue');
    is_string($dat) && $dat = json_decode($dat,true);
    if($dat)
    {
      $ret = D('RpcApi')->call('Account/cashByWeixinRedPack',
      [
        'id'  => (int)$dat['id'],
        'uid' => (int)$dat['uid'],
      ]);
    }
    die(json_encode(compact('ret','dat')));
  }


  // 自动打分
  public function auto_scoring()
  {
    $mod = D('Scoring');
    $rds = $mod->get_redis();
    $lst = $rds->zRange($mod->redis_list_key,-10,-1,true) ?: [];
    if($ids = array_keys($lst))
    {
      $uls = D('UserBase')->klist('uid',['uid' => ['in',$ids]]) ?: [];
    }
  }


  // 机器人自动相互关注
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/let_robot_auto_fans
  public function let_robot_auto_fans()
  {
    D('LiveGuest')->let_robot_auto_fans();
  }


  // 统计近期活跃且头像超过N张的用户数
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/count_user_album
  public function count_user_album()
  {
    cli_echo('start...'.PHP_EOL);
    $sql = 'SELECT uid,album FROM cj_user_base
WHERE uid IN
(
  SELECT uid FROM cj_location_base WHERE update_time >= UNIX_TIMESTAMP(\'2016-03-21\')
)';
    $sql = 'SELECT uid,album FROM cj_user_base WHERE reg_time >= UNIX_TIMESTAMP(\'2016-04-24\') and reg_time <= UNIX_TIMESTAMP(\'2016-04-24 23:59:59\')';
    $arr = D('UserBase')->query($sql) ?: [];
    $cnt = 0;
    foreach($arr as $v)
    {
      $alb = json_decode($v['album'],true);
      if(count($alb) >= 2)
      {
        $cnt++;
        cli_echo($cnt.PHP_EOL);
      }
    }
    cli_echo($cnt.' in '.count($arr).PHP_EOL);
    cli_echo($sql.PHP_EOL);
    cli_die(PHP_EOL);
  }


  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/users_by_active_and_album
  public function users_by_active_and_album()
  {
    cli_echo('start...'.PHP_EOL);
    $cnt = $avg = 0;
    $cnt_sex = [];
    $max = 20000;
    $psz = 2000;
    $uls = [];
    $fnm = C('LOG_PATH').DIRECTORY_SEPARATOR.'users-'.date('Ymd').'.log';
    $mod = D('UserBase');
    $sql = 'SELECT u.uid,u.album,u.sex,l.update_time from cj_user_base u
left join cj_location_base l on l.uid = u.uid
where u.score >= 6 and u.type = 0 and l.update_time >= unix_timestamp(\'2015-12-31\')';
    @file_put_contents($fnm,'');
    for($i = 0;$i < 100;$i++)
    {
      if($cnt >= $max) break;
      $arr = $mod->query($sql.' limit '.($i * $psz).','.$psz) ?: [];
      cli_echo($mod->get_last_sql().PHP_EOL);
      foreach($arr as $v)
      {
        if($cnt >= $max) break;
        $avg = round((int)$cnt_sex['0'] / $cnt,2);
        if($avg < 0.66 && $v['sex'] == 1) continue;
        $uid = (int)$v['uid'];
        $alb = json_decode($v['album'],true);
        if(count($alb) >= 2 && !isset($uls[$uid]))
        {
          $uls[$uid] = $uid;
          $cnt++;
          $cnt_sex[$v['sex']]++;
          @file_put_contents($fnm,$uid.PHP_EOL,FILE_APPEND);
          cli_echo($uid.' - '.$cnt.PHP_EOL);
        }
      }
    }
    cli_echo($cnt_sex['0'].' in '.$cnt.' = '.round($cnt_sex['0'] / $cnt,2).PHP_EOL);
    cli_die(PHP_EOL);
  }


  // 会员活动送彩票
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/vip_activity_lotterys
  public function vip_activity_lotterys()
  {
    $arr = [749,109,559,181,054,630,037,788,176,725,995,403,452,743,602,707,910,346,738,074,836,193,810,861,507,979,972,146,663,318,895,771,567,077,825,198,114,614,374,839,611,292,356,381,801,409,148,366,184,341];
    $key = 'php_vip_activity_lottery_20160602';
    $rds = D('AccountBase')->get_redis();
    $rds->del($key);
    $cnt = 0;
    for($i = 0;$i < 1000;$i++)
    {
      if($cnt >= 50) break;
      $num = sprintf('%03d',$arr[$i]);
      if(preg_match('/^(\d)\1\1$/',$num)) continue;
      $ret = $rds->sAdd($key,$num);
      if($ret)
      {
        $cnt++;
        cli_echo($num.' is ok '.PHP_EOL);
      }
    }
    $rds->expire($key,60 * 60 * 24 * 7);
    cli_die(PHP_EOL);
  }


  // 统计Redis容量
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/count_redis_memory
  // php /opt/wwwroot/admin.chujian.im/liehuo/index.php cli/count_redis_memory
  public function count_redis_memory()
  {
    $patterns =
    [
      'php_.+',
      '.+',
    ];
    $cfg = C('redis_live') ?: [];
    $rds = new \Redis();
    $rds->connect($cfg['host'],$cfg['port'],0);
    //$rds->setOption(4/*\Redis::OPT_SCAN*/,1/*\Redis::SCAN_RETRY*/);
    $result = [];//array_fill_keys($patterns,0);
    while($keys = $rds->scan($it,$match = '*',$count = 1000))
    {
      if(0) foreach($keys as $key)
      {
        $atk = preg_replace('/[_-][0-9a-z]+$/i','',$key);
        if($atk && $atk != $key)
        {
          $atk .= '_.+';
          if(!in_array($atk,$patterns)) $patterns[] = $atk;
        }
      }
      sleep(1);
      foreach($keys as $key)
      {
        foreach($patterns as $pattern)
        {
          isset($result[$pattern]) || $result[$pattern] = 0;
          if(preg_match("/^{$pattern}$/i",$key))
          {
            if($v = $rds->debug($key))
            {
              $result[$pattern] += $v['serializedlength'];
            }
            elseif($v = strlen($rds->dump($key)))
            {
              $result[$pattern] += $v;
            }
            cli_echo(''.json_encode($v)."\t\t".$key."\t\t".PHP_EOL);
            break;
          }
        }
      }
    }
    cli_echo(PHP_EOL);
    //print_r($patterns);
    cli_echo(PHP_EOL);
    print_r($result);
  }


  // 重置机器人荣耀等级
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/reset_robot_users_glory
  public function reset_robot_users_glory()
  {
    $ret = false;
    $mod = D('AccountBase');
    $rds = D('LiveGuest')->get_redis();
    $rls = $rds->sMembers('php_set_robot_library') ?: [];
    $arr = array_chunk($rls,1000) ?: [];//将uid分段
    $cnt = 0;
    foreach($arr as $i => $ids)
    {
      if(!$ids || !is_array($ids)) continue;
      $als = $mod->where(['uid' => ['in',$ids]])->klist('uid') ?: [];
      foreach($als as $k => $v)
      {
        $ret = false;
        $uid = (int)$v['uid'];
        $glo = (int)$v['glory'];
        // 清荣耀值
        if($glo >= 1)
        {
          $rnd = rand(0,200);
          $gra = 0;
          $rnd >=  1 && $gra = 1;
          $rnd >=  5 && $gra = 2;
          $rnd >= 10 && $gra = 3;
          $rnd >= 20 && $gra = 4;
          $rnd >= 40 && $gra = 5;
          $rnd >= 80 && $gra = 6;
          $rnd >=160 && $gra = 7;
          $ret = $mod->where(['uid' => $uid])->limit(1)->save(['glory' => $rnd,'glory_grade' => $gra]);
          cli_echo("user:$uid set glory = $rnd \t & glory_grade = $gra".PHP_EOL);
        }
        $ret && $cnt++;
        $mod->del_cache($uid);
      }
      usleep(500);
    }
    cli_echo(PHP_EOL);
    cli_echo("$cnt is ok in ".count($rls).".".PHP_EOL);
    cli_echo(PHP_EOL);
  }

  // 重置测试账号账户数据
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/reset_test_users_account
  public function reset_test_users_account()
  {
    $str = '
14017827
14266123
14034200
13996021
13780432
13060255
13059802
14121246
12200005
12200027
13420720
12200024
12200027
12200023
12200027
12200027
12200032
13657693
12200021
14168728
12200021
12200021
14168728
12201665
13550822
12200037
12200021
13550822
12201665
14168728
12200024
12201047
12201047
13059774
13055448
13591895
13591895
13591709
13591502
12200043
12200018
12200018
12200018
12883915
13555352
12200721
12209910
14281149
14281143';
    $ids = preg_split('/\s+/',trim($str)) ?: [];//12200026 保留晓勇测试账号
    $ids = array_unique($ids);
    $mod = D('AccountBase');
    $als = $mod->where(['uid' => ['in',$ids]])->klist('uid') ?: [];
    $rpc = D('RpcApi');
    $dbg = 1;
    $cnt = 0;
    foreach($als as $k => $v)
    {
      $ret = false;
      $uid = (int)$v['uid'];
      // 清钻石
      if($v['diamond'] >= 10)
      {
        if(!$dbg)
        {
          $ret = $rpc->call('Account/setDiamond',
          [
            'uid'     => $uid,
            'diamond' => 0 - (int)$v['diamond'],
            'remark'  => '测试账号钻石清理',
          ]);
          rlog([date('H:i:s'),$uid,'diamond',"old:{$v['diamond']}","ret:$ret"],'reset_test_users_account');
        }
        cli_echo("user:$uid set diamond ".(0 - (int)$v['diamond']).PHP_EOL);
      }
      // 清消费钻石
      if($v['total_expense_diamond'] >= 300)
      {
        if(!$dbg)
        {
          $ret = $mod->where(['uid' => $uid])->limit(1)->setField('total_expense_diamond',0);
          rlog([date('H:i:s'),$uid,'total_expense_diamond',"old:{$v['total_expense_diamond']}","ret:$ret"],'reset_test_users_account');
        }
        cli_echo("user:$uid set total_expense_diamond 0".PHP_EOL);
      }
      // 清荣耀值
      if($v['glory'] >= 300)
      {
        if(!$dbg)
        {
          $ret = $mod->where(['uid' => $uid])->limit(1)->save(['glory' => 0,'glory_grade' => 0]);
          rlog([date('H:i:s'),$uid,'glory',"old:{$v['glory']}","ret:$ret"],'reset_test_users_account');
        }
        cli_echo("user:$uid set glory & glory_grade 0".PHP_EOL);
      }
      // 清魅力值
      if($v['total_glamour'] >= 20000 && 0)
      {
        if(!$dbg)
        {
          $ret = $mod->where(['uid' => $uid])->limit(1)->setField('total_glamour',0);
          rlog([date('H:i:s'),$uid,'total_glamour',"old:{$v['total_glamour']}","ret:$ret"],'reset_test_users_account');
        }
        cli_echo("user:$uid set total_glamour 0".PHP_EOL);
      }
      $ret && $cnt++;
      $mod->del_cache($uid);
      usleep(100);
    }
    cli_echo(PHP_EOL);
    cli_echo("$cnt is ok.".PHP_EOL);
    cli_echo(PHP_EOL);
  }


  // 重置部分用户历史提现金额
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/reset_user_total_cash_fee
  public function reset_user_total_cash_fee()
  {
    cli_echo('start...'.PHP_EOL);
    $mod = D('AccountBase');
    $uls = $mod->klist('uid',['total_cash' => ['lt',0]]) ?: [];
    $cnt = 0;
    if($ids = array_keys($uls))
    {
      $cls = D('WithdrawCash')
      ->field('uid,sum(fee_cash) as fee')
      ->where(
      [
        'uid'   => ['in',$ids],
        'state' => ['in',[1,2]],
      ])->klist('uid') ?: [];
      cli_echo(D('WithdrawCash')->_sql().PHP_EOL);
      foreach($uls as $uid => $v)
      {
        $old = $v['total_cash'];
        $new = round($cls[$uid]['fee'],2);
        $ret = $mod->where(['uid' => $uid])->limit(1)->setField('total_cash',$new);
        $ret && $cnt++;
        $mod->del_cache($uid);
        cli_echo("user:$uid total_cash set $new\t, old is $old\t, ret is $ret".PHP_EOL);
      }
    }
    cli_echo(PHP_EOL);
    cli_echo("$cnt in ".count($uls)." is ok.".PHP_EOL);
    cli_echo(PHP_EOL);
  }

  // 重置用户直播收入魅力
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/reset_user_total_live_glamour
  public function reset_user_total_live_glamour()
  {
    cli_echo('start...'.PHP_EOL);
    $clear_list = [12200001,12200005,12200006,12200009,12200013,12200017,12200018,12200019,12200021,12200022,12200023,12200024,12200026,12200027,12200031,12200032,12200036,12200037,12200043,12200720,12200721,12200724,12200734,12201413,12201442,12201493,12201665,12205416,12209910,12212785,12883915,12997541,13025284,13048574,13059774,13059802,13060255,13060338,13060349,13060565,13060592,13260418,13420720,13530020,13550822,13657693,13683906,13780432,14017827,14034200,14121246,14168728,14281149];//上线前内部人员直播
    $mod = D('AccountBase');
    $uls = D('LiveHost')->klist('uid',['uid' => ['not in',$clear_list]]) ?: [];
    $als = $mod->klist('uid',['uid' => ['in',array_keys($uls) ?: null]]) ?: [];
    $grm = D('GlamourRecord');
    $cnt = 0;
    if($ids = array_keys($uls))
    {
      foreach($uls as $uid => $v)
      {
        if($uid <= 12200000) continue;
        $grm = D('GlamourRecord')->set_type(Model\GlamourRecordModel::TYPE_INCOME_LIVE_GIFT)->set_uid($uid);
        //if(in_array($uid,$clear_list ?: [])) $grm->where(['create_time' => ['elt',1468580280]])->delete();
        //if(in_array($uid,$clear_list ?: [])) continue;
        $tlg = (int)$grm->where(['uid' => $uid])->sum('glamour');
        $old = $als[$uid]['total_live_glamour'];
        $all = $als[$uid]['total_glamour'];
        $new = round($tlg,0);
        $ret = $mod->where(['uid' => $uid])->limit(1)->setField('total_live_glamour',$new);
        $mod->del_cache($uid);
        $ret && $cnt++;
        cli_echo("user:$uid total_live_glamour set $new\t\t, old is $old\t, all is $all\t, ret is $ret".PHP_EOL);
        rlog([date('H:i:s'),compact('uid','new','old','all','ret')],'reset_total_live_glamour');
      }
    }
    cli_echo(PHP_EOL);
    cli_echo('sql:'.$grm->_sql().PHP_EOL);
    cli_echo("$cnt in ".count($uls)." is ok.".PHP_EOL);
    cli_echo(PHP_EOL);
  }


  // 批量封禁用户和设备
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/closure_users_by_pkg_channels_bat
  public function closure_users_by_pkg_channels_bat()
  {
    cli_echo('start...'.PHP_EOL);
    //$mod = D('UserBase');
    $mod = new Model\UserBaseModel('','','conn_chujiandw');
    $sql = "SELECT u.uid,u.nickname,u.pkg_channel,l.lat,l.lng,LENGTH(u.nickname) as len,u.device_id
FROM cj_user_base u
LEFT JOIN cj_location_base l ON l.uid = u.uid
WHERE u.pkg_channel IN ('shop11','shop4')
AND l.lat = 0 AND l.lng = 0
AND u.type = 0
HAVING len = 3 OR len = 6 OR len = 9";
    $sql = "SELECT u.uid,u.nickname,u.pkg_channel,l.lat,l.lng,u.device_id
FROM cj_user_base u
LEFT JOIN cj_location_base l ON l.uid = u.uid
WHERE u.pkg_channel IN ('shop11','shop4')
AND u.uid IN (14237945,14331386,14331417,14331436,14331458,14331475,14331496,14331517,14331752,14331779,14331798,14331818,14331843,14331890,14331917,14331942,14331972,14332018,14332040,14332059,14332081,14332102,14332124,14332145,14332199,14332230,14332255,14332265,14332284,14332294,14332300,14332310)
AND l.lat >= 31.2758 AND l.lat <= 31.2760
AND l.lng >= 121.485 AND l.lng <= 121.487
AND u.type = 0";
    $sql = "SELECT u.uid,u.nickname,u.type,u.phone,u.pkg_channel,l.lat,l.lng,u.device_id,FROM_UNIXTIME(u.reg_time) as ymd
,LENGTH(u.nickname) as len
FROM cj_user_base u
LEFT JOIN cj_location_base l ON l.uid = u.uid
WHERE u.pkg_channel IN ('shop11','shop4')
AND u.reg_time >= UNIX_TIMESTAMP('2016-07-19')
AND u.reg_time < UNIX_TIMESTAMP('2016-07-23')
AND l.lat = 0 AND l.lng = 0
AND u.type = 0
HAVING len = 3 OR len = 6 OR len = 9";
    $sql = "SELECT u.uid,u.phone,u.nickname,u.birthday,u.`password`,u.pkg_channel,u.device_model,u.device_id,FROM_UNIXTIME(u.reg_time) as reg_ymd
,l.lat,l.lng,FROM_UNIXTIME(l.update_time) as login_ymd,LENGTH(u.nickname) as len
FROM cj_user_base u
LEFT JOIN cj_location_base l ON l.uid = u.uid
WHERE u.pkg_channel IN ('shop11','shop4')
AND u.reg_time >= UNIX_TIMESTAMP('2016-07-23')
AND u.reg_time < UNIX_TIMESTAMP('2016-07-30')
AND l.lat = 0 AND l.lng = 0
AND u.type = 0
HAVING len = 3 OR len = 6 OR len = 9";
    $sql = "SELECT u.uid,u.nickname,u.type,u.phone,u.`password`,u.pkg_channel,l.lat,l.lng,u.device_id,FROM_UNIXTIME(u.reg_time) as ymd,FROM_UNIXTIME(l.update_time) as ymd_login,LENGTH(u.nickname) as len
FROM cj_user_base u
LEFT JOIN cj_location_base l ON l.uid = u.uid
WHERE u.pkg_channel IN ('shop11')
AND u.type = 0
AND u.reg_time >= UNIX_TIMESTAMP('2016-07-29')
AND u.reg_time < UNIX_TIMESTAMP('2016-07-31')
AND l.lat = 0 AND l.lng = 0
HAVING len = 3 OR len = 6 OR len = 9";
    $sql = "SELECT u.uid,u.nickname,u.type,u.phone,u.`password`,u.pkg_channel,l.lat,l.lng,u.device_id
,FROM_UNIXTIME(u.reg_time) as ymd,FROM_UNIXTIME(p.send_time) as ymd_send,FROM_UNIXTIME(l.update_time) as ymd_login,LENGTH(u.nickname) as len
FROM cj_user_base u
LEFT JOIN cj_location_base l ON l.uid = u.uid
LEFT JOIN cj_phone_base p ON p.phone = u.phone
WHERE u.pkg_channel IN ('shop11','shop2','gf')
AND u.type = 0
AND u.reg_time >= UNIX_TIMESTAMP('2016-07-29')
AND u.reg_time < UNIX_TIMESTAMP('2016-08-08')
AND u.phone <> ''
#AND l.lat = 0 AND l.lng = 0
AND l.city = '北京' AND l.area = '东城区'
HAVING len = 3 OR len = 6 OR len = 9";
    $sql = "SELECT u.uid,u.nickname,u.type,u.phone,u.`password`,u.pkg_channel,l.lat,l.lng,u.device_id
,FROM_UNIXTIME(u.reg_time) as ymd,FROM_UNIXTIME(p.send_time) as ymd_send,FROM_UNIXTIME(l.update_time) as ymd_login,LENGTH(u.nickname) as len
FROM cj_user_base u
LEFT JOIN cj_location_base l ON l.uid = u.uid
LEFT JOIN cj_phone_base p ON p.phone = u.phone
WHERE u.pkg_channel IN ('shop11','shop1','shop2')
AND u.type = 0
AND u.reg_time >= UNIX_TIMESTAMP('2016-07-18')
AND u.reg_time < UNIX_TIMESTAMP('2016-08-09')
AND u.phone <> ''
AND l.lat = 0 AND l.lng = 0
#AND l.city = '北京' AND l.area = '东城区'
HAVING len = 6 OR len = 9";
    $sql = "SELECT u.uid,u.nickname,u.type,u.phone,u.`password`,u.pkg_channel,l.lat,l.lng,u.device_id
,FROM_UNIXTIME(u.reg_time) as ymd,FROM_UNIXTIME(p.send_time) as ymd_send,FROM_UNIXTIME(l.update_time) as ymd_login,LENGTH(u.nickname) as len
FROM cj_user_base u
LEFT JOIN cj_location_base l ON l.uid = u.uid
LEFT JOIN cj_phone_base p ON p.phone = u.phone
WHERE u.pkg_channel IN ('shop1')
AND u.type = 0
AND u.reg_time >= UNIX_TIMESTAMP('2016-08-08')
AND u.reg_time < UNIX_TIMESTAMP('2016-08-09')
AND u.phone <> ''
#AND l.lat = 0 AND l.lng = 0
AND l.area IN ('东城区','金水区','虹口区','玄武区')
HAVING len = 6 OR len = 9";
    $sql = "SELECT u.uid,u.nickname,u.type,u.phone,u.`password`,u.pkg_channel,l.lat,l.lng,u.device_id
,FROM_UNIXTIME(u.reg_time) as ymd,FROM_UNIXTIME(p.send_time) as ymd_send,FROM_UNIXTIME(l.update_time) as ymd_login,LENGTH(u.nickname) as len
FROM cj_user_base u
LEFT JOIN cj_location_base l ON l.uid = u.uid
LEFT JOIN cj_phone_base p ON p.phone = u.phone
WHERE u.pkg_channel IN ('shop1','shop2','shop11')
AND u.type = 0
AND u.reg_time >= UNIX_TIMESTAMP('2016-07-21')
AND u.reg_time < UNIX_TIMESTAMP('2016-07-24')
AND u.phone <> ''
#AND l.lat = 0 AND l.lng = 0
AND l.area IN ('东城区','金水区','虹口区','玄武区')
HAVING len = 6 OR len = 9";
    $sql = "SELECT u.uid,u.nickname,u.type,u.phone,u.`password`,u.pkg_channel,l.lat,l.lng,u.device_id
,FROM_UNIXTIME(u.reg_time) as ymd,FROM_UNIXTIME(p.send_time) as ymd_send,FROM_UNIXTIME(l.update_time) as ymd_login,LENGTH(u.nickname) as len
FROM cj_user_base u
LEFT JOIN cj_location_base l ON l.uid = u.uid
LEFT JOIN cj_phone_base p ON p.phone = u.phone
WHERE u.phone <> ''
AND u.type = 0
AND u.reg_time >= UNIX_TIMESTAMP('2016-08-14')
AND u.reg_time < UNIX_TIMESTAMP('2016-08-16')
AND u.device_id = ''";
    $uls = $mod->query($sql) ?: [];
    cli_echo('sql:'.$mod->_sql().PHP_EOL);
    $mod = new Model\UserBaseModel('','','conn_chujiandw');//D('UserBase');
    $isd = 0;//是否调试
    $cnt = 0;
    foreach($uls as $v)
    {
      $uid = (int)$v['uid'];
      $did = $v['device_id'];
      $isd || $mod->closure($uid,5/*封禁*/);
      cli_echo("user:$uid has closure".PHP_EOL);
      if($did && $did !== '0')
      {
        $isd || $mod->closure_device($did);
        $isd || $mod->set_devices_remark([$did => date('ymd').'渠道刷量']);
        cli_echo("device:$did has closure".PHP_EOL);
        $cnt++;
      }
      $mod->del_user_token($uid);
      $mod->del_user_cache($uid);
      $isd || D('OperLog')->log('closure',
      [
        '渠道刷量封禁账号和设备',
        '设备ID' => $did,
      ],$uid);
      if($cnt % 500 === 0) usleep(500);
    }
    cli_echo(PHP_EOL);
    cli_echo("$cnt in ".count($uls)." is ok.".PHP_EOL);
    cli_echo(PHP_EOL);
  }


  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/clear_user_live_record_replay
  public function clear_user_live_record_replay()
  {
    $uls = D('LiveRecord')->field('distinct uid')->where(['time_begin' => [['elt',1468580280],['egt',1]]])->select() ?: [];
    $rds = D('LiveRecord')->get_redis();
    $cnt = 0;
    foreach($uls as $v)
    {
      $uid = (int)$v['uid'];
      $rds->hDel('php_hash_latestrecord',$uid);
      $cnt++;
      cli_echo("user:$uid has clear".PHP_EOL);
    }
    cli_echo('sql:'.D('LiveRecord')->_sql().PHP_EOL);
    cli_echo(PHP_EOL);
    cli_echo("$cnt in ".count($uls)." is ok.".PHP_EOL);
    cli_echo(PHP_EOL);
  }


  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/count_user_account_data
  public function count_user_account_data()
  {
    $str = '
14041412
14201698
14289703
14289701
14289750
14290174
14290099
14292170
13622573
13387974
14297165
14298147
14310761
14171008';
    $ids = preg_split('/\s+/',trim($str)) ?: [];
    $ids = array_unique($ids);
    $mod = D('UserBase');
    $cnt = 0;
    $stm = strtotime('2016-07-01');
    $etm = strtotime('2016-07-31 23:59:59');
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
    }
    cli_echo(PHP_EOL);
    cli_echo("$cnt in ".count($ids)." is ok.".PHP_EOL);
    cli_echo(D('GlamourRecord')->_sql().PHP_EOL);
    cli_echo(D('WithdrawCash')->_sql().PHP_EOL);
    cli_echo(D('OrderGlamour')->_sql().PHP_EOL);
    cli_echo(PHP_EOL);
  }


  // 820活动 八大主播周榜第一名充值返100%
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/activity_live_host_week_top
  public function activity_live_host_week_top()
  {
    $ids = [13363954,13497455,14318993,13220962,14370335,14171920,14325443,13421711];
    $key = 'php_activity_live_host_week_top_rebate';
    $rds = D('UserBase')->get_redis();
    foreach($ids as $uid)
    {
      $mod = D('GlamourRecord')->set_type(Model\GlamourRecordModel::TYPE_INCOME_LIVE_GIFT)->set_uid($uid);
      $row = $mod->field(
      [
        'oid',
        'sum(glamour)' => 'gla',
      ])->where(
      [
        'uid'         => $uid,
        'create_time' => [['egt',strtotime('2016-08-15')],['elt',strtotime('2016-08-20 21:30:59')]],
      ])->group('oid')->order('gla desc')->find();
      if(!$row) cli_echo("$uid has no week top...".PHP_EOL);
      else
      {
        $ord = D('OrderV2')->field(
        [
          'uid',
          'sum(fee)' => 'fee',
        ])->where(
        [
          'uid'         => $row['oid'],
          'state'       => 2,
          'create_time' => [['egt',strtotime('2016-08-01')],['elt',strtotime('2016-08-20 21:30:59')]],
        ])->find();
        $ret = false;
        if($ord['fee'])
        {
          $dia = $ord['fee'] * 10;
          if(NOW_TIME >= strtotime('2016-08-20 21:29') && NOW_TIME <= strtotime('2016-08-20 23:59')) $ret = $rds->zAdd($key,$dia,$row['oid']);
        }
        cli_echo("uid:$uid\toid:{$row['oid']}\tglamour:{$row['gla']}\tcharge:$dia\tret:{$ret}".PHP_EOL);
        rlog([date('H:i:s'),compact('uid','row','ord','ret')],'activity_live_host_week_top',86400 * 15);
      }
    }
  }

  // 820活动 八大主播周榜第一名充值十天返还
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/activity_live_host_week_top_rebate
  public function activity_live_host_week_top_rebate()
  {
    $key = 'php_activity_live_host_week_top_rebate';
    $rds = D('UserBase')->get_redis();
    $dat = $rds->zRange($key,0,-1,true);
    foreach($dat as $uid => $sco)
    {
      if((NOW_TIME < strtotime('2016-08-21') || NOW_TIME > strtotime('2016-08-30 23:59:59')) || $uid != 12200022)
      {
        cli_die('time over');
        return false;
      }
      $hsk = 'php_activity_live_host_week_top_rebate_detail_'.$uid;
      $day = date('Ymd');
      $fee = (int)((float)$sco / 10);
      $old = $rds->zScore($hsk,$day);
      if($fee > 0 && $fee < $sco && !$old)
      {
        D('RpcApi')->call('Account/setDiamond',
        [
          'uid'     => $uid,
          'diamond' => $fee,
          'remark'  => '820充值返钻',
          'with_glory' => 0,
        ]);
        $ret = $rds->zAdd($hsk,$fee,$day);
        if(1)
        {
          $idx = intval((NOW_TIME - strtotime('2016-08-20')) / 86400);
          $msg = '爱一直在身边，烈火满月嘉年华100%返钻活动，今天第'.$idx.'天您收到'.$fee.'钻石返还，已经到账了哦~（共计10天）';
          //D('Message')->add_msg_system($uid,$msg);
          Model\MessageModel::Instance($uid,0,
          [
            'text_type' => 1,
          ])->send_timing_chat(NOW_TIME + 29,
          [
            'type'   => 7,
            'status' => 1,
            'hint'   => '',
            'text'   => $msg,
          ]);
        }
        cli_echo("uid:$uid\tfee:$fee".PHP_EOL);
        rlog([date('H:i:s'),compact('uid','fee','ret')],'activity_live_host_week_top_rebate_detail',86400 * 15);
      }
    }
  }

  // 820活动 八大主播20:00在线观众送钻
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/activity_live_online_reward_diamond
  public function activity_live_online_reward_diamond()
  {
    $ids = [13363954,13497455,14318993,13220962,14370335,14171920,14325443,13421711,14553954/*test*/];
    $key = 'php_activity_live_online_reward_diamond';
    $rds = D('UserBase')->get_redis();
    foreach($ids as $uid)
    {
      $ret = false;
      $rcd = D('LiveRecord')->where(
      [
        'uid' => $uid,
        'live_state' => 1,
        'time_begin' => ['egt',1],
      ])->order('id desc')->find();
      if(!$rcd) cli_echo("$uid is not living...".PHP_EOL);
      else
      {
        $jss = file_get_contents("http://10.162.43.38/live_chat/live_chat_room/{$rcd['live_chatroomid']}/members");
        $dat = json_decode($jss,true) ?: [];
        $lst = $dat['userids'] ?: [];
        if($lst && (NOW_TIME >= strtotime('2016-08-20 19:58') || $uid == 14553954))
        {
          foreach($lst as $oid)
          {
            $ret = false;
            $old = $rds->zScore($key,$oid);
            if($old) continue;
            $fee = rand(1,29);
            D('RpcApi')->call('Account/setDiamond',
            [
              'uid'     => $oid,
              'diamond' => $fee,
              'remark'  => '820活动赠送',
              'with_glory' => 0,
            ]);
            $ret = $rds->zIncrBy($key,$fee,$oid);
            cli_echo("uid:$uid\toid:$oid\tfee:$fee".PHP_EOL);
            rlog([date('H:i:s'),compact('uid','oid','fee','ret')],'activity_live_online_reward_diamond_detail',86400 * 15);
          }
        }
        $cnt = count($lst);
        cli_echo(json_encode(compact('uid','cnt','rcd','ret')).PHP_EOL);
        rlog([date('H:i:s'),compact('uid','rcd','lst','ret')],'activity_live_online_reward_diamond',86400 * 15);
      }
    }
  }

  // 820活动 八大主播20:00在线观众送钻
  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/activity_live_online_reward_diamond2
  public function activity_live_online_reward_diamond2()
  {
    if(1)
    {
      Model\MessageModel::Instance(12200022,0,
      [
        'text_type' => 1,
      ])->send_timing_chat(NOW_TIME + 5,
      [
        'type'   => 7,
        'status' => 1,
        'hint'   => '',
        'text'   => '测试123456',
      ]);
      $mmd = Model\MessageModel::Instance();
      $mmd->add_msg(12200022,
      [
        'type'   => 7,
        'status' => 1,
        'hint'   => '',
        'text'   => '测试123456',
      ]);
      cli_echo(json_encode($mmd->message).PHP_EOL);
      cli_die('test over'.PHP_EOL.PHP_EOL);
    }

    $ids = [14316464];
    foreach($ids as $uid)
    {
      $ret = false;
      $rcd = D('LiveRecord')->where(
      [
        'uid' => $uid,
        'live_state' => 1,
        'time_begin' => ['egt',1],
      ])->order('id desc')->find();
      if(!$rcd) cli_echo("$uid is not living...".PHP_EOL);
      else
      {
        $jss = file_get_contents("http://10.162.43.38/live_chat/live_chat_room/{$rcd['live_chatroomid']}/members");
        $dat = json_decode($jss,true) ?: [];
        $lst = $dat['userids'] ?: [];
        if($lst && (NOW_TIME >= strtotime('2016-08-20 22:00') || $uid == 14553954))
        {
          foreach($lst as $oid)
          {
            $ret = false;
            $rnd = rand(1,100);
            $fee = rand(1,29);
            $rnd >= 95 && $fee = rand(30,59);
            $rnd >= 99 && $fee = rand(60,99);
            D('RpcApi')->call('Account/setDiamond',
            [
              'uid'     => $oid,
              'diamond' => $fee,
              'remark'  => '820活动赠送',
              'with_glory' => 0,
            ]);
            $msg = '恭喜小公主直播间收集10量跑车，您获得'.$fee.'钻石！';
            Model\MessageModel::Instance($oid,0,
            [
              'text_type' => 1,
            ])->send_timing_chat(NOW_TIME + 29,
            [
              'type'   => 7,
              'status' => 1,
              'hint'   => '',
              'text'   => $msg,
            ]);
            cli_echo("uid:$uid\toid:$oid\tfee:$fee".PHP_EOL);
            rlog([date('H:i:s'),compact('uid','oid','fee','ret')],'activity_live_online_reward_diamond2_detail',86400 * 15);
          }
        }
        $cnt = count($lst);
        cli_echo(json_encode(compact('uid','cnt','rcd','ret')).PHP_EOL);
        rlog([date('H:i:s'),compact('uid','rcd','lst','ret')],'activity_live_online_reward_diamond2',86400 * 15);
      }
    }
  }

  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/set_lucky_times_by_live_gift_666
  public function set_lucky_times_by_live_gift_666()
  {
    cli_die('over'.PHP_EOL);
    $sql = "SELECT uid,COUNT(id) as cnt
FROM cj_live_gift_record
WHERE goods_id = 3015
AND create_time >= UNIX_TIMESTAMP('2016-08-21')
AND create_time <= UNIX_TIMESTAMP('2016-08-21 23:23')
GROUP BY uid,goods_id
ORDER BY goods_id";
    $dat = D('LiveGiftRecord')->query($sql) ?: [];
    $rds = D('AccountBase')->get_redis();
    foreach($dat as $v)
    {
      $uid = (int)$v['uid'];
      $cnt = (int)$v['cnt'];
      $key = 'php_user_daily_'.$uid;
      $ret = $rds->hIncrBy($key,'lucky_draw_times_160820',$cnt) && $rds->expire($key,86400 * 7);
      cli_echo("uid:$uid\tcnt:$cnt\tret:$ret".PHP_EOL);
      alog([date('H:i:s'),compact('uid','cnt','ret')],'set_lucky_times_by_live_gift_666');
      rlog([date('H:i:s'),compact('uid','cnt','ret')],'set_lucky_times_by_live_gift_666',86400 * 15);
    }
    cli_echo("".count($dat)." is ok.".PHP_EOL);
  }


  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/clear_user_cache_bat
  public function clear_user_cache_bat()
  {
    $str = '
13904472
13996246
14186679
14314399
14336144
14355020
14368331
14375157
14381789';
    $ids = preg_split('/\s+/',trim($str)) ?: [];
    $ids = array_unique($ids);
    $mod = D('UserBase');
    $cnt = 0;
    foreach($ids as $uid)
    {
      $cnt++;
      $mod->del_user_cache($uid);
      cli_echo("user:$uid cache has clear".PHP_EOL);
    }
    cli_echo(PHP_EOL);
    cli_echo("$cnt in ".count($ids)." is ok.".PHP_EOL);
    cli_echo(PHP_EOL);
  }

  //php /opt/wwwroot/admin.chujian.im/liehuo/index.php cli/set_live_token
  public function set_live_token()
  {
    $sign = md5('lhqktk'.date('H'));
    $str = file_get_contents('http://218.244.157.252/index.php/live/get_live_api_token?sign='.$sign);
    $arr = json_decode($str,true);
    if(!empty($arr['token'])){
      $rds = D('QukLive')->get_redis();
      $rds->set('php_string_livetoken',$arr['token']);
//        cli_echo($str);
    }
    alog([$str],'set_live_token');
  }

  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/count_redis_by_type
  public function count_redis_by_type()
  {
    $rds = D('UserBase')->get_redis();
    $dat = [];
    $lst = $rds->keys('php_conf_*');
    foreach($lst ?: [] as $v)
    {
      $typ = $rds->type($v);
      $dat[$typ] = (int)$dat[$typ] + 1;
      if($typ == \Redis::REDIS_HASH) $rds->del($v);
      cli_echo($v.PHP_EOL);
    }
    cli_echo(PHP_EOL);
    print_r($dat);
    cli_echo(PHP_EOL);
  }


  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php cli/test_nginx_limit_req
  public function test_nginx_limit_req()
  {
    $start_time = microtime(true);
    for($i = 0;$i < 1000;$i++)
    {
      $stime = microtime(true);
      $ret = file_get_contents('https://apitest1.chujianapp.com/auth/login',false,stream_context_create(
      [
        'http' =>
        [
          'timeout' => 10,
        ],
      ]));
      $jso = json_decode($ret,true) ?: [];
      $hds = $http_response_header ?: [];
      cli_echo("test:".sprintf('%-4s',$i)."\tHeader:".reset($hds)."\tRuntime:".sprintf('%-10s',round(microtime(true) - $stime,6))."\t\tReturn:".$jso['status'].PHP_EOL);
    }
    cli_echo("Runtime:".round(microtime(true) - $start_time,6).PHP_EOL);
  }


  // 提现失败手动退款
  public function cash_failed_refund()
  {
    return false;
    $uid = 0;
    $fee = 0;
    $gla = $fee * 1000;
    D('AccountBase')->set_glamour_inc($uid,$gla,
    [
      'type'   => Model\GlamourRecordModel::TYPE_INCOME_REFUND,//系统退款
      'remark' => '提现失败退还',
    ]);
    D('OperLog')->log('cash_set_state',
    [
      '提现失败手动退款',
      '金额' => $fee,
      '魅力' => $gla,
    ],$uid);
    die(json_encode(compact('uid','fee','gla')));
  }

}