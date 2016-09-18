<?php
namespace Liehuo\Controller;
use Common\Controller\CommonController;

class PublicController extends CommonController
{

    public $page_size;

    public function __construct()
    {
        parent::__construct();

        // 每页显示条数
        $this->page_size = (int)C('ITEMS_PER_PAGE') ?: 50;
        if($pgs = (int)$_REQUEST['page_size']) $this->page_size = $pgs;
        if($_REQUEST['page_size'] == 'export') $this->page_size = 5000;
        $Nav = A('Navigation');
        if($Nav)
        {
          $navPath = $Nav->getNavPath();
          $this->assign("nav_path", $navPath);
        }
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
    public function aliyup($bucket,$name,$resources)
    {
        import('Org.AliyunOss.sdk');
        $Aliyun  = new \ALIOSS();
        return $Aliyun->upload_file_by_file($bucket,$name,$resources);
    }

    /*
     * 删除文件
     * @name        名称
     * @resources   上传资源
     *
     * */
    public function aliydel($bucket,$resources)
    {
        import('Org.AliyunOss.sdk');
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

    // 数据导出
    public function export()
    {
      $dat = $this->data['export'];
      $typ = strtoupper(trim($_REQUEST['download']));
      $typ && $typ = in_array($typ,['XML','CSV','TSV','HTML','JSON','XLSX']) ? $typ : 'XML';
      if($typ)
      {
        if(!$dat) $this->error('没有数据');
        layout(false);
        import('Vendor.SimpleExcel.SimpleExcel');
        $exc = new \SimpleExcel\SimpleExcel();
        $exc->constructWriter($typ);
        foreach($dat ?: [] as $v)
        {
          $exc->writer->addRow($v);
        }
        set_time_limit(0);
        //ob_end_clean();
        $fnm = 'export.'.CONTROLLER_NAME.'.'.ACTION_NAME.'.'.date('Ymd');
        $str = $exc->writer->saveString();
        //$exc->writer->saveFile($fnm);
        header('Content-Type: application/xml');//application/vnd.ms-excel
        //header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename='.$fnm.'.'.strtolower($typ));
        header('Content-Length: '.strlen($str));
        //exit($str);
        foreach(str_split($str,10240) ?: [] as $v)
        {
          echo $v;
          usleep(500);
        }
        ob_end_flush();
        die;
      }
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