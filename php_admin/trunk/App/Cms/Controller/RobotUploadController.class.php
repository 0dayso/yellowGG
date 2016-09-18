<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/20
 * Time: 11:33
 */

namespace Cms\Controller;

use Cms\Controller;
class RobotUploadController extends PublicController{

    public function index()
    {
        $User = D('User');
        $map  = array();
        $map['uid'] = array('EGT',C('ROBOT_ACCOUNT_UID_MIN'));
        $map['uid'] = array('ELT',C('ROBOT_ACCOUNT_UID_MAX'));
        $list = $User->where($map)->field('uid,nickname')->select();
        $this->assign("list",$list);
        $this->display();
    }

    public function multi_upload_img()
    {
        $pic = $_POST['pic'];
        $uid = $_POST['uid'];
        if($uid == '请选择用户')
            $this->error('请选择用户昵称');

        $files = $_FILES['file'];
        if($files['name'][0]=='')
            $this->error('请选取图片');

        switch($pic){
            case '1'://头像相册及视频认证照
                $files = $this->process_file_array($uid,$files,$pic);
                $this->upload_head_img($files['head']);
                $this->upload_head_img($files['certificate_video']);
                //$this->upload_certificate_img($files['certificate_video']);
                foreach($files['certificate_video'] as $value){
                    $files['head'][] = $value;
                }
                $this->save_user_head_image($uid,$files['head']);
                //$this->save_headimg_json($uid,$files['head']);
                //$this->save_certificate_video($uid,$files['certificate_video']);
                break;
            case '2'://车辆认证照
                $files = $this->process_file_array($uid,$files,$pic);
                $this->upload_certificate_img($files['certificate_car']);
                $this->save_certificate_car($uid,$files['certificate_car']);
                break;
            default:
                break;
        }

        $this->redirect('RobotUpload/index');
    }

    /*
     * 将文件数组处理成我们可用的形式
     *
     * */
    protected function process_file_array($uid,$list,$type)
    {
        $ret = array('head'=>array(),'certificate_car'=>array(),'certificate_video'=>array());
        $len = count($list['name']);
        for($i=0;$i<$len;$i++){
            $temp = array(
                'name'=>$list['name'][$i],
                'type'=>$list['type'][$i],
                'location'=>$list['tmp_name'][$i],
                'error'=>$list['error'][$i],
                'size'=>$list['size'][$i],
            );
            if($temp['error']>0){
                $this->error('图片加载出错，请重新加载');
            }
            else{
                if($type == '1'){
                    if(($i+1 == 1)||($i+1>2)){//第一张是头像
                        //todo 修改上传图片时生成路径的方式
                        $temp['key'] = create_cloud_img_path($uid);
                        //$temp['key'] = 'head_img_'.md5($list['name'][$i].microtime());

                        array_push($ret['head'],$temp);
                    }
                    else//第二张是认证照
                    {
                        $temp['key'] = create_cloud_img_path($uid);
                        //$temp['key'] = 'certificate_video_'.md5($list['name'][$i].microtime());
                        array_push($ret['certificate_video'],$temp);
                    }
                }
                else{
                    $temp['key'] = create_cloud_img_path($uid);
                    //$temp['key'] = 'certificate_car_'.md5($list['name'][$i].microtime());
                    array_push($ret['certificate_car'],$temp);
                }
            }
        }

        return $ret;
    }

    public function upload_head_img($files)
    {
        $bucket = C('ALIYUN_HEADIMG_BUCKET');
        $Aliyun = new AliyunController();
        $Aliyun->upload_multi_files($files,$bucket);
    }

    public function upload_certificate_img($files)
    {
        $bucket = C('ALIYUN_CERTIFICATE_BUCKET');
        $Aliyun = new AliyunController();
        $Aliyun->upload_multi_files($files,$bucket);
    }

    public function save_headimg_json($uid,$picArr)
    {
        $temp = array();
        foreach($picArr as $value){
            array_push($temp,$value['key']);
        }
        $json = array_to_json($temp);
        $map  = array('uid'=>array('EQ',$uid));
        $data = array('uid'=>$uid,'path'=>$json);
        $Headimg = D('Headimg');
        $item = $Headimg->get_single_item($map);
        if($item == false){
            $Headimg->insert_single_item($data);
        }
        else{
            $Headimg->update_single_item($map,$data);
        }
    }

    public function save_user_head_image($uid,$picArr)
    {
        $temp = array();
        $Request = D('HeadImageModifyRequest');
        foreach($picArr as $value){
            array_push($temp,$value['key']);
            $Request->insert_single_item(array(
                'uid'=>$uid,
                'image'=>$value['key'],
                'sub_time'=>date('Y-m-d H:i:s',time()),
            ));
        }
        $data = array('head_images'=>array_to_json($temp));
        $User = D('User');
        $User->update_single_item('uid='.$uid,$data);
    }

    public function save_certificate_video($uid,$picArr)
    {
        if(count($picArr)>0){
            $temp = current($picArr);
            $data = array(
                'uid'=>$uid,
                'p1'=>$temp['key'],
                'p2'=>$temp['key'],
                'p3'=>$temp['key'],
                'p4'=>$temp['key'],
                'status'=> 0,
                'dtime'=>date('Y-m-d H:i:s',time()),
            );

            $Model = D('CertificateVideo');
            $newId = $Model->insert_single_item($data);

            $Req   = D('CertificateVideoReqUnallocated');
            $Req->insert_single_item(array('certificate_video_id'=>$newId));
        }
    }

    public function save_certificate_car($uid,$picArr)
    {
        if(count($picArr)>0){
            $temp = current($picArr);
            $data = array(
                'uid'=>$uid,
                'p1'=>$temp['key'],
                'p2'=>$temp['key'],
                'status'=> 0,
                'dtime'=>date('Y-m-d H:i:s',time()),
            );

            $Model = D('CertificateCar');
            $newId = $Model->insert_single_item($data);

            $Req   = D('CertificateCarReqUnallocated');
            $Req->insert_single_item(array('certificate_car_id'=>$newId));
        }
    }
}
