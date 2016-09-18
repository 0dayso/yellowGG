<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/1/30
 * Time: 14:59
 */

namespace Cms\Model;

use Cms\Controller\CrossDatabaseController;
use Cms\Model;
class CertificateVideoRequestModel extends PublicModel{

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
        //$sql = $Model->getLastSql();
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
        //$sql = $Model->getLastSql();
    }

    public function delete_multi_items($map)
    {
        $Model = D($this->name);
        $Model->where($map)->delete();
    }

    public function truncate_table()
    {
        $Req = D('CertificateVideoRequest');
        $sql = "TRUNCATE cj_certificate_video_request";
        $count = $Req->count();
        if($count>0)
            $Req->execute($sql);
    }*/

    public function reset_test_data()
    {
        $resetModel = C('RESET_MODEL');
        $Model = D($this->name);
        switch($resetModel)
        {
            case 'local':
                for($i=10010;$i<10020;$i++){
                    $items[]=array('certificate_video_id'=>$i);
                }
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

    public function admin_task_count()
    {
        $aid   = $_SESSION['authId'];
        $Model = D($this->name);
        $map   = array('aid'=>array('EQ',$aid),'operation'=>array('EQ',1),'status'=>0);
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
     * 获取任务大厅未分配任务需要显示的数据
     * */
    public function task_hall_list($map,$Page,$case)
    {
        $Cross = new CrossDatabaseController();
        $initModel = array(
            'model_name' => $this->name,
            'data_field' => 'id,certificate_video_id,aid,allocate_time,status,certificate_time,result,remark',
            'limit' => 10,
        );

        $joinModelArr = array(
            'CertificateVideo' => array(
                'model_name' => 'CertificateVideo',
                'table_name' => '',
                'type' => 'left',
                'left_match_field' => 'certificate_video_id',
                'right_match_field'=>'id',
                'left_combine_field' => 'certificate_video_id',
                'right_combine_field' => 'certificate_video_id',
                'data_field' => 'p1,p2,p3,p4,uid,sub_time,id as certificate_video_id,replace_content',
            ),
            'User' => array(
                'model_name' => 'User',
                'table_name' => '',
                'type' => 'left',
                'left_match_field' => 'uid',
                'right_match_field'=>'uid',
                'left_combine_field' => 'uid',
                'right_combine_field' => 'uid',
                'data_field' => 'uid,nickname,status as user_status',
            ),
        );

        if(($case == 'task_hall_allocated')||($case = 'task_hall_processed')||($case = 'task_hall_unallocated')){
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

    /*public function task_hall_list($map,$Page,$case)
    {
 
        $sql = "SELECT    cr.id,cr.certificate_video_id,cr.aid,cr.allocate_time,cr.status,cr.certificate_time,cr.result,cr.remark,
                          c.p1,c.p2,c.p3,c.p4,c.uid,c.sub_time,c.id as certificate_video_id,
                          u.uid,u.nickname,u.status as user_status,
                          a.aid,a.nickname as allocated_to
                FROM  (SELECT * FROM  cj_admin.`cj_certificate_video_request` AS cr1 WHERE 1 ORDER BY cr1.id DESC  )  AS cr
                LEFT JOIN chujian.`cj_certificate_video`                      AS c   ON c.id  = cr.certificate_video_id
                LEFT JOIN chujian.`cj_user`                                   AS u   ON u.uid = c.uid 
                LEFT JOIN cj_admin.`cj_admin`                                 AS a   ON a.aid = cr.aid 
                WHERE 1 AND $map GROUP BY c.uid  ORDER BY cr.id DESC LIMIT ".$Page->firstRow.','.$Page->listRows;   
        return D('User')->query($sql);
    }*/

}
