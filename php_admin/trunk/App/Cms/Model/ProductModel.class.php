<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/17
 * Time: 14:12
 * Description: IM服务器mysql数据库中的表product，用来存储
 *              产品用户反馈信息；
 */

namespace Cms\Model;

use Cms\Controller\CrossDatabaseController;
use Cms\Model;
class ProductModel extends CjImServerModel{
    protected $tableName='product';


    public function get_im_server_data_test()
    {
        $Product = D('Product');
        $ret = $Product->select();

        return $ret;
    }

    /*
     * 插入多条记录
     * */
    public function insert_multi_items($data=array(),$sql=null)
    {
        $Product = D('Product');
        if($sql==null)
            $Product->data($data)->add();
        else
            $Product->query($sql);

    }

    /*
     * 获取多条用户记录数据
     * */
    public function get_multi_items($map,$field=null){
        $Product = D('Product');
        if($field == null)
            $ret = $Product->where($map)->select();
        else
            $ret = $Product->where($map)->field($field)->select();

        return $ret;
    }

    /*
     * 删除多条记录
     * */
    public function delete_multi_items($map)
    {
        $Product = D('Product');
        $Product->where($map)->delete();
    }

    /*
     * 以uid为单位获取记录组数
     * */
    public function task_hall_unallocated_count($map)
    {
        $Req = D('Product');

        $sql = 'SELECT COUNT(*) AS count
                FROM product
                GROUP BY sender';

        $temp = $Req->query($sql);
        $ret  = count($temp);
        return $ret;
    }

    /*
     * 以uid为单位获取记录组数
     * */
    public function task_hall_unallocated_list($map)
    {
        $ret  = array();
        $Req  = D('Product');

        //$temp = $Req->where($map)->field('id,sender,text,texttype,time')->select();
        $temp = $Req->where($map)->select();
        //$sql  = $Req->getLastSql();
        $Cross = new CrossDatabaseController();
        $temp  = $Cross->process_group($temp,'sender');
        foreach($temp as $value){
            array_push($ret,sort_array($value,'time',$type='asc'));
        }
        return $ret;
    }

    /*
     * 清空数据表
     * */
    public function truncate_table()
    {
        $Product = D('Product');
        $sql     = "TRUNCATE product";
        $count = $Product->count();
        if($count>0)
            $Product->execute($sql);
    }

    /*
     * 重置测试数据
     * */
    public function reset_test_data()
    {
        $resetModel = C('RESET_MODEL');
        $Model = D('Product');
        switch($resetModel)
        {
            case 'local':
                $Model->truncate_table();
                $sql = "INSERT INTO product(sender, recver, smsid, text, texttype, chattype, time) VALUES (16,50,0,'你好啊，后台管理员',1,2,'2015-01-17 12:36:26'),
                        (23,50,0,'问你个问题',1,2,'2015-01-17 12:36:29'),
                        (23,50,0,'我的认证有问题',1,2,'2015-01-17 15:30:01'),
                        (16,50,0,'我的头像怎么被删光了',1,2,'2015-01-18 08:36:26'),
                        (16,50,0,'有没有功德心啊',1,2,'2015-01-18 11:36:23');";
                $Model->insert_multi_items(array(),$sql);
                break;
            case 'server':
                break;
            default:
                $this->error('测试配置出错');
                break;
        }
    }
}
