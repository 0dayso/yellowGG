<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/13
 * Time: 11:50
 */

namespace Liehuo\Model;

class ChatLogBaseModel extends CjImBaseModel
{

    protected $autoCheckFields = false;
    protected $connection      = 'conn_im_log';
    protected $redis_config    = 'redis_im';

  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    if($uid = (int)$arr['uid'])
    {
      if($arr['filter'] == 'feedback')
      {
        $fid = (int)A('Feedback')->account_feedback ?: 1000;
        $map['_string'] = '(sender = '.$uid.' and recver = '.$fid.') or (sender = '.$fid.' and recver = '.$uid.')';
      }
      else $map['_string'] = 'sender = '.$uid.' or recver = '.$uid;
    }
    if($smsid  = (int)$arr['smsid'])  $map['smsid']  = $smsid;
    if($sender = (int)$arr['sender']) $map['sender'] = $sender;
    if($recver = (int)$arr['recver']) $map['recver'] = $recver;
    if($texttype = (int)$arr['texttype']) $map['texttype'] = $texttype;
    if($chattype = (int)$arr['chattype']) $map['chattype'] = $chattype;
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      $this->sday = (int)((NOW_TIME - $stime) / 24 / 60 / 60);
      $map['time'][] = array('egt',date('Y-m-d 00:00:00',$stime));
    }
    if($arr['etime'] && $etime = strtotime($_REQUEST['etime'] = $_GET['etime'] = urldecode(urldecode($arr['etime']))))
    {
      $fmt = 'Y-m-d H:i:59';
      strlen($arr['etime']) <= 10 && $fmt = 'Y-m-d 23:59:59';
      $this->eday = (int)((NOW_TIME - $etime) / 24 / 60 / 60);
      $map['time'][] = array('elt',date($fmt,$etime));
    }
    if($kwd = urldecode(trim($arr['kwd'])))
    {
      $arr['kwd'] = $_GET['kwd'] = $kwd;
      $field = trim($arr['search_field']);
      $map['_complex'] || $map['_complex'] = ['_logic' => 'or'];
      //if(!$field || $field == 'text') $map['_complex'][$alias.'text'][] = ['like','%'.$kwd.'%'];
      foreach(preg_split('/[\s|;,]+/',$kwd) ?: [] as $v)
      {
        $map['_complex'][$alias.'text'][] = ['like','%'.$v.'%'];
      }
      $map['_complex'][$alias.'text'][] = 'or';
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'sender') $map['_complex'][$alias.'sender'] = ['like',$kwd];
        if(!$field || $field == 'recver') $map['_complex'][$alias.'recver'] = ['like',$kwd];
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

    /*
     * 获取表名
     * @param    day 0为当天,1为昨天,2为前天...
     * */
    public function get_table($day = 0,$typ = null)
    {
        isset($typ) || $typ = 'send';
        $date      = date('Ymd',strtotime('-'.(int)$day.' day'));
        $tableName = $typ.$date;
        return $tableName; 
    }

    // Union方式获取多天的聊天记录
    public function get_chat_log_union($map = array(),$day = 0,$eday = 0,$has_product = false)
    {
        $day = (int)$day;
        $tbs = $this->query('select table_name from information_schema.tables where table_name like \'send%\' or table_name like \'product%\'')
            ?: $this->query('show tables like \'send%\'')
            ?: [];
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
            //$map['sender'] && $arr[] = M('','',$this->connection)->table('sendsystem')->field($fds)->where($map)->buildSql();
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
            if(($tb1 = $this->get_table($i,'product')) && in_array($tb1,$tbs))
            {
              $sql = M('','',$this->connection)->table($tb1)->field($fds)->where($map)->buildSql();
              $arr[] = $sql;
            }
            if(($tb2 = $this->get_table($i,'sendsystem')) && in_array($tb2,$tbs))
            {
              $sql = M('','',$this->connection)->table($tb2)->field($fds)->where($map)->buildSql();
              $arr[] = $sql;
            }
        }
        if($arr)
        {
          $mod->union($arr,true);
          $mod->table($mod->buildSql().' tmp');
        }
        if($map) $mod->where($map);
        $mod->order('time desc');
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
        if(in_array((int)$v['texttype'],array(2,3,4,5,100)) || preg_match('/^\s*\{.+\}\s*$/i',$v['text']))
        {
          $v['text_json'] = json_decode($v['text'],true) ?: [];
          if($v['texttype'] == '100' && in_array($v['text_json']['type'],[7,8,10,11]))
          {
            $v['text_json']['json'] = json_decode($v['text_json']['text'],true) ?: [];
          }
          $v['text_json']['text'] = htmlspecialchars($v['text_json']['text']);//xss
        }
        $v['text'] = htmlspecialchars($v['text']);//xss
        if($v['texttype'] == '1')
        {
          $v['text_html'] = emoji_unified_to_html(strtr($v['text'],$wds ?: array()));
          $v['text_html'] = nl2br($v['text_html']);
        }
        return $v;
      },$arr ?: array());
      return $arr;
    }

    // 获取文本聊天内容
    public function get_msg_text($txt = '')
    {
      if(preg_match('/^\{.*\}$/',$txt) && $jso = json_decode(trim($txt),true))
      {
        $txt = trim($jso['text']);
        if(preg_match('/^\{.*\}$/',$txt) && json_decode(trim($txt),true)) $txt = '';
      }
      elseif(preg_match('/^\[.*\]$/',$txt) && json_decode(trim($txt),true)) $txt = '';
      return $txt;
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
