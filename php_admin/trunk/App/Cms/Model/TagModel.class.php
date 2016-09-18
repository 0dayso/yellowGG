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
class TagModel extends CjDataModel{

    // 用户认证
    public function authentication($uid){

    	$sql = "SELECT ut.id AS ut_id,ut.uid,t.id,t.title,t.certificate_time,sg.id AS sg_id,sg.thumb,sg.description,sg.create_time,s.user_tag_id
    			FROM  	   cj_user_tag         AS ut
    			LEFT JOIN  cj_tag              AS t  ON t.id  = ut.tag_id
    			LEFT JOIN  cj_user_tag_surging AS s  ON ut.id = s.user_tag_id
    			LEFT JOIN  cj_surging          AS sg ON sg.id = s.surging_id
    			WHERE  ut.uid = $uid    ";
    	return D('Tag')->query($sql);
    }


}
 