<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/5
 * Time: 14:36
 */

namespace Cms\Event;

namespace Cms\Event;

use Cms\Event;
class CarEvent extends PublicEvent{

    /*
     * 将某个汽车品牌或者车型放到品牌或车型序列的第一个
     * */
    public function sort_list($list=array(),$firstId)
    {
        for($i=0;$i<count($list);$i++){
            if($list[$i]['id'] == $firstId){
                $temp = $list[$i];
                unset($list[$i]);
                break;
            }
        }
        array_unshift($list,$temp);

        return $list;
    }

    /*
     * 将某个汽车品牌下的所有车型显示出来，用户的车型放在第一个
     * */
    public function car_model_display($brandId,$firstModelId)
    {
        $str        = '';
        $Model      = D('CarModel');
        $modelArr   = $Model->get_all_model($brandId);
        $modelArr   = $this->sort_list($modelArr,$firstModelId);
        //array_shift($modelArr);

        foreach($modelArr as $value){
            $name = $value['name'];
            $id   = $value['id'];
            $str .= "<option value='{$id}:{$name}'> {$name} </option>" ;
        }

        return $str;
    }
}
