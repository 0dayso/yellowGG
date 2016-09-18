<?php
namespace Cms\Controller;

class FeedBaseController extends PublicController
{

  public function index()
  {
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    $map = $mod->get_filters();//筛选搜索
    $dat['list'] = $mod->plist(60,$map)->lists('','create_time desc');//C('ITEMS_PER_PAGE')
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->pager = $mod->pager;
    $dat['feed_audit'] = $mod->feed_audit ?: array();//审核状态
    $dat['accusation_reasons'] = D('AccusationBaseLog')->accusation_reasons;
    if($ids = array_unique(array_column($dat['list'],'id')))
    {
      $dat['feed_logs'] = D('FeedBaseLog')->where(array('feed_id' => array('in',$ids),'type' => 1))->lists();
      if($dat['feed_logs']) $dat['feed_logs'] = array_combine(array_column($dat['feed_logs'],'feed_id'),$dat['feed_logs']);
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  // 动态详情
  public function view()
  {
    $fid = $id = (int)$_REQUEST['id'] ?: (int)$_REQUEST['fid'];
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    if(!$dat['item'] = $mod->find($fid))
    {
      $this->error('对象不存在!');
    }
    else
    {
      $map = array('feed_id' => $fid);
      $dat['feed_audit'] = $mod->feed_audit ?: array();//审核状态
      // 审核日志
      $dat['feed_log'] = D('FeedBaseLog')->where(array('feed_id' => $fid,'type' => 1))->order('log_time desc')->find();
      // 用户打分记录
      $sco = D('ScoreBase');
      $dat['scores'] = $sco->plist(C('ITEMS_PER_PAGE') ?: 20,$map)->lists('','score_time desc');
      $sco->pager->parameter = array('tab' => 'tab-feed-scores');
      $dat['scores_pager'] = $sco->pager;
      $dat['score_types'] = $sco->score_types ?: array();
      $dat['accusation_reasons'] = D('AccusationBaseLog')->accusation_reasons;
      // 打分团分派记录 单条
      $dat['assign'] = D('ScoreAssignLog')->where($map)->find() ?: array();
      if($dat['assign'])
      {
        $dat['assign']['group_name'] = D('UserScoringGroup')->where(array('id' => $dat['assign']['group_id']))->getField('name');
        $dat['assign']['range_name'] = D('UserScoringRange')->where(array('id' => $dat['assign']['range_id']))->getField('name');
      }
      // 打分团/后台打分记录
      $dat['logs'] = D('ScoreLog')
        ->alias('s')->field(array('s.*','a.resource','a.create_time','a.group_id','a.range_id'))
        ->join('left join '.D('ScoreAssignLog')->getTableName().' a on a.feed_id = s.feed_id')
        ->lists(array('s.feed_id' => $fid),'s.score_time desc,s.id desc') ?: array();
    }
    $this->data = $dat;
    $this->display();
  }

  // 动态删除
  public function del()
  {
    $fid = $id = (int)$_REQUEST['id'] ?: (int)$_REQUEST['fid'];
    $reason = trim($_REQUEST['reason']);
    $remark = I('request.remark');
    $mod = D(CONTROLLER_NAME);
    if($id < 1)
    {
      $this->error('ID错误');
    }
    elseif(!$old = $mod->find($id))
    {
      $this->error('对象不存在');
    }
    elseif(!$mod->where(array('id' => $id))->delete())
    {
      $this->error('操作失败');
    }
    else
    {
      // 删除缓存
      $rds = D('PhpServerRedis');
      $rds->del_feed_last($old['uid']);
      $scoring = new ScoringController();
      $rds->new_redis()->zRem($scoring->redis_list_key,$id);
      // 删除OSS资源
      $this->aliydel($mod->feed_oss_bucket,$old['resource']);
      // 举报已处理
      $rpt = array(
        'status' => 3,//举报已处理并删除动态
        'remark' => $remark,
        'atime'  => time(),
      );
      $acc = D('AccusationBaseLog');
      if($reason != '' && array_key_exists($reason,$acc->accusation_reasons))
      {
        $rpt['reason'] = $reason;
        $remark = $acc->accusation_reasons[$reason].($remark ? ' - ' : '').$remark;
      }
      $remark || $remark = '后台删除动态';
      D('FeedBaseLog')->log($old,array('type' => 0,'remark' => $remark));
      D('ReportBase')->where(array('feed_id' => $id,'status' => 0))->save($rpt);
    }
    $this->success('操作成功',U('index'));
  }

  // 动态审核
  public function audit()
  {
    $fid = $id = (int)$_REQUEST['id'] ?: (int)$_REQUEST['fid'];
    $mod = D(CONTROLLER_NAME);
    if($id < 1)
    {
      $this->error('ID错误');
    }
    elseif(!$old = $mod->find($id))
    {
      $this->error('对象不存在('.$id.')');
    }
    elseif($old['audited'] == '1')
    {
      $this->error('已被审核('.$id.')');
    }
    elseif(!$mod->where(array('id' => $id,'audited' => 0))->setField('audited',1))
    {
      $this->error('保存失败('.$id.')');
    }
    else
    {
      D('FeedBaseLog')->log($old,array('type' => 1,'remark' => '审核通过'));
    }
    $this->success('操作成功('.$id.')');
  }

  // 打分日志列表
  public function logs()
  {
    $mod = D('FeedBaseLog');
    $dat = array();
    $dat['list'] = $mod->plist(C('ITEMS_PER_PAGE') ?: 60)->lists('','log_time desc,id desc');//C('ITEMS_PER_PAGE')
    $this->page = $dat['page_html'] = $mod->pager->show();
    $dat['log_types'] = $mod->log_types ?: array();
    if($ids = array_unique(array_column($dat['list'],'aid')) ?: array())
    {
      $dat['log_admins'] = D('Admin')->lists(array('aid' => array('in',$ids))) ?: array();
      if($dat['log_admins']) $dat['log_admins'] = array_combine(array_column($dat['log_admins'],'aid'),$dat['log_admins']);
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

}