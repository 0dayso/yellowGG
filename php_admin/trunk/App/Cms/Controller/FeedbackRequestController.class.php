<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/18
 * Time: 11:30
 */

namespace Cms\Controller;

use Cms\Controller;
class FeedbackRequestController extends PublicController{

    /*
     * 处理审核时需要处理的请求,动作：审核
     * */
    public function action_certificate($menu,$type,$data=array())
    {
        $id  = $data['accusation_id'];
        $time = $data['accept_time'];
        $offender_uid = $data['offender_uid'];
        //清楚请求队列该item记录，添加到相应的队列
        switch($menu) {
            case '1'://任务大厅
                if($type == '1'){
                    //需要删除一个未处理任务,同时增加一个已处理任务
                    $this->delete_unallocated_accusation_request($map=array('accusation_id'=>array('EQ',$id)));
                    $this->add_processed_accusation_request($data=array('accusation_id'=>$id,'dtime'=>$time));
                }
                elseif($type == '2'){
                    //todo 在任务大厅已分配里审核数据不符合逻辑，待讨论
                    //todo 需要删除一个已分配给某个管理员的任务,同时增加一个已处理任务
                    /*$TaskHall  = A('TaskHall');
                    $TaskHall->add_finished_accusation_request($uid,$addLog['dtime']);*/
                }
                elseif($type == '3'){
                    //需要覆盖一个已处理任务
                    $this->add_processed_accusation_request($data=array('accusation_id'=>$id,'dtime'=>$time));
                }
                else{
                    $this->error('页面出错啦');
                }
                break;
            case '2'://我
                $this->delete_allocated_accusation_request_aid($map=array('accusation_id'=>array('EQ',$id)));
                $this->add_processed_accusation_request($data=array('accusation_id'=>$id,'offender_uid'=>$offender_uid,'dtime'=>$time));
                break;
            default:
                break;
        }
    }

    /*
     * 处理分配时需要处理的请求,只有任务大厅有分配功能，动作：分配
     * 在已分配里再分配时data里应该包含需要被删除任务的aid
     * */
    public function action_allocate($param,$data)
    {
        $allocateTime = $param['allocate_time'];
        $allocateTo   = $param['aid'];

        switch($param['type'])
        {
            case 'unallocated'://在未分配里分配任务
                $idArr = $this->delete_product_data_add_feedback_data($map = array('sender'=>array('IN',$data)));
                $insertMultiItems = array();
                foreach($idArr as $value){
                    $temp = array('feedback_data_id'=>$value,'dtime'=>$allocateTime);
                    array_push($insertMultiItems,$temp);
                }
                $this->add_allocated_request_aid($allocateTo,$insertMultiItems,'feedback_data_id,dtime');
                for($i=0;$i<count($insertMultiItems);$i++){
                    $insertMultiItems[$i]['aid'] = $allocateTo;
                }
                $this->add_allocated_request($insertMultiItems);
                for($i=0;$i<count($insertMultiItems);$i++){
                    $insertMultiItems[$i]['allocate_admin_id'] = $_SESSION['authId'];
                }
                $this->add_allocate_log($insertMultiItems);
                break;
            case 'allocated'://在已分配里分配任务
                foreach($data as $value){
                    $this->delete_allocated_request_aid($map=array('feedback_data_id'=>array('EQ',$value['feedback_data_id'])),$value['aid']);
                    $this->update_allocated_request($map=array('feedback_data_id'=>array('EQ',$value['feedback_data_id'])),array('aid'=>$allocateTo,'dtime'=>$allocateTime));
                    $insertMultiItems[] = array('feedback_data_id'=>$value['feedback_data_id'],'dtime'=>$allocateTime);
                }
                $this->add_allocated_request_aid($allocateTo,$insertMultiItems,'feedback_data_id,dtime');
                break;
            default:
                break;
        }
    }

