<?php
namespace Cms\Controller;

class ScoringController extends PublicController
{

  public $ret = array('ret' => 0,'msg' => '','data' => array());

  // Redis配置文件
  public $redis_cfg = 'score_info';//php_server_redis_config

  // 未打分动态列表 sSet
  public $redis_list_key = 'php_score_list';

  // 动态详情 hash 与ID组合
  public $redis_feed_key = 'php_score_feed';

  // 自动打分列表 list
  public $redis_auto_score_list = 'php_feed_auto_score_list';

  // 动态超时时间 秒
  public $redis_feed_timeout = 72;

  public function _initialize()
  {
    // 动态图片根路径
    $this->feed_img_root = D('FeedBase')->feed_img_root;
    $this->feed_timeout  = $this->redis_feed_timeout;

    // 可打分管理员
    $this->scoring_admins = array(5,28,38);
    $this->is_lan = preg_match('/^127\.0+\.0+\.\d+|^192\.168\.\d+\.\d+|^::1/i',$_SERVER['SERVER_ADDR']);
  }


  public function index()
  {
    if(!$this->is_lan && $this->scoring_admins && !in_array((int)$_SESSION['authId'],$this->scoring_admins))
    {
      $this->error('没有权限');
    }
    $this->display();
  }

  public function query()
  {
    $dat = array();
    $dat['list'] = $this->get_datas();
    $dat['time'] = time();
    $dat['time_ymd'] = date('Y-m-d H:i:s');
    $this->ret['data'] = $dat;
    $this->ajaxReturn($this->ret);
  }

