<?php
namespace Liehuo\Controller;

class ReportBaseController extends PublicController
{

  public function index()
  {
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    $map = $mod->get_filters(true);
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','dtime desc');
    $this->pager = $mod->pager;
    $this->page  = $dat['page_html'] = $mod->pager->show();
    $dat['report_types']   = $mod->report_types;
    $dat['report_status']  = $mod->report_status;
    $dat['report_reasons'] = $mod->report_reasons;
    $dat['accusation_reasons'] = D('AccusationBaseLog')->accusation_reasons;
    $dat['users'] = D('UserBase')->get_users_account($dat['list'],'offender_uid');
    $dat['hosts'] = D('LiveHost')->get_by_list($dat['list'],'','offender_uid');
    $dat['contract_types'] = D('LiveContractType')->get_all() ?: [];
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