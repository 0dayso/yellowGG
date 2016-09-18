<?php
/**
 * Created by PhpStorm.
 * User: zsj
 * Date: 2015/4/30
 * Time: 11:00
 */

namespace Cms\Event;
use Cms\Event;
class LocationEvent extends PublicEvent{

	// 获取在线人数
	public function getonlineuser(){
		$model = D('Location');
		$now   = 'SELECT count(uid) as num FROM cj_location WHERE  FROM_UNIXTIME(`update_time`,"%Y-%m-%d") = curdate() ';
		$yes   = 'SELECT count(uid) as num FROM cj_location WHERE  FROM_UNIXTIME(`update_time`,"%Y-%m-%d") = date_sub(curdate(),interval 1 day) ';
		$now   = $model->query($now);
		$yes   = $model->query($yes);
		$num['now']  = $now[0]['num'];
		$num['yes']  = $yes[0]['num'];
 		return $num;
	}



}