<?php
namespace Liehuo\Controller;
use Liehuo\Model;

class TestController extends PublicController
{

  // 无需登录的操作
  public $guest_actions =
  [
    'msg',
    'msg_form',
    'msg2',
    'msg_form2',
    'msg_group',
    'msg_group_form',
    'import_fee_record_subtable',
    'create_fee_record_subtable',
    'device_closure_hold_min',
    'device_unclosure_hold_min',
    'device_closure_hold_min_remark',
    'clear_account_cache_bat',
    'test_mysql_prepare',
    'merge_chuchai_shop_field_values',
    'export_live_ranking',
    'test_web',
  ];

  public function __construct()
  {
    parent::__construct();
  }

  public function test_redis_rpush()
  {
    $rds = D('Rpc')->new_redis();
    $rds->del('test_redis_rpush');
    $rds->del('test_redis_rpush2');
    $ret = [];
    for($i = 1; $i <= 100; $i++)
    {
      $ret[1][$i] = $rds->rPush('test_redis_rpush',$i);
      $ret[2][$i] = $rds->rPush('test_redis_rpush2',$i);
    }
    die(json_encode(['data' => $ret]));
  }

  public function cash_bat()
  {
    $len = (int)$_REQUEST['length'] ?: 100;
    $rds = D('Rpc')->new_redis();
    //$rds->del('test_redis_rpush');
    $dat = [];
    for($i = 1; $i <= $len; $i++)
    {
      $dat[] = ['uid' => $i,'sum' => rand(1,1000) / 100];
    }
    $str = json_encode([
      'type'    => 'cash',
      'content' => json_encode([
        'items' => $dat,
        'update_time' => time(),
      ]),
    ]);
    $rds->rPush('go_list_rpc',$str);
    die($str);
  }

  public function cash_bat2()
  {
    $rds = D('Rpc')->new_redis();
    //$rds->del('test_redis_rpush');
    $dat = [];
    for($a = 1; $a <= rand(1000,1000); $a++)
    {
      for($i = 1; $i <= 1; $i++)
      {
        $dat[$a] = ['uid' => rand(100000,999999),'sum' => rand(1,1000) / 100];
      }
      $str = json_encode([
        'type'    => 'cash',
        'content' => json_encode([
          'items' => [$dat[$a]],
          'update_time' => time(),
        ]),
      ]);
      $rds->rPush('go_list_rpc',$str);
    }
    die(json_encode(['data' => $dat,$str]));
  }


  public function fix_cash_account_cache()
  {
    $ids = D('WithdrawCash')->where(['state' => 3])->getField('uid',true) ?: [];
    $ids = array_unique($ids) ?: [];
    $mod = D('AccountBase');
    $ret = [];
    foreach($ids as $uid)
    {
      $ret[$uid] = $mod->del_cache($uid);
    }
    die(json_encode($ret));
  }


  public function redis_bat()
  {
    for($i = 1;$i <= 100;$i++)
    {
      $this->redis_ajax();
    }
  }

/*
(function(i,fun)
{
  fun(i || 0,fun);
})
(0,function(i,fun)
{
  $.ajax(
  {
    url:'/liehuo/index.php/test/redis_ajax',
    data:{index:i}
  })
  .done(function(data)
  {
    if(i < 1000) setTimeout(function(){fun(i + 1,fun)},1);
  });
});
*/
  public function redis_ajax()
  {
    layout(false);
    //ini_set('default_socket_timeout',-1);
    try {
      D('Rpc')     ->get_redis()->rPush('test_alone_list',rand(1000,9999));
      D('UserBase')->get_redis()->rPush('test_alone_list',rand(1000,9999));
      D('Rpc')     ->get_redis()->expire('test_alone_list',60 * 60 * 24);
      D('UserBase')->get_redis()->expire('test_alone_list',60 * 60 * 24);
      ob_start();
      print_r(Model\RpcModel::$redis_instances);
      //$obc = ob_get_contents();
      ob_end_clean();
      echo $obc;
      echo "\n\n<br><br>\n\n";
    }
    catch(Exception $e)
    {
      echo $e->getMessage()."\r\n";
    }
  }


  public function import_offline_user_active()
  {
    $uls = D('Stat')->set_table('__DT_USER_SOURCE__')->getField('uid',true);
    $rds = D('UserBase')->get_redis('redis_default');
    $ret = [];
    foreach($uls ?: [] as $uid)
    {
      $uid = (int)$uid;
      $ret[$uid] = $rds->sAdd('php_dt_users',$uid);
    }
    die(json_encode(compact('ret','uls')));
  }


