<?php
/**
 * Created by PhpStorm.
 * User: zsj
 * Date: 2015/03/12
 * Time: 11:10
 */
namespace Cms\Controller;

use Cms\Controller;
class FaceCarRateController extends PublicController{
	// 给脸打分
	public function index(){
		if($_GET['uid']!=''){
			$and = ' AND uid = '.$_GET['uid'];
		}

		if($_GET['rates'] =='no'){
			$where = 'total = 1 AND f_rtime = 0 AND c_rtime = 0 ';  
		}else{
			$where = 'is_rate = 1';
		}

		$umodel   = D('User');
		$count    = $umodel->where($where)->count(); 
		$userinfo = $umodel->where($where.$and)->order('rand()')->limit(1)->find(); 
		
		$vmap 	  = array('uid'=>$userinfo['uid'],'status'=>1);
		$uvideo   = D('CertificateVideo')->get_single_item($vmap,'p1'); // 视频认证

		$this->assign('uvideo',$uvideo['p1']); 
		$this->assign('count',$count);
		$this->assign('userinfo',$userinfo); 
		$this->assign('h_img',json_decode($userinfo['head_images']));
		$this->display();
	}

	// 打分信息总览
	public function facecarinfo(){

		$admin_id = $this->admin_permission(C('FACE_CAR_INFOSHOW'));
        if(!$admin_id){
            $this->error('没有权限');
            $this->error_message = '没有权限';
        }
        
		$count    = A('User','Event')->getcarcount();
		$this->assign('face',D('User')->where('is_rate = 1')->count());
		$this->assign('car',$count[0]['num']);
		$this->display();
	}

	// 给脸打分动作
	public function faceratepost(){
		$rate = A('Rate','Event');
		$face = $rate->facerate($_POST['uid'],$_POST['rate'],$_POST['pnum']);
		echo 1;
	}

	// 给车打分
	public function car(){
 
		$count    = A('User','Event')->getcarcount();

		$userinfo = A('User','Event')->getcar($_GET['uid']); 

		$this->assign('count',$count[0]['num']);
		$this->assign('car',$car);
		$this->assign('userinfo',$userinfo[0]);
		$this->display();
	}	

	// 给车打分动作
	public function facecarepost(){
		$rate = A('Rate','Event');
		$face = $rate->carrate($_POST['uid'],$_POST['rate']);
		echo 1;
	}


	// 倒入文件
	public function file(){
		ini_set('max_execution_time',1000);
		require_once("./ThinkPHP/Library/Org/Excel/Reader.class.php");
		$data = new \Spreadsheet_Excel_Reader();
		 
		$data->setOutputEncoding('utf-8');
		$data->read($_FILES['rate']['tmp_name']);
		error_reporting(E_ALL ^ E_NOTICE);
		$rest = $data->sheets;
		//ob_start();
		foreach ($rest[0]['cells'] as $key => $value) {
			$pic = D('User')->get_single_item('uid = '.$value[1],'uid,head_images');

			if($pic['uid'] ==''	){
				continue;
			}else{
				if($value[2] == 'A'){
					$rates = 8;
				}elseif($value[2] == 'B'){
					$rates = 7;
				}elseif($value[2] == 'C'){
					$rates = 4;
				}else{
					$rates = 2;
				}
				 
				$rate = A('Rate','Event');
				$face = $rate->facerate($value[1],$rates,count(json_decode($pic['head_images'])),1);
			}
			echo $value[1].'<br>';	
			 
		}
		echo '导入成功';
	}


}




?>


