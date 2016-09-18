<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/9
 * Time: 13:57
 */

namespace Cms\Controller;

use Cms\Controller;
class BannerController extends PublicController{

    // banner 列表
    public function setbanner(){
        $this->spermission(54);
        $this->assign('list',D('Banner')->where()->order('id DESC')->select());
        $this->display();
    }

    // banner 添加
    public function setbanneradd(){
 
        if(IS_POST){
            // 删除缓存
            $phpServer = D('PhpServerRedis');
            //$phpServer->delete_recommend_user($uid);
            
            $p1 = $_FILES['file'];
            if($p1['name'] =='') 
                $this->error('请选择图片！',U('setbanneradd'));
           
            $baseUrl = 'banner/';
            $picName = md5($p1['name']);
            $pic     = $baseUrl.$picName;
            $ret     = $this->aliyup('cjstatic',$pic,$p1['tmp_name']);

            if($ret->status == 200 ){
                
                $banner = array(
                        'title'        =>trim($_POST['title']),
                        'thumb'        =>$picName,
                        'page'         =>$_POST['page'],
                        'type'         =>$_POST['type'],
                        'clickoff'     =>intval($_POST['clickoff']),
                        'valid_begin'  =>strtotime($_POST['valid_begin']),
                        'valid_end'    =>strtotime($_POST['valid_end']),
                        'index'        =>$_POST['index'], 
                        'create_time'  =>time() 
                    );
                $id = D('Banner')->data($banner)->add();
                if($id!='' ){
                    $this->success('跳转到第二步！',U('bannertwo',array('type'=>$_POST['type'],'id'=>$id)));
                }else{
                    $this->success('添加失败！');
                } 
            }else{
                $this->error('图片上传失败！',U('setbanneradd'));
            }
        
        }else{
            $this->spermission(53);
            $this->display();
        }

    }   


    public function bannertwo(){
        if(IS_POST){
            A('Search','Event')->bannertwo($_POST['id'],$_POST['type'],$_POST['item']);
        }else{
            $id   = intval($_GET['id']);
            $type = intval($_GET['type']);
            if($type=='' || $id =='' ){
                $this->error('banner不存在！');
            }
            $this->assign('list',A('Search','Event')->bannercontentget($id,$type,'banner_id'));
            //layout(false);
            $this->display();
        }
        
    }

    public function del_items(){
        if(IS_POST && $_POST['id']!=''){
            D('BannerContent')->where('id = '.$_POST['id'])->delete();  
            echo 'ok';          
        }

    }
    
    // banner查看
    public function showbanner(){
        $id = $_GET['id'];
        $type = $_GET['type'];
        if($id =='' || $type ==''){
            $this->error('banner不存在！');
        }
        $list = A('Search','Event')->getcontentbanner($_GET['id'],$_GET['type']);

        $this->assign('list',$list);
        $this->display();
    }
    

    // 编辑banner
    public function editbanner(){
        if(IS_POST){
            // 删除缓存
            $phpServer = D('PhpServerRedis');
            $phpServer->delete_recommend_user();

            $p1 = $_FILES['file'];
            if($p1['name']!=''){ // 修改图片
                $bucket  = 'cjstatic';
                $baseUrl = 'banner/';
                $picName = md5($p1['name']);
                $pic     = $baseUrl.$picName;
                $banner['thumb']  = $picName;
                $ret     = $this->aliyup($bucket,$pic,$p1['tmp_name']);        // 上传新图片
                $del     = $this->aliydel($bucket,'banner/'.$_POST['thumb']);  // 删除图片
            }

            $bid = intval($_POST['id']);

            $banner['title']       = trim($_POST['title']);
            $banner['type']        = intval($_POST['type']);
            $banner['clickoff']    = intval($_POST['clickoff']);
            $banner['valid_begin'] = strtotime($_POST['valid_begin']);
            $banner['valid_end']   = strtotime($_POST['valid_end']);
            $banner['index']       = intval($_POST['index']);
            $banner['create_time'] = time();
            $banner['page']        = intval($_POST['page']);
            // 清除以前banner内容
            $ba = D('Banner')->get_single_item('id = '.$bid,'type');
            if( $ba['type']!= $_POST['type'] ){
                D('BannerContent')->where("banner_id = ".$bid)->delete();
                D('SetSurging')->where("banner_id = ".$bid)->delete();
            }

            D('Banner')-> update_single_item('id='.$bid,$banner);
            $this->redirect('banner/bannertwo',array('id'=>$bid,'type'=>$_POST['type']));
   
        }else{
            $info   = D('Banner')->get_single_item('id='.$_GET['id']);
            $info_c = D('BannerContent')->get_single_item('banner_id='.$_GET['id']);
            $info['item']  = $info_c['item'];
          
            $this->assign('info',$info);
            $this->display('setbanneradd');
        }
    }

    public function addBanner() {
        $p1 = $_FILES['p1'];
        if(!$p1)echo "没有要上传的文件";

        $bucket = 'cjstatic';
        $baseUrl = '/banner/';
        $ret = $this->aliyup($bucket,$baseUrl.$p1['name'],$p1['tmp_name']);
    }








}



