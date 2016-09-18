<?php

function cli_echo($str = '')
{
  if(PHP_SAPI == 'cli') echo $str;
}

function cli_die($str = '')
{
  if(PHP_SAPI == 'cli') die($str);
}


function alog($log = [],$typ = 'log')
{
  is_array($log) || $log = [$log];
  array_unshift($log,date('Y-m-d H:i:s'),time());
  $log = array_map(function($v,$k)
  {
    if(is_array($v) || is_object($v))
    {
      ob_start();
      print_r($v);
      $v = ob_get_contents();
      ob_end_clean();
    }
    return (is_numeric($k) ? '' : ($k.':')).$v;
  },$log ?: [],array_keys($log) ?: []);
  $fnm = C('LOG_PATH').DIRECTORY_SEPARATOR.$typ.'-'.date('Ymd').'.log';
  return @file_put_contents($fnm,implode('|',$log)."\n",FILE_APPEND);
}

/*
 * 使用Redis记录Log
 * 默认保存32天
 * */
function rlog($log = [],$typ = 'log',$exp = null)
{
  is_string($log) || $log = json_encode($log);//JSON_UNESCAPED_UNICODE
  $key = 'rlog-'.$typ.'-'.date('Ymd');
  $rds = D('PhpServerRedis')->new_redis();
  isset($exp) || $exp = 60 * 60 * 24 * 32;
  if($ret = $rds->zAdd($key,time(),$log)) $rds->expire($key,$exp);
  $rds->publish('liehuo.adm.'.$typ,$log);
  return $ret;
}


function oss_img_srv($src = '',$ext = '')
{
  if($src)
  {
    $src = preg_replace('/@[\w.]*\s*$/i','',$src);
  }
  $src .= ($ext ? '@' : '').$ext;
  return $src;
}


if(!function_exists('boolval'))
{
  function boolval($var)
  {
    return !!$var;
  }
}

function array_get($arr = [],$key = null)
{
  is_array($arr) || $arr = [];
  return $arr[$key];
}