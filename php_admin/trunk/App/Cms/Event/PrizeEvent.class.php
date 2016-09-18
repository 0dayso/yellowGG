<?php
/**
 * Created by PhpStorm.
 * User: zsj
 * Date: 2015/03/12
 * Time: 16:32
 */

namespace Cms\Event;
use Cms\Event;

class PrizeEvent extends PublicEvent{

	// 奖品列表 
	public function getlistprizeinfo(){

		$list  = D('Prize')->getlist('del=0');

		$phone = array();
		$date  = date('Y-m-d',strtotime('-1 day '));
		foreach ($list as $key => $value) {
			$map = "date_format(from_unixtime(convert_time),'%Y-%m-%d') = '{$date}' AND result = 1 AND prize_id = ".$value['id'];
			$list[$key]['phone'] = D('Exchange')->getclassnum($map);
		}

		return $list;

	}

	// 中奖纪录
	public function lottery(){
		$list  = D('Prize')->getlist('del=0');

		$yt    = date('Y-m-d',strtotime('-1 day ')); // 昨天
		$jt    = date('Y-m-d',time()); // 今天
		
		$inthe = D('Guess')->where()->count();


		$emodel = D('Exchange');
		foreach ($list as $key => $value) {
			$map = "date_format(from_unixtime(convert_time),'%Y-%m-%d') = '{$yt}' AND result = 1 AND prize_id = ".$value['id'];
			$maj = "date_format(from_unixtime(convert_time),'%Y-%m-%d') = '{$jt}' AND result = 1 AND prize_id = ".$value['id'];
			$all = "result = 1 AND prize_id = ".$value['id'];
			$list[$key]['z']   = $emodel->where($map)->count();
			$list[$key]['j']   = $emodel->where($maj)->count();
			$list[$key]['all'] = $emodel->where($all)->count();
			$list[$key]['gl']  =   sprintf("%.2f",($list[$key]['all']/$inthe)*100) ;
		}
		return $list;
	}


	// 根据奖品获取当前月的兑奖信息
	public function dayinfo($pid,$date){
		$sql   = "SELECT date_format(from_unixtime(convert_time),'%d') AS day  
					   FROM cj_exchange  
					   WHERE date_format(from_unixtime(convert_time),'%Y-%m') = '{$date}' AND prize_id = $pid AND result = 1 ORDER BY day ASC ";
		$list  = D('Exchange')->query($sql);
 		
	 	if(!empty($list)){
			$info  = array_count_values(array_column($list,'day'));
			for($i=1; $i <=31; $i++){ 
				if($info[$i]==''){
					$info[$i] = 0;
				}
			}
			ksort($info);
			foreach ($info as $key => $value) {
				$str .= "[$key,$value],";
			}
	 	}else{
	 		for($i=1; $i <=31; $i++){ 
				$str .= "[$i,0],";
			}
	 	}
	 	
	 	return trim($str,',');

	}

	// 兑奖详情
	public function infolistprize($map){
		 
		if(!empty($map['date'])){
			$where .= " AND date_format(from_unixtime(convert_time),'%Y-%m-%d') = '{$map["date"]}' ";
		}
		if(!empty($map['uid'])){
			$where .= " AND uid = '{$map["uid"]}' ";
		}
		if( $map['state'] >0 || $map['state']==='0' ){
			$where .= " AND state = '{$map["state"]}' ";
		} 

		$itemsPerPage = C('ITEMS_PER_PAGE');
		$cont = D('Exchange')->query("SELECT COUNT(id) AS count  FROM cj_exchange  WHERE  result = 1  $where ");
		 
        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($cont[0]['count'], $itemsPerPage);
        $show = $Page->show();


		$sql   = "SELECT *  FROM cj_exchange  WHERE  result = 1  $where  ORDER BY convert_time DESC LIMIT ".$Page->firstRow.','.$Page->listRows;
		$list  = D('Exchange')->query($sql);

		$prizelist = D('Prize')->get_multi_items('','id,prize');
		$newprize  = array_column($prizelist,'prize','id');

		$phone['yd'] = array(139,138,137,136,135,134,178,188,187,183,182,159,158,157,152,150,147);
        $phone['lt'] = array(186,185,130,131,132,155,156);
        $phone['dx'] = array(189,181,180,153,133);

		foreach ($list as $key => $value) {
			$list[$key]['prize'] = strtr($value['prize_id'],$newprize);
			$myphones = substr($value['phone'],0,3);

			if(in_array($myphones,$phone['yd'])){
				$list[$key]['operators'] = '移动';
			}
			if(in_array($myphones,$phone['lt'])){
				$list[$key]['operators'] = '联通';
			}
			if(in_array($myphones,$phone['dx'])){
				$list[$key]['operators'] = '电信';
			}

		}

		return array('list'=>$list,'page'=>$show);

	}

	 


}