<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/15
 * Time: 16:18
 */

namespace Cms\Event;

use Cms\Event;
class CountEvent extends PublicEvent{

    /**
     * 获取使用某邀请码一段时间内注册用户统计信息
     * */
    public function getUserAnalysisInfo($inviteCode,$validBegin,$validEnd) {
        //if($validBegin == $validEnd) $validEnd += 86399;
        $validEnd += 86399;
        $ret        = array();
        $uidStr     = '(';
        $Invitation = D('invitation');
        $users      = $Invitation->user_analysis($inviteCode,$validBegin,$validEnd);
        $regSum     = 0;
        $femaleSum  = 0;
        $verifySum  = 0;
        $nDayRet    = 0;
        $tDayRet    = 0;
        $sDayRet    = 0;
        foreach($users as $user) {
            $regSum++;
            if($user['sex'] == 1) $femaleSum++;
            if($user['tag_verify_time'] > 1) $verifySum++;
            $uidStr .= $user['uid'].',';
        }

        $uidStr = trim($uidStr,',');
        $uidStr .= ')';

        $Thumb = D('ThumbUp');
        $thumbSum = $Thumb->getThumbUpSum($uidStr,$validBegin,$validEnd);
        $userTime = D('Location')->usersUpdateTime($uidStr);

        foreach($users as $user) {
            $uid     = $user['uid'];
            $regTime = $user['reg_time'];
            if(isset($userTime[$uid]) && $userTime[$uid] > 0) {
                $updateTime = $userTime[$uid];
                $restTime = $updateTime - $regTime;
                if($restTime < 86400) $nDayRet++;
                elseif($restTime>= 86400 && $restTime < 86400*4)$tDayRet++;
                elseif($restTime> 86400*4 && $restTime < 86400*8) $sDayRet++;
                else continue;
            }
        }

        $ret['reg_sum'] = $regSum;//注册总量
        $ret['female_sum'] = $femaleSum;//女性用户总量
        $ret['verify_sum'] = $verifySum;//认证总量
        $ret['verify_rate'] = $verifySum/$regSum;//认证率
        $ret['thumb_up_aver'] = ceil($thumbSum/$regSum);//人均被赞数
        $ret['1_day_ret_sum'] = $nDayRet;//次日留存数
        $ret['1_day_ret_rate'] = sprintf('%.2f',$nDayRet/$regSum);//次日留存率
        $ret['3_day_ret_sum'] = $tDayRet;//3日留存数
        $ret['3_day_ret_rate'] = sprintf('%.2f',$tDayRet/$regSum);//3日留存率
        $ret['7_day_ret_sum'] = $sDayRet;//7日留存数
        $ret['7_day_ret_rate'] = sprintf('%.2f',$sDayRet/$regSum);//7日留存率

        return $ret;
    }

    /*
     * $start 2015-01-11
     * $end   2015-01-12
     */
    public function countldr($starttime='',$endtime=''){


        // 动态（图片/人）
        if($starttime=='' && $endtime==''){
            $start = date("Y-m-d",strtotime("-6 day"));
            $end   = date("Y-m-d",time());
        }else{
            $start = $starttime;
            $end   = $endtime;
        }

        $surging = D('Surging');
        $s_all = "SELECT from_unixtime(create_time,'%Y-%m-%d') as times,COUNT(DISTINCT uid) as num
                  FROM cj_surging
                  WHERE from_unixtime(create_time,'%Y-%m-%d') BETWEEN '{$start}' AND '{$end}' GROUP BY  times ";

        $s_img = "SELECT from_unixtime(create_time,'%Y-%m-%d') as times,COUNT(id) as num
                  FROM cj_surging
                  WHERE from_unixtime(create_time,'%Y-%m-%d') BETWEEN '{$start}' AND '{$end}' GROUP BY  times ";
        $allsu = $surging->query($s_all);
        $s_img = $surging->query($s_img);
        $data['surging']['man'] = $allsu;
        $data['surging']['num'] = $s_img;


        // user_tag通过（数量/人）
        $t_all = "SELECT from_unixtime(create_time,'%Y-%m-%d') as times,COUNT(DISTINCT uid) as num
                  FROM cj_user_tag
                  WHERE verify = 1 AND from_unixtime(create_time,'%Y-%m-%d') BETWEEN  '{$start}' AND '{$end}' GROUP BY times";

        $t_man = "SELECT from_unixtime(create_time,'%Y-%m-%d') as times,COUNT(id) as num
                  FROM cj_user_tag
                  WHERE verify = 1 AND from_unixtime(create_time,'%Y-%m-%d') BETWEEN  '{$start}' AND '{$end}' GROUP BY times";
        $t_all = $surging->query($t_all);
        $t_man = $surging->query($t_man);
        $data['tag']['man'] = $t_all;
        $data['tag']['num'] = $t_man;


        // 聊天（次数/人)

        if($starttime=='' || $endtime==''){
            $starttime = date("Y-m-d",strtotime("-6 day"));
            $endtime   = date("Y-m-d",time());
        }

        $begin = strtotime($starttime);
        $end   = strtotime($endtime);
        for($i=$begin; $i<=$end;$i+=(24*3600))
        {
            $timedata[] = date("Y-m-d",$i);
            $views .= ' select * from send'.date("Ymd",$i).' union';
        }

        $chatlog =  D('ChatLog');
        $chatlog -> execute("drop view if exists sendunion");
        $chatlog -> execute("create view sendunion as ".trim($views,'union'));

        $c_nsql  = "SELECT DATE_FORMAT(`time`,'%Y-%m-%d') as times,COUNT(*) as num
                    FROM sendunion WHERE  DATE_FORMAT(`time`,'%Y-%m-%d') BETWEEN  '{$starttime}' AND '{$endtime}' GROUP BY times";
        $c_msql  = "SELECT DATE_FORMAT(`time`,'%Y-%m-%d') as times,COUNT(DISTINCT sender) as num
                    FROM sendunion WHERE  DATE_FORMAT(`time`,'%Y-%m-%d') BETWEEN  '{$starttime}' AND '{$endtime}' GROUP BY times";
        $charnum =  $chatlog->query($c_nsql);
        $charman =  $chatlog->query($c_msql);

        $data['chat']['man'] = $charman;
        $data['chat']['num'] = $charnum;
        foreach($data as $key => $value){
            foreach($value as $kk => $val){
                foreach($val as $k => $v){
                    $newdata[$key][$kk][$v['times']]= $v;
                }
            }
        }
        krsort($timedata);
        $all['data'] = $newdata;
        $all['for']  = $timedata;
        return $all;
    }









}