<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/25
 * Time: 12:33
 */

namespace Cms\Controller;

use Cms\Controller;
class TaskHallController extends PublicController{

    //显示管理主页面
    public function index()
    {
        $CertificateCar          = D('CertificateCarRequest');
        $certificateCarCount     = $CertificateCar->task_hall_count(array('operation'=>array('EQ',0)));

        $CertificateVideo        = D('CertificateVideoRequest');
        $certificateVideoCount   = $CertificateVideo->task_hall_count(array('operation'=>array('EQ',0)));

        $Accusation              = D('AccusationRequest');
        $accusationCount         = $Accusation->task_hall_count(array('operation'=>array('EQ',0)));

        $list = array(
            'certificate_car_count'=>$certificateCarCount,
            'certificate_video_count'=>$certificateVideoCount,
            'accusation_count'=>$accusationCount,
        );

        $this->assign("list",$list);
        $this->display();
    }

    /******************************************************用户反馈**********************************************/
    /*
     * 用户反馈总需求
     * */
    public function feedback($type)
    {
        $ret = array();
        switch($type)
        {
            case 'unallocated':
                $ret = $this->feedback_unallocated();
                break;
            case 'allocated':
                $ret = $this->feedback_allocated();
                break;
            case 'processed':
                $ret = $this->feedback_processed();
                break;
            default:
                break;
        }

        return $ret;
    }

    /*
     * 未分配举报业务
     * */
    public function feedback_unallocated()
    {
        $map   = array();
        $Req   = D('Product');
        $itemsPerPage = C('ITEMS_PER_PAGE');

        $count = $Req->task_hall_unallocated_count($map);

        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();

        $ret = $Req->task_hall_unallocated_list($map, $Page);

        return array(
            'list'=>$ret,
            'page'=>$show,
        );
    }

    /*
     * 已分配用户反馈业务
     * */
    public function feedback_allocated()
    {
        $map   = array();
        $Req   = D('FeedbackReqAllocated');
        $itemsPerPage = C('ITEMS_PER_PAGE');

        $count = $Req->task_hall_count($map);

        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();

        $ret = $Req->task_hall_list($map, $Page);

        return array(
            'list'=>$ret,
            'page'=>$show,
        );
    }

    /*
     * 已处理用户反馈业务
     * */
    public function feedback_processed()
    {
        $map   = array();
        $Req   = D('FeedbackReqProcessed');
        $itemsPerPage = C('ITEMS_PER_PAGE');

        $count = $Req->task_hall_count($map);

        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();

        $ret = $Req->task_hall_list($map, $Page);

        return array(
            'list'=>$ret,
            'page'=>$show,
        );
    }
}
