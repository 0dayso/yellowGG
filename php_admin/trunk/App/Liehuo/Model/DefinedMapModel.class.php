<?php
namespace Liehuo\Model;

class DefinedMapModel extends CjDatadwModel
{

  public static $defined_map = [];

  public function get_map($pid = 0)
  {
    if(!self::$defined_map)
    {
      $arr = $this->klist('node',['parent' => (int)$pid]) ?: [];
      foreach($arr as $k => $v)
      {
        self::$defined_map[$v['node']] = $v['name'];
      }
    }
    return self::$defined_map;
  }

}