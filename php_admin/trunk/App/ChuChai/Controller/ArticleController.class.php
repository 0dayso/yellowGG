<?php
namespace Chuchai\Controller;
use ChuChai\Model;

class ArticleController extends \Liehuo\Controller\ArticleController
{

  public function __construct()
  {
    parent::__construct();

    $this->navs =
    [
      CONTROLLER_NAME.'/index' => '文章管理',
      CONTROLLER_NAME.'/edit'  => '新增文章',
    ];
  }

  public function view()
  {
    layout(false);
    $id  = (int)$_REQUEST['id'];
    $mod = D(CONTROLLER_NAME);
    $dat = [];
    if($id < 1) $this->error('对象不存在');
    else
    {
      $dat['item'] = $mod->find($id);
      $dat['item'] = $mod->complete_fields($dat['item']);
    }
    $this->data = $dat;
    $this->display();
  }

}