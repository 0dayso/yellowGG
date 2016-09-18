<?php
namespace Liehuo\Model;

class LikeModel extends CjDatadwModel
{

  protected $redis_config = 'redis_recommend';

  // 赞类型
  public $types = [
    0 => '普通赞',
    1 => '超级赞',
    2 => '金星赞',
  ];

  // 设置用户ID
  public function set_user($uid = 0)
  {
    $this->uid = (int)$uid;
    $this->redis_recm_zset = 'go_zset_myrecommend_'.$this->uid;
    $this->redis_myslide   = 'go_set_myslide_'.$this->uid;
    $this->redis_dislikeme = 'go_set_dislikeme_'.$this->uid;
    $this->redis_likeme    = 'go_set_thumbme_'.$this->uid;
    $this->redis_slikeme   = 'go_set_likeme_'.$this->uid;
    $this->redis_glikeme   = 'go_set_starme_'.$this->uid;
    return $this;
  }


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = true,$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid'])      $map[$alias.'uid']   = $uid;
    if($oid = (int)$arr['oid'])      $map[$alias.'oid']   = $oid;
    if($_REQUEST['type'] == 'paid')  $map[$alias.'like_type'] = ['in',[1,2]];
    if($_REQUEST['like_type'] != '') $map[$alias.'like_type'] = (int)$_REQUEST['like_type'];
    if($arr['matched'] != '')        $map[$alias.'matched']   = (int)$arr['matched'];
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'like_time']) || $map[$alias.'like_time'] = [];
      $map[$alias.'like_time'][] = ['egt',strtotime(date('Y-m-d',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'like_time']) || $map[$alias.'like_time'] = [];
      $map[$alias.'like_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($arr['sex'] != '')//性别筛选
    {
      $sex = (int)$arr['sex'] == 0 ? 0 : 1;
      $sql = D('UserBase')->field('uid')
        ->where(['uid' => ['exp',' = '.$alias.'uid'],'sex' => $sex])
        ->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($kwd = trim($arr['kwd']))
    {
      $map['_complex'] = [
          '_logic' => 'or',
          //$alias.'remark' => ['like','%'.$kwd.'%'],
      ];
      if(preg_match('/^\d+$/i',$kwd))
      {
        $map['_complex'][$alias.'uid'] = ['like','%'.$kwd.'%'];
        $map['_complex'][$alias.'oid'] = ['like','%'.$kwd.'%'];
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

  // 获取用户推荐列表
  public function get_recommend_list()
  {
    return $this->get_redis()->zRange($this->redis_recm_zset,0,-1,true);
  }

  // 获取用户滑动记录
  public function get_slide_list($page_size = 50)
  {
    $rds = $this->get_redis();
    $arr = $rds->sMembers($this->redis_myslide) ?: [];
    $ids = [];
    $kwd = trim(I('request.kwd'));
    foreach($arr as $v)
    {
      $uid = (int)$v;
      if(!$kwd || preg_match('/'.$kwd.'/isu',$uid)) $ids[$uid] = $uid;
    }
    $ids = array_values($ids);
    rsort($ids);
    $cnt = count($ids);
    $pag = new \Think\Page($cnt,$page_size);
    $ids = array_slice($ids,$pag->firstRow,$pag->listRows) ?: [];
    $lst = [];
    if($ids)
    {
      $mod = D('UserBase');
      $mod->field('uid,nickname,phone,sex,score,reg_time');
      $mod->where(['uid' => ['in',$ids]]);
      $mod->limit($page_size);
      $lst = $mod->lists('','reg_time desc,uid desc');
    }
    $this->pager = $pag;
    return $lst;
  }

  // 获取不喜欢我的用户IDs
  public function get_dislikeme_uids()
  {
    $arr = $this->get_redis()->sMembers($this->redis_dislikeme);
    $ids = [];
    $kwd = trim(I('request.kwd'));
    foreach($arr as $v)
    {
      $uid = (int)$v;
      if(!$kwd || preg_match('/'.$kwd.'/isu',$uid)) $ids[$uid] = $uid;
    }
    $ids = array_values($ids);
    return $ids;
  }

}