<?php
namespace Liehuo\Model;

class SensitiveWordsModel extends PublicModel
{

  protected $redis_config  = 'redis_user';
  public    $redis_key     = 'php_sensitive_words';
  public    $redis_sync_mq = 'php_sensitive_words_sync';
  public static $words     = [];

  public function __construct()
  {
    parent::__construct();
  }


  public function get_words()
  {
    $wds = self::$words;
    if(!$wds)
    {
      $wds = $this->get_redis()->sMembers($this->redis_key);
      if(!$wds)
      {
        $wds = $this->getField('word',true);
      }
      if($wds)
      {
        usort($wds,function($a,$b)
        {
          $la = strlen($a);
          $lb = strlen($b);
          if($la == $lb) return 0;
          return $la < $lb ? -1 : 1;
        });
        self::$words = $wds;
      }
    }
    return $wds ?: [];
  }

  // 判断文本有没有违规
  public function check_text($txt = '',$typ = 'htm')
  {
    // 白名单
    $wls = $fls = $tls = [];
    foreach(
    [
      '微笑','加油','加班',
      '自信','相信','通信','电信',
      '星球','足球','篮球','台球','羽毛球','球球大作战',
      '挪威','夏威夷','新加坡',
      'QQ飞车','QQ炫舞',
      'quot','text',
    ] as $k => $v)
    {
      if(!$v) continue;
      if(!$k || is_numeric($k))
      {
        $k = $v;
        $v = md5($v);
      }
      $wls[$k] = $v;
      $fls[] = $k;
      $tls[] = $v;
    }
    // 敏感词
    $wds = $this->get_words() ?: [];
    $arr = [];
    foreach($wds as $v)
    {
      $sto = '<b class="high-light">'.$v.'</b>';
      $typ == 'txt' && $sto = '【'.$v.'】';
      $arr[$v] = $sto;
    }
    $wls && $txt = str_ireplace($fls,$tls,$txt);
    //$new = strtr($txt,$arr);
    $new = str_ireplace(array_keys($arr),array_values($arr),$txt);
    return [
      'ret'     => $txt == $new,
      'checked' => $wls ? str_ireplace($tls,$fls,$new) : $new,
    ];
  }

  public function check_text_all($arr = [],$fds = [],$typ = 'htm')
  {
    $ret = [];
    is_string($fds) && $fds = preg_split('/\s+,\s+/',$fds);
    foreach($arr ?: [] as $k => $v)
    {
      foreach($fds ?: [] as $f)
      {
        if(!isset($v[$f])) continue;
        $cdt = $this->check_text($v[$f],$typ);
        $v = array_merge($v,
        [
          $f.'_ret'     => $cdt['ret'],
          $f.'_checked' => $cdt['checked'],
        ]);
      }
      $ret[$k] = $v;
    }
    return $ret;
  }

  public function update_cache()
  {
    $ret = false;
    $rds = $this->get_redis();
    $rds->del($this->redis_key);
    $arr = $this->getField('word',true) ?: [];
    $wds = [];
    foreach($arr as $v)
    {
      if($v)
      {
        $ret = $rds->sAdd($this->redis_key,$v);
        $wds[$v] = $v;
      }
    }
    if($wds) self::$words = array_keys($wds);
    return $ret;
  }


  // 同步关键词库
  //   $typ  操作类型  add|del
  /*

  redis -h redisuser.chujianapp.com -p 6379 -a c30690277da3464f:Lhapp123
  list rPush php_sensitive_words_sync
  [
    {
      "type" : 'add',//add|del
      "data" : ['关键词'],
      "time" : 1444444444
    }
  ]

  */
  public function sync_words($wds = [],$typ = 'add')
  {
    $ret = false;
    $wds && is_string($wds) && $wds = [$wds];
    if(is_array($wds) && $wds)
    {
      $rds = $this->get_redis();
      $ret = $rds->rPush($this->redis_sync_mq,json_encode(
      [
        'type' => strtolower($typ),
        'data' => $wds,
        'time' => time(),
      ]));
    }
    return $ret;
  }

}