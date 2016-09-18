<?php
namespace Liehuo\Controller;
use Liehuo\Model;

class ScoringController extends PublicController
{

  public $ret = array('ret' => 0,'msg' => '','data' => []);

  // Redis配置文件
  public $redis_cfg = 'redis_user';

  // 未打分动态列表 zSet
  public $redis_list_key = 'php_avatar_scoring';

  // 正在打分的队列
  public $redis_init_key = 'php_zset_scoring';

  // 打分队列分配
  public $redis_assign_key   = 'php_scoring_assign';
  public $scoring_assign_max = 10;

  // 动态超时时间 秒
  public $redis_feed_timeout = 999999999;

  public function _initialize()
  {
    // 图片根路径
    $this->feed_img_root = D('UserBase')->avatar_url_root;
    $this->feed_timeout  = $this->redis_feed_timeout;
    $this->feed_timeout_highlight = 2880 * 5;
    // 不合格
    $this->score_rank_fail = D('Avatar')->score_rank_fail ?: [];

    // 可打分管理员
    $this->scoring_admins = array();
    $this->is_lan = preg_match('/^127\.0+\.0+\.\d+|^192\.168\.\d+\.\d+|^::1/i',$_SERVER['SERVER_ADDR']);

    // 服务器维护
    if(NOW_TIME >= 1456336800 && NOW_TIME <= 1456351200 && 1)
    {
      die('由于服务器升级，打分暂停！预计早上5-6点恢复！');
    }
  }


  public function index()
  {
    $tpl = '';
    if(!$this->is_lan && $this->scoring_admins && !in_array((int)$_SESSION['authId'],$this->scoring_admins))
    {
      $this->error('没有权限');
    }
    if(D('Auth')->check(CONTROLLER_NAME.'/open') || $_REQUEST['type'] == 'open')
    {
      $tpl = 'open';
      layout(false);
      $this->is_open = true;
    }
    $rds = $this->rds ?: $this->redis();
    $this->scoring_count = $rds->zSize($this->redis_list_key);
    isset($_SESSION['scoring_assign_stop']) || session('scoring_assign_stop',1);//默认不接受新分配
    $this->assign_stop = session('scoring_assign_stop');
    //if($this->scoring_count < 200) D('Avatar')->import_avatar_miao(100);//Miao用户重新打分
    //if($this->scoring_count <   2) D('Avatar')->import_scoring_miao_lost();//Miao用户漏打分
    $this->display($tpl);
  }

  public function query()
  {
    $dat = array();
    $dat['list']  = $this->get_datas();
    $dat['count'] = $this->scoring_count;
    $dat['total'] = $this->scoring_total;
    $dat['time']  = time();
    $dat['time_ymd'] = date('Y-m-d H:i:s');
    $this->ret['data'] = $dat;
    $this->ajaxReturn($this->ret);
  }


