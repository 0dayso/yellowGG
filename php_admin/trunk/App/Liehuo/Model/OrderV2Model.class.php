<?php
namespace Liehuo\Model;

class OrderV2Model extends OrderModel
{

  // 烈火 钱包相关数据库配置
  protected $connection = 'conn_wallet';
  protected $tableName  = 'order';

}