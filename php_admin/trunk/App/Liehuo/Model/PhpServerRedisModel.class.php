<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/22
 * Time: 11:02
 */

namespace Liehuo\Model;
use Think\Model;
use Think\Cache\Driver\Redis;
use Common\Model\CommonModel;

class PhpServerRedisModel extends CommonModel
{

    protected $autoCheckFields = false;

    /*
     * 将用户的视频认证状态保存到redis缓存里
     *
     * @input uid 初见号
     * @input status 认证状态
     *
     * */
    public function save_status_in_redis($uid,$status,$type)
    {
        if(C('REDIS_START')){
            $option = C('php_server_redis_config');
            $Redis = new Redis($option);
            $Redis->set($uid.'_'.$type,$status);
        }
    }

    /*
     * 封禁用户后删除用户token
     * */
    public function delete_user_token($uid)
    {
        $ret = false;
        if(C('REDIS_START')){
            $option = C('user_token');
            $Redis = new Redis($option);
            $ret = $Redis->hDel($uid, 'auth_token');
        }

        return $ret;
    }

    /*
     * 删除用户推荐
     * */
    public function delete_recommend_user()
    {
        $ret = false;
        if(C('REDIS_START')){
            $option = C('php_server_redis_config');
            $Redis = new Redis($option);
            $ret = $Redis->del('recommend_user');
        }

        return $ret;
    }

    /*
     * 删除用户标签
     * */
    public function delete_user_tag($userTagId)
    {
        $ret = false;
        if(C('REDIS_START')){
            $option = C('php_server_user_info_v2');
            $Redis = new Redis($option);
            $ret = $Redis->hDel('tag_info',$userTagId);
        }

        return $ret;
    }

    /*
     * 删除用户标签动态
     * */
    public function delete_user_surging($userTagId)
    {
        $ret = false;
        if(C('REDIS_START')){
            $option = C('php_server_user_info_v2');
            $Redis = new Redis($option);
            $ret = $Redis->hDel('surging_info',$userTagId);
        }

        return $ret;
    }

    /*
     * 视频认证和车辆认证都需要清除用户的信息
     * 信息包含两部分:所有信息和基本信息
     * todo 暂时还没有加进去
     * */
    public function delete_user_info($uid)
    {
        $vn = D('User')->get_single_item('uid ='.$uid,'server_version');

        if( $vn['server_version'] == 0 ){

            $option = C('php_server_user_info');

        }else{
            $option = C('php_server_user_info_v2');
        }

        $ret = false;
        if(C('REDIS_START')){
            //$option = C('php_server_user_info');
            $Redis = new Redis($option);
            $ret1 = $Redis->hDel('fullinfo', $uid);
            //todo 需要测试redis为什么在删除一条之后登上一段时间才能删第二条
            /*for($i=0;$i<100000;$i++){
                $j=0;
            }*/
            $ret2 = $Redis->hDel('baseinfo', $uid);
            $ret  = $ret1|$ret2;
        }

        return $ret;
    }

    /*
     * 生成用户token
     * */
    public function create_user_token($uid)
    {
        $ret = false;
        if(C('REDIS_START')){
            $option = C('php_server_redis_config');
            $Redis = new Redis($option);
            $token = md5($uid.time());
            $ret   = $Redis->hSet($uid, 'auth_token', $token);
        }
        return $ret;
    }

    /**
     * @param 获取用户token
     * @return array
     */
    public function get_user_token($uid){
        $option = C('user_token');
        $Redis = new Redis($option);
        return $Redis->hGet($uid, 'auth_token');
    }



    public function create_auth_token_and_delete_test($uid)
    {
        $createRes = $this->create_user_token($uid);
        $deleteRes = $this->delete_user_token($uid);

        return array('create'=>$createRes,'delete'=>$deleteRes);
    }

    /*
     * 更新默认关注redis
     * */
    public function changerecommendredis($data)
    {
        if(API_TAG == 'api.chujian.im'){
            $option = C('php_server_user_info_v2');
        }else{
            $option = C('php_server_redis_config');
        }
        $Redis = new Redis($option);
        $Redis->del('recommend_follow');
        $Redis->hSet('recommend_follow',0,json_encode($data[0]));
        $Redis->hSet('recommend_follow',1,json_encode($data[1]));

    }

    /*
     * 更新存储首页推荐用户 标签动态内容集合
     * */
    public function updateusertagsurging($group_id)
    {
        if(API_TAG == 'api.chujian.im'){
            $option = C('php_server_user_info_v2');
        }else{
            $option = C('php_server_redis_config');
        }
        $Redis = new Redis($option);
        $Redis->del('recommend_user_surging_list');
        $surging = D('RecommendUser')->searchs('group_id ='.$group_id);
        if(!empty($surging)){
            foreach($surging as $k => $val){
                $Redis->hSet('recommend_user_surging_list',$val['uid'],$val['content']);
            }
        }

    }

    /*
     * 更新存储热门分类推荐
     * */
    public function updatehostsetclass($group_id)
    {
        if(API_TAG == 'api.chujian.im'){
            $option = C('php_server_user_info_v2');
        }else{
            $option = C('php_server_redis_config');
        }
        $Redis = new Redis($option);
        $Redis->del('recommend_hot_class_list');
        $surging = D('RecommendUser')->searchs('group_id ='.$group_id);
        if(!empty($surging)){
            foreach($surging as $k => $val){
                $Redis->hSet('recommend_hot_class_list',$val['uid'],$val['content']);
            }
        }

    }

    /*
     * 删除 打分班次redis
     * */
    public function delscoring()
    {
        $option = C('score_info');
        $Redis = new Redis($option);
        $Redis->del('php_score_index');
    }


    public function del_user_info($uid)
    {
        $option = C('user_info_v6');
        $ret = false;
        $Redis = new Redis($option);
        $ret = $Redis->del('php_user_'.$uid);
        return $ret;
    }

    // 删除用户最后一条动态
    public function del_feed_last($uid)
    {
        $option = C('user_info_v6');
        $Redis = new Redis($option);
        return $Redis->del('php_feed_'.$uid);
    }


    // 初始化Redis
    public function new_redis_instances($cfg = '')
    {
        $cfg || $cfg = 'redis_default';
        if(is_string($cfg) && isset($this->redis_obj[$cfg]) && $this->redis_obj[$cfg])
        {
            $rds = $this->redis_obj[$cfg];
        }
        else
        {
            $rds = new Redis(is_string($cfg) ? C($cfg) : $cfg);
            if(is_string($cfg)) $this->redis_obj[$cfg] = $rds;
        }
        return $rds;
    }




}
