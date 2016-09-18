<?php
namespace Yanzhi\Model;

class FeedCommentModel extends CjDatadwModel
{

  // 评论类型
  public $comment_types = array(
    1 => '文字',
    2 => '语音',
  );

  // 评论人类型
  public $user_types = array(
    1 => '普通用户',
    2 => '经纪人',
  );

  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = array())
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: array();
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = array();
    if($arr['ctype'] != '')    $map[$alias.'ctype']    = (int)$arr['ctype'];
    if($arr['usertype'] != '') $map[$alias.'usertype'] = (int)$arr['usertype'];
    if($arr['feed_id'] != '')  $map[$alias.'feed_id']  = (int)$arr['feed_id'];
    if($arr['uid'] != '')      $map[$alias.'uid']      = (int)$arr['uid'];
    if($arr['oid'] != '')      $map[$alias.'oid']      = (int)$arr['oid'];
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'createtime']) || $map[$alias.'createtime'] = array();
      $map[$alias.'createtime'][] = array('egt',strtotime(date('Y-m-d',$stime)));
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'createtime']) || $map[$alias.'createtime'] = array();
      $map[$alias.'createtime'][] = array('elt',strtotime(date('Y-m-d 23:59:59',$etime)));
    }
    if($kwd = trim($arr['kwd']))
    {
      $map['_complex'] = array(
          '_logic' => 'or',
          $alias.'content' => array('like','%'.$kwd.'%'),
      );
    }
    return $map;
  }

}