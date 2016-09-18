<?php
/**
 * Created by PhpStorm.
 * User: zsj
 * Date: 2015/03/12
 * Time: 11:10
 */
	
	namespace Cms\Controller;
	use Cms\Controller;

	class PrizeManageController extends PublicController{
		
		// 奖品列表
		public function index(){

			 
            $admin_id = $this->admin_permission(C('PRIZE_MANAGE_INDEX'));
            if(!$admin_id){
                $this->error('没有权限,帮你跳转到中奖纪录！',U('prize_manage/prizerecord',array('menu'=>'account_manage')));
            }
 			// 获取奖品列表
 			$list = A('Prize','Event')->getlistprizeinfo();

			$this->assign('list',$list);
			$this->display();
		}

		// 奖品删除
		public function delprize(){
			$del = D('Prize')->update_multi_items('id = '.$_GET['id'],array('del'=>1));
 			$this->redirect('index');
			$this->display();
		}

		// 设置奖品 （添加|修改）
		public function setprize(){
		 
			if($_POST){

				$pdata['prize']         = $_POST['name'];
				$pdata['description']  = $_POST['description'];
				
				$data['model']    = $_POST['type'];
				$data['num']     = $_POST['number'];
				$data['list']    = $_POST['list'];
				$data['prizeid'] = $_POST['id'];

				$prize = D('Prize');
				$prule = D('PrizeRule');

				
				// 修改奖品信息
				if($_POST['id']){ 
					$rmap['id']  = $_POST['id'];
					$umap['id']  = $_POST['prizeid'];
					$prize->update_single_item($rmap,$pdata);
					if($umap['id']){
						$prule->update_single_item($umap,$data);
					}else{
						$prule->insert_single_item($data);
					}
				}else{ // 添加
					$id = $prize->insert_single_item($pdata);
					$data['prizeid'] = $id;
					$prule->insert_single_item($data);
				}	
				$this->redirect('index');
			}else{
				if($_GET['id']){
					$pinfo = D('Prize')->get_single_item('id ='.$_GET['id']);
					$prule = D('PrizeRule')->get_multi_items('prizeid = '.$_GET['id']);
					if($_GET['puid']){
						$pr = D('PrizeRule')->get_single_item('prizeid = '.$_GET['id'].' AND id = '.$_GET['puid']);
						$this->assign('pr',$pr);
					}
					
					$this->assign('pinfo',$pinfo);
					$this->assign('prule',$prule);
					
				}

				$this->display();
			}

		}

		// 中奖纪录
		public function prizerecord(){
			$pid    = $_GET['pid'];
			$date  = ($_GET['date']=='')?date('Y-m',time()):$_GET['date'];


			$model = A('Prize','Event');
			$list  = $model->lottery();
			
			// 获取第一个奖品的兑奖情况
			if($pid){ 
				$str    = $model->dayinfo($pid,$date);
			}else{
				$str    = $model->dayinfo($list[0]['id'],$date);
			}


			$this->assign('date',$date);
			$this->assign('str',$str);
			$this->assign('list',$list);
			$this->display();
		}
 

		// 兑换详情
		public function pxchange(){

			$info = A('Prize','Event')->infolistprize($_GET);
			 
			$this->assign('info',$info);
			$this->assign('data',$_GET);
			if($_GET['download']!=''){
				$this->display('dowlist');
			}else{
				$this->display();
			}
		}

		// 修改状态奖品的兑换状态
		public function changestate(){
			if(IS_GET){
				$id = trim($_GET['ck'],',');
				if(  $id !='' ){
					$map['id']     = array('in',$id);
					$data['state'] = $_GET['state'];
					$ok = D('Exchange')->update_multi_items($map,$data);
					
					$this->success('修改成功！',U('Prize_manage/pxchange'));
					 
				}else{
					$this->error('请选择！');
				}
			}
		}	

		// 卡牌日志
		public function car(){
			if($_GET['uid']){
				$map['uid']    = $_GET['uid'];
				$map['result'] = 1;
				$list = D('Exchange')->field('uid,convert_time')->where($map)->select(); // 获取兑奖清零
				$card = D('Guess')->field('uid,card,guess_time')->where($map)->select();
				foreach ($list as $key => $value) {
					$list[$key]['guess_time'] = $value['convert_time'];
					unset($list[$key]['convert_time']);
				}

			
				$this->assign('list',sort_array(array_merge($list,$card),'guess_time','desc'));
			}
 
			
		 
			$this->display();

		}




	}



?>