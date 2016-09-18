<?php
namespace Liehuo\Model;

class AppLiveBannerModel extends AppCfgModel
{

  protected $redis_config = 'redis_live';
  protected $redis_key = 'php_live_banners';

}