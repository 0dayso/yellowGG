<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/10
 * Time: 14:25
 */

namespace Cms\Model;

//use Think\Model;
use Cms\Model;
class AdminModel extends PublicModel{

    public $_validate = array(
        array('nickname','require','帐号必须'),
    );

    /*public function insert_single_item($data)
    {
        $Admin     = D('Admin');
        $insert_id = $Admin->data($data)->add();

        return $insert_id;
    }

    public function get_single_item($map,$field=null)
    {
        $Model = D($this->name);
        if($field == null)
            $ret = $Model->where($map)->find();
        else
            $ret = $Model->where($map)->field($field)->find();

        return $ret;
    }

    public function get_multi_items($map,$field=null)
    {
        $Model = D($this->name);
        if($field == null)
            $ret = $Model->where($map)->select();
        else
            $ret = $Model->where($map)->field($field)->select();

        return $ret;
    }*/

    public function get_count($map)
    {
        $Admin = D('Admin');
        $ret = $Admin->where($map)->count();

        return $ret;
    }

    /*public function lists($map,$Page)
    {
        $Admin = D('Admin');
        $ret   = $Admin->join('LEFT JOIN cj_admin_group_admin ON cj_admin_group_admin.aid=cj_admin.aid')
                       ->join('LEFT JOIN cj_admin_group ON cj_admin_group.admin_group_id=cj_admin_group_admin.admin_group_id')
                       ->field('
                             cj_admin.aid as aid,
                             cj_admin.nickname as nickname,
                             cj_admin.email as email,
                             cj_admin_group.admin_group_id as group_id,
                             cj_admin_group.name as group_name
                            ')
                       ->where($map)
                       //->where('concat(cj_admin_group.name)')
                       //->group('aid')
                       ->limit($Page->firstRow.','.$Page->listRows)
                       ->select();

        //$ret = $Admin->table($sql.' a')->limit($Page->firstRow.','.$Page->listRows)->select();//query('group_concat(group_name)');

        //$sql = $Admin->getLastSql();

        return $ret;
    }*/
    public function lists($map,$Page)
    {
        $Admin  = D('Admin');
        $ret    = $Admin->field('aid,nickname,email')->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
        $group  = D('AdminGroup')->where()->select(); // 管理员所有在的组

        $galist = D('AdminGroupAdmin')->where()->select(); // 管理员所有在的组
         
        $group   = array_column($group,'name','admin_group_id');
        
        foreach ($ret as $key => $value) {
            foreach($galist as $k => $val){
                if( $value['aid'] == $val['aid'] ){
                    $ret[$key]['group_name'] .= "<input type=checkbox class='glsid group-in{$value['aid']}' u={$value['aid']} name=admin_group_id value={$val['admin_group_id']} checked >{$group[$val['admin_group_id']]}";
                    $ret[$key]['group_id'][]  = $val['admin_group_id'];
                }
            }
        } 
       
        return $ret;
    }

    public function get_all()
    {
        $Admin = D('Admin');
        $ret   = $Admin
                ->join('LEFT JOIN cj_admin_group_admin   ON cj_admin_group_admin.aid = cj_admin.aid  ')
                ->field('cj_admin.aid,cj_admin.nickname')
                ->where('cj_admin_group_admin.admin_group_id = 11 ')
                ->select();

        //$sql   = $Admin->getLastSql();
        return $ret;
    }

}
