<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/30
 * Time: 11:39
 */

namespace Cms\Controller;

use Cms\Controller;
class AccountManageController extends PublicController{

    public function index()
    {
        $this->display();
    }

    /*
     * 用户列表
     *
     *
     * */
    public function account()
    {
        $map = user_search();
        $type = '1';//trim($_GET['type']);

        $User = D('User');
        $itemsPerPage = C('ITEMS_PER_PAGE');

        $count = $User->get_count($type, $map);

        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();

        $list = $User->lists($type, $map, $Page);
        $list = $this->process_lists($list);

        $this->assign("type", $type);
        $this->assign("list", $list);
        $this->assign("page", $show);
        $this->display();
    }

    /*
     * 显示用户信息
     * description：显示的内容包含4个部分
     *              1、用户个人资料cj_user
     *              2、用户的两个认证操作记录cj_certificate_car_log&cj_certificate_video_log
     *              3、用户的头像cj_headimg
     *              4、用户的认证照cj_certificate_video&cj_certificate_car
     * TODO 现在的图片-头像、认证照都存放在本地，格式都是自定义的。
     * TODO 后面需要将这些图片存放到七牛云存储上，图片格式都会转换成key值，不需要再对path进行处理
     * */
    public function info()
    {
        $keyword = trim($_REQUEST['uid']);
        $map['uid'] = array('EQ',$keyword);
        $User = D('User');
        $userInfo = $User->get_single_item($map);

        $CertificateVideoLog = D('CertificateVideoLog');
        $videoLog = $CertificateVideoLog->get_items_with_uid($keyword);

        $CertificateCarLog = D('CertificateCarLog');
        $carLog = $CertificateCarLog->get_items_with_uid($keyword);

        $CertificateVideo = D('CertificateVideo');
        $cVideo = $CertificateVideo->get_single_item($map);
        $cVideo = $CertificateVideo->process_img($cVideo);

        $CertificateCar = D('CertificateCar');
        $cCar   = $CertificateCar->get_single_item($map);
        $cCar   = $CertificateCar->process_img($cCar);

        $Head    = D('Headimg');
        $Qiniu   = A('Qiniu');
        $headImg = $Head->get_single_item($map);
        $headImg = json_to_array($headImg['path']);
        $headImg = $Qiniu->get_img_preview_path($headImg,200,200);
        $head    = $this->show_head_img($headImg);
        if (count($head) > 1) {
            $this->assign("head1", $head[0]);
            $this->assign("head2", $head[1]);
        } else {
            $this->assign("head1", $head[0]);
        }

        $this->assign("user", $userInfo);
        $this->assign("car", $carLog);
        $this->assign("video", $videoLog);
        $this->assign("ccar", $cCar);
        $this->assign("cvideo", $cVideo);

        $this->display();
    }

    protected function process_lists($list)
    {
        $ret = array();

        foreach($list as $value){
            switch($value['video_verify']){
                case '-1':
                    $value['video_verify'] = '未提交视频认证照';
                    break;
                case '0':
                    $value['video_verify'] = '等待视频审核';
                    break;
                case '1':
                    $value['video_verify'] = '通过视频认证';
                    break;
                case '2':
                    $value['video_verify'] = '视频认证失败';
                    break;
                default:
                    break;
            }

            switch($value['car_verify']){
                case '-1':
                    $value['car_verify'] = '未提交车辆认证照';
                    break;
                case '0':
                    $value['car_verify'] = '等待车辆审核';
                    break;
                case '1':
                    $value['car_verify'] = '通过车辆认证';
                    break;
                case '2':
                    $value['car_verify'] = '车辆认证失败';
                    break;
            }

            if($value['sex'] == 0)
                $value['sex'] = '男';
            else
                $value['sex'] = '女';

            $value['reg_time'] = date('Y-m-d H:i:s',$value['reg_time']);

            array_push($ret,$value);
        }

        return $ret;
    }

    /*
     * 在用户信息显示界面删除用户头像
     * */
    public function delete_head_img()
    {
        $uid = $_GET['uid'];
        $picArr = array_keys($_POST);
        $Headimg = A('Headimg');
        $Headimg->delete_head_img($picArr,$uid,2);//管理员删除
        $this->redirect("AccountManage/info",array('uid'=>$uid));
    }

