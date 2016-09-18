<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/14
 * Time: 12:01
 */

namespace Cms\Model;

use Cms\Model;
class CertificateVideoModel extends CjDataModel{
    //protected $tableName = 'certificate_video';

    /*
     * mysql event专用接口
     * */
    public function get_multi_items_for_mysql_event($map,$field)
    {
        $Video = D($this->name);
        $ret = $Video->where($map)->field($field)->select();

        return $ret;
    }

    /*
     * 更新单条记录
     * */
    public function update_single_item($map=array(),$data=array())
    {
        $Video  = D($this->name);
        $Video->where($map)->save($data);
    }

    public function update_multi_items($map,$data)
    {
        $Model = D($this->name);
        $Model->where($map)->data($data)->save();
    }

    /*
     * 更新某uid下，最近的一条记录
     * */
    public function update_latest_item($uid,$data)
    {
        $Model  = D($this->name);
        $map  = array(
            'uid'=>array('EQ',$uid),
        );
        $items = $Model->where($map)->order('sub_time')->select();
        $temp  = end($items);
        $id    = $temp['id'];
        $Model->where('id='.$id)->data($data)->save();
        //$sql = $Model->getLastSql();
    }

    public function insert_single_item($data)
    {
        $Video = D($this->name);
        $newId = $Video->data($data)->add();

        return $newId;
    }

    public function insert_multi_items($dataList)
    {
        $Video = D($this->name);
        $Video->addAll($dataList);
    }

    /*
     * 获取单条记录
     * */
    public function get_single_item($map,$field='')
    {
        $CertificateVideo = D('CertificateVideo');
        if($field == '')
            $ret = $CertificateVideo->where($map)->find();
        else
            $ret = $CertificateVideo->where($map)->field($field)->find();

        return $ret;
    }

    /*
     * 获取多条记录
     * */
    public function get_multi_items($map,$field=null)
    {
        $CertificateVideo = D('CertificateVideo');
        if($field == null)
            $ret = $CertificateVideo->where($map)->select();
        else
            $ret = $CertificateVideo->where($map)->field($field)->select();

        return $ret;
    }

    /*
     * 重置测试数据
     * */
    public function reset_test_data()
    {
        $resetModel = C('RESET_MODEL');
        switch($resetModel)
        {
            case 'local':
                $in  = array();
                for($i=10010;$i<10020;$i++){
                    array_push($in,$i);
                }
                $item  = array('status'=>0,'sub_time'=>1422772858,'pass_time'=>0);
                $map   = array('id'=>array('IN',$in));
                $Model = D($this->name);
                $Model->update_multi_items($map,$item);
                break;
            case 'server':
                break;
            default:
                $this->error('测试配置出错');
                break;
        }
    }
}
