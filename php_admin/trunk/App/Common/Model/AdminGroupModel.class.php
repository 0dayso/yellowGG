<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/5
 * Time: 19:40
 */

namespace Common\Model;
use Think\Model;

class AdminGroupModel extends  Model
{

    public function get_count($map)
    {
        $AdminGroup = D('AdminGroup');
        $ret = $AdminGroup->join('LEFT JOIN cj_action_admin_group ON cj_action_admin_group.admin_group_id=cj_admin_group.admin_group_id')
                          ->join('LEFT JOIN cj_action ON cj_action.action_id=cj_action_admin_group.action_id')
                          ->where($map)
                          ->count();

        return $ret;
    }

    public function lists($map,$Page)
    {
        $AdminGroup = D('AdminGroup');
        $ret = $AdminGroup->join('LEFT JOIN cj_action_admin_group ON cj_action_admin_group.admin_group_id=cj_admin_group.admin_group_id')
                          ->join('LEFT JOIN cj_action ON cj_action.action_id=cj_action_admin_group.action_id')
                          ->field('cj_admin_group.admin_group_id as admin_group_id,
                                   cj_admin_group.name as admin_group_name,
                                   cj_action.action_id as action_id,
                                   cj_action.name as action_name')
                          ->where($map)
                          ->limit($Page->firstRow.','.$Page->listRows)
                          ->select();

        return $ret;
    }

}
