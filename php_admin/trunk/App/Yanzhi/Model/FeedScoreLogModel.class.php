<?php
namespace Yanzhi\Model;

class FeedScoreLogModel extends CjAdminLogModel
{

  public $feed_scoring_times    = 3;//需要几个人打分后计算最终得分
  public $feed_scoring_timeout  = 1800;//打分超时时间 秒
  public $redis_scoring_assign  = 'php_scoring_assign_list';//待分配列表
  public $redis_scoring_feed    = 'php_scoring_feed';//PC打分记录
  public $redis_scoring_special = 'php_scoring_special';//需要特殊处理的打分队列

  // 照片不合格 score >= key
  public $score_rank_fail = array(
    '0.11' => array(
      'reason' => '涉嫌色情或低俗',
      'msg'    => '我们向那种色色的图片说NO！',
    ),
    '0.12' => array(
      'reason' => '非人像',
      'msg'    => '打分无限循环中…敢不敢换张人像试试看？',
    ),
    '0.13' => array(
      'reason' => '模糊不清/拉伸变形/照片倒置',
      'msg'    => '模糊和拉伸或颠倒都会影响你的颜值哦~',
    ),
    '0.14' => array(
      'reason' => '小孩（儿时）',
      'msg'    => '小朋友玩社交是不是太早了？',
    ),
    '0.15' => array(
      'reason' => '性别不符',
      'msg'    => '男是男，女是女，瞎发照片可不行！',
    ),
    '0.16' => array(
      'reason' => '无法辨识（五官）',
      'msg'    => '清楚的五官才能辨识颜值哦~换张美美的照片吧！',
    ),
    '0.17' => array(
      'reason' => '明显盗图',
      'msg'    => '盗用他人照片是违法的哟~',
    ),
    '0.18' => array(
      'reason' => '拍实体照片/拍屏幕/手机截屏',
      'msg'    => '也许你颜值很高，但可不可以不是截屏或对着照片拍照呢？',
    ),
    '0.19' => array(
      'reason' => '第三方水印/联系方式或广告',
      'msg'    => '什么水印啦~联系方式啦~广告啦~万万不可有哦~',
    ),
    // 其他始终为最后一个
    '0.20' => array(
      'reason' => '其他',
      'msg'    => '',
      'hidden' => true,
    ),
  );

  // 低颜值
  public $score_rank_pass = array(
    '5' => array(
      'reason' => '极其丑',
      'msg'    => '哼！我知道你有更好的自拍，别藏了！',
    ),
    '6' => array(
      'reason' => '很丑',
      'msg'    => '别偷懒啦，PP图~拍摄技术再好一点点，就完美咯！',
    ),
    '7' => array(
      'reason' => '丑',
      'msg'    => '还差一丢丢，上8分~有可能会被经纪人评价哦~',
    ),
  );

  // 高颜值 女
  public $score_rank_fine1 = array(
    '8.0' => array('msg' => '小荷才露尖尖角，再来几张行不行。'),
    '8.2' => array('msg' => '我们文化人，夸人就俩字，好看！'),
    '8.4' => array('msg' => '纯天然活力满满小清新妹砸'),
    '8.6' => array('msg' => '炫酷狂拽美炸天，美的不要不要的'),
    '8.8' => array('msg' => '美到让男神吴彦祖都倾心不已'),
    '9.0' => array('msg' => '太美腻了，求女神狠狠鞭挞'),
    '9.5' => array('msg' => '百万伏特的放电女神。'),
    '9.9' => array('msg' => '颜值爆表，由你来拯救世界。'),
    '10'  => array('msg' => '鲜肉大叔通杀的仙女姐姐。'),
  );

  // 高颜值 男
  public $score_rank_fine0 = array(
    '8.0' => array('msg' => '帅到隔壁老王都自愧不如。'),
    '8.5' => array('msg' => '来自异世界的鲜嫩多汁小肥羊'),
    '9.0' => array('msg' => '荷尔蒙爆棚的聒噪帅哥'),
    '9.5' => array('msg' => '简直帅到令人血脉喷张根本停不下来'),
    '10'  => array('msg' => '少妇萝莉御姐全面通杀。'),
  );