  /*
    导入头像有变动但未打分的用户
    SELECT uid FROM cj_avatar
    WHERE score_time = 0 AND type = 1
    AND create_time >= UNIX_TIMESTAMP('2016-01-27 15:00')
    AND create_time <= UNIX_TIMESTAMP('2016-01-28 12:30')
    GROUP BY uid
  */
  public function import_avatar_changed_unscored()
  {
    $uls = D('Avatar')->field(['uid','max(create_time)' => 'create_time'])
      ->where([
        'score_time'  => 0,
        'type'        => 1,
        'create_time' =>
        [
          ['egt',strtotime('2016-01-27 15:00')],
          ['elt',strtotime('2016-01-28 12:30')],
        ],
      ])
      ->group('uid')
      ->select();
    $rds = D('PhpServerRedis')->new_redis('redis_user');
    foreach($uls ?: [] as $v)
    {
      $rds->zAdd('php_avatar_scoring',$v['create_time'],$v['uid']);
    }
  }



  /*
    合并手机号重复的Miao用户
    SELECT phone,min(uid) as uid_min,max(uid) as uid_max,COUNT(uid) as cnt FROM `cj_user_base` GROUP BY phone HAVING cnt >= 2 ORDER BY uid_min
  */
  public function merge_miao_users()
  {
    //return false;
    $usr = D('UserBase');
    $lik = D('Like');
    $pls = $usr->field('phone,min(uid) as uid_min,max(uid) as uid_max,count(uid) as cnt')
      ->group('phone')->having('cnt >= 2')
      ->order('uid_min')->klist('phone');
    if($pls)
    {
      $uls = $usr->field('uid,phone,album')->where(['phone' => ['in',array_keys($pls)]])->klist('uid');
      $mar = $lik->field('uid,oid,like_type,like_time,matched')->where(
      [
        'uid'     => ['in',array_column($pls,'uid_max')],
        'matched' => 1,
      ])->select();
      $mls = [];
      foreach($mar ?: [] as $v) $mls[$v['uid']][] = $v;
      $adt = $mdt = [];
      foreach($uls ?: [] as $v)
      {
        $uid = $v['uid'];
        $tel = $v['phone'];
        $min = $pls[$tel]['uid_min'];
        $max = $pls[$tel]['uid_max'];
        $alb = json_decode($v['album'],true) ?: [];
        if(!$uid || !$tel) continue;
        // 烈火
        elseif($uid == $min)
        {
          $adt[$min] = array_merge($alb,$adt[$min] ?: []);
        }
        // Miao
        elseif($uid == $max && $uid >= 12345679)
        {
          $adt[$min] = array_merge($adt[$min] ?: [],$alb);
          //$lik->where(['uid' => $uid])->save(['uid' => $min]);
          //$lik->where(['oid' => $uid])->save(['oid' => $min]);
        }
        $adt[$min] = array_unique($adt[$min]);
        $adt[$min] = array_slice($adt[$min],0,6);
      }
    }
    die(json_encode(compact('pls','uls','als')));
  }


  public function msg_form2()
  {
    layout(false);
    $this->display();
  }

  public function msg2()
  {
    $msg = D('Message');
    //$msg->set_offline(0);
    $uid = $_REQUEST['uid'] ?: 12200022;
    $jss = $_REQUEST['json'];
    $jso = json_decode(trim(preg_replace('/\/\/[^"\r\n]*(?=[\r\n]|$)/','',$jss)),true) ?: $jss;
    is_string($jso) || $jso = json_encode($jso);
    //die(json_encode(compact('jss','jso')));
    $msg->recver = (int)$uid;
    $msg->msg_data_type = (int)$_REQUEST['chat_type'] ?: 2;
    $_REQUEST['sender'] && $msg->sender = (int)$_REQUEST['sender'] ?: 10000;
    if($_REQUEST['txt_type'] == 100 && $_REQUEST['msg_type'] == 9)
    {
      $msg->recver = 0;
      $msg->set_offline(0);
      $msg->set_target(1);
    }
    $msg->set_offline(0);
    $msg->add_queue(
    [
      'txt_type' => (int)$_REQUEST['txt_type'] ?: 100,
      'message'  => in_array($msg->msg_data_type,[3]) ? $jso : json_encode(
      [
        'type'   => (int)$_REQUEST['msg_type'] ?: 7,
        'status' => 1,
        'text'   => $jso,
        'hint'   => trim($_REQUEST['hint']),
        'cnmsg'  => ($tp1 = json_decode(trim($tp2 = $_REQUEST['cnmsg']))) ? $tp1 : $tp2,
      ]),
      'send_to_offline_user' => trim($_REQUEST['hint']) ? 1 : 0,
    ]);
    echo is_string($msg->message) ? $msg->message : json_encode($msg->message);
    //die("\n\n<br><br>".date('Y-m-d H:i:s'));
    //echo PHP_EOL.'<br>'.PHP_EOL;
    //die(json_encode($_REQUEST));
    die;
  }


