<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/17
 * Time: 16:55
 */

namespace Cms\Model;

use Think\Model;
class FeedbackDataModel extends Model{

    /*
     * 获取单条记录
     * */
    public function get_single_item($map,$field=null)
    {
        $FeedbackData = D('FeedbackData');
        if($field == null)
            $ret = $FeedbackData->where($map)->find();
        else
            $ret = $FeedbackData->where($map)->field($field)->find();

        //$sql = $FeedbackData->getLastSql();
        return $ret;
    }

    /*
     * 获取到im服务器的反馈信息后将数据处理成json格式并存储
     * @param data 来自ProductModel已处理成json格式
     * */
    public function insert_single_item($data=array())
    {
        $FeedbackData = D('FeedbackData');
        $ret = $FeedbackData->data($data)->add();

        return $ret;
    }

    /*
     * 清空整张表
     * */
    public function truncate_table()
    {
        $FeedbackData = D('FeedbackData');
        $count = $FeedbackData->count();
        if($count>0){
            $sql = "TRUNCATE cj_feedback_data";
            $FeedbackData->execute($sql);
        }
    }

    /*
     * 重置测试数据
     * */
    public function reset_test_data()
    {
        $resetModel = C('RESET_MODEL');
        $FeedbackData = D('FeedbackData');
        switch($resetModel)
        {
            case 'local':
                $FeedbackData->truncate_table();
                $AidReq = D('FeedbackReqAid');
                $AidReq->truncate_table('cj_feedback_req_aid_1');
                $AidReq->truncate_table('cj_feedback_req_aid_2');
                $AllocatedModel = D('FeedbackReqAllocated');
                $AllocatedModel->truncate_table();
                $ProcessedModel = D('FeedbackReqProcessed');
                $ProcessedModel->truncate_table();
                $AllocateLogModel = D('FeedbackAllocateLog');
                $AllocateLogModel->truncate_table();
                $ProcessLogModel = D('FeedbackProcessLog');
                $ProcessLogModel->truncate_table();
                break;
            case 'server':
                break;
            default:
                $this->error('测试配置出错');
                break;
        }
    }
}
