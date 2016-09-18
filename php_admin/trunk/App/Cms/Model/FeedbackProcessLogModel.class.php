<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/18
 * Time: 14:25
 */

namespace Cms\Model;

use Cms\Model;
class FeedbackProcessLogModel extends CjAdminLogModel{

    /*
     * 获取多条记录
     * */
    public function get_multi_items($map,$field=null)
    {
        $Model = D('FeedbackProcessLog');
        if($field == null)
            $ret = $Model->where($map)->select();
        else
            $ret = $Model->where($map)->field($field)->select();
        $sql = $Model->getLastSql();
        return $ret;
    }

    /*
     * 插入一条新的log
     * */
    public function insert_single_item($data)
    {
        $Log = D('FeedbackProcessLog');
        $Log->data($data)->add();
    }

    public function truncate_table()
    {
        $Log = D('FeedbackProcessLog');
        $sql = "TRUNCATE cj_feedback_process_log";
        $count = $Log->count();
        if($count>0)
            $Log->execute($sql);
    }
}
