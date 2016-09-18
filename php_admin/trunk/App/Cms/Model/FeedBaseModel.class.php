<?php
namespace Cms\Model;

class FeedBaseModel extends CjDatadwModel
{

  // 审核状态
  public $feed_audit = array(
    0 => '未审核',
    1 => '已审核',
  );

  public $feed_oss_bucket = 'cjfeed';

  // 动态图片根路径
  public $feed_img_root = 'http://feed.chujian.im/';

  public function _initialize()
  {
    // 模板自动替换 动态图片根路径
    $cfg = C('TMPL_PARSE_STRING') ?: array();
    if(is_array($cfg) && !isset($cfg['__FEED_IMG_ROOT__']))
    {
      $cfg['__FEED_IMG_ROOT__'] = $this->feed_img_root;
      C('TMPL_PARSE_STRING',$cfg);
    }
  }

  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = array())
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: array();
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = array();
    if($arr['audited'] != '')
    {
      $map[$alias.'audited'] = (int)$arr['audited'];
    }
    if($arr['uid'] != '')
    {
      $uid = (int)$arr['uid'];
      if($uid < 0) $map[$alias.'uid'] = array('egt',1);
      else $map[$alias.'uid'] = $uid;
    }
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'create_time']) || $map[$alias.'create_time'] = array();
      $map[$alias.'create_time'][] = array('egt',strtotime(date('Y-m-d',$stime)));
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'create_time']) || $map[$alias.'create_time'] = array();
      $map[$alias.'create_time'][] = array('elt',strtotime(date('Y-m-d 23:59:59',$etime)));
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
      $map[$alias.'score'] = array($exp,$sco);
    }
    if($arr['sex'] != '')//性别筛选
    {
      $sex = (int)$arr['sex'] == 0 ? 0 : 1;
      $sql = D('UserBase')->field('uid')
        ->where(array('uid' => array('exp',' = '.$this->getTableName().'.uid'),'sex' => $sex))
        ->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($kwd = trim($arr['kwd']))
    {
      $map['_complex'] = array(
          '_logic' => 'or',
          $alias.'text' => array('like','%'.$kwd.'%'),
      );
    }
    return $map;
  }

  // 保存打分结果
  public function scoring($fid = 0,$score = 0)
  {
    $ret = array('ret' => 0,'msg' => '');
    $fid = (int)$fid;
    $sco = round((float)$score,2);
    $dat = array(
      'base_score' => $sco,
      'base_score_time' => time(),
      'score'      => $sco,
      'score_cnt'  => 1,
      'finished'   => 1,
    );
    if($fid < 1)
    {
      $ret['ret'] = 1;
      $ret['msg'] = 'ID错误';
    }
    elseif(!$old = $this->find($fid))
    {
      $ret['ret'] = 1;
      $ret['msg'] = '对象不存在';
    }
    elseif($old['base_score'] > 0 || $old['finished'] == 1)
    {
      $ret['ret'] = 1;
      $ret['msg'] = '已被打分';
    }
    elseif(false === $this->where(array('id' => $fid))->save($dat))
    {
      $ret['ret'] = 1;
      $ret['msg'] = '保存失败';
    }
    else
    {
      $ret['msg'] = '打分成功';
      $ret['feed'] = array_merge($old,$dat);
      $uid = (int)$ret['feed']['uid'];
      // 更新用户最后动态
      D('LocationBase')->location_update($uid,array(
        'latest_feed'      => $fid,
        'latest_score'     => $sco,
        'latest_feed_time' => $ret['feed']['create_time'],
        'latest_feed_img'  => $ret['feed']['resource'],
      ));
      D('PhpServerRedis')->del_feed_last($uid);
      D('UserBase')->del_user_cache($uid);
    }
    return $ret;
  }

  // 生成OSS图片路径
  public function get_file_name_oss($filename = '')
  {
    $temp  = explode('.',$filename);
    $ext   = (count($temp) > 1) ? '.'.$temp[1] : '';
    $path  = date("Ymd").'/';
    $path .= md5(uniqid(rand(), true)).$ext;
    return $path;
  }

}