<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/30
 * Time: 17:35
 */

namespace Cms\Controller;

use Think\Controller;
class CreateTableController extends Controller{

    /*
     * 准备车辆认证数据表，如果没有则生成，同时存储表名到session
     * */
    public function create_certificate_car_req_aid_table($aid)
    {
        $ret = true;
        $sql = "CREATE TABLE IF NOT EXISTS cj_certificate_car_req_aid_".$aid."(
                id int(10) NOT NULL AUTO_INCREMENT,
                certificate_car_id int(10) NOT NULL DEFAULT 0,
                aid int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
                dtime datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
                PRIMARY KEY (id)
                )ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='车辆审核管理员请求任务单';";

        $Model = M();
        $res = $Model->execute($sql);
        if($res===false)
            $ret = false;

        return $ret;
    }

    /*
     * 删除车辆认证数据表
     * */
    public function drop_certificate_car_req_aid_table($aid)
    {
        $ret = true;
        $sql = "DROP TABLE IF EXISTS cj_certificate_car_req_aid_".$aid;
        $Model = M();
        $res = $Model->execute($sql);
        if($res===false)
            $ret = false;

        return $ret;
    }

    /*
     * 准备视频认证数据表,如果没有则生成，同时存储表名到session
     * */
    public function create_certificate_video_req_aid_table($aid)
    {
        $ret = true;

        $sql = "CREATE TABLE IF NOT EXISTS cj_certificate_video_req_aid_".$aid."(
                id int(10) NOT NULL AUTO_INCREMENT,
                certificate_video_id int(10) NOT NULL DEFAULT 0,
                aid int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
                dtime datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
                PRIMARY KEY (id)
                )ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='视频认证管理员请求任务单';";

        $Model = M();
        $res = $Model->execute($sql);
        if($res===false)
            $ret = false;

        return $ret;
    }

    /*
     * 删除视频认证任务表
     * */
    public function drop_certificate_video_req_aid_table($aid)
    {
        $ret = true;

        $sql = "DROP TABLE IF EXISTS cj_certificate_video_req_aid_".$aid;
        $Model = M();
        $res = $Model->execute($sql);

        if($res === false)
            $ret = false;

        return $ret;
    }

    /*
     * 准备举报业务数据表,如果没有则生成，同时存储表名到session
     * */
    public function create_accusation_req_aid_table($aid)
    {
        $ret = true;

        $sql = "CREATE TABLE IF NOT EXISTS cj_accusation_req_aid_".$aid."(
                id int(10) NOT NULL AUTO_INCREMENT,
                accusation_id int(10) NOT NULL DEFAULT 0,
                offender_uid int(10) NOT NULL DEFAULT 0,
                aid int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
                dtime datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
                PRIMARY KEY (id)
                )ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='举报业务管理员请求任务单';";

        $Model = M();
        $res = $Model->execute($sql);
        if($res===false)
            $ret = false;

        return $ret;
    }

    /*
     * 删除业务举报表
     * */
    public function drop_accusation_req_aid_table($aid)
    {
        $ret = true;

        $sql = "DROP TABLE IF EXISTS cj_accusation_req_aid_".$aid;

        $Model = M();
        $res = $Model->execute($sql);
        if($res === false)
            $ret = false;

        return $ret;
    }

    /*
     * 准备举报业务数据表,如果没有则生成，同时存储表名到session
     * */
    public function create_feedback_req_aid_table($aid)
    {
        $ret = true;

        $sql = "CREATE TABLE IF NOT EXISTS cj_feedback_req_aid_".$aid."(
                  id int(10) NOT NULL AUTO_INCREMENT,
                  feedback_data_id int(10) NOT NULL DEFAULT 0,
                  dtime timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
                  text text NOT NULL DEFAULT '' COMMENT '回复信息',
                  atime timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '处理时间',
                  PRIMARY KEY (id),
                  UNIQUE KEY feedback_data_id (feedback_data_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户反馈客服任务' AUTO_INCREMENT=1 ;";

        $Model = M();
        $res = $Model->execute($sql);
        if($res===false)
            $ret = false;

        return $ret;
    }

    /*
     * 删除业务举报表
     * */
    public function drop_feedback_req_aid_table($aid)
    {
        $ret = true;

        $sql = "DROP TABLE IF EXISTS cj_feedback_req_aid_".$aid;

        $Model = M();
        $res = $Model->execute($sql);
        if($res === false)
            $ret = false;

        return $ret;
    }
}
