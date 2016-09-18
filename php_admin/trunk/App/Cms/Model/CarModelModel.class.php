<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/12
 * Time: 11:49
 */

namespace Cms\Model;

use Cms\Model;
class CarModelModel extends CjDataModel{

    public function lists($map,$Page)
    {
        $Lib = D($this->name);
        $ret = $Lib->join('LEFT JOIN cj_car_brand ON cj_car_brand.id=cj_car_model.brand_id')
                   ->where($map)
                   ->field('cj_car_model.id,
                            cj_car_model.name,
                            cj_car_brand.name as modelName')
                   ->limit($Page->firstRow.','.$Page->listRows)
                   ->select();
        //$sql = $Lib->getLastSql();

        return $ret;
    }

    public function car_style($type,$brand_id,$id)
    {
        $Lib = D($this->name);
        if($type<1){
            $array = $Lib->field('id,name')->where('model='.$brand_id)
                         ->union('select id,name from cj_car_model where id<>'.$id)
                         ->select();
        }
        else{
            $array = $Lib->field('id,name')->where('model='.$brand_id)->select();
        }

        return $array;
    }

    /*
     * 根据某汽车品牌获取其下所有车型信息
     * */
    public function get_all_model($brand_id)
    {
        $Lib = D($this->name);
        $ret = $Lib->field('id,name')->where('brand_id='.$brand_id)->select();
        return $ret;
    }

    public function get_lib_info($map)
    {
        $Lib     = D($this->name);
        $ret   =  $Lib->join('LEFT JOIN cj_car_model ON cj_car_model.id=cj_car_model.model')
                      ->where($map)
                      ->field('cj_car_model.id as id,cj_car_model.name as name')
                      ->select();

        return $ret;
    }

    public function get_license_info_with_name($name)
    {
        $Lib     = D($this->name);

        $model_id = $Lib->where("name='%s'",$name)->field('model')->find();
        $ret      = $Lib->join('LEFT JOIN cj_car_model ON cj_car_model.id=cj_car_model.model')
                        ->where('cj_car_model.id='.$model_id['model'])
                        ->field('cj_car_model.id as id,cj_car_model.name as name')
                        ->select();

        return $ret;
    }

    public function get_single_item($map)
    {
        $Lib = D($this->name);
        $ret = $Lib->join('LEFT JOIN cj_car_brand ON cj_car_brand.id=cj_car_model.model')
                   ->where($map)
                   ->field('cj_car_brand.id as id,
                            cj_car_brand.name as name,
                            cj_car_model.id as modelId,
                            cj_car_model.name as modelName')
                   ->find();

        return $ret;
    }
}
