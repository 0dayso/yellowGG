<?php
namespace ChuChai\Controller;
use Common\Controller\CommonController;

class PublicController extends CommonController
{

  public function __construct()
  {
    parent::__construct();

    // 每页显示条数
    $this->page_size = (int)C('ITEMS_PER_PAGE') ?: 50;
    if($pgs = (int)$_REQUEST['page_size']) $this->page_size = $pgs;
    if($_REQUEST['page_size'] == 'export') $this->page_size = 5000;
  }

}