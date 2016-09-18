<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/18
 * Time: 14:23
 */

namespace Cms\Model;

use Cms\Model;
class FeedbackAllocateLogModel extends CjAdminLogModel{

    public function insert_multi_items($data)
    {
        $Log = D('FeedbackReqAllocateLog');
        if(count($data)>0) {
            $fieldArr = array_keys(current($data));
            $fieldStr = array_to_str($fieldArr);
            $fieldStr = substr($fieldStr, 0, strlen($fieldStr) - 1);//删除最后一个,号
            $sql = "insert into cj_feedback_req_allocate_log(" . $fieldStr . ") values";
            foreach ($data as $value) {
                $sql .= "(";
                foreach ($value as $field => $fieldValue) {
                    if ($field == 'dtime')
                        $temp = "'" . $fieldValue . "'";
                    else
                        $temp = $fieldValue;
                    $sql .= $temp . ',';
                }
                $sql = substr($sql, 0, strlen($sql) - 1);//删除最后一个,号
                $sql .= "),";
            }
            $sql = substr($sql, 0, strlen($sql) - 1);//删除最后一个,号
            $sql .= ';';

            $Log->query($sql);
        }
    }

    public function truncate_table()
    {
        $Log = D('FeedbackAllocateLog');
        $sql = "TRUNCATE cj_feedback_allocate_log";
        $count = $Log->count();
        if($count>0)
            $Log->execute($sql);
    }
}
