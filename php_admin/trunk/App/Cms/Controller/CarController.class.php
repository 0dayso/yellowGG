<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/12
 * Time: 11:56
 */
namespace Cms\Controller;

use Cms\Controller;
class CarController extends PublicController{

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

    public function index()
    {
        $Model = D('CarBrand');
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
        $this->display();
    }

    public function lib()
    {
        $Lib = D('CarLibrary');
        $itemsPerPage = 30;//C('ITEMS_PER_PAGE');

        $map = car_lib_search();

        $count = $Lib->where($map)->count();

        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();

        $list = $Lib->lists($map,$Page);

        $this->assign("page", $show);
        $this->assign("list", $list);
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

    public function get_car_style()
    {
        $str = '';
        $brand = $_GET['brand'];
        if(!$brand) return false;
        $Lib     = D('CarLibrary');
        $map['cj_car_model.id'] = array('EQ',$brand);
        $array = $Lib->get_lib_info($map);
        $str .= "<option value='0' selected='selected'>请选择车型</option>";
        foreach($array as $value){
            $name = $value['name'];
            $id   = $value['id'];
            $str .= "<option value='{$id}:{$name}'> {$name} </option>" ;
        }

        echo $str;
    }

    /*
     * 根据汽车品牌获取车型
     * */
    public function get_car_model()
    {
        $brand      = $_GET['brand'];
        $temp       = explode(':',$brand);
        $brand_id   = current($temp);
        $carModel   = $_GET['model'];
        $firstModelId = current($carModel);

        $Event    = A('Car','Event');
        $ret      = $Event->car_model_display($brand_id,$firstModelId);

        echo $ret;
    }

    public function add_license()
    {
        $data = array();
        $data['style']   = trim($_REQUEST['series']);//行驶证序列
        $data['lib_id']  = trim($_REQUEST['style']);//车型id
        $License = D('CarLicenseSeries');
        $info = $License->add($data);
        if($info)
            $this->success('添加成功');
        else
            $this->error('添加失败');
    }

    //显示修改license_series的页面
    //根据cj_car_license_series.id获取车型名称以及车型id
    public function edit_license()
    {
        $id   = trim($_REQUEST['id']);
        $name = trim($_REQUEST['name']);

        $Lib     = D('CarLibrary');
        $License = D('CarLicenseSeries');

        $list = $Lib->get_license_info_with_name($name);
        if($id){
            $map['cj_car_license_series.id']  = array('EQ',$id);
            $ret = $License->get_license_info_with_model($map);
            $info = current($ret);
            $this->assign("info", $info);
        }
        else{
            $this->error('不存在该条记录');
        }

        $this->assign("list", $list);
        $this->display("Car:edit_license");
    }

    //在license_series页面操作了后进行处理相关数据
    public function process_license()
    {
        if(empty($_GET)){
            $this->error('没有传递参数');
        }
        else{
            $id      = trim($_GET['id']);//cj_car_license_series.id
            $series  = trim($_GET['series']);//行驶证序列
            $lib_id  = trim($_GET['style_id']);//车型id:汽车品牌

            $data    = array('style'=>$series,'lib_id'=>$lib_id);
            $License = D('CarLicenseSeries');
            $License->where('id='.$id)->save($data);

            $this->redirect('Car/license');
        }
    }

    public function delete_license()
    {
        $License = D('CarLicenseSeries');

        if($id = $_GET['id']){
            $License->where('id='.$id)->delete();
        }
        else{
            $this->error("该记录缺id");
        }

        $this->redirect("Car/license");
    }

    public function edit()
    {
        $Lib  = D("CarLibrary");
        $mode = D("CarBrand");
        $list = $mode->field("id,name")->select();

        if(isset($_GET['id'])){
            $map['cj_car_model.id']  = array('EQ',$_GET['id']);
            $info = $Lib->get_single_item($map);

            if ($info['id'] == '') {
                $this->error("不存在该条记录");
            }
            $this->assign("info", $info);
        }
        $this->assign("list", $list);
        $this->display("Car:add");
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
        $this->redirect("Car/lib");
    }

    public function process()
    {
        if(empty($_GET)){
            $this->error('没有传递参数');
        }
        else{
            $type        = trim($_GET['name']);//车型名称
            $model       = trim($_GET['model']);//车型id:汽车品牌
            $series      = trim($_GET['series']);//行驶证序列号
            $lib         = D('CarLibrary');
            $exist       = strpos($model,':',0);
            if($exist){
                $car         = explode(':',$model);
                $data        = array('name'=>$type,'model'=>$car[1],'license_series'=>$series);
                $map['id']   = $car[0];
                $lib->where($map)->save($data);
            }
            else{
                //$data        = array('name'=>$type,'model'=>$model,'license_series'=>$series);
                $data        = array('name'=>$type,'model'=>$model);
                $lib->add($data);
            }

            $this->redirect('Car/lib');
        }
    }
}
