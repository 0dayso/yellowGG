<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/1/29
 * Time: 15:48
 */

namespace Cms\Controller;

use Cms\Controller;
class MassDataTestController extends PublicController{

    public function index()
    {
        $this->display();
    }

    /*******************************视频认证海量数据测试********************************/

    /*
     * 重置视频认证数据
     * todo 1、插入视频认证数据cj_certificate_video,只插一次;
     * todo 2、插入视频认证未分配数据cj_certificate_video_req_unallocated;
     * todo 3、
     * */
    public function reset_certificate_video_data()
    {
        $this->insert_certificate_video_data();
        $this->success('操作成功');
    }


    /*
     * 首次插入10000个用户
     * todo 1、id从10000~20000，只插一次，如果有数据则不插
     * */
    public function insert_user_data()
    {
        $Model = D('User');
        $map   = array('uid'=>array('EQ',10001));
        $item  = $Model->get_single_item($map);
        if($item == null){
            for($i=10001;$i<20001;$i++){
                $uid = $i;

                $dataList[] = array(
                    'uid'=>$uid,
                    'nickname'=>get_rand_char(6),
                    'sex'=>rand(0,1),
                    'video_verify'=>0,
                    'car_verify'=>0,
                );
            }

            $Model->insert_multi_items($dataList);
            $this->success('操作成功');
        }
    }


    /*
     * 首次插入10000条视频认证数据
     * todo 1、id从10000~20000，只插一次，如果有数据则不插
     * */
    public function insert_certificate_video_data()
    {
        $Model = D('CertificateVideo');
        $map   = array('id'=>array('EQ',10001));
        $item  = $Model->get_single_item($map);
        if($item == null){
            for($i=10001;$i<20001;$i++){
                $id  = $i;
                $uid = $i;

                $dataList[] = array(
                    'id'=>$id,
                    'uid'=>$uid,
                    'p1'=>'20150122_1_c50005006_VideoVerify0',
                    'p2'=>'20150122_1_c50005006_VideoVerify1',
                    'p3'=>'20150122_1_c50005006_VideoVerify2',
                    'p4'=>'20150122_1_c50005006_VideoVerify3',
                    'status'=>0,
                    'dtime'=>get_rand_time($t1="2015-02-01 06:06:06",$t2="2015-02-02 06:56:56"),
                    'pass_time'=>'0000-00-00 00:00:00'
                );
            }

            $Model->insert_multi_items($dataList);
            $this->success('操作成功');
        }
    }

    /*
     * 插入10000视频认证数据
     * */
    public function insert_certificate_video_unallocated_req_data()
    {
        $Model = D('CertificateVideoReqUnallocated');
        $map   = array('id'=>array('EQ',10001));
        $item  = $Model->get_single_item($map);
        if($item == null){
            for($i=10001;$i<20001;$i++){
                $id  = $i;
                $certificate_video_id = $i;

                $dataList[] = array(
                    'id'=>$id,
                    'certificate_video_id'=>$certificate_video_id,
                );
            }

            $Model->insert_multi_items($dataList,'test');
            $this->success('操作成功');
        }
    }

    /*******************************车辆认证海量数据测试********************************/
}
