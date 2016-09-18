<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/13
 * Time: 14:06
 */

namespace Cms\Model;

use Think\Model;
class UserBatchAddModel extends Model{

    public function get_single_item($map)
    {
        $UserBatchAdd = D('UserBatchAdd');
        $ret = $UserBatchAdd->where($map)->find();
        return $ret;
    }
} 