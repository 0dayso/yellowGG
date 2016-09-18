<?php
namespace Liehuo\Model;

class ArticleModel extends CjDatadwModel
{

  public $resource_root_url = 'http://static.chujianapp.com/';

  // 自动完成
  protected $_auto =
  [
    ['thumb','auto_thumb',self::MODEL_BOTH,'callback'],
    ['content','auto_content',self::MODEL_BOTH,'callback'],
  ];

  protected function auto_thumb($url = '')
  {
    if($url)
    {
      $url = str_replace($this->resource_root_url,'',$url);
    }
    return $url;
  }

  protected function auto_content($str = '')
  {
    if($str)
    {
      $str = str_replace($this->resource_root_url,'__STATIC_URL_ROOT__',$str);
    }
    return $str;
  }


  public function get_url($id = 0)
  {
    return 'http://src.chujianapp.com/article/?id='.(int)$id;
  }

  public function complete_list($arr = [])
  {
    if($arr)
    {
      $arr = array_map(function($v)
      {
        return $this->complete_fields($v);
      },$arr ?: []);
    }
    return $arr;
  }

  public function complete_fields($arr = [])
  {
    if($arr)
    {
      $arr['thumb']   = $this->complete_thumb($arr['thumb']);
      $arr['content'] = $this->complete_content($arr['content']);
    }
    return $arr;
  }

  public function complete_thumb($url = '')
  {
    if($url) $url = $this->resource_root_url.$url;
    return $url;
  }

  public function complete_content($str = '')
  {
    if($str)
    {
      $str = htmlspecialchars_decode($str);
      $str = str_replace('__STATIC_URL_ROOT__',$this->resource_root_url,$str);
    }
    return $str;
  }

}