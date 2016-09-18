<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/30
 * Time: 14:03
 */

namespace Cms\Controller;

use Cms\Controller;
class TestController extends PublicController{

    public function index()
    {
        $this->display();
    }

    public function user_verify_code()
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
        $list = unixToTime($list,'dtime');

        $this->assign("list", $list);
        $this->assign("page", $show);
        $this->display();
    }

    public function clear_test_data()
    {
        $this->display();
    }

    /*
     * 重置车辆认证测试数据，这里要注意的是不要清理log的数据
     * */
    public function clear_certificate_car_data()
    {
        if(C('RESET_PERMISSION') !== true)
            $this->error('现阶段不允许重置数据');

        $admin_id = $this->admin_permission(C('ACTION_RESET_CERTIFICATE_CAR_TEST_DATA'));
        if(!$admin_id)
            $this->error('没有权限');

        $Car = D('CertificateCar');
        $Req = D('CertificateCarRequest');
        $Log = D('CertificateCarRequestLog');
        $Car->reset_test_data();
        $Req->reset_test_data();
        $Log->reset_test_data();
        $this->success('操作成功');
    }

    /*
     * 重置测试视频认证测试数据，这里要注意的是不要清理log的数据
     * */
    public function clear_certificate_video_data()
    {
        if(C('RESET_PERMISSION') !== true)
            $this->error('现阶段不允许重置数据');

        $admin_id = $this->admin_permission(C('ACTION_RESET_CERTIFICATE_VIDEO_TEST_DATA'));
        if(!$admin_id)
            $this->error('没有权限');

        $Video = D('CertificateVideo');
        $Req   = D('CertificateVideoRequest');
        $Log   = D('CertificateVideoRequestLog');
        $Video->reset_test_data();
        $Req->reset_test_data();
        $Log->reset_test_data();

        $this->success('操作成功');
    }

    /*
     * 重置举报数据，内涵封禁帐号
     * */
    public function clear_accusation_data()
    {
        if(C('RESET_PERMISSION') !== true)
            $this->error('现阶段不允许重置数据');

        $admin_id = $this->admin_permission(C('ACTION_RESET_ACCUSATION_TEST_DATA'));
        if(!$admin_id)
            $this->error('没有权限');

        $Report   = D('Report');
        $Req      = D('AccusationRequest');
        $Log      = D('AccusationRequestLog');
        $User     = D('User');
        $Location = D('Location');

        $Report->reset_test_data();
        $Req->reset_test_data();
        $Log->reset_test_data();
        $User->reset_test_data();
        $Location->reset_test_data();

        $this->success('操作成功');
    }

    /*
     * 增添几个举报数据，复现同一个被举报人被举报多次的情况下的bug
     * */
    public function insert_items_for_same_offender()
    {
        if(C('RESET_PERMISSION') !== true)
            $this->error('现阶段不允许重置数据');

        $admin_id = $this->admin_permission(C('ACTION_RESET_ACCUSATION_TEST_DATA'));
        if(!$admin_id)
            $this->error('没有权限');

        $Report   = D('Report');
        $Req      = D('AccusationRequest');

        $Report->insert_items_for_same_offender();
        $Req->insert_items_for_same_offender();

        $this->success('操作成功');
    }

    /*
     * 重置文字审查数据
     * */
    public function clear_modify_user_info_data()
    {
        if(C('RESET_PERMISSION') !== true)
            $this->error('现阶段不允许重置数据');

        $admin_id = $this->admin_permission(C('ACTION_RESET_MODIFY_USER_INFO_TEST_DATA'));
        if(!$admin_id)
            $this->error('没有权限');

        $Request   = D('UserInfoModifyRequest');
        $Log       = D('UserInfoModifyRequestLog');

        $Request->reset_test_data();
        $Log->reset_test_data();

        $this->success('操作成功');
    }

    /*
     * 重置用户反馈测试数据
     * */
    public function clear_feedback_data()
    {
        if(C('RESET_PERMISSION') !== true)
            $this->error('现阶段不允许重置数据');

        $admin_id = $this->admin_permission(C('ACTION_RESET_FEEDBACK_TEST_DATA'));
        if(!$admin_id)
            $this->error('没有权限');

        $Product      = D('Product');
        $FeedbackData = D('FeedbackData');

        $Product->reset_test_data();
        $FeedbackData->reset_test_data();

        $this->success('操作成功');
    }

    /*
     * redis删除用户token测试
     * */
    public function delete_user_token_test()
    {
        $Redis = D('PhpServerRedis');
        $Redis->create_auth_token_and_delete_test(50001);
        $this->success('操作成功');
    }

    /*
     * 往redis的list插入数据测试
     * */
    public function redis_list_push_test()
    {
        $Redis = D('ImServerRedis');
        $Redis->list_push_test(C('REDIS_ADMIN_LIST_KEY'));

        $this->success('操作成功');
    }

    /*
     * protocol buffer测试
     * */
    public function protocol_buffer_test()
    {
        $Pro = new ProtocolBufferController();
        $Pro->test();
        $this->success('操作成功');
    }

    /*
     * 从IM服务器获取数据测试
     * */
    public function get_im_server_data_test()
    {
        $Product = D('Product');
        $Product->get_im_server_data_test();

        $this->success('操作成功');
    }

    /*
     * 从IM服务器获取几天的聊天信息
     * */
    public function get_chat_log_test()
    {
        $Chat = D('ChatLog');
        $map  = array(
            'sender'=>array('EQ',50031),
            'recver'=>array('EQ',50031),
        );
        $Chat->get_chat_log($map,2);//获取前天的聊天信息
        $Chat->get_multi_chat_log($map,2);//获取前天到今天的聊天信息

        $this->success('操作成功');
    }

    public function temp_test()
    {
        $bucket = C('ALIYUN_HEADIMG_BUCKET');
        $keys   = array('head_img_376fc957a7750890b5a6e00ac1e52458','head_img_3d2fc89136f5748b0002acf5226910f5');
        $Ali = new AliyunController();
        $Ali->delete_multi_files($keys,$bucket);

        $this->success('操作成功');
    }

    public function send_system_message_test()
    {
        $Message = new MessageController();
        $Message->send_system_message(12,'certificate_video','pass','');

        $this->success('操作成功');
    }

    public function ajax_test()
    {

    }

}
