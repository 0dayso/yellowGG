<?php
/**
 * Created by PhpStorm.
 * User: zsj
 * Date: 2015/03/12
 * Time: 16:32
 */

namespace Cms\Event;
use Cms\Event;

class RateEvent extends PublicEvent{

	 
	// 获取用户的打分信息
	public function getrate($uid)
    {
        return D('User')->get_single_item('uid='.$uid);
    }



	/* 脸打分
	*  uid  用户id
	*  rate 打分数
	*  pnum 照片的数量
	*/
	public function facerate($uid,$rate,$pnum){
		$info  = $this->getrate($uid);
		if($pnum < 4 ){ // 如果 图片小于 4 张
			if($rate == 8 ||  $rate == 7 ){  // 给分数进行折扣
				$rate -= 2; 
			}else{
				$rate -= 1;
			}
		}

	 	// 获取用户的视频是否认证
		if( $info['video_verify'] == '1'  ){
			$voide = ($info['sex']==0)?2:5;
		}
	 
		$total =  $rate + $info['c_rate'] + $voide ;  //总分数  =    现在的脸打分数 + 以前的车打分 + 视频认证分数
		$this->startrate($uid,$total,$rate,'f_rate');
	}

	// 车打分
	public function carrate($uid,$rate){
		$info  = $this->getrate($uid);
		if($info['sex'] ==0){ // 男
			if($rate ==1 ){
				$rate +=1;
			}elseif($rate== 2 ){
				$rate +=3;
			}else{
				$rate +=5;
			}
		}

		$total = ($info['total'] - $info['c_rate']) + $rate;  //总分数 - 以前的车打分 + 现在的车打分 
		$this->startrate($uid,$total,$rate,'c_rate');
	}

	/* 开始打分
	*  uid   用户id 
	*  total 总分 
	*  rate  打分
	*  type  打分类型 f_rate脸打分  c_rate 车打分  
	*/
	public function startrate($uid,$total,$rate,$type){
		if($total  >= 13 ){
			$total = 13;
		}


		$map['uid']      = $uid; 

		$data['total']   = $total; 
		$data[$type]     = $rate;   
		$data['is_rate'] = 0;  //将是否打分改为 0 
		$time = time();
		if($type == 'f_rate' ){
			$data['f_rtime'] = $time;
			$ndata           = 0;    
		}else{
			$data['c_rtime'] = $time;
			$ndata           = 1;
		}
		// 修改用户数据
		$model   = D('User')->update_single_item($map,$data);
		$n_data['uid']   = $uid;
		$n_data['rate']  = $rate;
		$n_data['type']  = $ndata;
		$n_data['aid']   = $_SESSION['authId'];;
		$n_data['time']  = $time;
		// 写入打分 log
		$ratelog =  D('RateRequest')->insert_single_item($n_data);
	}


	// 打分记录操作日志
    public function operationlog($data){
     
        ($data['aid']>0)?$map['aid'] = $data['aid']:'';
        ($data['uid']>0)?$map['uid'] = $data['uid']:'';
        $map['time'] = array('between',array(strtotime($data['s_date']),strtotime($data['t_date'])));
        
        $itemsPerPage = C('ITEMS_PER_PAGE');
        $count = D('RateRequest')->where($map)->count();

        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();
        
        $vlist = D('RateRequest')->field('time,uid,aid,type,rate')->where($map)->limit($Page->firstRow,$Page->listRows)->select();
        $user  = D('Admin')->get_multi_items('','aid,nickname');
        foreach ($user as $k => $u) {
            $uarray[$u['aid']] = $u['nickname'];
        }
        
        foreach ($vlist as $key => $value) {
            $vlist[$key]['aid'] = strtr($vlist[$key]['aid'],$uarray);
        }

        $data['list'] = $vlist;
        $data['show'] = $show;
        return $data;
    } 


}