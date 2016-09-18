<?php
namespace Liehuo\Model;

class AdverModel extends CjAdminModel
{

  protected $redis_config = 'redis_user';
  public    $redis_adver  = 'php_rdrs_adver';


  public function del_cache()
  {
    $this->get_redis()->del($this->redis_adver);
  }

}