  // 获取未打分图片数据
  protected function get_datas()
  {
    $ava = D('Avatar');
    $dat = [];
    $rds = $this->rds ?: $this->redis();
    $lst = $this->get_score_list() ?: [];
    $rls = $ava->list_scored($lst) ?: [];
    $now = time();
    $usr = D('UserBase');
    $uls = [];
    foreach($lst as $k => $v)
    {
      $uid = $v['uid'];
      $uls[$uid] = $uid;
      if(isset($rls[$v['resource']]) && $uid == $rls[$v['resource']]['uid'])
      {
        $sco = round($rls[$v['resource']]['score'],2);
        $rnk = $ava->get_score_rank($sco);
        $usr->scoring($uid,$sco);//已被打分 自动打分
        D('OperLog')->log('scoring',
        [
          '自动打分',
          '分值' => $sco,
          '理由' => $rnk['reason'],
          '话术' => $rnk['msg'],
          '照片' => $v['resource'],
        ],$uid);
        $this->del_run($uid);
        if($rnk['msg'])
        {
          D('Message')->add_msg_scoring($uid,
          [
            'comment'  => $rnk['msg'],
            'show_tip' => $sco < 6 ? 1 : 0,
          ],$this->feed_img_root.$v['resource']);
          //D('Message')->add_msg_system($uid,$rnk['msg']);//发送系统消息
        }
        continue;
      }
      $dat[$uid] = $v;
    }
    $dat = $this->assign_scoring($dat);
    $dat = array_map(function($v) use($rds,$now)
    {
      $uid = $v['uid'];
      // 首次加载到打分页面的时间
      $stm = (int)$rds->zScore($this->redis_init_key,$uid);
      if($stm) $v['timeout'] = $now - $stm;
      else
      {
        $rds->zAdd($this->redis_init_key,$now,$uid);
        $v['timeout'] = 0;
      }
      return $v;
    },$dat ?: []);
    if($uls && 0)
    {
      $hls = $ava->where(['uid' => ['in',array_keys($uls)],'score_time' => ['egt',86400]])->limit(30)->order('score_time desc')->select() ?: [];
      foreach($hls ?: [] as $v)
      {
        $uid = (int)$v['uid'];
        if(!$dat[$uid]) continue;
        if(count($dat[$uid]['score_history']) > 3) continue;
        $dat[$uid]['score_history'][] = $v['score'] >= 6 ? $v['score'] : '不合格';
      }
    }
    //die(json_encode(array('data' => $dat)));
    return $dat;
  }

  // 打分队列
  protected function get_score_list()
  {
    $dat = [];
    $rds = $this->rds ?: $this->redis();
    $aid = (int)$_SESSION[C('USER_AUTH_KEY')];
    $this->scoring_total = $rds->zSize($this->redis_list_key);
    $this->scoring_count = $rds->zCount($this->redis_assign_key,$aid,$aid);
    $arr = $rds->zRange($this->redis_list_key,0,100,true) ?: [];
    $ids = array_keys($arr);
    if($ids)
    {
      $uls = D('UserBase')
        ->field('uid,nickname,phone,sex,album,score,birthday,description,type,power,reg_time,dblocking_time')
        ->klist('uid',['uid' => ['in',$ids]]) ?: [];
      $hls = D('LiveHost')->get_by_list($uls) ?: [];
      $cts = D('LiveContractType')->get_all() ?: [];
      foreach($uls as $uid => $row)
      {
        $album = json_decode($row['album'],true) ?: [];
        if($album[0])
        {
          $res = is_array($album[0]) ? $album[0]['resource'] : $album[0];
          $row['id']          = $uid;
          $row['resource']    = oss_img_srv($res ?: '','640w_80Q_1pr.jpg');
          $row['create_time'] = $arr[$uid] ?: $row['reg_time'];
          $row['timeout']     = 0;//time() - (int)$row['create_time'];
          $row['contract_name'] = $cts[$hls[$uid]['contract_type']]['attrs']['name'];
          $row['remark']      = Model\UserAttrsModel::Instance($uid)->get_attr('remark_scoring');
          unset($row['password']);
          if(!isset($_SESSION['scoring_assign_sex']) || $_SESSION['scoring_assign_sex'] == $row['sex'])
          {
            $dat[$uid] = $row;
          }
          else $rds->zRem($this->redis_assign_key,$uid);
        }
        else $this->del_run($uid);//删除Redis队列
      }
      $nos = array_diff($ids ?: [],array_keys($uls ?: [])) ?: [];
      foreach($nos as $uid) $this->del_run($uid);
    }
    return $dat;
  }

