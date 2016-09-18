<?php
/**
 * Created by PhpStorm.
 * User: fengj
 * Date: 2015/6/30
 * Time: 13:26
 */
namespace Cms\Controller;

use Cms\Controller;
use Cms\Controller\MessageController;
class TagController extends PublicController{


    public function tagsurging(){
        $type = intval($_GET['type']);
        $this->assign('list',A('Search','Event')->tagsurging($type));
        $this->display();
    }

    public function confirm_all_certificated(){
        if(IS_POST){
            $list = trim(implode(',',$_POST['list']),',');
            if($list !='' ){
                foreach($_POST['list'] as $key => $val){
                    $data[$key]['surging'] = $val;
                    $data[$key]['aid']    = $_SESSION['authId'];
                    $data[$key]['certificate_time'] = time();
                    $data[$key]['status'] = 1;
                }
                D('SurgingLog')->addAll($data);
                D('Surging')->update_single_item(" id IN($list) ",array('status'=>1));
                echo 'ok';
            }
        }
    }

    public function confirm_all_query(){
        if(IS_POST){
            $list = trim(implode(',',$_POST['list']),',');
            if($list !='' ){
                $data['confirm']      = $_SESSION['authId'];
                $data['confirm_time'] = time();
                D('SurgingLog')->update_single_item(" surging IN($list) ",$data);
                echo 'ok';
            }
        }
    }


}