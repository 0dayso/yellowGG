<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/15
 * Time: 20:08
 */

return array(
    'page_menu' => array(
        '/admin_task/index'=>array(
            array('title'=>'我的任务','url'=>"AdminTask/index/")
            //array('title'=>'我的任务','url'=>"{:U('AdminTask/index/')}")
        ),

        '/certificate_car_factory/index/menu/2/type/1'=>array(
            array('title'=>'我的任务','url'=>"AdminTask/index/"),
            array('title'=>'车辆认证','url'=>"CertificateCarFactory/index?menu=2&type=1"),
            array('title'=>'等待审核','url'=>"CertificateCarFactory/index?menu=2&type=1"),
        ),

        '/certificate_video_factory/index/menu/2/type/1'=>array(
            array('title'=>'我的任务','url'=>"AdminTask/index/"),
            array('title'=>'视频认证','url'=>"CertificateVideoFactory/index?menu=2&type=1"),
            array('title'=>'等待审核','url'=>"CertificateVideoFactory/index?menu=2&type=1"),
        ),

        '/accusation_factory/index/menu/2/type/1'=>array(
            array('title'=>'我的任务','url'=>"AdminTask/index/"),
            array('title'=>'举报','url'=>"AccusationFactory/index?menu=2&type=1"),
            array('title'=>'等待处理','url'=>"AccusationFactory/index?menu=2&type=1"),
        ),

        '/task_hall/index'=>array(
            array('title'=>'任务大厅','url'=>"TaskHall/index/"),
        ),

        '/certificate_car_factory/index/menu/1/type/1'=>array(
            array('title'=>'任务大厅','url'=>"TaskHall/index/"),
            array('title'=>'车辆认证','url'=>"CertificateCarFactory/index?menu=1&type=1"),
            array('title'=>'待分配','url'=>"CertificateCarFactory/index?menu=1&type=1"),
        ),

        '/certificate_car_factory/index/menu/1/type/2'=>array(
            array('title'=>'任务大厅','url'=>"TaskHall/index/"),
            array('title'=>'车辆认证','url'=>"CertificateCarFactory/index?menu=1&type=1"),
            array('title'=>'已分配','url'=>"CertificateCarFactory/index?menu=2&type=2"),
        ),

        '/certificate_car_factory/index/menu/1/type/3'=>array(
            array('title'=>'任务大厅','url'=>"TaskHall/index/"),
            array('title'=>'车辆认证','url'=>"CertificateCarFactory/index?menu=1&type=1"),
            array('title'=>'已处理','url'=>"CertificateCarFactory/index?menu=1&type=3"),
        ),

        '/certificate_video_factory/index/menu/1/type/1'=>array(
            array('title'=>'任务大厅','url'=>"TaskHall/index/"),
            array('title'=>'视频认证','url'=>"CertificateVideoFactory/index?menu=1&type=1"),
            array('title'=>'待分配','url'=>"CertificateVideoFactory/index?menu=1&type=1"),
        ),

        '/certificate_video_factory/index/menu/1/type/2'=>array(
            array('title'=>'任务大厅','url'=>"TaskHall/index/"),
            array('title'=>'视频认证','url'=>"CertificateVideoFactory/index?menu=1&type=1"),
            array('title'=>'已分配','url'=>"CertificateVideoFactory/index?menu=1&type=2"),
        ),

        '/certificate_video_factory/index/menu/1/type/3'=>array(
            array('title'=>'任务大厅','url'=>"TaskHall/index/"),
            array('title'=>'视频认证','url'=>"CertificateVideoFactory/index?menu=1&type=1"),
            array('title'=>'已处理','url'=>"CertificateVideoFactory/index?menu=1&type=3"),
        ),

        '/content_manage/index' => array(
            array('title'=>'内容管理','url'=>"ContentManage/index/"),
        ),

        '/content_manage/car_model' => array(
            array('title'=>'内容管理','url'=>"ContentManage/index/"),
            array('title'=>'车辆管理','url'=>"ContentManage/car_model/"),
            array('title'=>'汽车品牌','url'=>"ContentManage/car_model/"),
        ),

        '/content_manage/car_lib' => array(
            array('title'=>'内容管理','url'=>"ContentManage/index/"),
            array('title'=>'车辆管理','url'=>"ContentManage/car_model/"),
            array('title'=>'汽车车型','url'=>"ContentManage/car_lib/"),
        ),

        '/account_manage/index' => array(
            array('title'=>'帐号管理','url'=>"AccountManage/index/"),
        ),

        '/account_manage/account' => array(
            array('title'=>'帐号管理','url'=>"AccountManage/index/"),
            array('title'=>'帐号管理','url'=>"AccountManage/account/"),
            array('title'=>'用户列表','url'=>"AccountManage/account/"),
        ),

        '/test/index' => array(
            array('title'=>'测试','url'=>"Test/index/"),
        ),

        '/test/user_verify_code' => array(
            array('title'=>'测试','url'=>"Test/index/"),
            array('title'=>'验证码管理','url'=>"Test/user_verify_code/"),
            array('title'=>'查看用户验证码','url'=>"Test/user_verify_code/"),
        ),

        '/test/clear_test_data' => array(
            array('title'=>'测试','url'=>"Test/index/"),
            array('title'=>'客服重复测试','url'=>"Test/clear_test_data/"),
            array('title'=>'查看用户验证码','url'=>"Test/clear_test_data/"),
        ),

        '/system_setting/index' => array(
            array('title'=>'后台管理','url'=>"SystemSetting/index/"),
        ),

        '/system_setting/admin' => array(
            array('title'=>'后台管理','url'=>"SystemSetting/index/"),
            array('title'=>'管理员管理','url'=>"SystemSetting/admin/"),
            array('title'=>'管理员','url'=>"SystemSetting/admin/"),
        ),

        '/system_setting/group' => array(
            array('title'=>'后台管理','url'=>"SystemSetting/index/"),
            array('title'=>'管理员管理','url'=>"SystemSetting/admin/"),
            array('title'=>'管理员组','url'=>"SystemSetting/group/"),
        ),

        '/cost_view/view_cost' => array(
            array('title'=>'后台管理','url'=>"SystemSetting/index/"),
            array('title'=>'第三方接口','url'=>"CostView/view_cost/"),
            array('title'=>'短信平台费用查看','url'=>"CostView/view_cost/"),
        ),
    ),

);