  // 保存打分结果
  public function save()
  {
    $uid = $id = (int)$_REQUEST['id'] ?: (int)$_REQUEST['uid'];
    $res = trim(I('request.resource'));
    $res = oss_img_srv($res);
    //rlog([date('H:i:s'),$uid,$res,I('request.resource')],'scoring_save',86400);
    $sco = round($_REQUEST['score'],2);
    $sco < 0  && $sco = 0;
    $sco > 10 && $sco = 10;
    $ava = D('Avatar');
    $rnk = $ava->get_score_rank($sco);
    $rds = $this->rds ?: $this->redis();
    if($uid < 1)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = 'UID错误！';
    }
    elseif(!$rds->zScore($this->redis_list_key,$uid))
    {
      //$this->ret['ret'] = 1;
      $this->ret['msg'] = '已被打分';
    }
    elseif(!$usr = D('UserBase')->where(['uid' => $uid])->find())
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '用户不存在';
    }
    //保存分数
    elseif(false === D('UserBase')->scoring($uid,$sco))
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '打分失败';
    }
    else
    {
      $his = $ava->field(
      [
        'uid',
        'max(score)' => 'max',
        'min(score)' => 'min',
      ])->where(
      [
        'uid'        => $uid,
        'score_time' => ['egt',1],
      ])
      ->select() ?: [];//获取用户历史最高、最低分数
      $ava->where(['uid' => $uid,'resource' => $res])->limit(1)->save(['score' => $sco,'score_time' => time()]);
      // 第一次被打6分以上
      if($sco >= 6 && (int)$his['max'] < 6 && (time() - (int)$usr['reg_time'] < 60 * 60 * 24 * 2))
      {
        // 首次打分强制曝光
        D('RpcUser')->add_go_list('force',
        [
          'uid'        => (int)$uid,
          'gender'     => (bool)$usr['sex'],
          'force_type' => 0,
          'num'        => 0,
        ]);
        0 && rlog(
        [
          'uid'        => (int)$uid,
          'gender'     => (bool)$usr['sex'],
          'force_type' => 0,
          'num'        => 0,
          'score'      => $sco,
          'history'    => $his,
        ],'first_pass_force');
      }
      // 第一次不合格
      elseif($sco < 6 && ((int)$his['min'] == 0 || (int)$his['min'] >= 6))
      {
        $rid = D('UserBase')->get_redis()->hGet('robot_sex_list',1 - (int)$usr['sex']);
        if($rid) D('RpcApi')->call('Recommend/forceLike',
        [
          'uid' => $uid,
          'oid' => $rid,
        ]);
        0 && rlog(
        [
          'uid'     => (int)$uid,
          'gender'  => (bool)$usr['sex'],
          'score'   => $sco,
          'rid'     => $rid,
          'history' => $his,
        ],'first_fail_force');
      }
      D('OperLog')->log('scoring',
      [
        '分值' => $sco,
        '理由' => $rnk['reason'],
        '话术' => $rnk['msg'],
        '照片' => $res,
      ],$uid);
      if(D('Auth')->check(CONTROLLER_NAME.'/open'))
      {
        // 打分团日志
        D('ScoreLog')->add_log(
        [
          'uid'      => $uid,
          'resource' => $res,
          'score'    => $sco,
          'timeout'  => (int)$_REQUEST['timeout'],
          'remain'   => (int)$rds->zSize($this->redis_list_key),
        ]);
      }
      $sex = (int)D('UserBase')->where(['uid' => $uid])->getField('sex');
      D('DailyCount')->set_scoring($sco,$sex);
      $this->del_run($uid);//删除Redis队列
      if($rnk['msg'])
      {
        // 发送系统消息
        D('Message')->add_msg_scoring($uid,
        [
          'comment'  => $rnk['msg'],
          'show_tip' => $sco < 6 ? 1 : 0,
        ],$this->feed_img_root.$res);
        //D('Message')->add_msg_system($uid,$rnk['msg']);
      }
    }
    $this->ajaxReturn($this->ret);
  }

  // 删除操作
  public function del()
  {
    $id = (int)$_REQUEST['id'];
    $this->del_run($id);
    $this->ajaxReturn($this->ret);
  }

  // 停止接收分配
  public function stop_assign()
  {
    $stop = (int)$_REQUEST['stop'];
    session('scoring_assign_stop',$stop);
    $this->ajaxReturn($this->ret);
  }

  // 按性别分配
  public function sex_assign()
  {
    $sex = (int)$_REQUEST['sex'] == 0 ? 0 : 1;
    if(trim($_REQUEST['sex']) == '') session('scoring_assign_sex',null);
    else session('scoring_assign_sex',$sex);
    $this->ajaxReturn($this->ret);
  }

  // 结束打分
  public function over()
  {
    $aid = (int)$_SESSION[C('USER_AUTH_KEY')];
    session('scoring_assign_stop',1);
    $rds = $this->rds ?: $this->redis();
    $rds->zRemRangeByScore($this->redis_assign_key,$aid,$aid) ?: [];
    $this->success('您已结束打分！',U('common/logout'));
  }

  // 第三方打分日志
  public function logs()
  {
    $tpl = '';
    $mod = D('ScoreLog');
    $aid = (int)$_SESSION[C('USER_AUTH_KEY')];
    $dat = [];
    $map = $mod->get_filters() ?: [];
    if(D('Auth')->check(CONTROLLER_NAME.'/open'))
    {
      // 61 为打分团经理 可看所有打分人的记录
      if($aid != 61)
      {
        $map['aid'] = $aid;
        $this->is_scorer = true;
      }
      $tpl = 'open-logs';
      layout(false);
      $this->is_open = true;
    }
    $dat['list'] = $mod->plist(60,$map)->lists('','score_time desc,id desc');
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    //$dat['users']  = D('UserBase')->get_by_list($dat['list'],'uid,nickname,sex,score');
    $dat['scores'] = $this->get_scoring_admins();
    $dat['admins'] = D('Admin')->get_by_list($dat['list'],'aid,nickname');
    if($res = array_column($dat['list'],'resource'))
    {
      $dat['avatars'] = D('Avatar')->klist('resource',['resource' => ['in',$res]]);
    }
    $dat['rank_fails'] = D('Avatar')->score_rank_fail ?: [];
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display($tpl);
  }

  // 检查盗图
  public function shitu()
  {
    $res = trim(I('request.resource'));
    if($res)
    {
      $src = $this->feed_img_root.$res;
      $htm = $this->http('http://image.baidu.com/n/pc_search',['queryImageUrl' => $src]);
      preg_match_all('/<(\w+)\b[^>]+source-card-topic\b[^>]+>[\S\s]*?<\/\1>/is',$htm,$arr);
      if($arr[0])
      {
        $this->ret['ret'] = 1;
        $this->ret['msg'] = '疑似盗图';
        $this->ret['data'] = $arr[0];
      }
    }
    $this->ajaxReturn($this->ret);
  }

  // 删除记录
  public function del_run($id = 0)
  {
    $rds = $this->rds ?: $this->redis();
    $rds->zRem($this->redis_list_key,$id);
    $rds->zRem($this->redis_init_key,$id);
    $rds->zRem($this->redis_assign_key,$id);
    return $this->ret;
  }

  // 分配给不同的打分人
  protected function assign_scoring($arr = [])
  {
    $aid = (int)$_SESSION[C('USER_AUTH_KEY')];
    $key = $this->redis_assign_key;
    $rds = $this->rds ?: $this->redis();
    //$cnt = (int)$rds->zCount($key,$aid,$aid);
    $ols = $rds->zRangeByScore($key,$aid,$aid) ?: [];
    $cnt = count($ols);
    $stp = (int)session('scoring_assign_stop');
    $dat = [];
    foreach($arr ?: [] as $k => $v)
    {
      $uid = (int)$v['uid'] ?: (int)$v;
      $oid = $rds->zScore($key,$uid);
      if($oid && $oid != $aid) continue;
      elseif(!$oid)
      {
        if($stp)                              continue;//停止分配
        if($cnt >= $this->scoring_assign_max) continue;
        $rds->zAdd($key,$aid,$uid);
        $cnt++;
      }
      if($rds->zScore($this->redis_list_key,$uid)) $dat[$k] = $v;
      else $this->del_run($uid);
    }
    //$dat = array_merge($ols,$dat);
    return $dat;
  }

  protected function get_scoring_admins()
  {
    return D('Admin')->alias('adm')
      ->join('left join __ADMIN_AUTH_ACCESS__ acc on acc.auth_id = adm.aid')->where(['acc.rule_id' => 19])->select();
  }

  protected function redis($cfg = '')
  {
    $cfg || $cfg = $this->redis_cfg;
    $rds = D('UserBase')->get_redis($cfg);
    $this->rds = $rds;
    return $rds;
  }

}