  public function __construct()
  {
    parent::__construct();
    $this->uid = (int)$_SESSION[C('USER_AUTH_KEY')];//打分人UID
  }


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = array())
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: array();
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = array();
    if($arr['feed_id'] != '') $map[$alias.'feed_id'] = (int)$arr['feed_id'];
    if($arr['uid'] != '')     $map[$alias.'uid']     = (int)$arr['uid'];
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'assign_time']) || $map[$alias.'assign_time'] = array();
      $map[$alias.'assign_time'][] = array('egt',strtotime(date('Y-m-d',$stime)));
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'assign_time']) || $map[$alias.'assign_time'] = array();
      $map[$alias.'assign_time'][] = array('elt',strtotime(date('Y-m-d 23:59:59',$etime)));
    }
    if($kwd = trim($arr['kwd']))
    {
      $map['_complex'] = array(
          '_logic' => 'or',
          $alias.'uid' => array('like','%'.$kwd.'%'),
      );
    }
    return $map;
  }

  // 根据 性别 及 分数 获取分数级别
  public function get_score_rank($sex,$score = 0)
  {
    $rnk = array();
    $score = (float)$score;
    if(!$rnk) foreach((int)$sex === 1 ? $this->score_rank_fine1 : $this->score_rank_fine0 as $sco => $v)
    {
      if($score >= (float)$sco)
      {
        $rnk['type'] = 'fine';
        $rnk['rank'] = $v;
      }
    }
    if(!$rnk) foreach($this->score_rank_pass ?: array() as $sco => $v)
    {
      if($score >= (float)$sco)
      {
        $rnk['type'] = 'pass';
        $rnk['rank'] = $v;
      }
    }
    if(!$rnk) foreach($this->score_rank_fail ?: array() as $sco => $v)
    {
      if($score >= (float)$sco)
      {
        $rnk['type'] = 'fail';
        $rnk['rank'] = $v;
      }
    }
    return $rnk;
  }

  // 自动分配
  public function assign_score_list($usr = array())
  {
    $ids = array();
    if(!$usr) return $ids;
    $this->redis || $this->new_redis();
    for($i = 0;$i < 2;$i++)
    {
      $fid = (int)$this->redis->lPop($this->redis_scoring_assign);
      if($fid)
      {
        $ids[] = $fid;
        $this->assign_user_scoring($fid,$usr);//分配
      }
    }
    return $ids;
  }

  // 分配动态到打分团
  public function assign_user_scoring($fid = 0,$usr = array())
  {
    if($fid < 1) return false;
    $dat = array();
    $old = $this->klist('uid',array('feed_id' => $fid)) ?: array();
    foreach($usr ?: array() as $i => $v)
    {
      $uid = is_array($v) ? (int)$v['uid'] : (int)$v;
      if(!array_key_exists($uid,$old))
      {
        $dat[] = array(
          'feed_id' => $fid,
          'uid'     => $uid,
          'assign_time' => time(),
        );
      }
    }
    if($dat) $this->addAll($dat);
    return $dat;
  }

  // 获取打分队列
  public function get_scoring_list($top = 10,$uid = 0)
  {
    $uid || $uid = $this->uid;
    $map = array(
      'uid'         => $uid,
      'score'       => -1,
      'assign_time' => array('egt',strtotime('-10 days')),
    );
    $arr = $this->order(array('id' => 'desc'))->limit($top * 10)
      ->klist('feed_id',$map) ?: array();
    $fls = array();
    if($ids = array_keys($arr))
    {
      $fls = D('FeedBase')->alias('f')->field(array('f.*','u.sex','u.reg_time'))->limit($top)
        ->join('left join __USER_BASE__ u on u.uid = f.uid')
        ->where(array('f.id' => array('in',$ids),'f.finished' => 0))->klist() ?: array();
    }
    //die(json_encode(array('data' => $fls,$ids,$arr)));
    return $fls;
  }

  // 获取平均分
  /*
    feed => [ //动态信息
      id,
      uid,
      resource,
      create_time,
      ...
      sex,
      scoring => [ // 打分信息
        fail => [...],
        pass => [...],
        fine => [  // 打分记录
          1001 => [...],
          1002 => [ // 打分详情
            score,
            score_time,
            type,
            rank => [ // 分数级别
              reason,
              msg,
            ],
          ],
        ],
      ],
    ]
   */
  public function get_score_avg($fid = 0,$sco = 0)
  {
    $this->redis || $this->new_redis();
    $avg = array();
    $sco = (float)$sco;
    $fed = $this->get_feed_score_cahce($fid);
    if(!$fed) return $avg;
    // 统计各个级别的打分次数
    $tms = $this->get_rank_times($fid,$fed);
    $len = array_sum($tms) ?: 0;
    if($len < $this->feed_scoring_times)
    {
      $arr = array(
        'feed_id'    => $fid,
        'uid'        => $this->uid,
        'score'      => $sco,
        'score_time' => time(),
      );
      $rnk = $this->get_score_rank($fed['sex'],$sco) ?: array();
      $arr += $rnk;
      if($arr['type'] && $this->uid)
      {
        $fed['scoring'][$arr['type']][$this->uid] = $arr;
        $len++;
        $tms[$arr['type']]++;
        $this->set_feed_score_cahce($fid,$fed);
        $this->set_score_log($fid,$sco,$rnk);//记录打分Log
      }
    }
    $avg['feed'] = $fed;
    $avg['scoring_count'] = array_sum($tms) ?: 0;
    if($len >= $this->feed_scoring_times)
    {
      $avg = array_merge($avg,$this->get_feed_score_avg($fid,$fed,$tms));
    }
    return $avg;
  }

  // 获取平均分 不限打分次数
  public function get_feed_score_avg($fid = 0,$fed = array(),$tms = array())
  {
    $fid || $fid = (int)$fed['id'] ?: (int)$fed['feed_id'];
    $fed || $fed = $this->get_feed_score_cahce($fid);
    $tms || $tms = $this->get_rank_times($fid,$fed);
    $avg = array();
    if($fid && $tms)
    {
      isset($avg['feed']) || $avg['feed'] = $fed;
      isset($avg['scoring_count']) || $avg['scoring_count'] = array_sum($tms) ?: 0;
      // 高颜值 & 低颜值
      if($tms['fine'] + $tms['pass'] > $tms['fail'])
      {
        $arr = array_merge($fed['scoring']['fine'],$fed['scoring']['pass']);
        $arr = array_column($arr,'score') ?: array();
        $avg['score'] = array_sum($arr) / count($arr);
        $avg['rank'] = $this->get_score_rank($fed['sex'],$avg['score']) ?: array();
      }
      // 不合格
      elseif($tms['fail'] > $tms['pass'] + $tms['fine'])
      {
        $tps = array();
        foreach($fed['scoring']['fail'] as $uid => $v)
        {
          $sco = $v['score'];
          isset($tps[$sco]) || $tps[$sco] = 0;
          $tps[$sco]++;
        }
        $max = -1;
        $max_key = '';
        foreach($tps as $key => $cnt)
        {
          if($cnt >= $max)
          {
            $max = $cnt;
            $max_key = $key;
          }
        }
        if($max >= 2 && $max_key)
        {
          $avg['score'] = 0;
          $avg['rank'] = $this->score_rank_fail[(string)$max_key] ?: array();
        }
        // 不合格 且 意见不统一
        else
        {
          $this->set_scoring_special($fid);
        }
      }
      // 意见不统一
      else
      {
        $this->set_scoring_special($fid);
      }
    }
    if($avg['score']) $avg['score'] = round((float)$avg['score'],2);
    return $avg;
  }

  // 获取平均分 不同级别视为意见不统一
  public function get_feed_score_avg_special3($fid = 0,$fed = array(),$tms = array())
  {
    $fid || $fid = (int)$fed['id'] ?: (int)$fed['feed_id'];
    $fed || $fed = $this->get_feed_score_cahce($fid);
    $tms || $tms = $this->get_rank_times($fid,$fed);
    $avg = array();
    if($fid && $tms)
    {
      isset($avg['feed']) || $avg['feed'] = $fed;
      isset($avg['scoring_count']) || $avg['scoring_count'] = array_sum($tms) ?: 0;
      // 高颜值
      if($tms['fine'] > $tms['fail'] && $tms['fine'] > $tms['pass'])
      {
        $arr = array_column($fed['scoring']['fine'],'score') ?: array();
        $avg['score'] = array_sum($arr) / count($arr);
        $avg['rank'] = $this->get_score_rank($fed['sex'],$avg['score']) ?: array();
      }
      // 低颜值
      elseif($tms['pass'] > $tms['fine'] && $tms['pass'] > $tms['fail'])
      {
        $arr = array_column($fed['scoring']['pass'],'score') ?: array();
        $avg['score'] = array_sum($arr) / count($arr);
        $avg['rank'] = $this->get_score_rank($fed['sex'],$avg['score']) ?: array();
      }
      // 不合格
      elseif($tms['fail'] > $tms['pass'] && $tms['fail'] > $tms['fine'])
      {
        $tps = array();
        foreach($fed['scoring']['fail'] as $uid => $v)
        {
          $sco = $v['score'];
          isset($tps[$sco]) || $tps[$sco] = 0;
          $tps[$sco]++;
        }
        $max = -1;
        $max_key = '';
        foreach($tps as $key => $cnt)
        {
          if($cnt >= $max)
          {
            $max = $cnt;
            $max_key = $key;
          }
        }
        if($max >= 2 && $max_key)
        {
          $avg['score'] = 0;
          $avg['rank'] = $this->score_rank_fail[(string)$max_key] ?: array();
        }
        // 不合格 且 意见不统一
        else
        {
          $this->set_scoring_special($fed['id']);
        }
      }
      // 意见不统一
      else
      {
        $this->set_scoring_special($fed['id']);
      }
    }
    if($avg['score']) $avg['score'] = round((float)$avg['score'],2);
    return $avg;
  }

  // 超时自动计算平均分
  public function get_score_timeout($fid = 0,$ctime = 0)
  {
    $fct = (int)$ctime;
    if($fid && $fct && time() - $fct > $this->feed_scoring_timeout)
    {
      $avg = $this->get_feed_score_avg($fid);
      // 超时并至少两个人打分
      if(isset($avg['score']) && $avg['score'] >= 0 && (int)$avg['scoring_count'] >= 2)
      {
        return $avg;
      }
    }
    return array();
  }

  // 统计各个级别的打分次数
  public function get_rank_times($fid = 0,&$fed = array())
  {
    $fed || $fed = $this->get_feed_score_cahce($fid);
    isset($fed['scoring']['fail']) || $fed['scoring']['fail'] = array();
    isset($fed['scoring']['pass']) || $fed['scoring']['pass'] = array();
    isset($fed['scoring']['fine']) || $fed['scoring']['fine'] = array();
    // 统计各个级别的打分次数
    $tms = array(
      'fail' => count($fed['scoring']['fail']),
      'pass' => count($fed['scoring']['pass']),
      'fine' => count($fed['scoring']['fine']),
    );
    return $tms;
  }

  // 记录打分Log
  public function set_score_log($fid = 0,$sco = 0,$dat = '')
  {
    $sco = round((float)$sco,2);
    $map = array(
      'feed_id' => $fid,
      'uid'     => $this->uid,
      'score'   => array('elt',-1),
    );
    $dat = array(
      'score'      => $sco,
      'score_time' => time(),
      'score_data' => is_string($dat) ? $dat : (json_encode($dat) ?: ''),
    );
    return $this->where($map)->limit(1)->save($dat);
  }

  // 打分系统消息
  public function scoring_msg($uid,$feed = array(),$rank = array())
  {
    if(!$feed) return false;
    $msgf = array(
      'uid'         => $uid,
      'feed_id'     => (int)$feed['id'],
      'create_time' => (int)$feed['create_time'],
      'resource'    => $this->feed_img_root.$feed['resource'],
      'text'        => $feed['text'],
      'score'       => round($feed['score'],1),
      'score_cnt'   => (int)$feed['score_cnt'],
    );
    if($rank)
    {
      $msgf['score_type']   = $rank['type'];
      $msgf['score_reason'] = $rank['reason'] ?: $rank['rank']['reason'] ?: '';
      $msgf['score_msg']    = $rank['msg']    ?: $rank['rank']['msg']    ?: '';
    }
    return A('Message')->scoreFeedMessage($msgf['uid'],$msgf);
  }

  public function get_feed_score_cahce($fid = 0)
  {
    $this->redis || $this->new_redis();
    is_array($fid) && $fid = (int)$fid['id'] ?: (int)$fid['feed_id'];
    $jss = $this->redis->get($this->redis_scoring_feed.$fid);
    $fed = is_array($jss) ? $jss : (json_decode($jss,true) ?: array());
    return $fed;
  }

  public function set_feed_score_cahce($fid = 0,$dat = array())
  {
    if(is_array($fid))
    {
      $dat || $dat = $fid;
      $fid = (int)$fid['id'] ?: (int)$fid['feed_id'];
    }
    if(!$dat) return false;
    $this->redis || $this->new_redis();
    is_array($dat) && $dat = json_encode($dat) ?: '';
    return $this->redis->set($this->redis_scoring_feed.$fid,$dat);
  }

  public function del_feed_score_cahce($fid = 0)
  {
    $this->redis || $this->new_redis();
    is_array($fid) && $fid = (int)$fid['id'] ?: (int)$fid['feed_id'];
    return $this->redis->del($this->redis_scoring_feed.$fid);
  }

  public function get_scoring_special($top = -1)
  {
    $this->redis || $this->new_redis();
    $top = (int)$top;
    $arr = $this->redis->zRange($this->redis_scoring_special,0,$top,true) ?: array();
    $fls = array();
    foreach($arr as $fid => $time)
    {
      $fed = $this->get_feed_score_cahce($fid);
      if(!$fed)
      {
        $this->del_scoring_special($fid);
        continue;
      }
      $fed['timeout'] = time() - (int)$fed['create_time'];
      $fls[$fid] = $fed;
    }
    return $fls;
  }

  // 设置特殊处理列表
  public function set_scoring_special($fid = 0)
  {
    $this->redis || $this->new_redis();
    is_array($fid) && $fid = (int)$fid['id'] ?: (int)$fid['feed_id'];
    return $this->redis->zAdd($this->redis_scoring_special,time(),$fid);
  }

  public function del_scoring_special($fid = 0)
  {
    $this->redis || $this->new_redis();
    is_array($fid) && $fid = (int)$fid['id'] ?: (int)$fid['feed_id'];
    return $this->redis->zRem($this->redis_scoring_special,$fid);
  }

  public function new_redis($cfg = '')
  {
    $this->redis = D('PhpServerRedis')->new_redis($cfg);
    return $this->redis;
  }

}