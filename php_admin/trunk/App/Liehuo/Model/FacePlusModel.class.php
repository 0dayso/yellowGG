<?php
namespace Liehuo\Model;
use \Common\Model\UtilModel;

class FacePlusModel extends PublicModel
{

  const API_KEY    = 'd836930722dfda8032b9e36eafea9f6f';
  const API_SECRET = '59jpjYg7XirT4h2TzWAD-dt7_Tn4ZY4c';
  const API_ROOT   = 'http://apicn.faceplusplus.com/v2/';

  protected $errno = 0;
  protected $error = '';
  protected $autoCheckFields = false;

  public function detect($url = '')
  {
    $dat = is_array($url) ? $url : ['url' => $url];
    return $this->get_api('detection/detect',$dat);
  }

  public function get_api($api = '',$dat = [])
  {
    $dat = array_merge(
    [
      'api_key'    => self::API_KEY,
      'api_secret' => self::API_SECRET,
    ],$dat ?: []);
    $jss = UtilModel::http(self::API_ROOT.$api,$dat);
    $ret = json_decode($jss,true);
    isset($ret['error_code']) && $this->errno = $ret['error_code'];
    isset($ret['error'])      && $this->error = $ret['error'];
    //die(json_encode(compact('jss','ret')));
    return $ret;
  }

}