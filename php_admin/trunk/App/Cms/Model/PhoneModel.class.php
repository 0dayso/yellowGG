<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/17
 * Time: 14:11
 */

namespace Cms\Model;

use Cms\Model;
class PhoneModel extends CjDataModel{

    public function get_count($map)
    {
        $Phone = D('Phone');
        $count = $Phone->where($map)->count();

        return $count;
    }

    public function lists($map,$Page)
    {
        $Phone = D('Phone');
        $ret   = $Phone->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();

        return $ret;
    }
} 