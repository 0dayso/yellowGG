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
class InvitationModel extends CjDataModel{


    /**
     * 获取使用某个邀请码在某段时间内注册的用户信息
     * uid，sex，reg_time
     * */
    public function user_analysis($inviteCode,$validBegin,$validEnd) {
        $Inv  = D('Invitation');
        $sql  = "select cj_invitation.oid as uid,cj_user.sex as sex,cj_user.reg_time as reg_time,cj_user.tag_verify_time from cj_invitation ".
                "left join cj_user on cj_user.uid=cj_invitation.oid ".
                "where cj_invitation.code='".$inviteCode."' ".
                "and cj_invitation.used_time between ".$validBegin." and ".$validEnd;
        $data = $Inv->query($sql);

        return $data;
    }


}
 