<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/8
 * Time: 19:14
 */

namespace Cms\Controller;

use Cms\Controller;
class AdminLogController extends PublicController{

    /*
     * 默认操作日志的主页面是视频认证
     * */
    public function index()
    {
        $map = certificate_video_log_search();

        $Log = D('CertificateVideoLog');
        $count = $Log->get_count($map);

        $itemsPerPage = C('ITEMS_PER_PAGE');
        import("THINK.Page");
        $Page = new \Think\Page($count,$itemsPerPage);
        $show = $Page->show();

        $list = $Log->lists($map,$Page);

        $this->assign("page",$show);
        $this->assign("list",$list);
        $this->display();
    }


    /*
     * 删除管理员操作记录
     * TODO 操作记录不可以删，但是可以加一个hidden字段，
     * TODO 删除操作让hidden=true，对管理员隐藏,批量的
     * TODO 记录可以以sql文件形式转移到磁盘
     * */
    public function delete_video_log()
    {
        $permission = $this->admin_permission(C('ACTION_DELETE_VIDEO_LOG'));
        if($permission){
            $data = $_GET;
            $Log  = D('CertificateVideoLog');
            $Log->where('id='.$data['id'])->delete();
            $this->redirect('AdminLog/index');
        }
        else{
            $this->error('您没有权限删除记录');
        }
    }

    /*
     * 显示车辆认证操作记录
     * */
    public function car()
    {
        $map = certificate_car_log_search();
        $Log = D('CertificateCarLog');
        $count = $Log->get_count($map);

        $itemsPerPage = C('ITEMS_PER_PAGE');
        import("THINK.Page");
        $Page = new \Think\Page($count,$itemsPerPage);
        $show = $Page->show();

        $list = $Log->lists($map,$Page);

        $this->assign("list",$list);
        $this->assign("page",$show);
        $this->display();
    }

    /*
     * 删除管理员操作记录
     * TODO 操作记录不可以删，但是可以加一个hidden字段，
     * TODO 删除操作让hidden=true，对管理员隐藏,批量的
     * TODO 记录可以以sql文件形式转移到磁盘
     * */
    public function delete_car_log()
    {
        $permission = $this->admin_permission(C('ACTION_DELETE_CAR_LOG'));
        if($permission){
            $data = $_GET;
            $Log  = D('CertificateCarLog');
            $Log->where('id='.$data['id'])->delete();
            $this->redirect('AdminLog/car');
        }
        else{
            $this->error('您没有权限删除记录');
        }
    }
}
