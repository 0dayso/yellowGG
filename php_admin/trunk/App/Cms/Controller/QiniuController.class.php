<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/12
 * Time: 11:31
 */

namespace Cms\Controller;

use Think\Controller;

require_once("./ThinkPHP/Library/Org/Qiniu/io.php");//require_once
require_once("./ThinkPHP/Library/Org/Qiniu/rs.php");
require_once("./ThinkPHP/Library/Org/Qiniu/fop.php");
class QiniuController extends Controller{

    public $bucket      = '';
    public $secretKey   = '';
    public $accessKey   = '';
    public $domain      = '';

    public function __construct()
    {
        parent::__construct();
        $this->bucket    = C('QINIU_BUCKET_NAME');
        $this->secretKey = C('QINIU_SECRET_KEY');
        $this->accessKey = C('QINIU_ACCESS_KEY');
        $this->domain    = C('QINIU_DOMAIN_URL');
    }

    public function get_up_token()
    {
        Qiniu_SetKeys($this->accessKey, $this->secretKey);
        $putPolicy = new \Qiniu_RS_PutPolicy($this->bucket);
        $upToken = $putPolicy->Token(null);

        return $upToken;
    }

    //上传用户头像到七牛云服务器
    public function upload_head_img($uid,$file)
    {
        $putExtra = new \Qiniu_PutExtra();
        $putExtra->Crc32 = 1;
        $temp = explode('\\',$file);
        $end  = end($temp);
        $number = rand(10000,90000);
        $time = time();
        //$key = 'head_img_'.$uid.'_'.md5($end);
        $key = 'head_img_'.md5($end.$number.$time);
        list($ret, $err) = Qiniu_PutFile($this->get_up_token(), $key, $file, $putExtra);
        if(empty($ret))
            return false;
        else
            return $ret['key'];
    }

    /*
     * 根据key值获取七牛上图片路径,生成图片预览
     * @input key string
     * @output href string
     * */
    public function get_path($key,$width,$height)
    {
        Qiniu_SetKeys($this->accessKey, $this->secretKey);

        //生成baseUrl
        $baseUrl = Qiniu_RS_MakeBaseUrl($this->domain, $key);

        //生成fopUrl
        $imgView         = new \Qiniu_ImageView;
        $imgView->Mode   = 1;
        $imgView->Width  = $width;//120;
        $imgView->Height = $height;//120;
        $imgViewUrl      = $imgView->MakeRequest($baseUrl);

        //对fopUrl 进行签名，生成privateUrl。 公有bucket 此步可以省去。
        $getPolicy = new \Qiniu_RS_GetPolicy();
        $imgViewPrivateUrl = $getPolicy->MakeRequest($imgViewUrl, null);

        return $imgViewPrivateUrl;
    }

    /*
     * 获取多个图片的七牛预览路径
     * @param img array
     * @param width   图片预览尺寸:宽度
     * @param height  图片预览尺寸:长度
     * @return ret array('key'=>$key,'path'=>$previewPath)
     * */
    public function get_img_preview_path($img=array(),$width,$height)
    {
        $ret = array();
        foreach($img as $value){
            if($value){
                $path = $this->get_path($value,$width,$height);
                array_push($ret,array('key'=>$value,'path'=>$path));
            }
        }

        return $ret;
    }

    //删除单个文件，包含图片
    public function delete_single_file($key)
    {
        Qiniu_SetKeys($this->accessKey, $this->secretKey);

        $client = new \Qiniu_MacHttpClient(null);

        $err = Qiniu_RS_Delete($client, $this->bucket, $key);
        if ($err !== null){
            //$this->error('删除云图出错');
            return array(
                'log'=>array_to_json($err),
            );
        }
        else{
            return null;
        }
    }

    //删除多个文件
    public function delete_multi_files($keyArr)
    {
        $entryPath = array();
        Qiniu_SetKeys($this->accessKey, $this->secretKey);

        $client = new \Qiniu_MacHttpClient(null);
        foreach($keyArr as $value)
        {
            $temp = array('bucket'=>$this->bucket,'key'=>$value);
            $temp = (object)$temp;
            array_push($entryPath,$temp);
        }
        list($ret,$err) = Qiniu_RS_BatchDelete($client, $entryPath);
        if ($err !== null){
            //$this->error('删除云图组出错');
            return array(
                'log'=>array_to_json($err),
            );
        }
        else{
            return null;
        }

    }
}
