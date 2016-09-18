<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/2
 * Time: 10:33
 */

namespace Cms\Model;

use Cms\Model;
class AccusationRequestLogModel extends CjAdminLogModel{

    public function insert_multi_items($data)
    {
        $Model = D($this->name);
        $Model->addAll($data);
    }

    public function insert_single_item($data)
    {
        $Model = D($this->name);
        $Model->data($data)->add();
    }

    public function truncate_table()
    {
        $Model = D($this->name);
        $sql   = "TRUNCATE ".$this->trueTableName;
        $count = $Model->count();
        if($count>0)
            $Model->execute($sql);
    }

    public function get_multi_items($map,$field=null)
    {
        $Model = D($this->name);
        if($field == null){
            $ret   = $Model->where($map)->select();
        }
        else{
            $ret   = $Model->where($map)->field($field)->select();
        }

        return $ret;
    }

    public function reset_test_data()
    {
        $resetModel = C('RESET_MODEL');
        $Log = D($this->name);
        switch($resetModel)
        {
            case 'local':
                $Log->truncate_table();
                break;
            case 'server':
                break;
            default:
                $this->error('测试数据配置出错');
                break;
        }
    }
}
