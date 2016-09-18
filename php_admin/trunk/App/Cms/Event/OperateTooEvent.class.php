<?php 
/**
 * Created by PhpStorm.
 * User: zsj
 * Date: 2015/4/22
 * Time: 14:37
 */

namespace Cms\Event;

use Cms\Controller\MessageController;
use Cms\Event;
class OperateTooEvent extends PublicEvent{
	
	// 获取所有的聊天记录
	public function getlistreal($type='0'){
		$type  = ($type==0)?'>0':' = '.$type;
		$model = D('User');
		$sql   = "SELECT uid FROM  cj_admin_log.cj_real_time_chat_log  GROUP BY uid ORDER BY id DESC ";
 		$rest  = implode(',',array_column($model->query($sql),'uid')); 
		$sqlt  = "SELECT log.id,log.uid,log.content,log.c_type,log.s_time,log.back_type,u.head_images,u.nickname
				  FROM  (SELECT * FROM  cj_admin_log.cj_real_time_chat_log WHERE   uid IN($rest) ORDER BY id DESC) AS log
				  LEFT JOIN chujian.cj_user AS u ON log.uid = u.uid
				  GROUP BY log.uid ORDER BY   log.id DESC";
	 
	    return $model->query($sqlt);
		 
	}

	// 获取聊天对象
	public function alltask(){

		$model = D('User');
 		// 我的任务
		$mysql  = "SELECT log.uid,u.head_images,u.nickname,u.face_url,u.server_version
				   FROM  cj_admin.cj_real_time_chat AS log
			       LEFT JOIN chujian.cj_user AS u ON log.uid = u.uid
			       WHERE log.aid = '{$_SESSION['authId']}'
		    	   GROUP BY log.uid ";
		$my = $model->query($mysql);
		if(!empty($my)){
			$i = 0;
			foreach($my as  $val){
                if($val['server_version'] == '1'){
                    $head[0] = $val['face_url'];
                }else{
                    $head    = json_decode($val['head_images']);
                }
				$li .= '<li data-id="'.$val['uid'].'" class="xxim_childnode" type="one" id="my_'.$val['uid'].'"  >
							<img src="http://headimage.chujian.im/'.$head[0].'" class="xxim_oneface">
							<span class="xxim_onename">'.$val['nickname'].'</span>
						</li>';
				$i++;
			}
			$data['my'] = array('list'=>$li,'num'=>$i);
		}
  			

		// 获取所有已分配给客服的用户反馈
 		$all   = "SELECT uid  FROM  cj_admin.cj_real_time_chat  ";
 		$getid  = implode(',',array_column($model->query($all),'uid'));

 		if( $getid  !=''){
 			$where = " log.uid NOT IN ($getid) AND ";
 		}
 		 
		$sqlt  = "SELECT log.id,log.uid,u.head_images,u.nickname,u.face_url,u.server_version
				  FROM   cj_admin_log.cj_real_time_chat_log   AS log
				  LEFT JOIN chujian.cj_user       AS u ON log.uid = u.uid
				  LEFT JOIN cj_admin.cj_real_back AS b ON log.uid = b.uid
				  WHERE $where b.b_type = 1
				  GROUP BY log.uid ";

		$allno = $model->query($sqlt);

		if(!empty($allno)){
			$j = 0;
			foreach($allno as  $val){
                if($val['server_version'] == '1'){
                    $head[0] = $val['face_url'];
                }else{
                    $head    = json_decode($val['head_images']);
                }
				$tli .= '<li data-id="'.$val['uid'].'" class="xxim_childnode" type="one">
							<img src="http://headimage.chujian.im/'.$head[0].'" class="xxim_oneface">
							<span class="xxim_onename">'.$val['nickname'].'</span>
						</li>';
				$j++;
			}
			$data['showall'] = array('list'=>$tli,'num'=>$j);
		}

		return $data;

	} 




	// ajax 获取用户聊天记录 $uid 客户的id   $id 客服的最后一条id
	public function onenumchatlog($uid,$id=''){
 		 
		if($id ==''){
			$id  = D('RealTimeChatLog')->field('id')->where('aid!=0 and uid =  '.$uid)->limit(1)->order('id desc')->select();
		 	$id  = intval($id[0]['id']);
		}else{
			$id  = intval($id);
		}

 		// 获取用户的最后一条回复
		$sql   = "SELECT * FROM cj_admin_log.cj_real_time_chat_log WHERE uid = {$uid} AND id > $id AND back_type = 1 LIMIT 0,1  ";
		$value = D('RealTimeChatLog')->query($sql);

  		if(!empty($value)){
            if($value[0]['c_type'] == '2' ){
                $img  = json_decode($value[0]['content'],true);
                $content = '<img src="'.$img['originPhotoUrl'].'" width="100" height="100">';
            }elseif($value[0]['c_type'] == '1'){
                $content = $value[0]['content'];
            }else{
                $content = '音频';
            }
			$data['id']      = $value[0]['id'];
			$data['uid']     = $value[0]['uid'];
			$data['content'] = $content;
			$data['time']    = date('Y-m-d H:i:s',$value[0]['s_time']);
			return $data;
  		}
	 
	}




}

	

?>