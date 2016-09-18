<?php
namespace Scoring\Controller;
use Think\Controller;

class PublicController extends Controller
{

  public $auth_key;

  public function __construct()
  {
    parent::__construct();
    $this->auth_key = C('USER_AUTH_KEY') ?: 'Auth-id';
    if(!$_SESSION[$this->auth_key] && CONTROLLER_NAME.'/'.ACTION_NAME != 'Index/login')
    {
      $this->redirect('Index/login');
    }
    $this->api_root = C('app_api_root');
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