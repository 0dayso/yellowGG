<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/6
 * Time: 10:06
 */
namespace Cms\Model;

use Cms\Controller\CrossDatabaseController;
use Cms\Model;
class UserModel extends CjDataModel{

    public $_validate = array(
        //array('account','/^[a-z]\w{3,}$/i','帐号格式错误'),
        array('nickname', 'require', '帐号必须'),
        array('password', 'require', '密码必须'),
        //array('nickname','require','昵称必须'),
        //array('repassword','require','确认密码必须'),
        //array('repassword','password','确认密码不一致',self::EXISTS_VALIDATE,'confirm'),
        //array('account','','帐号已经存在',self::EXISTS_VALIDATE,'unique',self::MODEL_INSERT),
    );

    public function get_count($type,$map)
    {
        $User = D('User');//所有用户
        if($type == 1){
            $ret = $User->where($map)->count();
        }
        elseif($type == 2){//车辆用户
            $map['cj_certificate_car.status'] = array('EQ',1);
            $ret = $User->join('LEFT JOIN cj_certificate_car ON cj_user.uid=cj_certificate_car.uid')
                        ->where($map)
                        ->count();
        }
        elseif($type == 3){
            $map['cj_certificate_video.status'] = array('EQ',1);
            $ret = $User->join('LEFT JOIN cj_certificate_video ON cj_user.uid=cj_certificate_video.uid')
                        ->where($map)
                        ->count();
        }
        else{

        }

        return $ret;
    }

    public function get_car_pass_count()
    {
        $Model = D($this->name);
        $map   = array('car_verify'=>array('EQ',1));
        $ret   = $Model->field('uid')->where($map)->count();

        return $ret;
    }

    public function get_video_pass_count()
    {
        $Model = D($this->name);
        $map   = array('video_verify'=>array('EQ',1));
        $ret   = $Model->field('uid')->where($map)->count();

        return $ret;
    }

