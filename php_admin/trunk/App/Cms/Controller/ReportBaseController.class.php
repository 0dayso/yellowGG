<?php
namespace Cms\Controller;

class ReportBaseController extends PublicController
{

  public function index()
  {
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    $dat['list'] = $mod->plist(100)->lists('','dtime desc');//C('ITEMS_PER_PAGE')
    $dat['report_types']   = $mod->report_types;
    $dat['report_status']  = $mod->report_status;
    $dat['report_reasons'] = $mod->report_reasons;
    $dat['accusation_reasons'] = D('AccusationBaseLog')->accusation_reasons;
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  // 标记已处理
  public function process()
  {
    $id = (int)$_REQUEST['id'];
    if(!D(CONTROLLER_NAME)->where(array('id' => $id,'status' => 0))->setField('status',1))
    {
      $this->error('操作失败');
    }
    $this->success('操作成功');
  }

}