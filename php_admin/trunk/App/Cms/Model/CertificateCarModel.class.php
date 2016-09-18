<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/14
 * Time: 11:59
 */

namespace Cms\Model;

//use Think\Model;
use Cms\Model;
class CertificateCarModel extends CjDataModel{

    /*
     * mysql event专用接口
     * */
    public function get_multi_items_for_mysql_event($map,$field)
    {
        $Car = D('CertificateCar');

        $ret = $Car->where($map)->field($field)->select();

        return $ret;
    }

    public function insert_single_item($data)
    {
        $Car = D('CertificateCar');
        $newId = $Car->data($data)->add();

        return $newId;
    }

    /*
     * 更新单条记录
     * */
    public function update_single_item($map=array(),$data=array())
    {
        $Car  = D('CertificateCar');
        $Car->where($map)->save($data);
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
        $map    = array('uid'=>array('EQ',$uid));
        $items = $Model->where($map)->order('sub_time')->select();
        $temp  = end($items);
        $id    = $temp['id'];
        $Model->where('id='.$id)->data($data)->save();
    }

    /*
     * 获取单条记录
     * */
    public function get_single_item($map=array(),$field='')
    {
        $Model = D($this->name);
        if($field == '')
            $ret = $Model->where($map)->find();
        else
            $ret = $Model->where($map)->field($field)->find();
        return $ret;
    }

    /*
     * 获取多条记录
     * */
    public function get_multi_items($map,$field=null)
    {
        $Model = D($this->name);
        if($field == null)
            $ret = $Model->where($map)->select();
        else
            $ret = $Model->where($map)->field($field)->select();

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
                for($i=1;$i<8;$i++){
                    array_push($in,$i);
                }
                $item  = array(
                    'status'=>0,
                    'car_brand_id'=>3,
                    'car_brand_name'=>'',
                    'car_model_id'=>57,
                    'car_model_name'=>'',
                    'sub_time'=>1422772858,
                    'pass_time'=>0
                );
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
