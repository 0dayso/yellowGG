<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/10
 * Time: 11:52
 */

namespace Cms\Model;

use Cms\Controller\CrossDatabaseController;
use Think\Model;
class HeadImageModifyRequestModel extends PublicModel{

    public function get_count($map)
    {
        $Model = D($this->name);
        $ret   = $Model->where(str_replace('i.','',$map))->count();

        return $ret;
    }

    public function get_list($map,$Page)
    {
        /*printr($map);
        $Cross = new CrossDatabaseController();
        $initModel = array(
            'model_name' => $this->name,
            'data_field' => 'id,uid,image,sub_time,aid,operation,result,status,pass_time',
            'limit' => 10,
        );

        $joinModelArr = array(
            'Admin' => array(
                'model_name' => 'Admin',
                'table_name' => '',
                'type' => 'left',
                'left_match_field' => 'aid',
                'right_match_field'=>'aid',
                'left_combine_field' => 'aid',
                'right_combine_field' => 'aid',
                'data_field' => 'aid,nickname',
            ),
        );*/
        if($map =='i.operation=0'){
            $sql = "SELECT i.id,i.uid,i.image,i.sub_time,i.aid,i.operation,i.result,i.status,i.pass_time
                FROM cj_admin.cj_head_image_modify_request  AS i
                WHERE $map LIMIT ".$Page->firstRow.','.$Page->listRows;
        }else{
            $sql = "SELECT i.id,i.uid,i.image,i.sub_time,i.aid,i.operation,i.result,i.status,i.pass_time,a.nickname
                FROM cj_admin.cj_head_image_modify_request  AS i
                LEFT JOIN  cj_admin.cj_admin                AS a ON i.aid = a.aid
                WHERE $map LIMIT ".$Page->firstRow.','.$Page->listRows;
        }

        //$ret   = $Cross->cross_multi_join($map,$Page,$initModel,$joinModelArr);
        return D('User')->query($sql);
    }


}
