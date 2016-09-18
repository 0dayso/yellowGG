<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/6
 * Time: 16:12
 */

namespace Common\Model;
use Think\Model;

class AdminGroupAdminModel extends Model
{

    public function check_action_permission($admin_id,$action)
    {
        $adminGroupAdmin = D('AdminGroupAdmin');
        $map['aid'] = array('EQ',$admin_id);
        $map['action_id'] = array('EQ',$action);
        $ret = $adminGroupAdmin->join('LEFT JOIN cj_action_admin_group ON cj_action_admin_group.admin_group_id=cj_admin_group_admin.admin_group_id')
                               ->where($map)->count();

        return $ret;
    }

    /*
     * 获取某个管理员拥有的所有权限
     * */
    public function get_admin_all_permission($admin)
    {
        $adminGroupAdmin = D('AdminGroupAdmin');
        $map['aid'] = array('EQ',$admin);
        $ret = $adminGroupAdmin->join('LEFT JOIN cj_action_admin_group ON cj_action_admin_group.admin_group_id=cj_admin_group_admin.admin_group_id')
            ->where($map)->group('action_id')->field('action_id')->select();

        return $ret;
    }
} 