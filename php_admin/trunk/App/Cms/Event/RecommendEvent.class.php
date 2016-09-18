<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/9
 * Time: 11:35
 */

namespace Cms\Event;
 
use Cms\Event;
class RecommendEvent extends PublicEvent{

    // 获取推荐列表
    public function getlist(){

        $sql = "SELECT g.*,COUNT(g.id) AS num,s.name
                FROM cj_recommend_user_group AS g 
                LEFT JOIN cj_recommend_user  AS u ON g.id = u.group_id
                LEFT JOIN cj_tag_class       AS s ON g.tag_class_id = s.id
                WHERE g.tag_class_id = 0 AND g.group_type = 0 GROUP BY g.id ORDER BY g.id DESC ";
        return D('RecommendUser')->query($sql);
    }

    // 获取推荐详情
    public function inforec($id){
        $sql = "SELECT g.id,g.valid_begin,g.valid_end,u.uid,s.sex,u.clicknums,g.tag_class_id
                FROM cj_recommend_user_group AS g 
                LEFT JOIN cj_recommend_user  AS u ON g.id  = u.group_id
                LEFT JOIN cj_user            AS s ON u.uid = s.uid
                LEFT JOIN cj_tag_class       AS c ON g.tag_class_id = c.id
                WHERE g.id= $id
                ";
        return D('RecommendUser')->query($sql);
    }

    // 检查推荐已存在的推荐用户
    public function haveuser($struid,$group_id){
        $sql  = "SELECT uid FROM cj_recommend_user WHERE group_id = $group_id AND  uid IN($struid)";
        $list = D('RecommendUser')->query($sql);
        if(!empty($list)){
            foreach ($list as $key => $value) {
                $uid .= $value['uid'].',';
            }
            return trim($uid,',');
        }

    }

    // 过滤不在用户表的uid
    public function deleteuser($uid){
        $sql  = "SELECT uid FROM cj_user WHERE   uid IN($uid)";
        $list = D('User')->query($sql);
        if(!empty($list)){
            return $list;
        }
    }

    // 获取系统推荐的标签
    public function getsystag(){

        $data['list']  = D('RecommendTag')->searchs();
        $data['class'] = D('TagClass')->searchs();
        return $data;
    }

    // 添加系统推荐标签
    public function addsystag($title,$classid){
        $data['title'] = $title;
        $data['tag_class_id'] = $classid;
        $radd = D('RecommendTag')->add($data);
        if($radd){
            return $radd;
        }else{
            return 'no';
        }
    }


    // 更新，默认推荐关注用户写到redis
    public function addredisrecommend(){
        //  当group_type = 2  ， cj_recommend_user用户的sex字段就是对应默认关注的用户分类。不同的性别关注默认关注不一样
        $sql ="SELECT u.uid,u.sex,u.content
               FROM cj_recommend_user_group AS g
               LEFT JOIN cj_recommend_user  AS u ON g.id = u.group_id
               WHERE g.group_type = 2
               ";
        $list  = D('RecommendUser')->query($sql);
        foreach($list as $k => $val){
            if($val['sex']==0 && $val['content']!='' ){
                $data[0][$k] = $val['content'];
            }elseif($val['sex']==1 && $val['content']!=''){
                $data[1][$k] = $val['content'];
            }
        }
        D('PhpServerRedis')->changerecommendredis($data);

    }

    /*
     * 添加分类推荐用户
     * @ $info tag_class_id分类id:tag_id标签:uid用户uid
     * */
    public function add_items($info){

        $info = explode(':',$info);
        $tagclassid = $info[0];
        $tag_id     = $info[1];
        $uid        = $info[2];

        $RecommendUser = D('RecommendUser');
        $rugmodel = D('RecommendUserGroup');
        $groupid  = $rugmodel->search(" tag_class_id = $tagclassid and group_type = 1 ");
        // 创建分组
        if(empty($groupid)){
            $groupdata['tag_class_id'] = $tagclassid;
            $groupdata['create_time']  = time();
            $groupdata['valid_begin']  = time();
            $groupdata['valid_end']    = time()*84600*24*365*2;
            $groupdata['group_type']   = 1;
            $gid = $rugmodel->add($groupdata);
        }else{
            $gid = $groupid['id'];
        }

        $rusermap['uid']      = $uid;
        $rusermap['group_id'] = $gid;
        $RecommendUser->where($rusermap)->delete();

        $userinfo = D('User')->search('uid = '.$uid,'sex');
        $ruserdata['uid']      = $uid;
        $ruserdata['sex']      = $userinfo['sex'];
        $ruserdata['group_id'] = $gid;
        $ruserdata['content']  = json_encode(array(array('user_tag_id'=>$tag_id)));
        if($RecommendUser->add($ruserdata)){
            D('PhpServerRedis')->updatehostsetclass($gid); // 更新redis
            return 'ok';
        }
    }

















}