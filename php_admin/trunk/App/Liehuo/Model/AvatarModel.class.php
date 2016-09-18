<?php
namespace Liehuo\Model;

class AvatarModel extends CjAdminModel
{

  protected $redis_config = 'redis_user';

  // 头像待打分队列
  public $redis_avatar_scoring = 'php_avatar_scoring';

  // 审核状态
  public $state_audit = array(
    0 => '未审核',
    1 => '已审核',
    2 => '已推荐',
  );

  // 照片不合格 score >= key
  public $score_rank_fail = array(
    '0.01' => array(
      'reason' => '不合格',
      'msg'    => '头像提醒：你暂时无法被他人看见！原因：非本人真实照片、看不清五官、性别不符、小孩(儿时)、涉及色情、带有联系方式的头像均无法通过审核，这样你将不能交友，请点击更换头像。',
      //'msg'    => '头像提醒：烈火是一款注重真实性的交友平台，这里所有用户的头像都是本人真实照片，为了公平起见，你的头像也必须符合要求，盗用他人照片、完全看不清五官、性别不符、小孩（儿时）、非人像、涉及色情低俗、带有联系方式的头像均无法通过审核，这样你将不能交友，不会被其他用户看到。',
    ),
    '0.10' => array(
      'reason' => '其他',
      'msg'    => '',
      'hidden' => true,
    ),
    '0.11' => array(
      'reason' => '涉嫌色情或低俗',
      'msg'    => '头像提醒：你将无法被他人看见，原因：头像涉嫌色情低俗的内容，请点击更换，收获更多魅力！',
      //'msg'    => '头像提醒：你将无法被他人看见，原因：头像涉嫌色情低俗的内容，请尽快更换，收获更多喜欢！',
      //'msg'    => '你将无法被他人看见，原因：烈火抵制色情低俗的照片，请尽快更换！',
    ),
    '0.12' => array(
      'reason' => '非人像',
      'msg'    => '头像提醒：你将无法被他人看见，原因：头像应使用本人真实照片，请点击更换，收获更多魅力！',
      //'msg'    => '头像提醒：你将无法被他人看见，原因：头像应使用本人真实照片，请尽快更换，收获更多喜欢！',
      //'msg'    => '你将无法被他人看见，原因：头像应使用本人真实照片，请尽快更换！',
    ),
    '0.13' => array(
      'reason' => '模糊不清/拉伸变形/照片倒置',
      'msg'    => '头像提醒：你将无法被他人看见，原因：模糊、拉伸、倒置的照片不能用做头像，请点击更换，收获更多魅力！',
      //'msg'    => '头像提醒：你将无法被他人看见，原因：模糊、拉伸、倒置的照片不能用做头像，请尽快更换，收获更多喜欢！',
      //'msg'    => '你将无法被他人看见，原因：模糊、拉伸、倒置的照片不能用做头像，请尽快更换！',
    ),
    '0.14' => array(
      'reason' => '小孩（儿时）',
      'msg'    => '头像提醒：你将无法被他人看见，原因：儿时照或儿童照不能用做头像，请点击更换，收获更多魅力！',
      //'msg'    => '头像提醒：你将无法被他人看见，原因：儿时照或儿童照不能用做头像，请尽快更换，收获更多喜欢！',
      //'msg'    => '你将无法被他人看见，原因：儿时照或儿童照不能用做头像，请尽快更换！',
    ),
    '0.15' => array(
      'reason' => '性别不符',
      'msg'    => '头像提醒：你将无法被他人看见，原因：性别与头像不符，请点击更换，收获更多魅力！',
      //'msg'    => '头像提醒：你将无法被他人看见，原因：性别与头像不符，请尽快更换，收获更多喜欢！',
      //'msg'    => '你将无法被他人看见，原因：性别与照片不符，请尽快更换！',
    ),
    '0.16' => array(
      'reason' => '背影',
      'msg'    => '头像提醒：你将无法被他人看见，原因：背影照不能出现在头像中，请点击更换，收获更多魅力！',
      //'msg'    => '头像提醒：你将无法被他人看见，原因：背影照不能出现在头像中，请尽快更换，收获更多喜欢！',
      //'msg'    => '你将无法被他人看见，原因：背影照不能出现在头像中，请尽快更换！',
    ),
    '0.17' => array(
      'reason' => '远景照看不清五官无法辨识',
      'msg'    => '',
      //'msg'    => '你将无法被他人看见，原因：该头像无法识别颜值，请尽快更换！',
      'hidden' => true,
    ),
    '0.18' => array(
      'reason' => '明显盗图',
      'msg'    => '头像提醒：你将无法被他人看见，原因：头像盗用他人照片，请点击更换，收获更多魅力！如果是您本人，请联系小秘书申诉。',
      //'msg'    => '头像提醒：你将无法被他人看见，原因：头像盗用他人照片，请尽快更换，收获更多喜欢！如果是您本人，请联系小秘书申诉。',
      //'msg'    => '你将无法被他人看见，原因：盗用他人照片违法，请尽快更换！如果是您本人，请联系小秘书申诉。',
    ),
    '0.19' => array(
      'reason' => '拍实体照片/拍屏幕/手机截屏',
      'msg'    => '',
      //'msg'    => '你将无法被他人看见，原因：拍实体照片、拍屏幕、手机截屏不能用做头像，请尽快更换！',
      'hidden' => true,
    ),
    '0.20' => array(
      'reason' => '联系方式或广告',
      'msg'    => '头像提醒：你将无法被他人看见，原因：联系方式、广告信息不能出现在头像中，请点击更换，收获更多魅力！',
      //'msg'    => '头像提醒：你将无法被他人看见，原因：联系方式、广告信息不能出现在头像中，请尽快更换，收获更多喜欢！',
      //'msg'    => '你将无法被他人看见，原因：联系方式、广告信息不能出现在头像中，请尽快更换！',
    ),
    '4.99' => array(
      'reason' => '头像上传失败',
      'msg'    => '头像提醒：非常抱歉，您的头像由于未知原因导致上传失败，请您点击重新上传，以便获得最佳体验，收获更多魅力！',
      //'msg'    => '头像提醒：非常抱歉，您的头像由于未知原因导致上传失败，请您重新上传，以便获得最佳体验，收获更多喜欢！',
      //'msg'    => '非常抱歉，您的头像由于未知原因导致上传失败，请您重新上传！',
    ),
    '5.00' => array(
      'reason' => '非常丑、随便拍、乡村非主流、大叔大妈',
      'msg'    => '头像提醒：非常遗憾您目前无法被他人看见！请点击尝试更换一张更为优秀的头像，以便获得最佳体验，收获更多魅力！',
      //'msg'    => '头像提醒：非常遗憾您目前无法被他人看见！请尝试更换一张更为优秀的头像，以便获得最佳体验，收获更多喜欢！',
      //'msg'    => '非常遗憾你目前无法被他人看见，无法配对聊天！！为了确保你能被更多人点赞，请尝试更换一张更为优秀的头像哦~这样可以获得更多赞呢~',
      //'msg'    => '非常遗憾你已被关入小黑屋！为了确保你能被更多人点赞，请尝试更换一张更为优秀的头像，否则将无法被他人看见，无法配对聊天！',
      'hidden' => true,
    ),
  );

