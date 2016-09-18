<?php 
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/9
 * Time: 13:57
 */

namespace Cms\Controller;

use Cms\Controller;
use Cms\Controller\MessageController;
use Think\Cache\Driver\Redis;
class OperateTooController extends PublicController{

	public function __construct(){
		parent::__construct();
		// 获取redis聊天信息，分配到数据库
		$Redis = D('ImServerRedis');
		$Redis->redis_gettall();
	}

	public function index(){
 
		$this->display();
	}	

	// 聊天主页面
	public function pullconten(){
        $this->spermission(51);
		$type = $_GET['type'];
		 
		
		$model = A('OperateToo','Event');
		$info  = $model->getlistreal($type); // 获取所有未分配的成员
 
		$this->assign('info',$info); 
		$this->display();	
		 
	}

	// 查看聊天记录
	public function chatrecord(){
		$uid  = intval($_GET['uid']);
		$info = D('RealTimeChatLog')->get_multi_items('uid = '.$uid);
		$this->assign('info',$info);
		$this->display();	
	}

	// 获取未分，我的任务。聊天用户
	public function alltask(){
		$info = A('OperateToo','Event')->alltask();
		die(json_encode($info));
	}

	
	public function getchatlog(){
		
		$info = A('OperateToo','Event')->onenumchatlog($_POST['uid'],$_POST['id']);
		die(json_encode($info));

	}


	// 客服信息写入数据库
	public function insertlog(){
		if(IS_POST){
			$_POST['c_type']    = 1;
			$_POST['aid']       = $_SESSION['authId'];
			$_POST['s_time']    = time();
			$_POST['back_type'] = 2;  // 1是用户提问 2客服回答

			$data = array(
	            'uid' => intval(10001),
	            'type'=> intval(2), 
	            'receive'=>intval($_POST['uid']),
	            'message'=>'',
	            'time'=> intval($_POST['s_time']),
	        );
			$temp = array(
			    'type' => 2,
			    'status'=> 1, 
			    'text' => $_POST['content'],
			);
        	$data['message'] = json_encode($temp);
        	
			// 发送redis消息
			$Redis = D('ImServerRedis');
			$Redis->insert_single_item_into_list(C('REDIS_ADMIN_LIST_KEY'),$data);

			// 修改回复状态
			D('RealBack')->update_single_item('uid = '.$_POST['uid'],array('b_type'=>2));

			echo D('RealTimeChatLog')->add($_POST); // 写入聊天记录
		}

	}

	// 获取当前管理员的名称
	public function getnickname(){
		echo $_SESSION['nickname'];
	}

	// 将未分配的用户，分配到我的任我里面
	public function mytask(){

		$uid  = intval($_POST['uid']);
		$aid  = $_SESSION['authId'];
		$damy = D('RealTimeChat')->query("SELECT * FROM cj_admin.cj_real_time_chat WHERE uid = {$uid} ");
	 
		if(!empty($damy)){
			if($damy[0]['aid'] == $_SESSION['authId'] ){
				echo 2;exit;
			}else{
				echo 1;exit;// 用户已分配
			}
		}else{
			$data['aid'] = $aid;
			$data['uid'] = $uid;
			D('RealTimeChat')->execute("INSERT INTO cj_admin.cj_real_time_chat (aid,uid) VALUES({$aid},{$uid}) ");
			echo '2';
		}
 
	}

	// 删除我的任务
	public function deletemytask(){
		D('RealBack')->update_single_item('uid ='.intval($_POST['uid']),array('b_type'=>3)); // 修改回复状态为 3
		D('RealTimeChat')->execute("DELETE FROM cj_admin.cj_real_time_chat WHERE uid = ".intval($_POST['uid'])." and aid = ".$_SESSION['authId']);
		echo 1;
	}

	public function friendjson(){
		echo  '{
				    "status": 1,
				    "msg": "ok",
				    "data": [
				        {
				            "name": "我的任务",
				            "nums": 0,
				            "id": 1,
				            "item": [
				  
				            ]
				        },
				        {
				            "name": "未分配",
				            "nums": 0,
				            "id": 1,
				            "item": [
				  
				            ]
				        }
				    ]
				}';

	}
 


	 
}


?>
