<?php
namespace Liehuo\Model;

class ResourceModel extends CjDatadwModel
{

  protected $redis_config = 'redis_default';

  const TYPE_TEXT  = 0;//文本
  const TYPE_IMAGE = 1;//图片
  const TYPE_AUDIO = 2;//音频
  const TYPE_VIDEO = 3;//视频
  const TYPE_OTHER = 4;//图文

  public $types = [
    self::TYPE_TEXT  => [
      'type' => '文本',
      'host' => '',
    ],
    self::TYPE_IMAGE => [
      'type' => '图片',
      'host' => 'feed.chujianapp.com',
      'upload' => 'cjfeed.oss-cn-hangzhou.aliyuncs.com',
    ],
    self::TYPE_AUDIO => [
        'type' => '音频',
        'host' => '',
    ],
    self::TYPE_VIDEO => [
      'type' => '视频',
      'host' => 'feed.chujianapp.com',
      'upload' => 'cjfeed.oss-cn-hangzhou.aliyuncs.com',
    ],
  ];

  public function __construct()
  {
    parent::__construct();
    $this->redis_res_del = 'php_resource_delete';//zSet
  }


  /*
  * 格式化资源路径
  * 去掉前缀和后缀
  *   $suf 是否保留后缀
  * */
  public function fmtResourceUrl($url = '',$suf = false)
  {
    $arr = parse_url($url) ?: [];
    $uri = $arr['path'] ?: '';
    if($suf) $uri = preg_replace('/^\s*\/*/i','',$uri);
    else     $uri = preg_replace('/^\s*\/*|@\w+\s*$/i','',$uri);
    return $uri;
  }

  /*
  * 格式化从APP提交过来的相册数据
  *   $withKey 以资源路径作为相册每个元素的key
  * */
  public function fmtNewAlbum($arr = [],$withKey = false)
  {
    $als = [];
    foreach($arr ?: [] as $v)
    {
      $row = $this->fmtNewResource($v);
      if($row)
      {
        $als[$row['resource']] = $row;
      }
    }
    return ($withKey ? $als : array_values($als)) ?: [];
  }

  /*
  * 格式化相册内的单个资源
  * */
  public function fmtNewResource($res = [])
  {
    $row = [];
    if(is_string($res) && $res)
    {
      $row = ['resource' => $res];
    }
    elseif(is_array($res) && $res['resource'])
    {
        $row = $res;
    }
    if($row)
    {
      $row = array_merge([
        'resource' => '',
        'thumb'    => '',
        'text'     => '',
        'type'     => 1,
      ],$row);
      $row['resource'] = $this->fmtResourceUrl($row['resource']);
      $row['thumb'] && $row['thumb'] = $this->fmtResourceUrl($row['thumb']);
    }
    return $row;
  }

  // 获取相册中视频的数量
  public function get_video_num($arr = [])
  {
    $vls = array_filter($arr ?: [],function($v)
    {
      return is_array($v) && $v['type'] == '3';
    }) ?: [];
    return count($vls);
  }

  // 视频加权
  public function scoring_video($sco = 0,$num = 0)
  {
    return D('RpcUser')->add_go_list('video',
    [
      'uid'         => (int)$this->uid,
      'num'         => (int)$num,
      'score'       => round($sco,2),
      'update_time' => time(),
    ]);
  }

  public function make_file_name($filename = '',$dir = '')
  {
    $ext = strrchr($filename,'.');
    $path = date('Ymd').'/';
    $dir && $path = $dir.'/'.$path;
    $path .= md5(uniqid(rand(),true)).$ext;
    return $path;
  }

  /*
   * 获取OSS对象
   */
  public function get_alyoss()
  {
    $dir = APP_PATH.'Library/oss/2.0.6/';
    include_once $dir.'autoload.php';
    $oss = new \OSS\OssClient('oqAC1oGBCiwXWW41','69EDRSyJNYgEJXRZbboA9VLmAFuksm','oss-cn-hangzhou.aliyuncs.com');
    return $oss;
    //$oss->uploadFile($bucket,$object,$filePath,$options);
    //$oss->deleteObject($bucket,$object);
  }

  // 上传文件至阿里云
  public function oss_upload($bucket,$file,$key = true)
  {
    $ret = false;
    if(!$file);
    else
    {
      $key === true && $key = $this->make_file_name();
      $oss = $this->get_alyoss();
      try
      {
        $oss->uploadFile($bucket,$key,$file);
        $ret = $key;
      }
      catch(\OSS\Core\OssException $e)
      {
        $this->error = $e->getMessage();
      }
    }
    return $ret;
  }

}