  // 根据 分数 获取分数级别
  public function get_score_rank($score = 0)
  {
    $rnk = array();
    $score = (float)$score;
    if(!$rnk) foreach($this->score_rank_fail ?: array() as $sco => $v)
    {
      if($score <= (float)$sco)
      {
        $rnk = $v;
        break;
      }
    }
    return $rnk;
  }

  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid']) $map[$alias.'uid']     = $uid;
    if($arr['type'] != '')      $map[$alias.'type']    = (int)$arr['type'];
    if($arr['audited'] != '')   $map[$alias.'audited'] = (int)$arr['audited'];
    if($arr['deleted'] != '')   $map[$alias.'delete_time'] = $arr['deleted'] ? ['egt',1] : 0;
    if($arr['filter'] == 'scored')   $map[$alias.'score_time']  = ['egt',1];
    if($arr['filter'] == 'unscored') $map[$alias.'score_time']  = ['elt',60 * 60 * 24];
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'create_time']) || $map[$alias.'create_time'] = [];
      $map[$alias.'create_time'][] = array('egt',strtotime(date('Y-m-d',$stime)));
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'create_time']) || $map[$alias.'create_time'] = [];
      $map[$alias.'create_time'][] = array('elt',strtotime(date('Y-m-d 23:59:59',$etime)));
    }
    if($arr['sex'] != '')//性别筛选
    {
      $sex = (int)$arr['sex'] == 0 ? 0 : 1;
      $sql = D('UserBase')->table('chujiandw.__USER_BASE__')->field('uid')
        ->where(['uid' => ['exp',' = '.$alias.'uid'],'sex' => $sex])
        ->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($arr['user_type'] != '')
    {
      $typ = (int)$arr['user_type'];
      if($typ == -1)
      {
        $sql = D('AccountBase')->table('chujiandw.__ACCOUNT_BASE__')->field('uid')
          ->where(['uid' => ['exp',' = '.$alias.'uid'],'total_expense' => ['gt',0]])
          ->buildSql();
        $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
      }
      else
      {
        $sql = D('UserBase')->table('chujiandw.__USER_BASE__')->field('uid')
          ->where(['uid' => ['exp',' = '.$alias.'uid'],'type' => $typ])
          ->buildSql();
        $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
      }
    }
    if($arr['score'] != '')
    {
      $sco = (int)$arr['score'];
      $exp = 'egt';
      if(0 - $sco > 0)
      {
        $exp = 'lt';
        $sco = 0 - $sco;
      }
      $map[$alias.'score'] = [$exp,$sco];
    }
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
        'uid'      => ['exp',' = '.$alias.'uid'],
        '_complex' => [
          '_logic'   => 'or',
          'province' => ['like',$prov.'%'],
          'city'     => ['like',$prov.'%'],
          'area'     => ['like',$prov.'%'],
        ],
      ];
      $sql = D('LocationBase')->table('chujiandw.__LOCATION_BASE__')->field('uid')->where($whe)->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $map['_complex'] = [
        '_logic' => 'or',
        //$alias.'nickname' => ['like','%'.$kwd.'%'],
      ];
      $sql = D('UserBase')->table('chujiandw.__USER_BASE__')->field('uid')
        ->where(['uid' => ['exp',' = '.$alias.'uid'],'pkg_channel' => $kwd])
        ->buildSql();
      $map['_complex']['_string'] .= ($map['_complex']['_string'] ? ' and ' : '').'exists '.$sql;
      if(preg_match('/^\d+$/i',$kwd)) $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

  // 获取已被打分的
  public function list_scored($arr = [])
  {
    $dat = [];
    if($res = array_column($arr ?: [],'resource'))
    {
      $dat = $this->where([
        'resource'   => ['in',$res],
        'score_time' => ['egt',time() - 60 * 60 * 24 * 30],
      ])->klist('resource') ?: [];
    }
    return $dat;
  }

  // 审核照片
  public function audit($id = 0,$dat = [])
  {
    $dat = array_merge([
      'audited'    => 1,
      'audit_aid'  => (int)$_SESSION[C('USER_AUTH_KEY')],
      'audit_time' => time(),
    ],$dat ?: []);
    return $this->where(['id' => $id,'audited' => 0])->limit(1)->save($dat);
  }

  // 推荐视频
  public function recommend_video($res = '',$isr = true)
  {
    $ret = false;
    $rds = $this->get_redis('redis_default');
    $key = 'php_recommend_videos';
    if($isr)
    {
      $ret = $rds->hSet($key,$res,1);
    }
    else
    {
      $ret = $rds->hDel($key,$res);
    }
    return $ret;
  }

  // 导入Miao的头像
  public function import_avatar_miao($limit = 0)
  {
    $stm = strtotime(date('Y-m-d',strtotime('-3 days')));
    $mls = D('UserBase')->get_redis()->zRangeByScore('php_active',$stm,time() + 1,
    [
      'withscores' => true,
      'limit'      => [0,1000],
    ]) ?: [];
    $ids = array_keys($mls) ?: [];
    $ids = array_slice($ids,0,1000);
    $sql = $this->table('cj_admin.__AVATAR__')->field('id')->where(['uid' => ['exp',' = u.uid']])->buildSql();
    $uls = D('UserBase')->alias('u')
      ->field('u.uid,u.album,l.update_time')
      ->join('left join __LOCATION_BASE__ l on l.uid = u.uid')
      ->order('u.score,l.update_time,u.uid')
      ->limit((int)$limit ?: 100)
      ->klist('uid',
      [
        '_complex'      =>
        [
          '_logic'        => 'or',
          'u.uid'         => ['in',$ids],
          'l.update_time' => ['egt',$stm],
        ],
        'l.uid'         => ['egt',12345679],
        'u.pkg_channel' => 'miao1',
        '_string'       => 'not exists '.$sql,
      ]);
    $als = $adt = [];
    foreach($uls ?: [] as $v)
    {
      $alb = json_decode($v['album'],true) ?: [];
      $res = is_array($alb[0]) ? $alb[0]['resource'] : $alb[0];
      if($alb && $res)
      {
        $als[$res] = $v['uid'];
      }
    }
    if($als)
    {
      $lst = $this->klist('resource',
      [
        'resource' => ['in',array_keys($als)],
        'uid'      => ['in',array_unique(array_values($als))],
      ]) ?: [];
      $rds = $this->get_redis();
      foreach($als as $res => $uid)
      {
        if(isset($lst[$res])) continue;
        $usr = $uls[$uid];
        $tim = $usr['update_time'] ?: $mls[$uid] ?: time();
        $rds->zAdd($this->redis_avatar_scoring,$tim,$uid);
        $adt[] =
        [
          'uid'         => $uid,
          'resource'    => $res,
          'create_time' => $tim,
        ];
      }
      if($adt) $this->addAll($adt);
    }
    return $adt;
  }

  // Miao用户漏打分
  /*
  SELECT a.uid,a.score,a.score_time,a.create_time,a.resource
  ,u.score as score_user,l.update_time,FROM_UNIXTIME(l.update_time) as ymd
  FROM `cj_avatar` a
  LEFT JOIN chujiandw.cj_user_base u ON u.uid = a.uid
  LEFT JOIN chujiandw.cj_location_base l ON l.uid = a.uid
  WHERE l.update_time >= 1450540800
  AND l.uid >= 12345679
  AND u.pkg_channel = 'miao1'
  AND a.score_time <= 0
  AND u.score <= 0
  ORDER BY l.update_time,u.uid
  */
  public function import_scoring_miao_lost($limit = 0)
  {
    $stm = strtotime(date('Y-m-d',strtotime('-3 days')));
    $lls = $this->alias('a')->field('a.uid,a.score,a.score_time,a.create_time,a.resource,u.score as score_user,u.album,l.update_time')
      ->join('left join chujiandw.__USER_BASE__ u on u.uid = a.uid')
      ->join('left join chujiandw.__LOCATION_BASE__ l on l.uid = a.uid')
      ->where(
      [
        'l.update_time' =>
        [
          ['egt',$stm],
          ['elt',strtotime('-3 hours')],
        ],
        'u.uid'         => ['egt',12345679],
        'u.pkg_channel' => 'miao1',
        'a.score_time'  => ['elt',0],
        'u.score'       => ['elt',0],
      ])
      ->order('l.update_time,u.uid')
      ->limit((int)$limit ?: 100)
      ->klist('resource');
    $als = $adt = [];
    foreach($lls ?: [] as $v)
    {
      $alb = json_decode($v['album'],true) ?: [];
      $res = is_array($alb[0]) ? $alb[0]['resource'] : $alb[0];
      if($alb && $res)
      {
        $als[$res] = $v['uid'];
      }
    }
    if($als)
    {
      $lst = $this->klist('resource',
      [
        'resource' => ['in',array_keys($als)],
        'uid'      => ['in',array_unique(array_values($als))],
      ]) ?: [];
      $rds = $this->get_redis();
      foreach($als as $res => $uid)
      {
        $usr = $lls[$res];
        $tim = $usr['update_time'] ?: time();
        $rds->zAdd($this->redis_avatar_scoring,$tim,$uid);
        if(isset($lst[$res])) continue;
        $adt[] =
        [
          'uid'         => $uid,
          'resource'    => $res,
          'create_time' => $tim,
        ];
      }
      if($adt) $this->addAll($adt);
    }
    return $adt;
  }

  // 获取头像质量统计数据
  public function analy_quality($map = [],$page_size = 2000)
  {
    $this->field([
      'from_unixtime(score_time,\'%Y-%m-%d\')' => 'reg_date',
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


  /*
   * 格式化用户头像
   * */
  public function avatar_query($res = '',$fix = true)
  {
    is_array($res) && $res = $res[0];
    $res = (string)$res;
    if($res)
    {
      preg_match('/^https?:/i',$res) || $res = 'http://feed.chujianapp.com/'.$res;
      if($fix !== true)
      {
        $res = preg_replace('/@\w*$/','',$res).(string)$fix;
      }
    }
    return $res;
  }

  /*
   * 百度识图
   * http://getapi.sinaapp.com/http/?method=GET&type=json&url=http%3A%2F%2Fimage.baidu.com%2Fn%2Fpc_search%3FqueryImageUrl%3Dhttp%3A%2F%2Ffeed.chujianapp.com%2F20151215%2Fb9535e0cd2fd50323f9fa2119340bd0f.jpg&match=%2F%3C%28\w%2B%29\b[^%3E]%2Bsource-card-topic\b[^%3E]%2B%3E[\S\s]*%3F%3C\%2F\1%3E%2Fis
   * /<(\w+)\b[^>]+source-card-topic\b[^>]+>[\S\s]*?<\/\1>/is
   */

}