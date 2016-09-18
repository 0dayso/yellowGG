<?php
/**
 * Created by PhpStorm.
 * User: zsj
 * Date: 2015/ 03/30
 * Time: 10:  22
 */
namespace Cms\Model;

 
use Cms\Model;
class ExchangeModel extends CjDataModel{

    // 根据 奖品id 获取个运营商的兑奖数量
    public function getclassnum($where){
        
        // 移动  139,138,137,136,135,134,178,188,187,183,182,159,158,157,152,150,147
        // 联通  186,185,130,131,132,155,156,        
        // 电信  189,181,180,153,133,

        $phone['yd'] = array(139,138,137,136,135,134,178,188,187,183,182,159,158,157,152,150,147);
        $phone['lt'] = array(186,185,130,131,132,155,156);
        $phone['dx'] = array(189,181,180,153,133);
        $list = D($this->name)->field('phone')->where($where)->select();

        $num['yd'] = $num['lt'] = $num['dx'] = 0;
        if(!empty($list)){
            foreach ($list as   $value){
                $strt = substr($value['phone'],0,3);
                if(in_array($strt,$phone['yd'])){ 
                    $num['yd']++;
                }elseif(in_array($strt,$phone['lt'])){
                    $num['lt']++;
                }else{
                    $num['dx']++;
                }
            }
        } 
        return $num;

    }

    public function update_multi_items($map,$data)
    {
        $User = D($this->name);
        return $User->where($map)->data($data)->save();

    }  

    

    


    

   
}
