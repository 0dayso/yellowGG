<?php
namespace Liehuo\Model;

class LiveHostLevelModel extends AppCfgModel
{

  protected $cfg_type     = self::TYPE_LIVE_LEVEL;
  protected $redis_config = 'redis_live';
  protected $redis_key    = 'php_host_levels';

  public function __construct()
  {
    parent::__construct();
  }

}