  public function msg_group_form()
  {
    layout(false);
    $this->display();
  }

  public function msg_group()
  {
    $msg = Model\MessageModel::Instance(0,0,
    [
      'major_type' => (int)$_REQUEST['major_type'] ?: Model\MessageModel::MAJOR_TYPE_SYSTEM,
      'minor_type' => (int)$_REQUEST['minor_type'] ?: Model\MessageModel::MINOR_TYPE_COMPOUND,
      'sender'     => (int)$_REQUEST['sender'] ?: 10000,
      'room_id'    => (int)$_REQUEST['chat_room_id'],
    ]);
    $jss = $_REQUEST['json'];
    $jso = json_decode(trim(preg_replace('/\/\/[^"\r\n]*(?=[\r\n]|$)/','',$jss)),true) ?: $jss;
    if($_REQUEST['minor_type'] == Model\MessageModel::MINOR_TYPE_HORN
    || is_array($jso) && $jso['type'] == 306)
    {
      $msg = $msg->set_option('all_room',true);
    }
    $ret = $msg->send_group_chat($jso);
    echo is_string($ret) ? $ret : json_encode($ret);
    //die("\n\n<br><br>".date('Y-m-d H:i:s'));
    //echo PHP_EOL.'<br>'.PHP_EOL;
    //die(json_encode($_REQUEST));
    die;
  }


  public function msg_form()
  {
    layout(false);
    $this->display();
  }

