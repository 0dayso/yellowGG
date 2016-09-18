<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/12
 * Time: 11:48
 */

namespace Cms\Model;

use Cms\Model;
class CarBrandModel extends CjDataModel{

    public function edit()
    {
        if(isset($_POST['id'])){
            $mode = D('CarBrand');
            return ($mode->save($_POST)) ? array('status'=>1,'修改成功') : array('status'=>0,'修改失败');
//          return ($M->save($data)) ? array('status' => 1, 'info' => '分类 ' . $data['name'] . ' 已经成功更新',
//         'url' => U('Product/category', array('time' => time()))) : array('status' => 0, 'info' => '分类 ' . $data['name'] . ' 更新失败');
        }
    }

    public function lists($map,$Page)
    {
        $Model = D('CarBrand');

        $ret = $Model->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();

        return $ret;
    }

    /*
     * 获取车型信息
     *
     * @output 车型id
     * @output 车型name
     * @output 汽车品牌id
     * */
    public function car_brand($type,$id)
    {
        $Model = D('CarBrand');
        if($type<1){
            $brand = $Model->field('id,name')->where('id='.$id)
                           ->union('select id,name from cj_car_model where id<>'.$id)
                           ->select();
        }
        else{
            $brand = $Model->field('id,name')->select();
        }

        return $brand;
    }

    public function get_all_car_brand()
    {
        $Model = D('CarBrand');
        $ret   = $Model->field('id,name')->select();
        return $ret;
    }
} 