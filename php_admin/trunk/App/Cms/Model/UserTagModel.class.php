<?php
/**
 * Created by PhpStorm.
 * User: zsj
 * Date: 2015/05/14
 * Time: 16:55
 */
namespace Cms\Model;

//use Cms\Controller\CrossDatabaseController;
use Cms\Model;
class UserTagModel extends CjDataModel{

    // 用户认证
    public function authentication($uid,$get=''){

        $sql = "SELECT ut.id AS ut_id,ut.uid,ut.id,ut.title,sg.id AS sg_id,sg.thumb,sg.description,sg.create_time,s.user_tag_id,tc.name as tag_class_name
    			FROM  	   cj_user_tag         AS ut
    			LEFT JOIN  cj_user_tag_surging AS s  ON ut.id = s.user_tag_id
    			LEFT JOIN  cj_surging          AS sg ON sg.id = s.surging_id
                LEFT JOIN  cj_tag_class        AS tc ON tc.id = ut.tag_class_id
    			WHERE  ut.uid = $uid    ";
        $list = D('Tag')->query($sql);
        if($get=='noget'){
            return $list;
        }
        return $this->getmimingsurging($list);
    }

    // 获取定时动态
    public function getmimingsurging($list){
        $list = get_group($list,'ut_id');
        if(!empty($list)){
            $surgingtiming = D('SurgingTiming');
            foreach($list as $k => $val){
                $list[$k]['miming'] = $surgingtiming->searchs(' unix_timestamp(now()) < timing_time and user_tag = '.$k);
            }
        }
        return $list;
    }

     
}
 