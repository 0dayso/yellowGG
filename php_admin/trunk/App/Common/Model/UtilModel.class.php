<?php
namespace Common\Model;
use Think\Model;

class UtilModel extends Model
{

  public static function http($url,$data = '',$method = 'GET',$rqhead = array())
  {
    is_array($data) && $data = http_build_query($data);
    $mat = self::parse_urls($url);
    $host = $mat['host'];
    $host && $header = 'Host: '.$host."\r\n";
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
          'timeout' => 6000,
          'ignore_errors' => true,
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
          'timeout' => 6000,
        )
      );
    }
    $stream_context = stream_context_create($context);
    $rb = file_get_contents($url,FALSE,$stream_context);
    return $rb;
  }

  public static function parse_urls($url = '')
  {
    $arr = array();
    if($url)
    {
      $reg = '/\s*(?<href>(?<protocol>(?<scheme>[^:\/?#]+):)?(?<slashes>\/\/)?(?<authority>(?:(?<username>(?<user>[^:@\/?#]+))(?::(?<password>(?<pass>[^@]*)))?@)?(?<host>(?<domain>\[[^\]]+\]|[^:\/\\\?#\']+)(?::(?<port>\d+))?))?(?<pathname>(?<path>\/[^?#]*))?(?<search>\?(?<query>[^#]*))?(?<hash>#(?<fragment>.*))?)\s*/isu';
      preg_match($reg,$url,$arr);
    }
    return $arr;
  }

  public static function chk_charset($str)
  {
    $bm = array('ASCII','GBK','UTF-8','BIG5');
    foreach($bm as $c)
    {
      if($str === iconv('UTF-8',$c,iconv($c,'UTF-8',$str))) return $c;
    }
    return null;
  }

}