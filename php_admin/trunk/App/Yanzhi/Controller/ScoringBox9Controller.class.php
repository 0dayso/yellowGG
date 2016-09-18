<?php
namespace Yanzhi\Controller;

class ScoringBox9Controller extends ScoringController
{

  public $ret = array('ret' => 0,'msg' => '','data' => array());

  // 动态超时时间 秒
  public $redis_feed_timeout = 999999999;

  public function __construct()
  {
    parent::__construct();
    $this->redis = D('PhpServerRedis')->new_redis();
    $this->model_feed = D('FeedBase');
    $this->feed_img_root = $this->model_feed->feed_img_root;//动态图片根路径

    $this->model_score = D('FeedScoreLog');
    $this->model_score->uid     = 0;
    $this->feed_scoring_times   = $this->model_score->feed_scoring_times;//需要几个人打分后计算最终得分
    $this->feed_scoring_timeout = $this->model_score->feed_scoring_timeout;
    $this->redis_scoring_feed   = $this->model_score->redis_scoring_feed;//PC打分记录
    // 不合格
    $this->score_rank_fail = $this->model_score->score_rank_fail ?: array();
    // 低颜值
    $this->score_rank_pass = $this->model_score->score_rank_pass ?: array();
    // 高颜值
    $this->score_rank_fine1 = $this->model_score->score_rank_fine1 ?: array();
    $this->score_rank_fine0 = $this->model_score->score_rank_fine0 ?: array();

    //$this->redis_feed_timeout     = $this->feed_scoring_timeout;
    $this->feed_timeout           = $this->redis_feed_timeout;
    $this->feed_timeout_highlight = (int)($this->feed_timeout / 60);//15;//
    $this->ajax_interval          = 10;//秒
  }


  public function index()
  {
    $box9 = D('FeedScoreBox9');
    $this->score_box9_7 = $box9->get_score_box9(7);
    $this->score_box9_8 = $box9->get_score_box9(8);
    $this->score_box9_9 = $box9->get_score_box9(9);
    $this->display('Scoring/special');
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

  // 获取未打分动态数据
  protected function get_datas()
  {
    return $this->get_score_list();
  }

  // 打分队列
  protected function get_score_list()
  {
    return $this->model_score->get_scoring_special(10);
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
      $avg['score'] = $sco < 5 ? 0 : $sco;
      $avg['feed'] = $fed = $this->model_score->get_feed_score_cahce($fid);
      if(!$fed)
      {
        $this->ret['msg'] = '动态缓存不存在';
        $this->model_score->del_scoring_special($fid);
      }
      else
      {
        $avg['rank'] = $this->model_score->get_score_rank($fed['sex'],$sco) ?: array();
        $this->ret['data'] = $avg;
        $ret = $this->save_score($fid,$avg) ?: array();
        $this->ret = array_merge($this->ret,$ret);
      }
    }
    $this->ajaxReturn($this->ret);
  }

  protected function save_score($fid = 0,$avg = array())
  {
    $sco = round((float)$avg['score'],1);
    $ret = $this->model_feed->scoring($fid,$sco) ?: array();//保存分数
    if(!$ret['ret'])
    {
      $feed = $ret['feed'] ?: array();
      $uid = (int)$feed['uid'];
      if($sco > 0)
      {
        //设置9宫格图片
        D('FeedScoreBox9')->set_score_box9($sco,$feed);
      }
      //记录打分Log
      $this->model_score->add(array(
        'feed_id'     => $fid,
        'assign_time' => $feed['create_time'],
        'score'       => $avg['score'],
        'score_time'  => time(),
        'score_data'  => json_encode($avg['rank']) ?: '',
      ));
      // 打分系统消息
      if($uid)
      {
        $this->model_score->scoring_msg($uid,$feed,$avg['rank'] ?: $avg);//打分系统消息
        //$rds->rPush($this->redis_auto_score_list,$fid);//自动打分队列
      }
      $this->model_score->del_feed_score_cahce($fid);
      $this->model_score->del_scoring_special($fid);
    }
    return $ret;
  }

  // 删除操作
  public function del()
  {
    $this->ajaxReturn($this->ret);
  }

}