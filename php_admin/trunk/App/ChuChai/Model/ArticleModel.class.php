<?php
namespace Chuchai\Model;

class ArticleModel extends \Liehuo\Model\ArticleModel
{

  protected $connection  = 'conn_chuchai';
  protected $tablePrefix = 'bd_';

  public function __construct()
  {
    parent::__construct();
  }

}