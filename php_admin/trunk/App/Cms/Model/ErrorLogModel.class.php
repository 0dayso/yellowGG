<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/19
 * Time: 18:05
 */

namespace Cms\Model;

use Think\Model;
class ErrorLogModel extends Model{

    public function insert_single_item($item)
    {
        $Log = D('ErrorLog');
        $Log->data($item)->add();
    }
} 