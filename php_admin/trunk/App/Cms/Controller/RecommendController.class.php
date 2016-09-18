<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/9
 * Time: 13:57
 */

namespace Cms\Controller;

use Think\Cache\Driver\Redis;
use Cms\Controller;
class RecommendController extends PublicController{

    // 推荐用户列表
    public function putinfo(){  
        $this->assign('list',A('Recommend','Event')->getlist());
        $this->display();
    }

    // 增加推荐 
    public function putinfoadd(){   

        if(IS_POST){
            // 删除缓存
            $phpServer = D('PhpServerRedis');
            //$phpServer->delete_recommend_user($uid);
            if($_POST['valid_begin']!='' && $_POST['valid_end']!=''){
                $data['valid_begin']  = strtotime($_POST['valid_begin']);
                $data['valid_end']    = strtotime($_POST['valid_end']);
            }
            $data['valid_begin']  = strtotime($_POST['valid_begin']);
            $data['valid_end']    = strtotime($_POST['valid_end']);
            $data['tag_class_id'] = ($_POST['tag_class_id']!='')?$_POST['tag_class_id']:0;
            $data['create_time']  = time();
            $id = D('RecommendUserGroup')->data($data)->add();
            $this->redirect('recommend/putinfoadduser',array('id'=>$id));

        }else{
            $this->assign('option',A('Search','Event')->classtagselecthtml());
            $this->display();   
        }
    }

    // 增加推荐用户
    public function putinfoadduser(){   
        if(IS_POST){
            // 删除缓存
            $phpServer = D('PhpServerRedis');
            $phpServer->delete_recommend_user($uid);

            A('Search','Event')->putinfoadduser($_POST['item'],$_POST['id']);
            $this->redirect('recommend/putinfoadduser',array('id'=>$_POST['id']));
        }else{
            $this->assign('list',A('Search','Event')->gettuijianinfo($_GET['id']));
            $this->display();
        }
    }

    // 增加默认关注推荐用户
    public function defaultattention(){
        $group = D('RecommendUserGroup')->search('group_type = 2','id');
        if(IS_POST){
            if($group['id']==''){
                $this->error('默认关注推荐组，不存在');
            }
            A('Search','Event')->putdefaultattention($_POST['item'],$_POST['sex'],$group['id']);
            $this->redirect('recommend/defaultattention');
        }else{
            $this->assign('list',A('Search','Event')->defaultattention($group['id']));
            $this->assign('id',$group['id']);
            $this->display();
        }
    }

    // 给用户设置标签内容
    public function addrecommendtag(){
        if(IS_POST){
            echo A('Search','Event')->addrecommendtag($_POST);
        }
    }


    // 热门分类推荐
    public function hostsetclass(){

        $search = A('Search','Event');
        $this->assign('post',$_GET);
        $this->assign('option',$search->classtagselecthtml($_GET['tag_class_id']));
        $this->assign('list',$search->hostsetclass($_GET));

        $this->display();
    }


    // 添加分类推荐用户
    public function add_items(){
        if(IS_POST && !empty($_POST['info'])){
            echo A('Recommend','Event')->add_items($_POST['info']);
        }
    }

    public function del_items(){
        $id = intval($_POST['id']);
        if(IS_POST && $id!=''){
            $model = D('RecommendUser');
            $gid   = $model->search('id ='.$id,'group_id');
            $model->where('id = '.$id)->delete();

            $gtype = D('RecommendUserGroup')->search('id ='.$gid['group_id'],'group_type');
            if($gtype['group_type']==2){ // 判断是否是默认关注推荐，如果是就重新设置redis
                A('Recommend','Event')->addredisrecommend();
            }
            echo 'ok';
        }
    }
 
    // 编辑推荐用户
    public function putinfomodi(){ 

        if(IS_POST){
            // 删除缓存
            $phpServer = D('PhpServerRedis');
            //$phpServer->delete_recommend_user($uid);

            $data['valid_begin'] = strtotime(trim($_POST['valid_begin']));
            $data['valid_end']   = strtotime(trim($_POST['valid_end']));
            $data['tag_class_id'] = ($_POST['tag_class_id']!='')?$_POST['tag_class_id']:0;
            D('RecommendUserGroup')->update_single_item('id='.$_POST['id'],$data);
            $this->redirect('recommend/putinfoadduser',array('id'=>$_POST['id']));
        }else{
            $list = A('Recommend','Event')->inforec($_GET['id']);
            $this->assign('list',$list);
            $this->assign('option',A('Search','Event')->classtagselecthtml($list[0]['tag_class_id']));
            $this->assign('te','edit');
            $this->display('putinfoadd');   
        }
    }

    // 删除推荐用户
    public function deluser(){
        // 删除缓存
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_recommend_user($uid);

        list($map['group_id'],$map['uid']) = explode('-',$_POST['id']);
        $del = D('RecommendUser')->where($map)->delete();
        if($del){
            echo 'ok';
        }
    }

    // 添加推荐用户
    public function adduser(){
        // 删除缓存
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_recommend_user($uid);

        $id   = $_POST['id'];
        // 检查组里面存在的用户
        $hvae = A('Recommend','Event')->haveuser($_POST['uid'],$id);
        if($hvae!=''){
            echo $hvae;
            exit;
        }
        
        //过滤不存user表里的数据
        $uid = A('Recommend','Event')->deleteuser($_POST['uid']);
 
        foreach ($uid as $key => $value) {
            $data[$key]['uid']       = $value['uid'];
            $data[$key]['group_id']  = $id;
        }
        $add = D('RecommendUser')->insert_multi_items($data);
        echo 'ok'; 
    }


    public function systag(){
        $this->assign('list',A('Recommend','Event')->getsystag());
        $this->display();
    }


    public function addsystag(){
        if(IS_POST){
            echo A('Recommend','Event')->addsystag($_POST['title'],$_POST['classid']);
        }
    }

    public function deltagsys(){
        $id = trim($_POST['id']);
        if(IS_POST && $id!='' ){
            $del = D('RecommendTag')->where(" id = $id ")->delete();
            if($del !==false){
                echo 'ok';
            }
        }
    }

         

}



