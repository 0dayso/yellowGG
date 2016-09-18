<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/25
 * Time: 11:49
 */

namespace Cms\Controller;

use Cms\Controller;
class AdminTaskController extends PublicController{

    /*
     * 个人任务的主界面
     * 视频认证，车辆认证，举报三个模块的任务总量显示
     * */
    public function index()
    {
        $CertificateCar          = D('CertificateCarRequest');
        $certificateCarCount     = $CertificateCar->admin_task_count();

        $CertificateVideo        = D('CertificateVideoRequest');
        $certificateVideoCount   = $CertificateVideo->admin_task_count();

        $Accusation              = D('AccusationRequest');
        $accusationCount         = $Accusation->admin_task_count();

        $list = array(
            'certificate_car_count'=>$certificateCarCount,
            'certificate_video_count'=>$certificateVideoCount,
            'accusation_count'=>$accusationCount,
        );

        $this->assign("list",$list);
        $this->display();
    }

    /************************************************意见反馈***********************************************************/

    /*
     * 意见反馈,根据请求值显示原始图片等信息
     * */
    public function feedback()
    {
        $map          = array();
        $table        = $_SESSION['table']['admin_feedback_request_table'];
        $Req          = D('FeedbackReqAid');
        $itemsPerPage = C('ITEMS_PER_PAGE');

        $count = $Req->admin_task_count($map,$table);

        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();

        $ret = $Req->admin_task_list($map,$table,$Page);

        return array(
            'list'=>$ret,
            'page'=>$show,
        );
    }
}
