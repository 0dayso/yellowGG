<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/13
 * Time: 11:50
 */

namespace Cms\Model;

use Cms\Model;
class ChatLogModel extends CjImServerModel{

    /*
     * 获取表名
     * @param    day 0为当天,1为昨天,2为前天...
     * */
    public function get_table($day)
    {
        $date      = date('Ymd',strtotime('-'.$day.' day'));
        $tableName = 'send'.$date;

        return $tableName; 
    }

    /*
     * 获取一天的聊天记录
     * @param    map array(sender,receiver)
     * @param    day 当天,或者往前推N天，有最高时间限制
     * */
    public function get_chat_log($map,$day)
    {
        $Chat  = D('ChatLog');
        $table = $this->get_table($day);
        $ret   = $Chat->table($table)->where($map)->select();

        return $ret;
    }

    // 获取某一张表的数据
    public function get_day_wlog($table,$key='',$Page='',$type='count',$img=''){
        if($key){
            $where = "  AND text like '%".$key."%' ";
        } 
        if($img){
            $and  = " AND texttype = 2 ";
        }

        if($type=='count'){
            $sql = "SELECT count(id) AS num FROM $table  WHERE 1 $where $and ";
        }else{
            $sql = "SELECT * FROM $table  WHERE 1 $where $and ORDER BY `time` DESC  LIMIT ".$Page->firstRow.','.$Page->listRows;
        }

        return D('ChatLog')->query($sql);

    }


    /*
     * 获取当前N天的聊天记录
     * @param map array(sender,receiver)
     * @param day 前N天
     * */
    public function get_multi_chat_log($map,$day)
    {
        $ret   = array();
        $Chat  = D('ChatLog');
        for($i=$day;$i>=0;$i--){
            $table = $this->get_table($i);
            $temp  = $Chat->table($table)->where($map)->select();
            if($temp == false||$temp == null)
                $temp = array();
            $ret   = array_merge($ret,$temp);
        }

        return $ret;
    }

    /*
     * 获取用户的所有聊天记录及回复
     * */
    public function get_user_chat_log($uid,$day)
    {
        $ret   = array();
        $Chat  = D('ChatLog');
        for($i=$day;$i>=0;$i--){
            $table = $this->get_table($i);
            $sendTemp  = $Chat->table($table)->where('sender='.$uid)->select();
            if($sendTemp == false||$sendTemp == null)
                $sendTemp = array();
            $ret   = array_merge($ret,$sendTemp);

            $receiveTemp  = $Chat->table($table)->where('recver='.$uid)->select();
            if($receiveTemp == false||$receiveTemp == null)
                $receiveTemp = array();
            $ret   = array_merge($ret,$receiveTemp);
        }

        $ret = sort_array($ret,'time','asc');

        return $ret;
    }

    /*
     * 获取举报用户对话记录
     * */
    public function get_dialog($sender,$receiver,$day)
    {
        $ret   = array();
        $Chat  = D('ChatLog');
        for($i=$day;$i>=0;$i--){
            $table = $this->get_table($i);
            $map   = array('sender'=>array('EQ',$sender),'recver'=>array('EQ',$receiver),);
            $sendTemp  = $Chat->table($table)->where($map)->select();
            if($sendTemp == false||$sendTemp == null)
                $sendTemp = array();
            $ret   = array_merge($ret,$sendTemp);

            $map   = array('sender'=>array('EQ',$receiver),'recver'=>array('EQ',$sender),);
            $receiveTemp  = $Chat->table($table)->where($map)->select();
            if($receiveTemp == false||$receiveTemp == null)
                $receiveTemp = array();
            $ret   = array_merge($ret,$receiveTemp);
        }

        $ret = sort_array($ret,'time','asc');
        return $ret;
    }
}
