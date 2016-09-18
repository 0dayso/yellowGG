<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/20
 * Time: 13:32
 */

namespace Cms\Model;

use Cms\Model;
class HeadImgModel extends CjDataModel{
    protected $tableName = 'headimg';

    //批量添加用户时新生成一条记录
    public function insert_single_item($item=array())
    {
        $Headimg = D('Headimg');
        $Headimg->data($item)->add();
    }

    public function get_single_item($map)
    {
        $Headimg = D('Headimg');
        $ret     = $Headimg->where($map)->find();

        return $ret;
    }

    public function update_single_item($map,$data)
    {
        $Headimg = D('Headimg');
        $Headimg->where($map)->data($data)->save();
    }

    /*
     * 插入单个path内容,记录在cj_headimg内不为空
     * @abstract 只有插入单个path的操作，没有同时上传多个文件的
     * @param uid  用户id
     * @param path 需要添加的path
     * */
    public function insert_single_path($uid,$path)
    {
        $map['uid'] = array('EQ',$uid);
        $Headimg    = D('Headimg');
        $item       = $Headimg->where($map)->find();
        if($item['path'] == null){
            $item['path']= [];
            array_push($item['path'],$path);
        }
        else{
            $item['path']     = json_to_array($item['path']);
            array_push($item['path'],$path);
        }

        $imgStr     = array_to_json($item['path']);
        $Headimg->where('uid='.$uid)->data(array('path'=>$imgStr))->save();
    }

    /*
     * 删除记录中path内容,记录在cj_headimg内不为空
     * @param uid 用户id
     * @param data 需要删除的path数组
     * @example  {head_img_fasjdlkjasfdg,head_img_jalskdjgla}
     * */
    public function delete_path($uid,$data)
    {
        $Headimg = D('Headimg');
        $map['uid'] = array('EQ',$uid);
        $path = [];
        try{
            $item  = $Headimg->where($map)->find();
            $pathAarr = json_to_array($item['path']);
            foreach($pathAarr as $value){
                if(!in_array($value,$data))
                    array_push($path,$value);
            }
            if(count($path)>0)
                $pathStr = array_to_json($path);
            else
                $pathStr = null;
            $Headimg->where('uid='.$uid)->data(array('path'=>$pathStr))->save();
            $ret = null;
        }
        catch(\Exception $e)
        {
            $ret = array(
                'uid'=>$uid,
                'action'=>'删除用户头像',
                'dtime'=>date('y-m-d h:m:s',time()),
                'db_table'=>'cj_headimg',
                'log'=>$e,
            );
        }

        return $ret;
    }

} 