<?php
namespace Cms\Controller;

class ScoringRegedController extends ScoringController
{

  // 动态超时时间 秒
  public $redis_feed_timeout = 999999999;

  public function _initialize()
  {
    // 动态图片根路径
    $this->feed_img_root = D('FeedBase')->feed_img_root;
    $this->feed_timeout  = $this->redis_feed_timeout;
    $this->ajax_interval = 10;//秒

    // 可打分管理员
    $this->scoring_admins = array();
  }


  public function index()
  {
    $this->display('Scoring/index');
  }

  // 获取未打分动态数据
  protected function get_datas()
  {
    $arr = $this->get_score_list();
    $lst = array();
    foreach($arr ?: array() as $fid => $row)
    {
      $row['timeout'] = time() - (int)$row['create_time'];
      $lst[$fid] = $row;
    }
    //die(json_encode(array('data' => $lst)));
    return $lst;
  }

  // 打分队列
  protected function get_score_list()
  {
    return D('FeedBase')->alias('f')->field(array('f.*','u.sex','u.reg_time'))->limit(10)
      ->join('left join '.D('UserBase')->getTableName().' u on u.uid = f.uid')
      ->klist('id',array('f.finished' => 0,'f.uid' => array('egt',1)),array('f.create_time')) ?: array();
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
      $feed = $this->ret['feed'] ?: array();
      $uid = (int)$feed['uid'];
      // 打分系统消息
      if($uid)
      {
        $this->scoring_msg($uid,$feed);//打分系统消息
        $rds->rPush($this->redis_auto_score_list,$fid);//自动打分队列
      }
      // 记录打分日志
      $log = array(
        'feed_id' => $fid,
        'score'   => $sco,
        //'timeout' => time() - (int)$feed['create_time'],
      );
      if(D('ScoreLog')->scoring_log($log) === false)
      {
        $this->ret['msg'] = '添加打分Log失败';
      }
    }
    $this->ajaxReturn($this->ret);
  }


  // 删除操作
  public function del()
  {
    $this->ajaxReturn($this->ret);
  }

}