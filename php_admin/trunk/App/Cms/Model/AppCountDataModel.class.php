<?php
/**
 * Created by PhpStorm.
 * User: Anl
 * Date: 2015/8/17
 * Time: 15:51
 */

namespace Cms\Model;

class AppCountDataModel extends CjAdModel
{

  public function lists($map = array())
  {
    if($map) $this->where($map);
    $arr = $this->order('create_time desc')->select();
    return $arr;
  }

}