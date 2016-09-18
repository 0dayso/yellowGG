<?php
namespace Liehuo\Model;

class RpcUserModel extends RpcModel
{

  protected $redis_list_key  = 'go_list_rpc_user';
  protected $redis_list_key1 = 'rpc_user_1';//备用

}