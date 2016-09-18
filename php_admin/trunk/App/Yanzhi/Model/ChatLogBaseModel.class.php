<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/13
 * Time: 11:50
 */

namespace Yanzhi\Model;

class ChatLogBaseModel extends CjImBaseModel
{

    protected $autoCheckFields = false;
    protected $redis_config    = 'redis_im';

    /*
     * 获取表名
     * @param    day 0为当天,1为昨天,2为前天...
     * */
    public function get_table($day = 0)
    {
        $date      = date('Ymd',strtotime('-'.(int)$day.' day'));
        $tableName = 'send'.$date;
        return $tableName; 
    }

    // Union方式获取多天的聊天记录
    public function get_chat_log_union($map = array(),$day = 0,$eday = 0,$has_product = false)
    {
        $day = (int)$day;
        $tbs = $this->query('show tables like \'send%\'') ?: array();
        $tbs = array_map(function($v)
        {
            return array_values($v)[0];
        },$tbs);
        $fds = $this->fields ?: array('id','sender','recver','smsid','text','texttype','chattype','time');
        $mod = $this;
        $mod->has_main_table = false;
        $arr = array();
        if($has_product)
        {
            $mod->table('product')->field($fds)->where($map);
            $mod->has_main_table = true;
        }
        for($i = $day;$i >= (int)$eday;$i--)
        {
            $tab = $this->get_table($i);
            if(!in_array($tab,$tbs)) continue;
            if(!$mod->has_main_table)
            {
                $mod->table($tab)->field($fds)->where($map);
                $mod->has_main_table = true;
                continue;
            }
            $sql = M('','',$this->connection)->table($tab)->field($fds)->where($map)->buildSql();
            $arr[] = $sql;
        }
        if($arr) $mod->union($arr,true);
        $mod->table($mod->buildSql().' tmp')->order('time desc');
        if($map) $mod->where($map);
        if(!$mod->has_main_table && $tbs[0]) $mod->table($tbs[0])->where(array('id' => 0))->limit(1);
        return $mod;
    }

    // 格式化聊天内容 emoji 替换敏感词
    public function format_text_all($arr = array())
    {
      import('Think.Emoji');
      $wds = array();
      foreach(D('SensitiveWords')->get_multi_items('','word') ?: array() as $v)
      {
        $wds[$v['word']] = '<a style=color:red;font-weight:900;>'.$v['word'].'</a>';
      }
      $arr = array_map(function($v) use($wds)
      {
        if(in_array((int)$v['texttype'],array(2,3,4,5)) || preg_match('/^\s*\{.+\}\s*$/i',$v['text']))
        {
          $v['text_json'] = json_decode($v['text'],true) ?: array();
        }
        if($v['texttype'] == '1')
        {
          $v['text_html'] = emoji_unified_to_html(strtr($v['text'],$wds ?: array()));
          $v['text_html'] = nl2br($v['text_html']);
        }
        return $v;
      },$arr ?: array());
      return $arr;
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