    public function lists($type,$map,&$Page)
    {
        $User = D('User');
        if($type == 1){
            $ret = $User->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        elseif($type == 2){
            $map['cj_certificate_car.status'] = array('EQ',1);
            $ret = $User->join('LEFT JOIN cj_certificate_car ON cj_user.uid=cj_certificate_car.uid')
                        ->where($map)
                        ->limit($Page->firstRow.','.$Page->listRows)
                        ->select();
        }
        elseif($type == 3){
            $map['cj_certificate_video.status'] = array('EQ',1);
            $ret = $User->join('LEFT JOIN cj_certificate_video ON cj_user.uid=cj_certificate_video.uid')
                        ->where($map)
                        ->limit($Page->firstRow.','.$Page->listRows)
                        ->select();
        }
        else{

        }

        return $ret;
    }

    //所有用户统计
    public function get_account_manage_user_count($map)
    {
        $User = D('User');
        $ret = $User->field('uid')->where($map)->count();
        return $ret;
    }

    /*
     * 获取帐号管理用户信息
     * */
    public function get_account_manage_user_list($map,$Page)
    {
        $User = D('User');
        $ret = $User->field('uid,nickname,sex,phone,reg_time,video_verify,car_verify,status,head_images,album,server_version')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('uid desc')->select();
        require_once("./ThinkPHP/Library/Think/Emoji.class.php");
        $location = D('Location');
        $searcha  = A('Search','Event');
        foreach ($ret as $key => $value){
            if($value['server_version']==1){
                $ret[$key]['img_num'] = count(json_decode($value['album'],true));
            }else{
                $ret[$key]['img_num'] = count(json_decode($value['head_images'],true));
            }
            $ret[$key]['taginfo']     = $searcha->tagsurgingnum($value['uid']);
            $result = $location->field('update_time')->where('uid = '.$value['uid'])->find();
            $ret[$key]['update_time'] = $result['update_time'];
            $ret[$key]['nickname']    = emoji_unified_to_html($value['nickname']);
        }
        return $ret;
    }




    /*
     * 获取帐号管理中用户信息
     * */
    public function get_account_manage_single_user_info($map)
    {
        $page  = array('firstRow'=>0,'listRows'=>1);
        $Page  = (object)$page;
        $Cross = new CrossDatabaseController();
        $initModel = array(
            'model_name' => 'User',
            'data_field' => 'uid,nickname,sex,head_images,birthday,phone,reg_time,constellation,
                             job,signature,height,hometown,video_verify,car_verify,
                             tags,movie,weekend,cooking,travel,restaurant,sport,dblocking_time,status,f_rate,c_rate,is_rate,total,gold_coin_cnt,face_url,album,server_version',
            'limit' => 1,
        );

        $joinModelArr = array(
            'Location' => array(
                'model_name' => 'Location',
                'table_name' => '',
                'type' => 'left',
                'left_match_field' => 'uid',
                'right_match_field'=>'uid',
                'left_combine_field' => 'uid',
                'right_combine_field' => 'uid',
                'data_field' => 'dtime as login_time',
            ),
        );

        //$ret   = $Cross->cross_multi_join($map,$Page,$initModel,$joinModelArr);
        $temp   = $Cross->cross_multi_join($map,$Page,$initModel,$joinModelArr);
        $ret    = current($temp);
        return $ret;
    }

    /*
     * 新版获取帐号管理中用户信息
     * */
    public function newget_account_manage_single_user_info($uid)
    {
        $sql = "SELECT l.update_time,u.uid,u.nickname,u.sex,u.head_images,u.birthday,u.phone,u.reg_time,u.constellation,u.job,u.signature,u.height,u.hometown,u.video_verify,u.car_verify,u.tags,u.movie,u.weekend,u.cooking,u.travel,u.restaurant,u.sport,u.dblocking_time,u.status,u.f_rate,u.c_rate,u.is_rate,u.total,u.gold_coin_cnt,u.face_url,u.album,u.server_version
                FROM chujian.cj_user AS u
                LEFT JOIN chujian.cj_location AS l ON u.uid = l.uid
                WHERE u.uid = $uid GROUP BY u.uid";
        $ret = D('User')->query($sql);
        return $ret[0];
    }

    //根据uid获取单条记录
    //TODO 修改了查询条件，可能会导致其他地方用的有问题
    public function get_single_item($map,$field=null)
    {
        $User = D('User');
        if($field == null)
            $ret  = $User->where($map)->find();
        else
            $ret  = $User->where($map)->field($field)->find();

        return $ret;
    }

    public function update_single_item($map=array(),$data=array())
    {
        $User = D($this->name);
        $User->where($map)->save($data);
    }

    public function update_multi_items($map,$data)
    {
        $User = D($this->name);
        $User->where($map)->data($data)->save();
    }

    public function insert_multi_items($dataList)
    {
        $User = D('User');
        $User->addAll($dataList);
    }

    public function get_closure_count($map)
    {
        $Model = D('User');
        $map['status'] = array('EQ','-1');
        $ret = $Model->where($map)->count();
        //$sql = $Model->getLastSql();

        return $ret;
    }

    /*public function get_closure_list($map,$Page)
    {
        $Model = D('User');
        //$time  = time();
        //$map['dblocking_time'] = array('GT',$time);
        $map['status'] = array('EQ',-1);
        $userItems = $Model->where($map)
                     ->field('uid,nickname,sex,phone,reg_time,dblocking_time')
                     ->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $Log = D('AccusationRequestLog');
        $aid = D('Admin');
        foreach($userItems as $key=>$value){
            $map = array(
                'offender_uid'=>array('EQ',$value['uid']),
                'status'=>array('GT',1),
            );
            $logItems = $Log->field('aid,certificate_time,status,reason,c_from,remark')->where($map)->order('certificate_time DESC')->limit('0,1')->select();
            //$sort     = sort_array($logItems,'certificate_time','desc');
            //$temp   = current($sort);
            $temp     = $logItems[0];
        
            $admins   = $aid->get_multi_items('aid='.$temp['aid'],'nickname');
            
            $stae = C('STATE_ACCUSATION_PROCESS_REASONS');

            $userItems[$key]['reason']  = $stae[$temp['reason']];
            $userItems[$key]['admin']   = $admins[0]['nickname'];
            $userItems[$key]['certificate_time'] = $temp['certificate_time'];
            $userItems[$key]['status']  = $temp['status'];
            $userItems[$key]['c_from']  = $temp['c_from'];
            $userItems[$key]['remark']  = $temp['remark'];
        }

        // $time  = time();
        // $map['dblocking_time'] = array('GT',$time);
        // $Cross = new CrossDatabaseController();
        // $initModel = array(
        //     'model_name' => $this->name,
        //     'data_field' => 'uid,nickname,sex,phone,reg_time,dblocking_time',
        //     'limit' => 1,
        // );

        // $joinModelArr = array(
        //     'AccusationRequestLog' => array(
        //         'model_name' => 'AccusationRequestLog',
        //         'table_name' => '',
        //         'type' => 'left',
        //         'map'  =>array('status'=>array('GT',1)),
        //         'left_match_field' => 'uid',
        //         'right_match_field'=>'offender_uid',
        //         'left_combine_field' => 'uid',
        //         'right_combine_field' => 'uid',
        //         'data_field' => 'offender_uid as uid,certificate_time,status',
        //     ),
        // );

        // $ret   = $Cross->cross_multi_join($map,$Page,$initModel,$joinModelArr);
        // $ret   = get_group($ret,'uid'); 

        return  sort_array($userItems,'certificate_time','desc');
    }*/

    public function get_closure_list($map,$Page){

        $Model = D('User');
        $sql = "SELECT u.uid,u.sex,u.phone,u.reg_time,u.dblocking_time,l.aid,l.certificate_time,l.status,l.reason,l.c_from,l.remark,a.nickname
                FROM      chujian.cj_user                        AS u 
                LEFT JOIN (SELECT aid,certificate_time,status,reason,c_from,remark,offender_uid FROM cj_admin_log.cj_accusation_request_log ORDER BY certificate_time DESC ) AS l ON u.uid = l.offender_uid
                LEFT JOIN cj_admin.cj_admin                      AS a ON l.aid = a.aid
                WHERE u.status = '-1' AND l.status between 2 and 5 GROUP BY u.uid ORDER BY certificate_time DESC  LIMIT ".$Page->firstRow.','.$Page->listRows; 
        return $Model->query($sql);
    }

    /*
     * 线上更新uid为50006和50008的dblocking_time
     * 本地更新uid为23和26的dblocking_time
     * */
    public function reset_test_data()
    {
        $resetModel = C('RESET_MODEL');
        switch($resetModel)
        {
            case 'local':
                $in  = array(16,23,25,26);
                $item  = array(
                    'dblocking_time'=>0,
                );
                $map   = array('uid'=>array('IN',$in));
                $Model = D($this->name);
                $Model->update_multi_items($map,$item);
                break;
            case 'server':
                break;
            default:
                $this->error('测试配置出错');
                break;
        }
    }

    public function content_manage_count($map)
    {
        $Model  = D('User');

        $sql = 'SELECT COUNT(*) AS count
                FROM cj_user
                WHERE uid IN(
                SELECT cj_user_info_modify.uid
                FROM cj_user_info_modify
                GROUP BY uid
                );';

        $temp = $Model->query($sql);
        $temp = current($temp);
        $ret  = $temp['count'];

        return $ret;
    }

    public function content_manage_list($map,$Page)
    {
        /*
        $ret  = $Model->join('LEFT JOIN cj_user ON cj_user.uid=cj_user_info_modify')
            ->where($map)
            ->field('uid,info_type,nickname,tags,signature,job,movie,weekend,cooking,travel,restaurant,sport')
            ->group('cj_user.uid')
            ->limit($Page->firstRow.','.$Page->numRows)
            ->select();
        */

        $Model = D('User');
        $Modify = D('UserInfoModify');
        $subQuery = $Modify->field('uid')->group('uid')->buildSql();

        $ret = $Model->where('uid in '.$subQuery)
                     ->where($map)
                     ->field('uid,nickname,job,tags,signature,movie,weekend,cooking,travel,restaurant,sport')
                     ->limit($Page->firstRow.','.$Page->numRows)
                     ->select();

        return $ret;
    }
}