  public function msg()
  {
    $msg = D('Message');
    $typ = (int)$_REQUEST['type'];
    $uid = $_REQUEST['uid'] ?: 12200022;
    $txt = $_REQUEST['txt'] ?: ("三等奖航班上三等奖航班\n上三等奖航班上三等奖航班上的变化就".time());
    $img = $_REQUEST['img'] ?: ('http://feed.chujianapp.com/20151209/7546edddbfa7870bd0782381910143d9.jpg@200w');
    $jso = $_REQUEST['json'] ? json_encode(json_decode($_REQUEST['json'],true)) : '';
    $typ == 1 && $msg->add_msg_system($uid,$txt);
    $typ == 2 && $msg->add_msg_scoring($uid,$txt,$img);
    $msg->recver = (int)$uid;
    $msg->msg_data_type = (int)$_REQUEST['chat_type'] ?: 1;
    $_REQUEST['sender'] && $msg->sender = (int)$_REQUEST['sender'] ?: 10000;
    $_REQUEST['txt_type'] == 1 && $msg->add_queue(
    [
      'txt_type' => (int)$_REQUEST['txt_type'] ?: 1,
      'message'  => json_encode(
      [
        'type'   => (int)$_REQUEST['msg_type'] ?: 7,
        'status' => 1,
        'text'   => $jso,
      ]),
    ]);
    $_REQUEST['txt_type'] == 100 && $_REQUEST['msg_type'] == 7 && $msg->add_queue(
    [
      'txt_type' => (int)$_REQUEST['txt_type'] ?: 1,
      'message'  => json_encode(
      [
        'type'   => (int)$_REQUEST['msg_type'] ?: 7,
        'status' => 1,
        'text'   => json_encode(
        [
          'comment'              => $txt,
          'sessionThumbImageUrl' => $img,
          'intent_name'          => '10002',
          'show_tip'             => 1,
        ]),
      ]),
    ]);

/*
运营消息格式：
{
    "type"  : 8,
    "status": 1,
    "hint"  : '离线通知',
    "text"  :
    [
        {
            "title": "运营消息标题1",
            "desc" : "运营消息描述运营消息描述运营消息描述\n运营消息描述运营消息描述1454309020",
            "link" : "http://app.chujianapp.com/hb/index.php?key=7ebdf6a0bf7dcbb1cc0f5d271278668d",
            "thumb": "http://feed.chujianapp.com/20160201/f4302310ab3f38043bcc1dbbfa90b6d4.jpg"
        }
    ]
}
*/
    $_REQUEST['txt_type'] == 100 && $_REQUEST['msg_type'] == 8 && $msg->add_queue(
    [
      'txt_type' => (int)$_REQUEST['txt_type'] ?: 1,
      'message'  => json_encode(
      [
        'type'   => (int)$_REQUEST['msg_type'] ?: 8,
        'status' => 1,
        'text'   => json_encode(
        [
          [
            'title' => $txt ?: '运营消息标题1',
            'desc'  => $_REQUEST['desc']  ?: "运营消息描述运营消息描述运营消息描述\n运营消息描述运营消息描述".(time() - rand(100,2000)),
            'link'  => 'http://app.chujianapp.com/hb/index.php?key=7ebdf6a0bf7dcbb1cc0f5d271278668d',
            'thumb' => $img ?: 'http://feed.chujianapp.com/20160201/f4302310ab3f38043bcc1dbbfa90b6d4.jpg',
          ],
          [
            'title' => '运营消息标题2',
            'desc'  => "运营消息描述运营消息描述运营消息描述\n运营消息描述运营消息描述".time(),
            'link'  => 'http://app.chujianapp.com/hb/index.php?key=7ebdf6a0bf7dcbb1cc0f5d271278668d',
            'thumb' => 'http://feed.chujianapp.com/20160201/f4302310ab3f38043bcc1dbbfa90b6d4.jpg',
          ],
        ]),
      ]),
    ]);

/*
[
    {
        "type": "text",
        "content": "文本1",
        "color": "000000",
        "fontSize": "14"
    },
    {
        "type": "text",
        "content": "文本2"
    },
    {
        "type": "link",
        "content": "链接1",
        "url": "http://wwww.baidu.com/",
        "color": "AA0000",
        "fontSize": "14"
    },
    {
        "type": "link",
        "content": "链接2",
        "url": "http://wwww.baidu.com/"
    },
    {
        "type": "intent",
        "content": "应用内跳转",
        "name": "1001",
        "color": "AA6600"
    },
    {
        "type": "icon",
        "name": "icon_gift"
    }
]
*/
    $_REQUEST['txt_type'] == 100 && in_array($_REQUEST['msg_type'],[10,11,214]) && $msg->add_queue(
    [
      'txt_type' => (int)$_REQUEST['txt_type'],
      'message'  => json_encode(
      [
        'type'   => (int)$_REQUEST['msg_type'],
        'status' => 1,
        'text'   => $jso ?: json_encode(
        [
          [
            'type'     => 'icon',
            'name'     => 'icon_gift',
          ],
          [
            'type'     => 'text',
            'content'  => $txt ?: '文本1',
            'color'    => '000000',
            'fontSize' => '14',
          ],
          [
            'type'     => 'text',
            'content'  => '文本2',
          ],
          [
            'type'     => 'link',
            'content'  => '链接1',
            'url'      => 'http://wwww.baidu.com/',
            'color'    => 'AA0000',
            'fontSize' => '14',
          ],
          [
            'type'     => 'link',
            'content'  => '链接2',
            'url'      => 'http://wwww.baidu.com/',
          ],
          [
            'type'     => 'intent',
            'content'  => '应用内跳转',
            'name'     => '1001',
            'color'    => 'AA6600',
          ],
        ]),
      ]),
    ]);
    echo is_string($msg->message) ? $msg->message : json_encode($msg->message);
    //die("\n\n<br><br>".date('Y-m-d H:i:s'));
    echo PHP_EOL.'<br>'.PHP_EOL;
    //die(json_encode($_REQUEST));
    die;
  }


  public function test_redis_auth()
  {
    $rds = D('PhpServerRedis')->new_redis('redis_recommend_20160121');
    sleep(10);
    $rds->set('test','auth ok '.time());
    die('ok '.time());
  }

  public function test_redis_10000()
  {
    $rds = D('PhpServerRedis')->new_redis('redis_default');
    for($i = 0;$i < 10000;$i++)
    {
      $rds->set('test','times '.$i);
    }
    die('ok '.time());
  }


  public function import_share_coupon_pubs()
  {
    $rds = D('PhpServerRedis')->get_redis();
    $arr = $rds->keys('php_share_coupon_*');
    foreach($arr ?: [] as $key)
    {
      if(preg_match('/(1\d{10})/',$key,$tmp) && ($tel = $tmp[1]) && ($dat = $rds->hGetAll($key)))
      {
        //echo $tel."<br>\n";
        $kbp = 'php_share_pub_'.$dat['pub_key'];
        $rds->zAdd($kbp,$dat['create_time'],$tel);
        $rds->expire($kbp,60 * 60 * 24 * 60 - (time() - $dat['create_time']));
      }
    }
    die(json_encode(
    [
      'count' => count($arr),
      'data'  => array_slice($arr,0,20),
    ]));
  }


  public function import_recommend_videos()
  {
    $mod = D('Avatar');
    $arr = $mod->where(['type' => 3,'audited' => 2])->select();
    foreach($arr ?: [] as $v)
    {
      $mod->recommend_video($v['resource']);
    }
    die(json_encode($arr));
  }


