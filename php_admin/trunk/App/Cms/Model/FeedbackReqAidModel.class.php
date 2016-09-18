<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/18
 * Time: 11:36
 */

namespace Cms\Model;

use Cms\Controller\CrossDatabaseController;
use Think\Model;
class FeedbackReqAidModel extends Model{

    /*
     * 插入多条记录
     * @param data array(0=>array(),1=>array()...)
     * @param table ex cj_feedback_req_aid_1
     * */
    public function insert_multi_items($data,$table,$field)
    {
        $Req = D('FeedbackReqAid');
        $sql = "insert ignore into ".$table."(".$field.") values";
        $len = count($data);
        $fieldArr = explode(',',$field);
        for($i=0;$i<$len;$i++)
        {
            $sql .= "(";
            $dataLen = count($data[$i]);
            for($j=0;$j<$dataLen;$j++){
                $subFiled = $fieldArr[$j];
                if($subFiled == 'dtime'||$subFiled=='atime')
                    $value = "'".$data[$i][$subFiled]."'";
                else
                    $value = $data[$i][$subFiled];

                if($j == $dataLen-1)
                    $sql .= $value;
                else
                    $sql .= $value.',';
            }
            if($i == $len-1)
                $sql .= ");";
            else
                $sql .= "),";
        }
        $Req->query($sql);
        //$sql = $Req->getLastSql();
    }

    /*
     *
     * */
    public function get_single_item($map,$table,$field=null)
    {
        $Req = D('FeedbackReqAid');
        if($field == null)
            $ret = $Req->table($table)->where($map)->find();
        else
            $ret = $Req->table($table)->where($map)->field($field)->find();

        return $ret;
    }

    /*
     * 删除单条记录
     * */
    public function delete_single_item($map,$table)
    {
        $Req = D('FeedbackReqAid');
        $Req->table($table)->where($map)->delete();
    }

    /*
     * 删除多条记录
     * @param map ex feedback_data_id in array
     * @param table ex cj_feedback_req_aid_1
     * */
    public function delete_multi_items($map,$table)
    {
        $Req = D('FeedbackReqAid');
        $Req->table($table)->where($map)->delete();
    }

    public function update_single_item($map,$table,$data)
    {
        $Req = D('FeedbackReqAid');
        $Req->table($table)->where($map)->data($data)->save();
    }

    /*
     * 清空整张表
     * @param table
     * */
    public function truncate_table($table)
    {
        $Req = D('FeedbackReqAid');
        $sql = "TRUNCATE ".$table;
        $count = $Req->table($table)->count();
        if($count>0)
            $Req->table($table)->execute($sql);
    }

    /*
     * 管理员个人页面意见反馈计数
     * */
    public function admin_task_count($map,$table)
    {
        $Req = D('FeedbackReqAid');
        $ret = $Req->table($table)->where($map)->count();
        return $ret;
    }

    /*
     * 管理员个人页面意见反馈list
     * */
    public function admin_task_list($map,$table,$Page)
    {
        $Cross = new CrossDatabaseController();

        $initModel = array(
            'model_name' => 'FeedbackReqAid',
            'table_name' => $table,
            'data_field' => 'id,feedback_data_id,dtime as allocate_time,text,remark,atime as accept_time',
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
            )
        );

        $ret   = $Cross->cross_multi_join($map,$Page,$initModel,$joinModelArr);

        return $ret;
    }
}
