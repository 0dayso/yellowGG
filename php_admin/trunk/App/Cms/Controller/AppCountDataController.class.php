<?php
/**
 * Created by PhpStorm.
 * User: Anl
 * Date: 2015/8/17
 * Time: 16:00
 */

namespace Cms\Controller;

class AppCountDataController extends PublicController
{

  public function index()
  {
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    $dat['list'] = $mod->plist(C('ITEMS_PER_PAGE') ?: 20)->lists();//C('ITEMS_PER_PAGE')
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

}