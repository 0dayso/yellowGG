<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/2
 * Time: 10:32
 */

namespace Cms\Model;

use Cms\Controller\CrossDatabaseController;
use Cms\Model;
class AccusationRequestModel extends PublicModel{

    /*public function get_single_item($map,$field=null)
    {
        $Model = D($this->name);
        if($field == null){
            $ret = $Model->where($map)->find();
        }
        else{
            $ret = $Model->where($map)->field($field)->find();
        }

        return $ret;
    }

    public function update_multi_items($map,$data)
    {
        $Model = D($this->name);
        $Model->where($map)->data($data)->save();
    }

    public function update_single_item($map,$data)
    {
        $Model = D($this->name);
        $Model->where($map)->data($data)->save();
    }

    public function insert_multi_items($data)
    {
        $Model = D($this->name);
        $Model->addAll($data);
    }

    public function delete_multi_items($map)
    {
        $Model = D($this->name);
        $Model->where($map)->delete();
    }

    public function truncate_table()
    {
        $Model = D($this->name);
        $sql = "TRUNCATE ".$this->trueTableName;
        $count = $Model->count();
        if($count>0)
            $Model->execute($sql);
    }*/

    public function reset_test_data()
    {
        $resetModel = C('RESET_MODEL');
        $Model = D($this->name);
        switch($resetModel)
        {
            case 'local':
                $items[]=array('accusation_id'=>1,'uid'=>16,'offender_uid'=>23);
                $items[]=array('accusation_id'=>2,'uid'=>16,'offender_uid'=>23);
                $items[]=array('accusation_id'=>3,'uid'=>25,'offender_uid'=>26);
                $items[]=array('accusation_id'=>4,'uid'=>25,'offender_uid'=>26);
                $items[]=array('accusation_id'=>5,'uid'=>25,'offender_uid'=>26);
                $items[]=array('accusation_id'=>6,'uid'=>25,'offender_uid'=>26);
                $items[]=array('accusation_id'=>7,'uid'=>25,'offender_uid'=>26);
                $items[]=array('accusation_id'=>8,'uid'=>25,'offender_uid'=>26);
                $Model->truncate_table();
                $Model->insert_multi_items($items);
                break;
            case 'server':
                break;
            default:
                $this->error('测试配置出错');
                break;
        }
    }

    public function insert_items_for_same_offender()
    {
        $Report = D('Report');
        $map = array('id'=>array('GT',8));
        $reportItems = $Report->get_multi_items($map,'id,uid,offender_uid');
        foreach($reportItems as $key=>$value){
            $items[] = array('accusation_id'=>$value['id'],'uid'=>$value['uid'],'offender_uid'=>$value['offender_uid']);
        }
        $this->insert_multi_items($items);
    }

    public function admin_task_count()
    {
        $aid   = $_SESSION['authId'];
        $Model = D($this->name);
        $map   = array('aid'=>array('EQ',$aid),'status'=>array('EQ',0));
        $ret   = $Model->where($map)->count();

        return $ret;
    }

    //获取任务大厅未分配任务需要显示的数据量
    public function task_hall_count($map)
    {
        $Model = D($this->name);
        $ret = $Model->where($map)->count();

        return $ret;
    }

    /*
     * 特定条件下举报业务任务总数
     * */
    public function single_uid_count($map)
    {
        $Req = D($this->name);
        $ret = $Req->where($map)->count();

        return $ret;
    }

    public function single_uid_list($map,$Page)
    {
        $Cross = new CrossDatabaseController();
        $initModel = array(
            'model_name' => $this->name,
            'data_field' => 'id,accusation_id,aid,allocate_time',
            'limit' => 10,
        );

        $joinModelArr = array(
            'Report' => array(
                'model_name' => 'Report',
                'table_name' => '',
                'type' => 'left',
                'left_match_field' => 'accusation_id',
                'right_match_field'=>'id',
                'left_combine_field' => 'accusation_id',
                'right_combine_field' => 'accusation_id',
                'data_field' => 'id as accusation_id,uid,offender_uid,report_type,remark,reason,status,dtime as sub_time,atime as accept_time',
            ),
            'User' => array(
                'model_name' => 'User',
                'table_name' => '',
                'type' => 'left',
                'left_match_field' => 'offender_uid',
                'right_match_field'=>'uid',
                'left_combine_field' => 'offender_uid',
                'right_combine_field' => 'offender_uid',
                'data_field' => 'uid as offender_uid,nickname,dblocking_time',
            ),
        );

        $ret   = $Cross->cross_multi_join($map,$Page,$initModel,$joinModelArr);

        return $ret;
    }

    /*
     * 获取任务大厅未分配任务需要显示的数据
     * */
    public function task_hall_list($map,$Page,$case)
    {
        $Cross = new CrossDatabaseController();
        $initModel = array(
            'model_name' => $this->name,
            'data_field' => 'id,accusation_id,uid,offender_uid,aid,allocate_time,status,certificate_time,result,remark',
            'limit' => 10,
        );

        $joinModelArr = array(
            'Report' => array(
                'model_name' => 'Report',
                'table_name' => '',
                'type' => 'left',
                'left_match_field' => 'accusation_id',
                'right_match_field'=>'id',
                'left_combine_field' => 'accusation_id',
                'right_combine_field' => 'accusation_id',
                'data_field' => 'id as accusation_id,report_type,dtime as sub_time',
            ),
        );

        if(($case == 'task_hall_allocated')||($case == 'task_hall_processed')||($case == 'admin_task_unprocessed')){

            $joinModelArr['Admin'] = array(
                'model_name' => 'Admin',
                'table_name' => '',
                'type' => 'left',
                'left_match_field' => 'aid',
                'right_match_field'=>'aid',
                'left_combine_field' => 'aid',
                'right_combine_field' => 'aid',
                'data_field' => 'aid,nickname as allocated_to',
            );
        }

        $ret   = $Cross->cross_multi_join($map,$Page,$initModel,$joinModelArr);

        return $ret;
    }
}
