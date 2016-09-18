<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/1/30
 * Time: 17:21
 */

namespace Cms\Model;

use Cms\Controller\CrossDatabaseController;
use Cms\Model;
class CertificateVideoRequestLogModel extends CjAdminLogModel{

    public function get_info_with_aid_nickname($map,$Page,$field)
    {
        $Cross = new CrossDatabaseController();
        $initModel = array(
            'model_name' => $this->name,
            'data_field' => $field,
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
        );

        $ret   = $Cross->cross_multi_join($map,$Page,$initModel,$joinModelArr);

        return $ret;
    }

    /*
     * 重置测试数据
     * */
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

        //$sql = $Log->getLastSql();
    }
}