  public function import_fee_record_subtable()
  {
    echo 'start'."\n\n";
    $file = @fopen('E:\Work-HuanYu\wwwroot\0\Data\liehuo.cj_fee_record.201602171124.37593895.sql','r') or exit('Unable to open file!');
    $cnt = 0;
    while(!feof($file))
    {
      $str = fgets($file);
      if(!preg_match('/^\s*\(([^\)]+)\)[,;]\s*$/',$str,$arr)) continue;
      $str = $arr[1];
      $row = array_map(function($v)
      {
        return eval('return '.$v.';');
      },explode(',',$str) ?: []);
      $row = array_combine(['id','uid','serial_no','type','fee','balance','order_id','oid','remark','create_time'],$row);
      if(!$row) continue;
      $cnt++;
      echo json_encode($row)."\n";
      //if($cnt % 1000 === 0) usleep(1000 * 1000 * 0.5);
    }
    fclose($file);
    die($cnt.' is ok');
  }

  public function create_fee_record_subtable_first()
  {
    $ret = [];
    $tnm = 'cj_fee_record';
    $max = D('UserBase')->max('uid');
    $max = (int)($max / 50000);
    C('conn_stat_chujiandw',[
      'DB_TYPE'   => 'mysql',
      'DB_HOST'   => 'rds4l952ux9a0e1xkt16.mysql.rds.aliyuncs.com',
      'DB_NAME'   => 'chujiandw',
      'DB_USER'   => 'lh_count123',
      'DB_PWD'    => 'lhapp123',
      'DB_PORT'   => 3306,
      'DB_PREFIX' => 'cj_',
    ]);
    for($num = 243;$num <= $max;$num++)
    {
      $tab = $tnm.'_'.$num;
      if(!$ret[$num])
      {
        echo $num."<br>";
        $ret[$num] = M('FeeRecord')->execute('create table if not exists '.$tab.' like '.$tnm);
        //$ret[$num] = M('','','conn_stat_chujiandw')->execute('create table if not exists '.$tab.' like '.$tnm);
        $ret[$num] = 1;
      }
    }
    die(json_encode(compact('ret')));
  }

  public function create_fee_record_subtable()
  {
    $ret = [];
    $min = D('UserBase')->where(['reg_time' => ['elt',strtotime('-2 days')]])->max('uid');
    $max = D('UserBase')->max('uid') + 200000;
    for($uid = $min;$uid <= $max;$uid++)
    {
      $num = (int)($uid / 50000);
      $tab = 'cj_fee_record_'.$num;
      if(!$ret[$num])
      {
        echo $num."<br>";
        $ret[$num] = D('FeeRecord')->execute('create table if not exists '.$tab.' like cj_fee_record');
        $ret[$num] = 1;
      }
    }
    die(json_encode(compact('min','max','ret')));
  }


