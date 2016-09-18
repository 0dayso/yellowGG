<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/16
 * Time: 11:39
 */

namespace Liehuo\Model;

use Think\Model;
use Think\Cache\Driver\Redis;
class ImServerRedisModel extends Model
{

    protected $autoCheckFields = false;

    /*
     * 往list里插入一条记录,如果是空list可以自动生成该list
     * */
    public function insert_single_item_into_list($listName,$data=array())
    {
        $ret = false;
        if(C('REDIS_START')) {
            $option = C('im_server_redis_send_config');
            $Redis = new Redis($option);
            $value = array_to_json($data);
            $ret = $Redis->rPush($listName, $value);
        }

        return $ret;
    }

    // 初见颜值版打分消息
    public function insert_single_item_into_list_score($listName,$data=array())
    {
        $ret = false;
        if(C('REDIS_START'))
        {
            $option = C('system_message_score');
            $Redis = new Redis($option);
            $value = array_to_json($data);
            $ret = $Redis->rPush($listName, $value);
        }
        return $ret;
    }

    /*
     * list插入测试
     * */
    public function list_push_test($listName)
    {
        if(C('REDIS_START')){
            $redisListArr = array();
            $option = C('im_server_redis_config');
            $Redis  = new Redis($option);
            $exists = $Redis->exists($listName);
            if(!$exists){
                $initLen  = $Redis->rPush($listName,'');//$ret = $Redis->lSet($listName,0,'x');
                $initData = $Redis->lPop($listName);
            }

            for($i=1;$i<11;$i++){
                $data = array(
                    'uid'=>'0',
                    'type'=>'2',
                    'message'=>'hello',
                    'receiver'=>('50000'.$i),
                    'time'=>time(),
                );

                $value = array_to_json($data);
                $ret = $Redis->rPush($listName, $value);
                //$ret = $Redis->hSet($listName,$i,$value);
            }

            for($i=1;$i<11;$i++){
                array_push($redisListArr,$Redis->lPop($listName));
            }
        }
    }

     
    /*  获取客服聊天信息 10001
        $list   = $Redis->lrange($keyName,0,-1);
        ToID        = 1;    //接收者
        Index       = 2;    //客户端编号
        Text        = 3;    //聊天内容
        TextType    = 1;    1文字 2 图片 3贴图 4语音 5地图
    */
    public function redis_gettall(){
        $option  = C('im_server_redis_config');
        $keyName = 'customer_service_10001';
        $Redis   = new Redis($option);
        
        $model = D('RealBack');
        $num   = $Redis->llen($keyName);
        $array = array();
        for($i = 0; $i < $num; $i++){ 
            $info = $Redis->lpop($keyName);
            $ars = json_decode($info);
            $array[$i]['uid']      = $back[$i]['uid'] = intval($ars->FromID);
            $array[$i]['content']  = $ars->Text;
            $array[$i]['c_type']   = intval($ars->TextType);
            $array[$i]['s_time']   = intval($ars->Time);
            $array[$i]['aid']      = 0;
            $array[$i]['back_type']= 1; 
 
            $back[$i]['b_type']    = 1;

            $uarr[] =  $array[$i]['uid'];
            //$model->update_single_item('uid='.intval($ars->FromID),array('b_type'=>1));
        } 
        if(!empty($array)){
            $model->delete_single_item(implode(',',$uarr));   // 删除用户回复状态，
            $model->insert_multi_items($back);                // 添加新的用户回复状态，
            D('RealTimeChatLog')->insert_multi_items($array); // 写入消息记录
        }
    }

    /*  获取客服聊天信息 10002
        $list   = $Redis->lrange($keyName,0,-1);
        ToID        = 1;    //接收者
        Index       = 2;    //客户端编号
        Text        = 3;    //聊天内容
        TextType    = 1;    1文字 2 图片 3贴图 4语音 5地图
    */
    public function redis_gettall_t(){
        $option  = C('im_server_redis_config');
        $keyName = 'customer_service_10002';
        $Redis   = new Redis($option);

        $model = D('RealBackT');
        $num   = $Redis->llen($keyName);
        $array = array();
        for($i = 0; $i < $num; $i++){
            $info = $Redis->lpop($keyName);
            $ars = json_decode($info);
            $array[$i]['uid']      = $back[$i]['uid'] = intval($ars->FromID);
            $array[$i]['content']  = $ars->Text;
            $array[$i]['c_type']   = intval($ars->TextType);
            $array[$i]['s_time']   = intval($ars->Time);
            $array[$i]['aid']      = 0;
            $array[$i]['back_type']= 1;

            $back[$i]['b_type']    = 1;

            $uarr[] =  $array[$i]['uid'];

        }
        if(!empty($array)){
            $model->delete_single_item(implode(',',$uarr));   // 删除用户回复状态，
            $model->insert_multi_items($back);                // 添加新的用户回复状态，
            D('RealTimeChatLogT')->insert_multi_items($array); // 写入消息记录
        }
    }




} 