  // 打分日志列表
  public function logs()
  {
    $mod = D('ScoreLog');
    $map = $dat = array();
    // 筛选搜索
    if($_REQUEST['type'] != '')
    {
      $map['type'] = (int)$_REQUEST['type'];
    }
    if($_REQUEST['stime'] && $stime = strtotime($_REQUEST['stime']))
    {
      is_array($map['score_time']) || $map['score_time'] = array();
      $map['score_time'][] = array('egt',strtotime(date('Y-m-d',$stime)));
    }
    if($_REQUEST['etime'] && $etime = strtotime($_REQUEST['etime']))
    {
      is_array($map['score_time']) || $map['score_time'] = array();
      $map['score_time'][] = array('elt',strtotime(date('Y-m-d 23:59:59',$etime)));
    }
    if(($group_id = (int)$_REQUEST['group_id']) >= 1)
    {
      $map['a.group_id'] = $group_id;
    }
    if(($range_id = (int)$_REQUEST['range_id']) >= 1)
    {
      $map['a.range_id'] = $range_id;
    }
    if(($uid = (int)$_REQUEST['uid']) >= 1)
    {
      $map['uid'] = $uid;
    }
    $dat['list'] = $mod->plist(100,$map)//C('ITEMS_PER_PAGE')
      ->alias('s')->field(array('s.*','a.resource','a.create_time','a.group_id','a.range_id'))
      ->join('left join '.D('ScoreAssignLog')->getTableName().' a on a.feed_id = s.feed_id')
      ->lists('','s.score_time desc,s.id desc');
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->pager = $mod->pager;
    $dat['score_types'] = D('ScoreBase')->score_types ?: array();
    if($ids = array_unique(array_column($dat['list'],'feed_id')))
    {
      $dat['score_feeds'] = D('FeedBase')->klist('id',array('id' => array('in',$ids)));
    }
    $dat['assign_groups'] = D('UserScoringGroup')->klist();
    $dat['assign_ranges'] = D('UserScoringRange')->klist();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  // 动态分配日志
  public function assign_logs()
  {
    $mod = D('ScoreAssignLog');
    $dat = array();
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist(100,$map)->klist('feed_id','','create_time desc,feed_id desc');
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->pager = $mod->pager;
    $dat['score_types'] = D('ScoreBase')->score_types ?: array();
    $dat['assign_groups'] = D('UserScoringGroup')->klist();
    $dat['assign_ranges'] = D('UserScoringRange')->klist();
    // 获取打分组人员
    if($gid = array_unique(array_column($dat['list'],'group_id')))
    {
      $dat['assign_users'] = D('UserScoring')->klist('uid',array('group_id' => array('in',$gid))) ?: array();
      $dat['score_users']  = D('ScoreLog')->alias('s')
        ->field(array('s.*','a.resource','a.create_time','a.group_id','a.range_id'))
        ->join('left join '.$mod->getTableName().' a on a.feed_id = s.feed_id')
        ->lists(array('a.group_id' => array('in',$gid)),'s.id desc') ?: array();
      foreach($dat['score_users'] as $i => $v)
      {
        isset($dat['list'][$v['feed_id']]) && $dat['list'][$v['feed_id']]['score_log'][$v['uid']] = $v;
      }
    }
    $this->data = $dat;
    //die(json_encode($dat));
    if($_REQUEST['download'] == 1)
    {
      layout(false);
      $this->display(ACTION_NAME.'.csv','utf-8','application/vnd.ms-excel');
      die;
    }
    $this->display();
  }


  // 获取Redis中未打分动态数据
  protected function get_datas()
  {
    if(!C('REDIS_START')) return false;
    $rds = $this->rds ?: $this->redis();
    $arr = $this->get_score_list();

    // 测试数据
    if($this->redis_list_key == 'php_score_list_test' && 1)
    {
      $arr = $this->test_datas($rds);
    }

    $lst = array();
    foreach($arr ?: array() as $fid => $gid)
    {
      $key = $this->redis_feed_key.$fid;
      $row = $this->get_score_feed($fid);

      // 动态已过期 漏单
      if(!$row)
      {
        $rds->zRem($this->redis_list_key,$fid);
        continue;
      }
      $row['group_id'] = $gid;

      // 以取到动态的开始时间为过期起始时间
      if(!isset($_SESSION['feeds'][$fid]['fetch_time'])) $_SESSION['feeds'][$fid]['fetch_time'] = time();
      $row['fetch_time'] = $_SESSION['feeds'][$fid]['fetch_time'];
      //$row['timeout'] = time() - (int)$row['fetch_time'];

      // 以Redis过期时间为起始时间
      $ttl = (int)$rds->ttl($key);
      $ttl < 0 && $ttl = $this->redis_feed_timeout;

      $row['timeout'] = $this->redis_feed_timeout - $ttl;
      if($row['timeout'] > $this->redis_feed_timeout + 6000)
      {
        $this->del_run($fid);
      }
      else $lst[$fid] = $row;
    }
    //die(json_encode(array('data' => $lst)));
    return $lst;
  }

  // 打分队列
  protected function get_score_list()
  {
    $rds = $this->rds ?: $this->redis();
    return $rds->zRange($this->redis_list_key,0,-1,true);
  }

  // 动态详情
  protected function get_score_feed($fid = 0)
  {
    $rds = $this->rds ?: $this->redis();
    $key = $this->redis_feed_key.$fid;
    $str = $rds->hGet($key,$fid) ?: '';
    $row = unserialize($str) ?: array();
    return $row;
  }

  // 生成测试数据
  protected function test_datas($rds = false)
  {
    is_object($rds) || $rds = $this->rds ?: $this->redis();
    $fid = rand(100000,999999);
    $siz = array(100,120,150,180,200,220,240,260,280,300);
    $dat = array(
      'id'          => $fid,
      'uid'         => rand(1000,9999),
      'resource'    => '//lorempixel.com/'.$siz[array_rand($siz)].'/'.$siz[array_rand($siz)].'/',
      'create_time' => time() - rand(0,30),
    );
    if(rand(1,10) <= 1)
    {
      $rds->zAdd($this->redis_list_key,0,$fid);//列表
      $rds->hSet($this->redis_feed_key.$fid,$fid,serialize($dat));//详情
      $rds->expire($this->redis_feed_key.$fid,$this->redis_feed_timeout);//设置过期
    }
    $arr = $this->get_score_list();
    return $arr;
  }

  // 保存打分结果
  public function save()
  {
    $fid = $id = (int)$_REQUEST['id'] ?: (int)$_REQUEST['fid'];
    $sco = round((float)$_REQUEST['score'],2);
    $sco < 0  && ($sco = 0);
    $sco > 10 && ($sco = 10);
    if($fid < 1)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = 'ID错误！';
    }
    else
    {
      $rds = $this->rds ?: $this->redis();
      $this->ret = array_merge($this->ret,D('FeedBase')->scoring($fid,$sco) ?: array());//保存分数
      $this->del_run($fid);//删除Redis队列
      $feed = $this->ret['feed'] ?: array();
      $uid = (int)$feed['uid'];
      if($uid)
      {
        $this->scoring_msg($uid,$feed);//打分系统消息
        $rds->rPush($this->redis_auto_score_list,$fid);//自动打分队列
      }
      // 记录打分日志
      $ttl = (int)$rds->ttl($this->redis_feed_key.$fid);
      //$gid = (int)$rds->zScore($this->redis_list_key,$fid);
      $log = array(
        'feed_id' => $fid,
        'score'   => $sco,
        'timeout' => $this->redis_feed_timeout - $ttl,
      );
      if(D('ScoreLog')->scoring_log($log) === false)
      {
        $this->ret['msg'] = '添加打分Log失败';
      }
      else
      {
        $this->ret['msg'] = '打分成功';
      }
    }
    $this->ajaxReturn($this->ret);
  }

  // 打分系统消息
  public function scoring_msg($uid,$feed = array())
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
    return A('Message')->scoreFeedMessage($msgf['uid'],$msgf);
  }

  // 删除操作
  public function del()
  {
    $id = (int)$_REQUEST['id'];
    $this->del_run($id);
    $this->ajaxReturn($this->ret);
  }

  // 删除记录
  protected function del_run($id = 0)
  {
    $rds = $this->rds ?: $this->redis();
    if($id < 1)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = 'ID错误！';
    }
    elseif(!$rds->zScore($this->redis_list_key,$id))
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '已被删除！';
    }
    elseif(!$rds->zRem($this->redis_list_key,$id))
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '删除失败！';
    }
    else
    {
      unset($_SESSION['feeds'][$fid]);
      $this->ret['msg'] = '删除成功！';
    }
    return $this->ret;
  }

  protected function redis($cfg = '')
  {
    $cfg || $cfg = $this->redis_cfg;
    $cfg && is_string($cfg) && $cfg = C($cfg);
    $rds = new \Think\Cache\Driver\Redis($cfg);
    $this->rds = $rds;
    return $rds;
  }

}