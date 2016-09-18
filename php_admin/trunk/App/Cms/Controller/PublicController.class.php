<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/28
 * Time: 18:01
 */

namespace Cms\Controller;

use Think\Controller;
class PublicController extends Controller{

    public function __construct()
    {
        parent::__construct();

        if (!$_SESSION[C('USER_AUTH_KEY')]) {
            $this->redirect('Index/login',array('request'=>$_SERVER['REQUEST_URI']));
        }   

        $Nav = A('Navigation');
        $navPath = $Nav->getNavPath();
        $this->assign("no_car",D('CertificateCarRequest')->where('operation=0')->count());         // 汽车待分配
        $this->assign("no_video",D('CertificateVideoRequest')->where('operation=0')->count());     // 视频待分配
        $this->assign("no_accusation",D('AccusationRequest')->where('operation=0')->count());      // 举报待分配
        $tag = A('Search','Event')->tagfor(1,'yes');
        $this->assign("no_tag",$tag);                             // 标签待分配
        $this->assign("nav_path", $navPath);
        $this->assign("mycount",$this->myparse());



    }

    /*
     * 管理员进行某项操作时查询其是否有权限
     * @input action 操作id,cj_action.id
     * @output 如果有权限，返回admin_id
     *         如果没有权限,返回false
     * */
    public function admin_permission($action)
    {
        $ret = null;
        //$actionArr = $_SESSION['action'];
        $AdminGroupAdmin = D('AdminGroupAdmin');
        $adminHaveAction = $AdminGroupAdmin->get_admin_all_permission($_SESSION['authId']);
        $actionArr = array_column($adminHaveAction,'action_id');
        
        foreach($actionArr as $value){
            if($action == $value)
                return $action;
        }

        return false;
    }

    // 我的任务
    public function myparse(){

        // 分类
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
            'tag'=>A('Search','Event')->mytag('my'),
        );

        return $list;

    }

    //删除php server用户基本信息和所有信息
    public function del_r_user_info(){
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_user_info($uid);
    }

    //删除php server用户 标签 、动态 缓存
    public function del_r_user_tag_surging($usertagid){
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_user_tag($usertagid);
        $phpServer->delete_user_surging($usertagid);
    }


    /*
     * 上传文件
     * @name        名称
     * @resources   上传资源
     *
     * */
    public function aliyup($bucket,$name,$resources){
        require_once("./ThinkPHP/Library/Org/AliyunOss/sdk.class.php");
        $Aliyun  = new \ALIOSS();
        return $Aliyun->upload_file_by_file($bucket,$name,$resources);
    }

    /*
     * 删除文件
     * @name        名称
     * @resources   上传资源
     *
     * */
    public function aliydel($bucket,$resources){
        require_once("./ThinkPHP/Library/Org/AliyunOss/sdk.class.php");
        $Aliyun  = new \ALIOSS();
        return $Aliyun->delete_object($bucket,$resources);
    }

    public function spermission($action)
    {
        $ret = null;
        //$actionArr = $_SESSION['action'];
        $AdminGroupAdmin = D('AdminGroupAdmin');
        $adminHaveAction = $AdminGroupAdmin->get_admin_all_permission($_SESSION['authId']);
        $actionArr = array_column($adminHaveAction,'action_id');

        foreach($actionArr as $value){
            if($action == $value)
                return true;
        }

        $this->error('你没有权限！');
    }


    // HTTP代理
    public function http($url,$data = '',$method = 'GET',$rqhead = array())
    {
      preg_match('/\s*(?<protocol>https?:)\/\/((?:(?<username>[^:@]+)(?::(?<password>[^@]*))?@)?(?<host>(?<domain>[^:\/\\\?#\']+)(?::(?<port>\d+))?))(?<path>\/[^\?#]*)?(?<search>\?[^#]*)?(?<hash>#.*)?\s*/is',$url,$mat);
      $host = $mat['host'];
      is_array($data) && $data = http_build_query($data);
      $header = 'Host: '.$host."\r\n";
      foreach($rqhead as $k => $v)
      {
        if(trim($v)) $header .= $k.': '.$v."\r\n";
      }
      if(in_array($method,array('POST','HEAD','PUT','TRACE','OPTIONS','DELETE')))
      {
        isset($rqhead['Content-Type'])   || $header .= 'Content-Type: application/x-www-form-urlencoded'."\r\n";
        isset($rqhead['Content-Length']) || $header .= 'Content-Length: '.strlen($data)."\r\n";
        $context = array(
          'http' => array(
            'method'  => $method,
            'header'  => $header,
            'content' => $data,
            'timeout' => 6000
          )
        );
      }
      else
      {
        if($data != '') $url .= (stristr($url,'?') ? '&' : '?').$data;
        $context = array(
          'http' => array(
            'method'  => 'GET',
            'header'  => $header,
            'timeout' => 6000
          )
        );
      }
      $stream_context = stream_context_create($context);
      $rb = file_get_contents($url,false,$stream_context);
      return $rb;
    }

}