<?php
/**
 * Created by PhpStorm.
 * User: zsj
 * Date: 2015/04/16
 * Time: 13:45
 * Created by PhpStorm.
 * User: Administrator
 * Description : data struct
 *             enum ETextType {     //信息内容类型
                    ETT_UNKNOWN = 0,//未知
                    ETT_TEXT = 1,   //文本
                    ETT_IMAGE = 2,  //图片
                    ETT_TIETU = 3,  //地图
                    ETT_YUYIN = 4,  //语音
                    ETT_DITU = 5    //视频
               };
               enum EChatType {         //聊天信息类型
                    ECT_UNKNOWN = 0,    //未知
                    ECT_NORMAL = 1,     //普通的聊天信息
                    ECT_SYSTEM = 2,     //系统消息
                    ECT_READSTATE = 3,  //读状态
                    ECT_PRODUCT = 4,    //产品
                    ECT_GROUP = 5       //群组消息
               };
 * Date: 2015/4/15
 * Time: 18:20
 */


namespace Liehuo\Model;

use Think\Model;
use Think\Cache\Driver\Redis;

class TblSystemMsg2Model extends Model
{
	protected $connection = 'db_config_score_imsys';
    protected $tableName  = 'tbl_system_msg';

	public function __construct() {
        parent::__construct();
        $this->option = array(
            'host'          => '10.162.43.38',
            'port'          => 6390,
            'timeout'       => 0.1,//响应超时
            'persistent'    => false,
        );
        /*$this->option = array(
            'host'          => '192.168.83.100',
            'port'          => 6379,
            'timeout'       => 0.1,//响应超时
            'persistent'    => false,
        );*/
        $this->msgStruct = array(
            'sys_user_id' => 10000,//系统帐号 1.7开始通过意见反馈账号发送系统消息
            'target'      => 1,//1: 发送给所有当前在线用户, 2: 发送给指定的用户ID(用户ID存在另一张表中) 3: 发送给全部cj用户(用户ID存在另一张表中)
            'msg_content' => '',//如果是图片/语音/地图/视频 url=>alioss
            'text_type'   => 1,//文本类型,1为文字,2为图片,3为地图,4为语音,5为视频
            'chat_type'   => 2,
            'send_to_offline_user' => 1,//发给离线用户，true/false
            'show_duration' => 0,//弹窗
            'valid_begin' => 0,//消息的有效起始时间,unix 年月日时分秒
            'valid_end'   => 0,
            'sys_msg_userid_table' => '',
            'tm_create'   => 0,//写入消息的时间
        );
        $this->tableName  = "";
        $this->tableAlias = "";
        $this->key        = 'cpp_system_msg';
    }

    /**
     * 发送系统消息
     * @param int $msgId
     * @return boolean $ret
     * data数组中的type为消息类型，1为系统，2为产品
     * message中的type，1为聊天，status为参数
     * */
    public function sendMessage($msgId) {
        $Redis = new Redis($this->option);
        $data = ['msgid'=>intval($msgId)];
        $ret = $Redis->rPush($this->key,json_encode($data));
        return $ret;
    }

    /**
     * 给所有用户发消息
     * @param  array     $params 消息内容及参数
     * @param  array     $users  接收消息的用户
     * @return integer   $msgId  消息id
     * */
    public function insertMsgAlluser($message) {

        $array = array('type'=>intval(1),'status'=>intval(1),'text'=>$message);
        $params['msg_content'] = str_replace('\n','\\\n',str_replace('\r','\\\r',json_encode($array,JSON_UNESCAPED_UNICODE)));
        $params['target']      = 2;             // 1: 发送给所有当前在线用户, 2: 发送给指定的用户ID(用户ID存在另一张表中)
        $params['valid_begin'] = NOWTIME;       //intval(self::params('valid_begin'));
        $params['valid_end']   = NOWTIME+86400; //intval(self::params('valid_end'));

        $sql  = "insert into cj_system_msg2.cj_tbl_system_msg
                (sys_user_id,send_to_offline_user,text_type,msg_content,chat_type,target,sys_msg_userid_table,valid_begin,valid_end)
                values(10000,1,1,'{$params['msg_content']}',2,".$params['target'].",'cj_sys_msg_all_users',1,1) ";
        $Model = D('TblSystemMsg2');
        $info  = $Model->execute($sql);
        if($info){
            $mlid  = $Model->query("SELECT sys_msg_id FROM cj_system_msg2.cj_tbl_system_msg  ORDER BY sys_msg_id DESC LIMIT 0,1 ");
            return $this->sendMessage($mlid[0]['sys_msg_id']);
            //return $mlid[0]['sys_msg_id'];
        }
    }

    /**
     * 每次发消息都需要将该消息插入到cj_system_msg2
     * @param  array     $params 消息内容及参数
     * @param  array     $users  接收消息的用户
     * @return integer   $msgId  消息id
     * */
    public function insertMsg($params=array(),$users=array()) {
        $msg_content = str_replace('\n','\\\n',str_replace('\r','\\\r',$params['msg_content']));
        $this->msgStruct['target']      = $params['target'];
        $this->msgStruct['msg_content'] = $msg_content;
        $this->msgStruct['valid_begin'] = $params['valid_begin'];
        $this->msgStruct['valid_end']   = $params['valid_end'];
        $this->msgStruct['tm_create']   = date('Y-m-d H:i:s',time());

        if(isset($params['text_type']))$this->msgStruct['text_type']   = $params['text_type'];
        if(isset($params['send_to_offline_user']))$this->msgStruct['send_to_offline_user'] = $params['send_to_offline_user'];
        if(isset($params['show_duration']))$this->msgStruct['show_duration'] = $params['show_duration'];

        if($params['target'] == 2){
            $this->create_table();
            $this->insert_rev_user($users);
            $this->msgStruct['sys_msg_userid_table'] = $this->tableName;
        }


        $sql  = "insert into cj_system_msg2.cj_tbl_system_msg (sys_user_id,send_to_offline_user,text_type,msg_content,chat_type,target,sys_msg_userid_table,valid_begin,valid_end)
                 values(10000,1,1,'{$msg_content}',2,".$params['target'].",'".$this->tableName."',1,1) ";

        $Model = D('TblSystemMsg2');
        $info  = $Model->execute($sql);

        $mlid  = $Model->query("SELECT  sys_msg_id FROM cj_system_msg2.cj_tbl_system_msg  ORDER BY  sys_msg_id  DESC LIMIT 0,1 ");
        return $mlid[0]['sys_msg_id'];
    }


    /**
     * 给指定用户发系统消息时需要将userid插入cj_user_msg
     * */
    public function insert_rev_user($users) {
        $sql = "insert into cj_system_msg2.".$this->tableName." (userid) values";
        foreach($users as $value){
            $sql .= '('.$value.'),';
        }
        $sql = rtrim($sql,',');
        $Model = D('TblSystemMsg2');
        $info  = $Model->execute($sql);
    }

    /**
     * 每次发消息都需要新建一张表类似sys_msg_user_20140415114530
     * */
    public function create_table() {
        $this->tableAlias = "sys_msg_user_".time();
        $this->tableName  = 'cj_'.$this->tableAlias;
        $sql = "CREATE TABLE cj_system_msg2.".$this->tableName." ("
               ."userid bigint(20) unsigned NOT NULL,"
               ."PRIMARY KEY (userid)"
               .") ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $Model = D('TblSystemMsg2');
        $Model->execute($sql);
    }


}


?>