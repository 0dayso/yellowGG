<?php
namespace Liehuo\Model;

class LiveContractTypeModel extends AppCfgModel
{

  protected $cfg_type     = self::TYPE_CONTRACT;
  protected $redis_config = 'redis_live';
  protected $redis_key    = 'php_contract_types';

  public function __construct()
  {
    parent::__construct();
  }

}