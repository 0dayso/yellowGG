<?php
namespace Liehuo\Model;

class RpcModel extends PublicModel
{

  protected $autoCheckFields = false;
  protected $redis_config    = 'redis_recommend';
  protected $redis_list_key  = 'go_list_rpc';


  public function add_go_list($typ = '',$dat = '')
  {
    $rds = $this->new_redis();
    is_string($dat) || $dat = json_encode($dat) ?: '';
    $arr = ['type' => $typ,'content' => $dat];
    $jss = json_encode($arr) ?: '';
    //$rds->rPush('go_list_rpc_user1',$jss);
    $ret = $rds->rPush($this->redis_list_key,$jss);
    if(isset($this->redis_list_key1)) $rds->rPush($this->redis_list_key1,$jss);
    return $ret;
  }

}