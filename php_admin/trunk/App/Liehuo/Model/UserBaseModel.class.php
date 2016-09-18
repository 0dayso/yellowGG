<?php
namespace Liehuo\Model;

class UserBaseModel extends CjDatadwModel
{

  protected $redis_config = 'redis_user';

  public $api_root;
  public $api_reg;
  public $api_loc;

  // 动态图片根路径
  public $avatar_url_root = 'http://feed.chujianapp.com/';

  const TYPE_NORMAL   = 0;
  const TYPE_VIRTUAL  = 1;
  const TYPE_CLOSURED = 2;
  const TYPE_WARNED   = 3;
  const TYPE_ROBOT    = 4;

  // 用户状态
  public $user_types =
  [
    self::TYPE_NORMAL   => '正常',
    self::TYPE_VIRTUAL  => '运营',
    self::TYPE_CLOSURED => '封禁',
    self::TYPE_WARNED   => '警告',
    self::TYPE_ROBOT    => '机器人',
  ];

  // 处罚状态
  public $warning_status = [
    0  => [
      'name' => '拒绝受理',
      'days' => '+0 days',
    ],
    1  => [
      'name' => '普通警告1天',
      'days' => '+1 days',
    ],
    2  => [
      'name' => '轻微警告3天',
      'days' => '+3 days',
    ],
    3  => [
      'name' => '中度警告5天',
      'days' => '+5 days',
    ],
    4  => [
      'name' => '严重警告7天',
      'days' => '+7 days',
    ],
    5  => [
      'name' => '永久封禁',
      'days' => '+3600 days',
    ],
    6  => [
      'name' => '封禁设备',
      'days' => '+3600 days',
    ],
    -1 => [
      'name' => '解除警告/封禁',
      'days' => '-1 days',
    ],
    -2 => [
      'name' => '已处罚不再处罚',
      'days' => '-0 days',
    ],
    -3 => [
      'name' => '已做其他处理',
      'days' => '-0 days',
    ],
  ];

/*
  $_album
  [
    {
      "resource" : "http://...", //原始路径
      "thumb"    : "http://...", //缩略图
      "text"     : "", //描述
      "type"     : 1, // 0:文本 1:图片 2:音频 3:视频 4:图文
    },
    {...}
  ]
*/


  // 自动验证
  protected $_validate = array(
    array('nickname','require','昵称不能为空'),
    array('phone','/^1[34578]\d{9}$/i','手机号格式错误'),
    array('sex',array(0,1),'性别错误',0,'in'),
  );

  // 自动完成
  protected $_auto = array(
    array('attrs','auto_attrs',3,'callback'),
  );