    /*
     * 在问分配里分配，首先要获取product表里的数据,处理并插入到cj_feedback_data
     * 然后在product里删除
     * @param map array()
     * @return ret array 新插入的cj_feedback_data.id
     * */
    public function delete_product_data_add_feedback_data($map)
    {
        $ret = array();
        $deleteIdArr = array();
        $Product = D('Product');
        $list    = $Product->task_hall_unallocated_list($map);
        foreach($list as $value){
            if(count($value)>0){
                $start = current($value);
                $end   = end($value);
                $productIdArr = array();
                foreach($value as $subValue){
                    array_push($deleteIdArr,$subValue['id']);
                    array_push($productIdArr,$subValue['id']);
                }
                $productIdArrJson = array_to_json($productIdArr);
                $newFeedbackItem = array(
                    'uid' => $start['sender'],
                    'data' => array_to_json($value),
                    'start_time' => $start['time'],
                    'end_time' => $end['time'],
                    'product_id_arr'=>$productIdArrJson,
                );
                $FeedbackData = D('FeedbackData');
                $newId = $FeedbackData->insert_single_item($newFeedbackItem);
                if($newId)
                    array_push($ret,$newId);
            }
        }

        //这里在删除时需要考虑瞬时进来的同一sender的记录
        $Product->delete_multi_items(array('id'=>array('IN',$deleteIdArr)));
        return $ret;
    }

    /*
     * 在完成提交单条任务后删除该举报request stack中的一条记录
     * @param map array('accusation_id')
     *
     * */
    public function delete_allocated_request_aid($map=array(),$aid)
    {
        $Req   = D('FeedbackReqAid');
        $table = C('FEEDBACK_REQUEST_AID').$aid;
        $Req->delete_single_item($map,$table);
    }

    /*
     * 删除单条未分配任务记录
     * @param map array('accusation_id')
     * */
    public function delete_unallocated_accusation_request($map=array())
    {
        $Req = D('AccusationReqUnallocated');
        $Req->delete_single_item($map);
    }

    /*
     * 向已分配列表里添加已经处理过的请求
     * @param data array('accusation_id','aid','dtime')
     * */
    public function add_allocated_request($data=array())
    {
        $Req = D('FeedbackReqAllocated');
        $Req->insert_multi_items($data);
    }

    /*
     * 向已分配列表里添加已经处理过的请求
     * @param data array('accusation_id','aid','dtime')
     * */
    public function add_allocate_log($data=array())
    {
        $Log = D('FeedbackAllocateLog');
        $Log->insert_multi_items($data);
    }

    /*
     * 更新已分配列表内容
     * @param data array('certificate_video_id','aid','dtime')
     * */
    public function update_allocated_request($map,$data)
    {
        $Req  = D('FeedbackReqAllocated');
        $Req->update_single_item($map,$data);
    }

    /*
     * 向某个aid表添加多条记录
     * @param data array(0=>array(),1=>array()...)
     * */
    public function add_allocated_request_aid($aid,$data=array(),$field)
    {
        $table = C('FEEDBACK_REQUEST_AID').$aid;

        $Req  = D('FeedbackReqAid');
        $Req->insert_multi_items($data,$table,$field);
    }

    /*
     * 向已处理列表里添加已经处理过的请求
     * */
    public function add_processed_accusation_request($data=array())
    {
        $aid = $_SESSION['authId'];
        $Req = D('AccusationReqProcessed');
        $haved = $Req->get_single_item(array(
            'accusation_id'=>$data['accusation_id'],
        ));

        //如果已经处理过就覆盖，没处理过就insert
        if(count($haved)>0){
            $Req->update_single_item(
                array(
                    'accusation_id'=>$data['accusation_id'],
                ),
                array(
                    'aid'=>$aid,
                    'dtime'=>$data['dtime'],
                    'offender_uid'=>$data['offender_uid'],
                )
            );
        }
        else{
            $Req->insert_single_item(array(
                'accusation_id'=>$data['accusation_id'],
                'aid'=>$aid,
                'dtime'=>$data['dtime'],
                'offender_uid'=>$data['offender_uid'],
            ));
        }
    }
} 