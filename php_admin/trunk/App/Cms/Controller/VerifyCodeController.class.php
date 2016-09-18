<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/17
 * Time: 14:09
 */

namespace Cms\Controller;

use Cms\Controller;
class VerifyCodeController extends PublicController{

    public function index()
    {
        $map = verify_code_search();

        $type = trim($_GET['type']);

        $Phone = D('Phone');
        $itemsPerPage = C('ITEMS_PER_PAGE');

        $count = $Phone->get_count($map);

        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();

        $list = $Phone->lists($map, $Page);

        $this->assign("list", $list);
        $this->assign("page", $show);
        $this->display();
    }
}
