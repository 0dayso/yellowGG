<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/30
 * Time: 13:03
 */

namespace Cms\Controller;

use Cms\Controller;
class ContentManageController extends PublicController{

    public function index()
    {
        $this->display();
    }

    public function car_brand()
    {
        $Model = D('CarBrand');
        $itemsPerPage = 30;//C('ITEMS_PER_PAGE');

        $map = car_brand_search();
        $count = $Model->where($map)->count();

        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();

        $list = $Model->lists($map,$Page);

        $this->assign("page", $show);
        $this->assign("list", $list);
        $this->display();
    }

    public function car_model()
    {
        $brand = D('CarBrand')->where(" name like '%{$_GET['keyword']}%' ")->find();
        if($brand['id']==''){
            $this->error('汽车品牌不存在！');
        }

        $Model = D('CarModel');
        $itemsPerPage = 30;//C('ITEMS_PER_PAGE');

        $map = car_model_search();

        $count = $Model->where($map)->count();

        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();

        $list = $Model->lists($map,$Page);

        $this->assign("page", $show);
        $this->assign("list", $list);
        $this->assign("brand", $brand);
        $this->display();
    }

    public function license()
    {
        $itemsPerPage = C('ITEMS_PER_PAGE');
        $License = D('CarLicenseSeries');
        $Model   = D('CarBrand');

        $map = car_license_search();

        $count = $License->search($map);

        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();

        $list  = $License->lists($map,$Page);
        $brand = $Model->field('id,name')->select();

        $this->assign('brand',$brand);
        $this->assign("page", $show);
        $this->assign("list", $list);
        $this->display();
    }

    public function add_car_model()
    {
        $Model  = D("CarModel");
        $Brand  = D("CarBrand");
        $list   = $Brand->field("id,name")->select();

        if(isset($_GET['id'])){
            $map['cj_car_model.id']  = array('EQ',$_GET['id']);
            $info = $Model->get_single_item($map);

            if ($info['id'] == '') {
                $this->error("不存在该条记录");
            }
            $this->assign("info", $info);
        }
        $this->assign("list", $list);
        $this->display("Content_manage/add_car_model");
    }

    public function delete()
    {
        $Lib  = D("CarLibrary");

        if($id=$_GET['id']){
            $Lib->where('id='.$id)->delete();
        }
        else{
            $this->error("该条记录缺id");
        }
        $this->redirect("ContentManage/add_car_lib");
    }

    public function confirm_add_car_model()
    {
        if(empty($_GET)){
            $this->error('没有传递参数');
        }
        else{
            $type        = trim($_GET['name']);//车型名称
            $model       = trim($_GET['model']);//车型id:汽车品牌
            $series      = trim($_GET['series']);//行驶证序列号
            $lib         = D('CarModel');
            $exist       = strpos($model,':',0);
            if($exist){
                $car         = explode(':',$model);
                $data        = array('name'=>$type,'model'=>$car[1],'license_series'=>$series);
                $map['id']   = $car[0];
                $lib->where($map)->save($data);
            }
            else{
                //$data        = array('name'=>$type,'model'=>$model,'license_series'=>$series);
                $data        = array('name'=>$type,'brand_id'=>$model);
                $lib->add($data);
            }

            $this->redirect('ContentManage/car_model');
        }
    }

    public function upload()
    {
        $name = $_GET['id'];
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('png');// 设置附件上传类型
        $upload->rootPath  =     '/opt/wwwroot/static.chujian.im/car_logo/';//'./upload/'; // 设置附件上传根目录
        $upload->autoSub   =     false;
        //$upload->savePath  =     'file'; // 设置附件上传（子）目录
        $upload->saveName  =     $name;//'';//array('date','Y-m-d');为空保存原文件名，也可以用date方式重新命名
        $upload->replace   =     true;//同名文件覆盖处理
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
            $this->success('上传成功！');
        }
    }

    /*
     * 内容管理文字审查
     * @param menu 1
     * @param type 1
     * */
    public function user_info_certificate()
    {

    }

    /*
     * 设置敏感词库
     * */
    public function set_sensitive()
    {
        $map['word'] = array('like','%'.$_GET['keyword'].'%');
        $list = D('SensitiveWords')->get_multi_items($map);
        $this->assign('list',$list);
        $this->display();
    }

    // 删除敏感词库
    public function del_sensitive(){
        if($_GET['id']){
            D('SensitiveWords')->delete_single_item('id='.$_GET['id']);
        }
        $this->redirect('set_sensitive');
    }

    // 修改敏感词库
    public function mod_sensitive(){
        if($_POST['id']){
            $data['word'] = str_replace(' ','',$_POST['word']);
            D('SensitiveWords')->update_multi_items('id='.$_GET['id'],$data);
        }
        echo 1;
    }

    // 添加敏感词库
    public function add_sensitive(){
        
        $data['word'] = str_replace(' ','',$_POST['word']);
        D('SensitiveWords')->insert_single_item($data);
        
        echo 1;
    }


}
