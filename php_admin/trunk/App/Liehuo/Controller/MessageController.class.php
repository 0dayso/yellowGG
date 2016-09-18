<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/15
 * Time: 19:16
 */

namespace Liehuo\Controller;
use Think\Controller;

class MessageController extends Controller
{

    /*
     * 发送系统消息
     * @param uid
     * @param type   7 统一的系统消息 标示
     * @param status
     * @param orths  附加信息
     * @ex {"uid":"10000","type":"1","receive":"50002","message":"hello","time":"142456233"}
     *
     * */
    public function send_system_message($uid,$type,$status,$reason='',$subTime=null,$orths=null)
    {
        $time    = time();
        $case    = $type.'/'.$status;
        $systemAccount = C('SYSTEM_ACCOUNT');
        $data = array(
            'uid' => (int)$systemAccount,
            'type'=> (int)C('REDIS_DATA_JSON_TYPE'),
            'receive'=>(int)$uid,
            'message'=>'',
            'time'=> (int)$time,
        );
        switch($case){
            case 'certificate_car/pass':
                $temp = array(
                    'type' => 1,
                    'status'=> 1,//认证通过
                    'text' => '恭喜您已经成功通过车辆认证！从此您的头像和个人空间都会显示车辆认证图标哦。'.$orths,
                );
                break;
            case 'certificate_car/failed':
                $temp = array(
                    'type' => 1,
                    'status'=> 2,//没有通过
                    'text' => '很抱歉，您没有通过车辆认证！原因是：'.$reason.'。您可尝试重新提交认证。'.$orths,
                );
                break;
            case 'certificate_video/pass':
                $temp = array(
                    'type' => 2,
                    'status'=> 1,
                    'text' => '恭喜您已经成功通过视频认证！从此您的头像和个人空间都会显示视频认证图标哦。'.$orths,
                );
                break;
            case 'certificate_video/failed':
                $temp = array(
                    'type' => 2,
                    'status'=> 2,
                    'text' => '很抱歉，您没有通过视频认证！原因是：'.$reason.'。您可尝试重新提交认证。'.$orths,
                );
                break;
            case 'certificate_accusation/pass':
                $temp = array(
                    'type' => 3,
                    'status'=> 1,
                    'text' => '举报成功！您在'.$subTime.$orths.'感谢您对我们工作的支持和帮助！如果已对您造成财产损失请立即报案。',
                );
                break;
            case 'certificate_accusation/failed':
                $temp = array(
                    'type' => 3,
                    'status'=> 2,
                    'text' => '您在'.$subTime.'对'.$orths.'的举报经核实由于证据不足，暂不支持处理。感谢您的支持！',
                );
                break;
            case 'certificate_accusation/havhandle':
                $temp = array(
                    'type' => 3,
                    'status'=> 2,
                    'text' => '您在'.$subTime.'对'.$orths.'的举报我们已做出警告处理，并会持续关注该用户后续行为，如果涉及明显违规我们将暂停TA使用相关功能！',
                );
                break;
            case 'certificate_accusation/already_forbidden':
                $temp = array(
                    'type' => 3,
                    'status'=> 2,
                    'text' => '您在'.$subTime.'提交的举报并未受理，原因是'.$reason.',初见坚决反对色情、欺诈、违法等行为，我们的宗旨是：打造绿色、健康的社交环境。请继续关注和支持我们的工作！',
                );
                break;
            case 'user_info/modify'://文字审查
                $temp = array(
                    'type'=>7,
                    'status'=>1,
                    'text'=>'您的个人信息（'.$reason.'）违反了初见用户条款，经审查我们已做出删除处理。初见坚决反对色情、违法等不良行为，若发现严重违禁我们将对该账号做封停处理！',
                );
                break;
            case 'head_image/modify'://图片审查
                $temp = array(
                    'type'=>7,
                    'status'=>1,
                    'text'=>'您的个人信息（照片）违反了初见用户条款，经审查我们已做出删除处理。初见坚决反对色情、违法等不良行为，若发现严重违禁我们将对该账号做封停处理！',
                );
                break;
            case 'pushmessage/system'://批量发系统消息
                $temp = array(
                    'type'=>7,
                    'status'=>1,
                    'text'=>$reason,
                );
                break;
            case 'search/golds': // 发放金币
                $temp = array(
                    'type'=>7,
                    'status'=>1,
                    'text'=>'又获得了新的金币，快来领取啊！',
                );
                break;
            case 'search/tag': // 标签未通过
                $temp = array(
                    'type'=>7,
                    'status'=>1,
                    'text'=>$reason,
                );
                break;
            default:
                break;
        }

        $temp = array_to_json($temp);
        $data['message'] = $temp;
        $this->insert_data_to_list($data);
    }

    /**
     * 打分团打分系统消息
     * type 202
     * systemAccount 1000000
     */
    public function scoreFeedMessage($uid,$feed=array())
    {
        $systemAccount = 10000;//C('SYSTEM_ACCOUNT');
        $data = array(
            'uid'     => (int)$systemAccount,
            'type'    => (int)C('REDIS_DATA_JSON_TYPE'),
            'receive' => (int)$uid,
            'message' => '',
            'time'    => time(),
        );
        $feed = json_encode($feed);
        $temp = array(
            'type'   => 202,
            'status' => 1,//认证通过
            'text'   => $feed,
        );
        $temp = array_to_json($temp);
        $data['message'] = $temp;
        return D('ImServerRedis')->insert_single_item_into_list_score(C('REDIS_ADMIN_LIST_KEY'),$data);
    }

    /*
     * 将需要发送的消息插入到redis list
     * */
    public function insert_data_to_list($data=array())
    {
        $Redis = D('ImServerRedis');
        $ret   = $Redis->insert_single_item_into_list(C('REDIS_ADMIN_LIST_KEY'),$data);

        return $ret;
    }

    /*public function array_to_str($data)
    {
        $ret = '';
        foreach($data as $key=>$value){
            $ret .= $key.':'.$value.',';
        }

        array_shift($ret);
        return $ret;
    }*/

    /* 升级版群发消息系统
    *  $users   数组格式 
    *  $message 发送内容
    */
    public function systemMessage($users,$message){    
        if(empty($users)){
            return '';
        }
        $array = array('type'=>intval(7),'status'=>intval(1),'text'=>$message);    
        $params['msg_content'] = json_encode($array,JSON_UNESCAPED_UNICODE);
        $params['target']      = 2;             // 1: 发送给所有当前在线用户, 2: 发送给指定的用户ID(用户ID存在另一张表中) 
        $params['valid_begin'] = NOWTIME;       //intval(self::params('valid_begin'));
        $params['valid_end']   = NOWTIME+86400; //intval(self::params('valid_end'));

        $modelmsg = D('TblSystemMsg');
        $msgId  = $modelmsg->insertMsg($params,$users);
        $ret    = $modelmsg->sendMessage($msgId);
        return  $ret;
        //self::output(['result'=>$ret]);
    }




}
