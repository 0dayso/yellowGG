<?php
namespace Liehuo\Model;

class UserAttrsModel extends CjAdminModel
{

  public $uid = 0;
  public static $datas = [];

  protected $redis_config = 'redis_user';
  protected $redis_key    = '';
  protected $redis_expire = -1;

  // 自动完成
  protected $_auto =
  [
    ['uid','auto_int',self::MODEL_BOTH,'callback'],
    ['attrs','auto_attrs',self::MODEL_BOTH,'callback'],
  ];

  public function __construct($uid = 0)
  {
    parent::__construct();
    $this->uid = (int)$uid;
    $this->redis_key    = 'php_user_attrs_'.$this->uid;
    $this->redis_expire = 60 * 60 * 24 * 30;
  }

  public function set_user($uid = 0)
  {
    return self::Instance($uid);
  }

  public function get_data()
  {
    $dat = self::$datas[$this->uid];
    if(!$dat || !$dat['uid'])
    {
      $rds = $this->get_redis();
      $dat = $rds->hGetAll($this->redis_key);
      if(!$dat || !$dat['uid'])
      {
        $dat = $this->find($this->uid);
        if(!$dat && $this->add(['uid' => $this->uid]))
        {
          $dat = $this->find($this->uid);
        }
        if($dat)
        {
          $rds->hMSet($this->redis_key,$dat);
          $rds->expire($this->redis_key,$this->redis_expire);
        }
      }
      if($dat)
      {
        $dat = $this->attr2array_row($dat);
        self::$datas[$this->uid] = $dat;
      }
    }
    return $dat ?: [];
  }

  public function get_field($field = '')
  {
    $dat = $this->get_data();
    return $dat[$field];
  }

  public function set_field($field = '',$val = '')
  {
    $old = $this->get_data();
    $val = $this->auto_field($field,$val);
    $ret = $this->where(['uid' => $this->uid])->save([$field => $val]);
    if($ret)
    {
      $this->get_redis()->hSet($this->redis_key,$field,$val);
      unset(self::$datas[$this->uid]);
    }
    return $ret;
  }

  public function get_attr($key = '')
  {
    $dat = $this->get_data();
    $adt = $dat['attrs'] ?: [];
    return $adt[$key];
  }

  public function set_attr($key = '',$val = '')
  {
    $dat = $this->get_data();
    $adt = $dat['attrs'] ?: [];
    $adt[$key] = $val;
    $ret = $this->set_field('attrs',$adt);
  }

}