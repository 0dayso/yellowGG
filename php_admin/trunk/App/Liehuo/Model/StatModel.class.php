<?php
namespace Liehuo\Model;

class StatModel extends RdrsModel
{

  protected $connection      = 'conn_stat';
  //protected $dbName          = 'cj_rdrs';
  protected $tablePrefix     = 'stat_';
  protected $autoCheckFields = false;

  public function __construct()
  {
    parent::__construct();
  }

}