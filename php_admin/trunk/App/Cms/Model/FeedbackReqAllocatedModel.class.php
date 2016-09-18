<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/18
 * Time: 14:17
 */

namespace Cms\Model;

use Cms\Controller\CrossDatabaseController;
use Think\Model;
class FeedbackReqAllocatedModel extends Model{

    public function delete_single_item($map)
    {
        $Req = D('FeedbackReqAllocated');
        $Req->where($map)->delete();
    }

    public function insert_multi_items($data)
    {
        $Req = D('FeedbackReqAllocated');
        if(count($data)>0){
            $fieldArr = array_keys(current($data));
            $fieldStr = array_to_str($fieldArr);
            $fieldStr = substr($fieldStr,0,strlen($fieldStr)-1);//删除最后一个,号
            $sql = "insert ignore into cj_feedback_req_allocated(".$fieldStr.") values";
            foreach($data as $value){
                $sql .= "(";
                foreach($value as $field=>$fieldValue){
                    if($field == 'dtime')
                        $temp = "'".$fieldValue."'";
                    else
                        $temp = $fieldValue;
                    $sql .= $temp.',';
                }
                $sql = substr($sql,0,strlen($sql)-1);//删除最后一个,号
                $sql .= "),";
            }
            $sql = substr($sql,0,strlen($sql)-1);//删除最后一个,号
            $sql .= ';';

            $Req->query($sql);
        }
    }

    public function update_single_item($map,$data)
    {
        $Req = D('FeedbackReqAllocated');
        $Req->where($map)->data($data)->save();
    }

    public function truncate_table()
    {
        $Req = D('FeedbackReqAllocated');
        $sql = "TRUNCATE cj_feedback_req_allocated";
        $count = $Req->count();
        if($count>0)
            $Req->execute($sql);
    }

    public function task_hall_count()
    {
        $Req = D('FeedbackReqAllocated');
        $ret = $Req->count();
        return $ret;
    }

    public function task_hall_list($map,$Page)
    {
        $Cross = new CrossDatabaseController();
        $initModel = array(
            'model_name' => 'FeedbackReqAllocated',
            'data_field' => 'id,feedback_data_id,aid,dtime as allocate_time',
            'limit' => $Page->listRows,
        );

        $joinModelArr = array(
            'FeedbackData' => array(
                'model_name' => 'FeedbackData',
                'table_name' => '',
                'type' => 'left',
                'left_match_field' => 'feedback_data_id',
                'right_match_field'=>'id',
                'left_combine_field' => 'feedback_data_id',
                'right_combine_field' => 'feedback_data_id',
                'data_field' => 'id as feedback_data_id,uid,data,start_time,end_time,product_id_arr',
            ),
            'Admin' => array(
                'model_name' => 'Admin',
                'table_name' => '',
                'type' => 'left',
                'left_match_field' => 'aid',
                'right_match_field'=>'aid',
                'left_combine_field' => 'aid',
                'right_combine_field' => 'aid',
                'data_field' => 'aid,nickname as allocated_to',
            ),
        );

        $ret   = $Cross->cross_multi_join($map,$Page,$initModel,$joinModelArr);

        return $ret;
    }

}
