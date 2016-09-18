<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/13
 * Time: 11:12
 */

namespace Cms\Controller;

use Think\Controller;

require_once("./ThinkPHP/Library/Org/AliyunOss/sdk.class.php");
class AliyunController extends Controller{
    public $object = null;

    public function __construct()
    {
        parent::__construct();
        $this->object = new \ALIOSS();
        $this->object->set_debug_mode(FALSE);//设置是否打开curl调试模式
    }

    /*
     * 上传单个文件
     * @param file location must
     *             key      must
     * @return error
     *         status 200 OK
     * */
    public function upload_single_file($file,$bucket)
    {
        $object = $this->object;
        $path   = $file['location'];
        $key    = $file['key'];
        $error  = $object->upload_file_by_file($bucket,$key,$path);

        return $error;
    }

    /*
     * 上传多个文件
     * @param  files array(array(location,key...))
     *               location and key required
     * @return error
     * */
    public function upload_multi_files($files,$bucket)
    {
        $error  = true;
        $object = $this->object;
        foreach($files as $value){
            $path   = $value['location'];
            $key    = $value['key'];
            $error  = $object->upload_file_by_file($bucket,$key,$path);
        }

        return $error;
    }

    public function download_single_file($name)
    {

    }

    /*
     * 删除单个文件
     * @param  file 阿里云key值
     * @param  file bucket
     *         test head_img_01c499910e229b0a9a958fb4785f8a5d
     * */
    public function delete_single_file($file,$bucket)
    {
        $object = $this->object;
        $error  = $object->delete_object($bucket,$file);

        return $error;
    }

    /*
     * 一次删除多个文件
     * @param files array('key1','key2',...)
     * @param bucket bucket name
     * @return option quiet=false status 200
     *         option quiet=true  status 204
     * */
    public function delete_multi_files($files=array(),$bucket)
    {
        $object = $this->object;
        $options = array(
            'quiet' => false,//false status 200;true status 204;
            //ALIOSS::OSS_CONTENT_TYPE => 'text/xml',
        );
        $error  = $object->delete_objects($bucket,$files,$options);

        return $error;
    }
}