    //添加用户
    public function add()
    {
        if (!empty($_GET['uid'])) {
            $uid = $_GET['uid'];
        } else {
            $uid = 0;
        }
        $this->assign("uid", $uid);
        $this->display();
    }

    //上传用户头像到七牛云服务器,键值用系统的随机值
    //获取系统键值，在cj_headimg内将键值与uid对应起来
    public function upload_head_img()
    {
        $uid   = $_GET['uid'];
        $Qiniu = A('Qiniu');
        $info  = $Qiniu->upload_head_img($uid,$_FILES['file']['tmp_name']);

        //如果有勾选图片则在数据库及云服务器删除相关数据
        if(!empty($_POST)) {
            $data = array_keys($_POST);
            $Headimg    = new HeadimgController();
            $Headimg->delete_head_img($data,$uid,1);//批量添加用户时删除的头片都当作是用户删除
        }

        //如果有上传头像则保存数据表cj_headimg.path
        if ($info){
            $Headimg = D('Headimg');
            $Headimg->insert_single_path($uid,$info);
            $HeadimgLog = D('HeadimgLog');
            $HeadimgLog->insert_single_item(array('uid'=>$uid,'path'=>$info,'dtime'=>time(),'status'=>0));
        }

        $this->redirect('AccountManage/show_new_user', array('uid' => $uid));
    }

    //添加用户确认
    public function confirm()
    {
        $User         = D('User');
        $UserBatchAdd = D('UserBatchAdd');
        $Headimg      = D('Headimg');//在创建一个用户的时候在cj_headimg添加一条记录
        $data = $_GET;

        if ((!isset($data['nickname'])) || ($data['nickname'] == ''))
            $this->error('没有填写昵称');
        elseif ((!isset($data['birthday'])) || ($data['birthday'] == ''))
            $this->error('没有填写生日');
        elseif ((!isset($data['phone'])) || ($data['phone'] == ''))
            $this->error('没有填写电话');
        elseif ((!isset($data['password'])) || ($data['password'] == ''))
            $this->error('没有填写密码');
        else {

        }

        $data['password'] = md5_password($data['password']);
        $data['constellation'] = get_constellation($data['birthday']);
        $data['reg_time'] = time();

        if ($data['type'] == 'add') {
            unset($data['type']);
            $arr = [];
            $lastId = $User->add($data);
            $UserBatchAdd->add(array('uid' => $lastId));
            $Headimg->insert_single_item(array('uid'=>$lastId,'path'=>array_to_json($arr),'status'=>0));
            $this->redirect('AccountManage/show_new_user', array('uid' => $lastId));
        } elseif ($data['type'] == 'edit') {
            unset($data['type']);
            $User->data($data)->where('uid=' . $data['uid'])->save();
            $this->redirect('AccountManage/show_new_user', array('uid' => $data['uid']));
        } else {
            $this->error('url错误');
        }

    }

    public function show_new_user()
    {
        $uid        = $_GET['uid'];
        $Batch      = D('UserBatchAdd');
        $map['uid'] = array('EQ',$uid);
        if (count($Batch->get_single_item($map)) == 0)
            $this->error('禁止访问');

        $User = D('User');
        $list = $User->get_single_item($map);

        $Headimg = D('Headimg');
        $headStr = $Headimg->get_single_item($map);
        $headImg = json_to_array($headStr['path']);
        $Qiniu   = A('Qiniu');
        $preview = $Qiniu->get_img_preview_path($headImg,200,200);

        $this->assign("head",$preview);
        $this->assign("list", $list);
        $this->display();
    }

    //一组图片超过8张则只显示8张,分两组输出
    protected function show_head_img($userImg=array())
    {
        $ret = array();
        $cnt=count($userImg);
        if ($cnt>4) {
            $ret[0] = array_slice($userImg, 0, 4);
            $ret[1] = array_slice($userImg, 4, $cnt - 4);
        } else
            $ret[0] = $userImg;

        return $ret;
    }
}
