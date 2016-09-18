<?php
/**
 * Created by PhpStorm.
 * User: Anl
 * Date: 2015/8/17
 * Time: 14:05
 */

namespace Cms\Model;

class CjAdModel extends PublicModel
{

  protected $connection  = 'cjad_db_config';
  protected $tablePrefix = 'mgad_';

  // 列表分页
  public function plist($page = 20,$map = array())
  {
    if($map) $this->where($map);
    $pag = is_object($page) ? $page : new \Think\Page($this->count(),$page);
    $this->limit($pag->firstRow.','.$pag->listRows);
    $this->pager = $pag;
    return $this;
  }

}