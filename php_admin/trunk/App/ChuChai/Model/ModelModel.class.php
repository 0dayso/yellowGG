<?php
namespace ChuChai\Model;

class ModelModel extends PublicModel
{

  protected static $datas = [];

  // 自动完成
  protected $_auto =
  [
    ['name','trim',self::MODEL_BOTH,'function'],
    ['create_time','time',self::MODEL_INSERT,'function'],
  ];

  public function __construct()
  {
    parent::__construct();
  }


  public function get_models($map = [])
  {
    is_array($map) || $map = [];
    $map['delete_time'] = 0;
    $key = md5(json_encode($map));
    if(!self::$datas[$key])
    {
      self::$datas[$key] = $this->klist(true,$map,'create_time,id');
    }
    return self::$datas[$key];
  }

}