  public function create_glamour_record_subtable_first()
  {
    $ret = [];
    $tnm = 'cj_glamour_record';
    $max = D('UserBase')->max('uid');
    $max = (int)($max / 50000);
    for($num = 243;$num <= $max;$num++)
    {
      $tab = $tnm.'_'.$num;
      if(!$ret[$num])
      {
        echo $num."<br>";
        $ret[$num] = D('GlamourRecord')->execute('create table if not exists '.$tab.' like '.$tnm);
        $ret[$num] = 1;
      }
    }
    die(json_encode(compact('ret')));
  }


  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php test/device_closure_hold_min
  public function device_closure_hold_min()
  {
    cli_echo('start...'.PHP_EOL);
    $mod = D('UserBase');
    $acc = D('AccusationBaseLog');
    $dat = [];
    $map = ['device_id' => [['neq','0'],['neq','']]];
    $map['device_id'][] = ['not in',$mod->get_devices_closured(false) ?: [-1]];
    $mod->where($map);
    $mod->field(
    [
      'count(uid)' => 'cnt',
      'min(uid)'   => 'uid',
      'device_id',
    ]);
    $mod->group('device_id');
    $mod->having('cnt >= 3 and cnt <= 9');
    $mod->order('cnt desc');
    $dat['devices'] = $mod->klist('device_id') ?: [];
    $dat['rls'] = $mod->get_devices_remark();
    $dat['dls'] = array_diff(array_keys($dat['devices']),array_keys($dat['rls']));
    $dat['hds'] = array_column($dat['devices'],'uid');
    if(PHP_SAPI == 'cli') echo 'devices count '.count($dat['devices'])."\n";
    $cnt = $cnt_user = 0;
    $dls = $hds = [];
    foreach($dat['devices'] as $did => $dv)
    {
      $cnt++;
      $dls[] = $did;
      $hds[] = $dv['uid'];
      if($cnt % 50 === 0 || $cnt === count($dat['devices']))
      {
        $uls = $mod->field('uid,device_id,type,dblocking_time')
          ->where(
          [
            'device_id' => ['in',$dls],
            'uid'       => ['not in',$hds],
            'type'      => 0,
          ])
          ->klist('uid');
        $dbt = 1777777777;//2026-05-03 11:09:37
        foreach($uls ?: [] as $k => $v)
        {
          $uid = (int)$v['uid'];
          $udi = $v['device_id'];
          $rmk = $dat['rls'][$udi];

          if(/*调试模式*/$debug = 1)
          {
            if($rmk) continue;
            cli_echo("uid:$uid\ttype:{$v['type']}\tdid:$udi\trmk:{$rmk}".PHP_EOL);
            $cnt_user++;
          }
          elseif($uid && !$rmk)
          {
            $mod->where(['uid' => $uid])->limit(1)->save(['type' => 2,'dblocking_time' => $dbt]);
            $mod->del_user_token($uid);
            $mod->warn($uid,5,$dbt);
            $acc->log(
            [
              'oid'    => $uid,//被封禁人ID
              'status' => 5,//封禁状态
              'remark' => date('ymd').'同设备用户封禁保留主账号',
            ]);
            $mod->set_devices_remark([$udi => date('ymd').'同设备用户封禁保留主账号']);

            cli_echo("uid:$uid\ttype:{$v['type']}\tdid:$udi\trmk:{$rmk}".PHP_EOL);
            alog([$uid,$udi],'device_closure_hold_min');
            $cnt_user++;
          }
        }
        if(PHP_SAPI == 'cli') echo $cnt.' device is ok'."\n";
        $dls = $hds = [];
        usleep(200);
      }
    }
    0 && D('OperLog')->log('closure',
    [
      '同设备用户封禁保留主账号',
      '人数'   => count($cnt_user),
      '设备数' => count($cnt),
    ]);
    if(PHP_SAPI == 'cli') echo $cnt_user.' user is ok'."\n";
    if(PHP_SAPI == 'cli') die;
    die(json_encode(compact('dat','map')));
  }

  public function device_closure_hold_min_remark()
  {
    $str = file_get_contents('/opt/wwwroot/adm.chujian.im/App/Runtime/Logs/Liehuo/device_closure_hold_min-20160222.1751.log');
    $mod = D('UserBase');
    $rls = $mod->get_devices_remark();
    $cnt = 0;
    foreach(explode("\n",$str) ?: [] as $v)
    {
      $row = explode('|',$v) ?: [];
      $did = $row[3];
      if(count($row) == 4 && $did && !$rls[$did])
      {
        $cnt++;
        $mod->set_devices_remark([$did => '同设备用户封禁保留主账号']);
        if(PHP_SAPI == 'cli') echo $did."\n";
        if($cnt % 100 === 0)
        {
          if(PHP_SAPI == 'cli') echo $cnt.' is ok'."\n";
        }
      }
    }
    if(PHP_SAPI == 'cli') echo $cnt.' is ok'."\n";
    die;
  }

  public function device_unclosure_hold_min()
  {
    $str = file_get_contents('/opt/wwwroot/adm.chujian.im/App/Runtime/Logs/Liehuo/device_closure_hold_min-20160222.log');
    $mod = D('UserBase');
    $acc = D('AccusationBaseLog');
    $rls = $mod->get_devices_remark();
    $cnt = 0;
    foreach(explode("\n",$str) ?: [] as $v)
    {
      $row = explode('|',$v) ?: [];
      $uid = $row[2];
      $did = $row[3];
      if(count($row) == 4 && $uid)
      {
        $cnt++;
        $mod->where(['uid' => $uid])->limit(1)->save(['type' => 0,'dblocking_time' => 0]);
        $mod->warn($uid,0,0);
        $acc->log(
        [
          'oid'    => $uid,//被封禁人ID
          'status' => 0,//封禁状态
          'remark' => '批量解封',
        ]);
        alog([$uid],'device_unclosure_hold_min');
        if(PHP_SAPI == 'cli') echo $uid."\n";
      }
    }
    if(PHP_SAPI == 'cli') echo $cnt.' is ok'."\n";
    die;
  }


  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php test/clear_account_cache_bat
  public function clear_account_cache_bat()
  {
    $str = file_get_contents('/opt/wwwroot/adm.chujian.im/App/Runtime/Temp/liehuo.account.glamour.frozen.20160525.txt');
    $mod = D('AccountBase');
    $cnt = 0;
    foreach(explode("\n",$str) ?: [] as $v)
    {
      $row = explode("\t",$v) ?: [];
      $uid = (int)$row[0];
      if(count($row) == 2 && $uid)
      {
        $cnt++;
        $mod->del_cache($uid);
        alog([$uid],'clear_account_cache_bat');
        if(PHP_SAPI == 'cli') echo $uid."\n";
      }
    }
    if(PHP_SAPI == 'cli') echo $cnt.' is ok'."\n";
    die;
  }