  public function __construct()
  {
    parent::__construct();
    $this->api_root = C('api_root_app');
    $this->api_reg  = $this->api_root.'auth/reg';
    $this->api_loc  = $this->api_root.'lbs/update_location';

    // 模板自动替换 动态头像根路径
    $cfg = C('TMPL_PARSE_STRING') ?: array();
    if(is_array($cfg) && !isset($cfg['__AVATAR_URL_ROOT__']))
    {
      $cfg['__AVATAR_URL_ROOT__'] = $this->avatar_url_root;
      C('TMPL_PARSE_STRING',$cfg);
    }
  }

  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    $time_type = $arr['time_type'] == 'update' ? 'l.update_time' : ($alias.'reg_time');
    if($arr['stime'] && $stime = strtotime($_REQUEST['stime'] = $_GET['stime'] = urldecode(urldecode($arr['stime']))))
    {
      is_array($map[$time_type]) || $map[$time_type] = [];
      $map[$time_type][] = ['egt',strtotime(date('Y-m-d H:i:s',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($_REQUEST['etime'] = $_GET['etime'] = urldecode(urldecode($arr['etime']))))
    {
      is_array($map[$time_type]) || $map[$time_type] = [];
      $map[$time_type][] = ['elt',strtotime(date('Y-m-d H:i:59',$etime))];
    }
    if($did = trim(urldecode($arr['device_id']))) $map[$alias.'device_id'] = $did;
    if($arr['has_desc']      != '') $map[$alias.'description'] = $arr['has_desc']    ? ['neq',''] : '';
    if($arr['has_home']      != '') $map[$alias.'home']      = $arr['has_home']      ? ['neq','0'] : '0';
    if($arr['has_job_haunt'] != '') $map[$alias.'job_haunt'] = $arr['has_job_haunt'] ? ['neq','0'] : '0';
    if($arr['has_interest']  != '') $map[$alias.'interest']  = $arr['has_interest']  ? ['neq','0'] : '0';
    if($arr['has_character'] != '') $map[$alias.'character'] = $arr['has_character'] ? ['neq','0'] : '0';
    if($arr['has_all_info']  != '') $map['_string'] .= ($map['_string'] ? ' and ' : '')
      .($arr['has_all_info']
        ? "(description != '' and home != '0' and job_haunt != '0' and interest != '0' and `character` != '0')"
        : "(description = '' and home = '0' and job_haunt = '0' and interest = '0' and `character` = '0')");
    if($arr['has_any_one']   != '') $map['_string'] .= ($map['_string'] ? ' and ' : '')
      .($arr['has_any_one']
        ? "(description != '' or home != '0' or job_haunt != '0' or interest != '0' or `character` != '0')"
        : "(description = '' or home = '0' or job_haunt = '0' or interest = '0' or `character` = '0')");
    if($arr['sex'] != '')//性别筛选
    {
      $sex = (int)$arr['sex'] == 0 ? 0 : 1;
      $map[$alias.'sex'] = $sex;
    }
    if($arr['score_rank'] == 'fail') $map[$alias.'score'] = [/*['egt',0],*/['elt',5.99]];
    if($arr['score_rank'] == 'pass') $map[$alias.'score'] = [['egt',6],['elt',10]];
    if($arr['score_range'] != '')
    {
      $sco = (int)$arr['score_range'];
      if($sco >= 9) $map[$alias.'score'] = ['egt',9];
      elseif($sco === 0)
      {
        $map[$alias.'score'] = [
          ['egt',0],
          ['elt',4.999],
        ];
      }
      else
      {
        $map[$alias.'score'] = [
          ['egt',$sco],
          ['elt',$sco + 0.999],
        ];
      }
    }
    if($prov = trim(urldecode($arr['province'])))//省份筛选
    {
      $_REQUEST['province'] = $_GET['province'] = $prov;
      $whe = [
        'uid'      => ['exp','= '.$alias.'uid'],
        '_complex' => [
          '_logic'   => 'or',
          'province' => ['like',$prov.'%'],
          'city'     => ['like',$prov.'%'],
          'area'     => ['like',$prov.'%'],
        ],
      ];
      $sql = D('LocationBase')->field('uid')->where($whe)->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($ver = trim($arr['app_version']))
    {
      $whe = [
        'uid'         => ['exp','= '.$alias.'uid'],
        'app_version' => $ver,
      ];
      $sql = D('LocationBase')->field('uid')->where($whe)->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($arr['type'] != '')
    {
      $typ = $arr['type'];
      if($typ == -1)
      {
        $sql = D('AccountBase')->field('uid')
          ->where(
          [
            //'uid' => ['exp',' = '.$alias.'uid'],
            'total_expense' => ['egt',0.01],
          ])
          ->buildSql();
        //$map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
        $map[$alias.'uid'] = ['exp','in '.$sql];
      }
      elseif($typ == 'has_video') $map[$alias.'album'] = ['like','%"type":3%'];//有视频
      else $map[$alias.'type'] = (int)$typ;
      if(in_array($typ,[2,3])) $map[$alias.'dblocking_time'] = ['gt',time()];
    }
    if($pub = trim(urldecode($arr['pub_key'])))//活动发布
    {
      $whe = [
        //'uid'     => ['exp',' = '.$alias.'uid'],
        'pub_key' => $pub,
      ];
      $sql = D('Activity')->table('__ACTIVITY_SUB__')->field('uid')->where($whe)->buildSql();
      //$map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
      $map[$alias.'uid'] = ['exp','in '.$sql];
    }
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $field = trim($arr['search_field']);
      $map['_complex'] =
      [
        '_logic' => 'or',
      ];
      if(!$field || $field == 'device')       $map['_complex'][$alias.'device']       = $kwd;
      if(!$field || $field == 'pkg_channel')  $map['_complex'][$alias.'pkg_channel']  = $kwd;
      if(!$field || $field == 'nickname')     $map['_complex'][$alias.'nickname']     = ['like','%'.$kwd.'%'];
      if(!$field || $field == 'description')  $map['_complex'][$alias.'description']  = ['like','%'.$kwd.'%'];
      if(!$field || $field == 'device_model') $map['_complex'][$alias.'device_model'] = ['like','%'.$kwd.'%'];
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'uid')   $map['_complex'][$alias.'uid']   = $kwd;
        if(!$field || $field == 'phone') $map['_complex'][$alias.'phone'] = ['like','%'.$kwd.'%'];
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

  // 根据IDs获取用户信息
  public function get_by_ids($ids = [],$fields = false)
  {
    $dat = [];
    if(is_array($ids) && $ids)
    {
      if($fields) $this->field($fields);
      $dat = $this->klist('uid',['uid' => ['in',array_values($ids)]]) ?: [];
    }
    return $dat;
  }

  // 获取列表中的用户信息
  public function get_by_list($arr = [],$fields = false,$field_pk = 'uid')
  {
    $dat = [];
    if($ids = array_unique(array_column($arr ?: [],$field_pk)) ?: [])
    {
      if($fields) $this->field($fields);
      $dat = $this->lists(['uid' => ['in',$ids]]) ?: [];
      if($dat) $dat = array_combine(array_column($dat,'uid'),$dat);
    }
    return $dat;
  }

  // 根据列表获取关联的用户及账户信息
  public function get_users_account($uls = [],$fields = 'uid')
  {
    $ret = [];
    $ids = [];
    $fds = is_array($fields) ? $fields : preg_split('/\s*,\s*/',$fields);
    foreach($fds ?: [] as $f) $ids = array_merge($ids,array_column($uls ?: [],$f) ?: []);
    if($ids = array_unique($ids))
    {
      $ret = $this->get_users_account_byids($ids);
    }
    return $ret;
  }

  // 根据列表获取关联的用户及账户信息
  public function get_users_account_byids($ids = [])
  {
    $ret = [];
    if($ids)
    {
      $ret = $this->alias('u')
        ->field([
          'u.*','a.balance',
          'a.thumb_income','a.super_like_income','a.gold_like_income',
          'a.vip_level','a.vip_valid_begin','a.vip_valid_end','a.glory_grade',
          'a.total_expense','a.total_like','a.total_super_like','a.total_gold_like',
        ])
        ->join('left join __ACCOUNT_BASE__ a on a.uid = u.uid')
        ->klist('uid',['u.uid' => ['in',$ids]]) ?: [];
    }
    return D('AccountBase')->vip_users($ret);
  }

  // 获取地推用户
  public function get_offline_users($map = [],$fields = 'uid,phone,nickname,sex,reg_time,pkg_channel,device_id')
  {
    $this->where(
    [
      'reg_time'    => ['egt',strtotime(date('Y-m-d',strtotime('-3 days')))],
      //'pkg_channel' => ['like','dt%'],
    ]);
    $map && $this->where($map);
    $fields && $this->field($fields);
    $this->order('reg_time desc,uid desc');
    //echo '<pre>';print_r($this);die;
    return $this->select();
  }

  // 获取设备列表
  public function get_device_list($page_size = 50,$map = [])
  {
    isset($map['device_id']) || $map['device_id'] = [['neq','0'],['neq','']];
    $map && $this->where($map);
    $this->field(
    [
      'count(uid)' => 'cnt',
      'device_id',
    ]);
    $this->group('device_id');
    //$this->having('cnt >= 2');
    $this->plist($page_size);
    $this->order('cnt desc');
    return $this->klist('device_id');
  }

  // 格式化昵称 emoji 替换敏感词
  public function format_nickname_all($arr = array())
  {
    import('Think.Emoji');
    $wds = array();
    if(0)/*已禁用*/ foreach(D('SensitiveWords')->get_multi_items('','word') ?: array() as $v)
    {
      $wds[$v['word']] = '<a style=color:red;font-weight:900;>'.$v['word'].'</a>';
    }
    $arr = array_map(function($v) use($wds)
    {
      //xss
      $v['nickname']       = htmlspecialchars($v['nickname']);
      $v['description']    = htmlspecialchars($v['description']);
      $v['pkg_channel']    = htmlspecialchars($v['pkg_channel']);
      $v['device']         = htmlspecialchars($v['device']);
      $v['device_model']   = htmlspecialchars($v['device_model']);
      $v['device_version'] = htmlspecialchars($v['device_version']);
      $v['device_id']      = htmlspecialchars($v['device_id']);
      $v['nickname_html']  = emoji_unified_to_html(strtr($v['nickname'],$wds ?: array()));
      return $v;
    },$arr ?: array());
    //print_r([$wds,$arr]);die;
    return $arr;
  }

  // 用户打分
  public function scoring($uid = 0,$sco = 0)
  {
    $sco = round($sco,2);
    $sco < 5  && $sco = 0;
    $sco > 10 && $sco = 10;
    $ret = $this->where(['uid' => $uid])->limit(1)->save(['score' => $sco]);
    if($ret)
    {
      $this->del_user_cache($uid);
      $tmp = D('RpcUser')->add_go_list('score',[
        'uid'         => (int)$uid,
        'score'       => $sco,
        'update_time' => time(),
      ]);
      //\Think\Log::write('自动打分:'.implode(':',[$uid,$sco,$ret,$tmp,time(),"\n\n"]));
      if($sco >= 6) $this->recommend_unscored($uid);
      else D('AccountBase')->set_daily_inc($uid,'avatar_fail_tip');//不合格头像每日提醒
      if($old !== false) D('UserScoreRecord')->log($uid,$sco);
    }
    return $ret;
  }

  // 未打分的用户打分后重新推荐
  public function recommend_unscored($uid = 0)
  {
    $ret = false;
    $rds = $this->new_redis('redis_recommend');
    if($rds->exists('negative_score_slide_'.$uid))
    {
      $ret = A($this->name)->http($this->api_root.'feed/my_negative_score',['uid' => $uid]);
      alog(['recommend_unscored',$uid,$ret]);
    }
    return $ret;
  }

  // 警告、封禁用户
  public function closure($uid = 0,$status = 0)
  {
    $ret = array('ret' => 0,'msg' => '操作成功');
    $now = time();
    $uid = (int)$uid;
    if($uid < 1)
    {
      $ret['ret'] = 1;
      $ret['msg'] = 'ID错误';
    }
    elseif(!array_key_exists($status,$this->warning_status))
    {
      $ret['ret'] = 1;
      $ret['msg'] = '封禁状态错误';
    }
    elseif(!$old = $this->find($uid))
    {
      $ret['ret'] = 1;
      $ret['msg'] = '用户不存在';
    }
    // 拒绝受理
    elseif($status == 0);
    // 已处罚不再处罚
    elseif($status == -2);
    // 已做其他处理
    elseif($status == -3);
    // 解除封禁 用户未封禁
    elseif($status == -1)
    {
      $this->where(['uid' => $uid])->limit(1)->save(['type' => 0,'dblocking_time' => $now]);
      $this->warn($uid,0,0);
      $this->del_user_token($uid);
    }
    // 封禁用户 用户已封禁
    elseif(in_array($status,[1,2,3,4]) && in_array($old['type'],[2,3]) && (int)$old['dblocking_time'] > $now)
    {
      $ret['ret'] = 1;
      $ret['msg'] = '用户已被处罚（解封时间：'.date('Y-m-d H:i',$old['dblocking_time']).'）';
    }
    else
    {
      $tim = strtotime($this->warning_status[$status]['days']);
      $typ = $status == 5 ? 2 : 3;
      $dat = array('type' => $typ,'dblocking_time' => $tim);
      if(false === $this->where(['uid' => $uid])->limit(1)->save($dat))
      {
        $ret['ret'] = 1;
        $ret['msg'] = '操作失败';
      }
      else
      {
        $ret['user'] = array_merge($old,$dat);
        $this->del_user_token($uid);
        $this->warn($uid,$status,$tim);
      }
    }
    return $ret;
  }

  // 警告用户
  public function warn($uid = 0,$prog = 0,$time = 0)
  {
    $dat = [
      'uid'         => (int)$uid,
      'prog'        => (int)$prog,
      'dblock_time' => (int)$time,
      'update_time' => time(),
    ];
    return D('RpcUser')->add_go_list('prog',$dat);
  }

  public function get_warning_status()
  {
    $arr = $this->warning_status ?: [];
    return array_combine(array_keys($arr),array_column($arr,'name'));
  }

  // 封禁设备
  public function closure_device($did = '')
  {
    $ret = false;
    if($did) $ret = $this->new_redis('redis_default')->zAdd('php_device_disabled',time(),$did);
    return $ret;
  }

  // 解封设备
  public function unclosure_device($did = '')
  {
    $ret = false;
    if($did) $ret = $this->new_redis('redis_default')->zRem('php_device_disabled',$did);
    return $ret;
  }

  public function get_device_ctime($did = '')
  {
    return $this->new_redis('redis_default')->zScore('php_device_disabled',$did);
  }

  public function get_devices_closured($withScore = true)
  {
    return $this->new_redis('redis_default')->zRange('php_device_disabled',0,-1,$withScore);
  }

  public function get_devices_remark($arr = [])
  {
    $rds = $this->new_redis('redis_default');
    return $arr ? $rds->hMGet('php_device_remark',$arr) : $rds->hGetAll('php_device_remark');
  }

  public function set_devices_remark($dat = [])
  {
    return $this->new_redis('redis_default')->hMSet('php_device_remark',$dat);
  }

  public function del_device_remark($did = '')
  {
    return $this->new_redis('redis_default')->hDel('php_device_remark',$did);
  }

  // 设备白名单
  public function get_devices_whitelist()
  {
    return $this->new_redis('redis_default')->sMembers('php_device_whitelist');
  }

  public function set_device_whitelist($did = '')
  {
    return $this->new_redis('redis_default')->sAdd('php_device_whitelist',$did);
  }

  public function del_device_whitelist($did = '')
  {
    return $this->new_redis('redis_default')->sRem('php_device_whitelist',$did);
  }


  // 获取用户质量统计数据
  public function analy_quality($map = [],$page_size = 20)
  {
    $this->field([
      'from_unixtime(reg_time,\'%Y-%m-%d\')' => 'reg_date',
      'score',
      'count(uid)' => 'count',
    ]);
    if($map) $this->where($map);
    $this->group('reg_date,score');
    $this->plist($page_size,$map);
    $this->order('reg_date desc');
    $arr = $this->select();
    foreach($arr ?: [] as $v)
    {
      $key = $v['reg_date'];
      $field = 'score'.(int)$v['score'];
      $v['score'] == '10' && $field = 'score9';
      $dat[$key] = array_merge($dat[$key] ?: [],[
        'date'        => $v['reg_date'],
        'create_date' => $v['reg_date'],
        'cnt_all'     => (int)$v['count'] + (int)$dat[$key]['cnt_all'],
        $field        => (int)$v['count'] + (int)$dat[$key][$field],
      ]);
    }
    return $dat;
  }

  // 通过API更新用户位置
  public function set_user_location($uid = 0,$dat = array())
  {
    $loc = array_merge(array(
      'uid'   => (int)$uid,
      'sex'   => $dat['sex'],
      'token' => $dat['token'],
      'lat'   => '31.103680',
      'lng'   => '121.515080',
    ),$dat ?: array());
    $jss = A($this->name)->http($this->api_loc,$loc,'POST');
    $arr = json_decode($jss,true) ?: array();
    return $arr;
  }

  // 密码加密
  public function password_encrypt($pwd = '')
  {
    return md5('AJTflQZ8ik7wfIRS_'.$pwd);
  }

  // 获取用户最后活跃时间
  public function get_active_time($uid = 0)
  {
    return $this->get_redis()->zScore('php_active',$uid);
  }

  public function set_active($uid = 0,$tim = null)
  {
    return $this->get_redis()->zAdd('php_active',(int)$tim ?: NOW_TIME,$uid);
  }

  // 获取用户头像地址
  public function get_avatar($uid = 0)
  {
    $src = $_SESSION['users-avatar'][$uid];
    if(!$src)
    {
      $dat = $this->get_user_cache($uid) ?: [];
      is_string($dat['album']) && $dat['album'] = json_decode($dat['album'],true);
      $alb = $dat['album'] ?: [];
      $src = is_array($alb[0]) ? $alb[0]['resource'] : $alb[0];
      $_SESSION['users-avatar'][$uid] = $src;
      //die(json_encode(compact('uid','dat')));
    }
    if($src) $src = $this->avatar_url_root.$src;
    return $src;
  }

  // 获取用户缓存
  public function get_user_cache($uid = 0,$field = null)
  {
    $this->redis || $this->redis = $this->new_redis();
    $ret = $this->redis->get('php_user_'.$uid);
    is_string($ret) && $ret = json_decode($ret,true) ?: [];
    return isset($field) ? $ret[$field] : $ret;
  }

  // 删除用户缓存
  public function del_user_cache($uid = 0)
  {
    $this->redis || $this->redis = $this->new_redis();
    return $this->redis->del('php_user_'.$uid)
        && $this->redis->del('php_account_'.$uid);
  }

  public function get_auth_data($uid = 0)
  {
    return $this->new_redis('redis_default')->hGetAll($uid) ?: [];
  }

  // 获取用户Token
  public function get_user_token($uid = 0)
  {
    return $this->new_redis('redis_default')->hGet($uid,'auth_token');
  }

  // 删除用户Token
  public function del_user_token($uid = 0)
  {
    $ret = $this->new_redis('redis_default')->del($uid);
    alog(['del_user_token',$uid,$ret]);
    return $ret;
  }

}