<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/15
 * Time: 17:07
 */

namespace Cms\Model;

use Cms\Model;
class ThumbUpModel extends CjDataModel {

    /**
     * 获取点赞总量
     * */
    public function getThumbUpSum($uidArr='',$validBegin,$validEnd) {
        $Thumb = D('ThumbUp');
        $sql   = "select count(id) as sum from cj_thumb_up where oid in ".
                 $uidArr." and create_time between ".$validBegin." and ".$validEnd;
        $data  = $Thumb->query($sql);
        return ($data[0]['sum'] > 0) ? $data[0]['sum'] : 0;
    }
}
