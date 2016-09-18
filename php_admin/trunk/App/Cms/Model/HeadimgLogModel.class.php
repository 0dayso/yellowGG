<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/16
 * Time: 17:05
 */

namespace Cms\Model;

use Cms\Model;
class HeadimgLogModel  extends CjDataModel{

    public function get_count($map)
    {
        $Log = D('HeadimgLog');
        return $Log->where($map)->count();
    }

    public function lists($map,$Page)
    {
        $Log = D('HeadimgLog');
        $map['status'] = array('EQ',0);//在照片管理里面只显示正常图片，被用户和管理员删除的图片都不显示

        $sql = $Log->field('uid,path,dtime,status')
                   ->group('path')->having('count(*)=1')
                   ->limit($Page->firstRow.','.$Page->rowNumbers)->select(false);

        $ret = $Log->table($sql.' a')->where($map)->order('dtime desc')->select();

        //$sql2 = $Log->getLastSql();
        return $ret;
    }


    /*
     * 根据uid在log里找出对应的头像,只在一个表里操作，
     * TODO 需要显示多天，跨两个月的数据显示问题
     * */
    public function get_img_with_uid($uid)
    {
        $Log = D('HeadimgLog');
        $sql = $Log->field('uid,path,dtime,status')
                   ->group('path')->having('count(*)=1')
                   ->select(false);

        $ret = $Log->table($sql.' a')->where('uid='.$uid)->select();

        return $ret;
    }

    /*
     * 往对应的数据表里插入一条记录
     * */
    public function insert_single_item($data=array())
    {
        try{
            $Log     = D('HeadimgLog');
            $Log->data($data)->add();
            $ret = null;
        }
        catch(\Exception $e)
        {
            $ret = array(
                'action'=>C('ACTION_DELETE_HEAD_IMG'),
                'dtime'=>date('y-m-d h:m:s',time()),
                'db_table'=>'cj_headimg',
                'log'=>$Log->getDbError(),
            );
        }

        return $ret;
    }

    /*
     * 插入多条记录
     * */
    public function insert_multi_items($data)
    {
        try{
            $Log     = D('HeadimgLog');
            $table   = $Log->get_table();
            $Log->table($table)->addAll($data);
            $ret = null;
        }
        catch(\Exception $e){
            $ret = array(
                'action'=>C('ACTION_DELETE_HEAD_IMG'),
                'dtime'=>date('y-m-d h:m:s',time()),
                'db_table'=>'cj_headimg',
                'log'=>$Log->getDbError(),
            );
        }

        return $ret;
    }
}
