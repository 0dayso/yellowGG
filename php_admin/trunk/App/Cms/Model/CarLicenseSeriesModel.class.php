<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/19
 * Time: 14:28
 */

namespace Cms\Model;

use Think\Model;
class CarLicenseSeriesModel extends Model{

    //因为在搜索的时候还需要按车型名称搜索，所以有join
    public function search($map)
    {
        $License = D('CarLicenseSeries');

        $ret = $License->join('LEFT JOIN cj_car_library ON cj_car_library.id=cj_car_license_series.lib_id')
                       ->where($map)
                       ->count();

        return $ret;
    }

    public function lists($map,$Page)
    {
        $License = D('CarLicenseSeries');

        $ret = $License->join('LEFT JOIN cj_car_library ON cj_car_library.id=cj_car_license_series.lib_id')
                       ->where($map)
                       ->field('cj_car_license_series.id as id,
                                cj_car_license_series.style as style,
                                cj_car_library.name as name')
                       ->limit($Page->firstRow.','.$Page->listRows)
                       ->select();

        return $ret;
    }

    /*public function find($id,$map)
    {
        $License = D('CarLicenseSeries');
        $ret     = $License->join('LEFT JOIN cj_car_library ON cj_car_library.id=cj_car_license_series.lib_id')
                           ->where($map)
                           ->field('cj_car_license_series.id as id,
                                    cj_car_license_series.style as style,
                                    cj_car_library.name as car_mode')
                           ->find();

        return $ret;
    }*/

    public function get_license_info_with_model($map)
    {
        $License = D('CarLicenseSeries');
        $ret = $License->join('LEFT JOIN cj_car_library ON cj_car_library.id=cj_car_license_series.lib_id')
                       ->where($map)
                       ->field('cj_car_license_series.id as license_series_id,
                                cj_car_license_series.style as series,
                                cj_car_license_series.lib_id as style_id,
                                cj_car_library.name as style_name')
                       ->select();
        return $ret;
    }
} 