  public function cash_failed_balance2glamour()
  {
    $ret = [];
    $sql = 'SELECT uid,balance,diamond,glamour,account_ver FROM cj_account_base
WHERE uid IN
(
  SELECT uid FROM cj_withdraw_cash
  WHERE state = 3 AND ( finish_time >= 1458576000 AND finish_time <= 1458662399 )
  AND glamour >= 1
)';//AND balance >= 1 AND account_ver = 2
    $mod = D('AccountBase');
    $arr = $mod->query($sql) ?: [];
    foreach($arr as $v)
    {
      $uid = (int)$v['uid'];
      if(!$uid) continue;
      $cdt = (int)D('WithdrawCash')->where('uid = '.$uid.' AND create_time >= 1458576000 AND create_time <= 1458662399')->sum('glamour');
      $ret[$uid] = $mod->where(['uid' => $uid])->save(['total_cash_glamour' => $cdt]);
      $mod->del_cache($uid);
    }
    die(json_encode(compact('ret')));
  }

/*

redis -h redisuser.chujianapp.com -p 6379 -a c30690277da3464f:Lhapp123
list rPush php_list_illegal_text
[
  {
    "type" : 'msg',//nickname|description|interest|job_haunt_character|msg
    "uid"  : 12200022,
    "text" : '文本',
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
  public function user_info_msg_queue()
  {
    $arr =
    [
      [
        'type' => 'msg',//nickname|description|interest|job_haunt_character|msg
        'text' => '文本',
      ],
    ];
  }


  public function test_mysql_prepare()
  {
    for($i=0; $i < 1000; $i++)
    { 
      M('AnlTest','','conn_base')->where(['id' => $i])->save(['dblocking_time' => rand()]);
      if(PHP_SAPI == 'cli') echo $i."\n";
    }
  }


  public function face_plus()
  {
    $mod = D('FacePlus');
    $url = 'http://feed.chujianapp.com/20160309/1a26ccba55b83bd9b2bd6b92ca117d34.jpg';//Anl
    //$url = 'http://feed.chujianapp.com/20151103/785606209aa62f298470e3dd636a50b5.jpg';//SWK
    //$url = 'http://feed.chujianapp.com/20151103/a2321084630772c57f48dcec3a4b7e4f.jpg';//SPM
    $dat = $mod->detect($_REQUEST['url'] ?: $url);
    die(json_encode(compact('dat')));
  }

  public function quklive_login()
  {
    $dat = D('QukLive')->user_login();
    //$dat = D('QukLive')->user_refresh_token();
    die(json_encode(compact('dat')));
  }


  // php /opt/wwwroot/adm.chujian.im/liehuo/index.php test/merge_chuchai_shop_field_values
  public function merge_chuchai_shop_field_values()
  {
    $frf = 0;
    $tof = 0;
    $cfg = require(APP_PATH.'ChuChai/Conf/database.php') ?: [];
    $cfg && C($cfg);
    //var_dump(C('conn_chuchai'));die;
    //$mod = D('ChuChai/ShopField');
    $mod = \ChuChai\Model\ShopFieldModel::Instance();
    $arr = $mod->where(['field_id' => ['in',[$frf,$tof]]])->select() ?: [];
    $lst = [];
    foreach($arr as $v)
    {
      $vid = $v['id'];
      $sid = $v['sid'];
      $fid = $v['field_id'];
      if(!($sid && $fid)) continue;
      $lst[$sid][$fid][$vid] = $v;
    }
    $cnt = 0;
    foreach($lst as $sid => $fls)
    {
      foreach($fls ?: [] as $fid => $vls)
      {
        if($fid != $frf)
        {
          cli_echo('field '.$fid.' is not from '.$frf.' continue...'.PHP_EOL);
          continue;
        }
        foreach($vls ?: [] as $vid => $val)
        {
          if($val['attrs'])
          {
            is_string($val['attrs']) && $val['attrs'] = json_decode($val['attrs'],true);
            $lst[$sid][$tof] || $lst[$sid][$tof] = [];
            $old = reset($lst[$sid][$tof]) ?: [];
            $oid = (int)key($lst[$sid][$tof]);
            if($old['attrs'])
            {
              is_string($old['attrs']) && $old['attrs'] = json_decode($old['attrs'],true);
              $adt = array_merge_recursive($old['attrs'] ?: [],$val['attrs'] ?: []);
              $mod->where(['id' => $oid])->save(['attrs' => $mod->auto_attrs($adt)]);
              cli_echo($mod->_sql().PHP_EOL);
              $cnt++;
            }
          }
          else
          {
            $mod->where(['id' => $vid])->save(['field_id' => $tof]);
            cli_echo($mod->_sql().PHP_EOL);
            $cnt++;
          }
        }
      }
    }
    die($cnt.' is ok'.PHP_EOL);
  }


  public function test_breadcrumbs()
  {
    die(json_encode(['data' => A('Navigation')->getNavPath()]));
  }


  public function count_redis_memory()
  {
    $patterns =
    [
      'foo:.+',
      'bar:.+',
      '.+',
    ];
    $cfg = C('redis_live') ?: [];
    $rds = new \Redis();
    $rds->connect($cfg['host'],$cfg['port'],0);
    $rds->setOption(4/*\Redis::OPT_SCAN*/,1/*\Redis::SCAN_RETRY*/);
    $result = array_fill_keys($patterns,0);
    while($keys = $rds->scan($it,$match = '*',$count = 1000))
    {
      foreach($keys as $key)
      {
        foreach($patterns as $pattern)
        {
          if(preg_match("/^{$pattern}$/",$key))
          {
            if($v = $rds->debug($key))
            {
              $result[$pattern] += $v['serializedlength'];
              cli_echo(json_encode($v).PHP_EOL);
            }
            break;
          }
        }
      }
    }
    cli_echo(PHP_EOL);
    var_dump($result);
  }


  public function add_live_ranking()
  {
    $uid = (int)$_REQUEST['uid'];
    $typ = trim($_REQUEST['type']);
    $inc = (int)$_REQUEST['inc'] ?: 1;
    $key = 'php_ranking_live_'.$typ;
    $rds = D('LiveGuest')->get_redis();
    if($sco = $rds->zScore($key,$uid))
    {
      $ret = $rds->zIncrBy($key,$inc,$uid);
      die('ok! '.$ret);
    }
    die('fail');
  }

  public function export_live_ranking()
  {
    @header('Content-Type: text/plain; charset=uft-8');
    $vfr = (int)$_REQUEST['vfr'];
    $typ = trim($_REQUEST['type']);
    $key = 'php_ranking_live_'.$typ;
    $rds = D('LiveGuest')->get_redis();
    if($dat = $rds->zRevRangeByScore($key,'+inf',$vfr,['withscores' => true]))
    {
      foreach($dat as $k => $v)
      {
        echo "$k\t$v\n";
      }
    }
    die;
  }


  public function test_web()
  {
    echo '<pre>';
    print_r([$_REQUEST,$_SERVER]);
    echo '<pre>';
    die;
  }


  public function executiontime1()
  {
    return false;
        $userlist = D('UserBase')->field('uid,album')->table('cj_miao_user')->limit(5000)->select();
        $i=0;
        foreach($userlist as $key => $val){
            //$myfile = fopen('ar/wwwujian1.7/photo/photo.txt','w');
            $current = file_get_contents('/opt/wwwroot/adm.chujian.im/App/Runtime/Temp/photo/photo.txt');
            $current .= "{$val['uid']}\n";
            file_put_contents('/opt/wwwroot/adm.chujian.im/App/Runtime/Temp/photo/photo.txt', $current);
            $album = json_decode($val['album'],true);
            if(!empty($album)){
                foreach($album as $k => $v){
                    if($v!=''){
                        $getname = explode('/',$v);
                        if($getname[0]!='' && $getname[1]!=''){
                            $picurl  = 'http://miaoimg.oss-cn-hangzhou-internal.aliyuncs.com/'.$v;
                            $this->GrabImage($picurl,'/opt/wwwroot/adm.chujian.im/App/Runtime/Temp/photo/'.$getname[1]);
                        }
                    }
                }
            }
            $i++;
        }
        echo $i;
        //die();
    }


    //URL是远程的完整图片地址，不能为空, $filename 是另存为的图片名字
    //默认把图片放在以此脚本相同的目录里
    public function GrabImage($url, $filename=""){
        //$url 为空则返回 false;
        if($url == ""){return false;}
        $ext = strrchr($url, ".");//得到图片的扩展名
        if($ext != ".gif" && $ext != ".jpg" && $ext != ".bmp"){echo "格式不支持！";return false;}
        if($filename == ""){$filename = time()."$ext";}//以时间戳另起名
        //开始捕捉
        ob_start();
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();
        $size = strlen($img);
        //if($size<1000) return true;
        $fp2 = fopen($filename , "a");
        fwrite($fp2, $img);
        fclose($fp2);
        return $filename;
    }


}