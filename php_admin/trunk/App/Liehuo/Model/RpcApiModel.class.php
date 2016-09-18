<?php
namespace Liehuo\Model;

class RpcApiModel extends PublicModel
{

  protected $autoCheckFields = false;
  protected $redis_config    = 'redis_user';
  protected $redis_key       = 'php_rpc_adm2api';


  public function call($cmd = '',$dat = [])
  {
    $rds = $this->new_redis();
    $arr =
    [
      'method' => $cmd,
      'params' => $dat,
    ];
    $arr['id'] = md5('rpc'.uniqid(rand(),true).rand());
    $ret = $rds->rPush($this->redis_key,json_encode($arr) ?: '');
    //die(json_encode(compact('cmd','dat','ret')));
    return $ret;
  }

  public function length()
  {
    return $this->new_redis()->lLen($this->redis_key);
  }

  public function handle()
  {
    return \Common\Model\UtilModel::http('https://api.chujianapp.com/rpc_adm2api/handle');
  }

}