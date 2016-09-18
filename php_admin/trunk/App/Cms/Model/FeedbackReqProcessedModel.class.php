<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/18
 * Time: 14:19
 */

namespace Cms\Model;

use Cms\Controller\CrossDatabaseController;
use Think\Model;
class FeedbackReqProcessedModel extends Model{

    public function truncate_table()
    {
        $Req = D('FeedbackReqProcessed');
        $sql = "TRUNCATE cj_feedback_req_processed";
        $count = $Req->count();
        if($count>0)
            $Req->execute($sql);
    }

    public function insert_single_item($data)
    {
        $Req = D('FeedbackReqProcessed');
        $Req->data($data)->add();
    }

    public function task_hall_count()
    {
        $Req = D('FeedbackReqProcessed');
        $Req->count();
    }

    public function task_hall_list($map,$Page)
    {
        $Cross = new CrossDatabaseController();
        $initModel = array(
            'model_name' => 'FeedbackReqProcessed',
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
