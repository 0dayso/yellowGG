<?php
namespace Liehuo\Model;

class LiveChatLogModel extends CjImBaseModel
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
    if($uid = (int)$arr['uid']) $map['_string'] = '(sender = '.$uid.' or recver = '.$uid.')';
    if($rid = ((int)$arr['live_chat_room_id'] ?: (int)$arr['room_id'])) $map['live_chat_room_id'] = $rid;
    if($msgid  = (int)$arr['msgid'])  $map['msgid']  = $msgid;
    if($sender = (int)$arr['sender']) $map['sender'] = $sender;
    if($major_type = ((int)$arr['chat_major_type'] ?: (int)$arr['major_type'])) $map['chat_major_type'] = $major_type;
    if($minor_type = ((int)$arr['chat_minor_type'] ?: (int)$arr['minor_type'])) $map['chat_minor_type'] = $minor_type;
    else $map['chat_minor_type'] = ['in',
    [
      MessageModel::MINOR_TYPE_TEXT,
      MessageModel::MINOR_TYPE_BARRAGE,
      MessageModel::MINOR_TYPE_HORN,
    ]];
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      $this->sday = (int)((NOW_TIME - strtotime(date('Y-m-d',$stime))) / 24 / 60 / 60);
      $map['time'][] = array('egt',date('Y-m-d 00:00:00',$stime));
      isset($arr['etime']) || $arr['etime'] = $_REQUEST['etime'] = date('Y-m-d 23:59:59',$stime);
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      $this->eday = (int)((NOW_TIME - strtotime(date('Y-m-d',$etime))) / 24 / 60 / 60);
      $map['time'][] = array('elt',date('Y-m-d 23:59:59',$etime));
    }
    if($kwd = urldecode(trim($arr['kwd'])))
    {
      $arr['kwd'] = $_GET['kwd'] = $kwd;
      $field = trim($arr['search_field']);
      $map['_complex'] || $map['_complex'] = ['_logic' => 'or'];
      //if(!$field || $field == 'content') $map['_complex'][$alias.'content'][] = ['like','%'.$kwd.'%'];
      foreach(preg_split('/[\s|;,]+/',$kwd) ?: [] as $v)
      {
        $map['_complex'][$alias.'content'][] = ['like','%'.$v.'%'];
      }
      $map['_complex'][$alias.'content'][] = 'or';
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'sender') $map['_complex'][$alias.'sender'] = ['like',$kwd];
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

    /*
     * 获取表名
     * @param    day 0为当天,1为昨天,2为前天...
     * */
    public function get_table_byday($day = 0)
    {
      $date = date('Ymd',strtotime('-'.(int)$day.' day'));
      return 'livechat_'.$date; 
    }

    // Union方式获取多天的聊天记录
    public function get_table_union($map = [],$day = 0,$eday = 0)
    {
      $day = (int)$day;
      $tbs = $this->query('show tables like \'livechat_%\'') ?: [];
      $tbs = array_map(function($v)
      {
        $arr = array_values($v) ?: [];
        return $arr[0];
      },$tbs);
      $fds = $this->getDbFields() ?: '*';
      $mod = $this;
      $mod->has_main_table = false;
      $arr = [];
      for($i = $day;$i >= (int)$eday;$i--)
      {
        $tab = $this->get_table_byday($i);
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
      if($arr)
      {
        $mod->union($arr,true);
        $mod->table($mod->buildSql().' tmp');
      }
      if($map) $mod->where($map);
      $mod->order('time desc');
      if(!$mod->has_main_table && $tbs[0]) $mod->table($tbs[0])->where(['id' => 0])->limit(1);
      return $mod;
    }

    // 格式化聊天内容 emoji 替换敏感词
    public function format_text_all($arr = [])
    {
      import('Think.Emoji');
      $wds = [];
      foreach(D('SensitiveWords')->get_multi_items('','word') ?: [] as $v)
      {
        $wds[$v['word']] = '<a style=color:red;font-weight:900;>'.$v['word'].'</a>';
      }
      $arr = array_map(function($v) use($wds)
      {
        if(preg_match('/^\s*\{.+\}\s*$/i',$v['content']))
        {
          $v['json'] = json_decode($v['content'],true) ?: [];
          if(preg_match('/^\s*\{.+\}\s*$/i',$v['json']['text']))
          {
            $v['json']['json'] = json_decode($v['json']['text'],true) ?: [];
          }
          $v['json']['text'] = htmlspecialchars($v['json']['text']);//xss
        }
        $v['content'] = htmlspecialchars($v['content']);//xss
        if($v['texttype'] == '1')
        {
          $v['html'] = emoji_unified_to_html(strtr($v['content'],$wds ?: []));
          $v['html'] = nl2br($v['html']);
        }
        return $v;
      },$arr ?: []);
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
      $tab = $this->get_table_byday($day);
      $ret = $this->table($tab)->where($map)->select();
      return $ret;
    }

}