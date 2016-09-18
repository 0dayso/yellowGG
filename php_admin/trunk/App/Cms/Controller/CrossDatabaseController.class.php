<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/5
 * Time: 15:22
 */

namespace Cms\Controller;

use Think\Controller;
class CrossDatabaseController extends PublicController{

    /*
     * 接口涉及原则
     *     1、如果有多个join则在multi_join中多次调用single_join；
     *     2、A join B的前提是A必须要有自增id，这样才能保证在调用
     *        接口的函数中使用了搜索和排序或者limit也都可以实现；
     *     3、调用原则，根据需要显示的数据唯一性确定initModel；
     *        如在CertificateBrandModel中task_hall_unallocated_list
     *        需要显示的唯一数据是任务本身，而不是任务内容--图片url--所以
     *        应该是unallocated join certificate_car；
     *     4、data_field应该包含after_left_match_field及after_right_match_field；
     *
     * */

    /*
     * 单个join连接，不跨数据库操作
     * */
    public function single_join()
    {

    }

    /*
     * 将三维数组处理成二维数组
     * */
    public function process_array($data=array())
    {

    }

    /*
     * 对已有的数据进行分组
     * 先对field1进行分组然后field2
     * @param data 形式如array(0=>array('uid'=>1),'1'=>array('uid'=>2)...)
     * @return array 形式如array(field=>array('uid'=>1),field=>array('uid'=>2))
     * */
    public function process_group($data=array(),$field1)
    {
        $ret = array();
        $len = count($data);
        if($len>0){
            foreach($data as $key=>$value){
                $fieldValue = $value[$field1];
                if(!isset($ret[$fieldValue])){
                    $ret[$fieldValue] = array();
                }
                array_push($ret[$fieldValue],$value);
            }
        }

        return $ret;
    }

    /*
     * 对已有数据进行排序
     * @param data 形式如 array(0=>array('uid'=>1),'1'=>array('uid'=>2)...)
     * @return ret 形式如 array(0=>array('uid'=>1),'1'=>array('uid'=>2)...)
     * todo 暂时只支持对整数排序 后面需要添加对字符串及timestamp排序
     * */
    public function process_order($data,$field,$type=null)
    {
        $ret       = array();
        $temp      = array();
        $unSortArr = array();

        if(count($data)>0){
            //$sortType = gettype($data[0][$field]);
            foreach($data as $key=>$value){
                //$temp[] = array('num'=>$key,'value'=>$value[$field]);
                //$unSortArr[] = array($value[$field]);
                array_push($temp,$value[$field]);
                $unSortArr = $temp;
            }

            if($type=='asc'||$type==null){
                sort($unSortArr);
            }
            elseif($type=='desc'){
                rsort($unSortArr);
            }
            else{
                $this->error('没有这种排序方式');
            }

            for($i=0;$i<count($unSortArr);$i++){
                $num = array_search($unSortArr[$i], $temp);
                array_push($ret,$data[$num]);
            }
        }
        else{
            $ret = $data;
        }

        return $ret;

    }

    /*
     * 跨库连接单个join连接
     * @param initModel 原始model,形式如：
     *                  array(
     *                      'model_name' => 'CertificateCarReqUnallocated',
     *                      'match_field' => 'certificate_car_id',
     *                      'data_field' => 'id,certificate_car_id',
     *                      'after_match_field' => 'certificate_car_id',
     *                      'limit' => 10,
     *                  );
     * @param joinModel join的model，是一个array，形式如：
     *                  array(
     *                      'model_name' =>'',
     *                      'table_name' => ''
     *                      'type' => 'left',
     *                      'match_field'=>'',
     *                      'data_field'=>'',
     *                      'after_match_field' => '',
     *                  );
     * @param field  最终需要获取的字段
     * @return $ret  以从数据库获取的数据为依据，组建二维数组
     * */
    public function cross_single_join($initModelData=array(),$joinModel=array())
    {
        $map        = array();
        $in         = array();
        $ret        = $initModelData;

        $data_field          = $joinModel['data_field'];
        $left_match_Field    = $joinModel['left_match_field'];
        $right_match_Field   = $joinModel['right_match_field'];
        $left_combine_Field  = $joinModel['left_combine_field'];
        $right_combine_Field = $joinModel['right_combine_field'];
        if(isset($joinModel['map']))
            $map             = $joinModel['map'];

        foreach($ret as $value){
            array_push($in,$value[$left_match_Field]);
        }

        $Model = D($joinModel['model_name']);
        switch($joinModel['type']){
            case 'left':
                $map[$right_match_Field] = array('IN',$in);
                if($joinModel['table_name'] == ''){
                    $temp = $Model->where($map)->field($data_field)->select();
                    //$sql = $Model->getLastSql();
                }
                else{
                    $temp = $Model->table($joinModel['table_name'])->where($map)->field($data_field)->select();
                }

                if(count($temp)>0){
                    $ret = $this->combine_data($ret,$temp,$left_combine_Field,$right_combine_Field);
                }
                break;
            case 'right':
                break;
            case 'default':
                break;
        }

        return $ret;
    }

    /*
     * 组合来自两个表的数据
     * */
    protected function combine_data($init=array(),$combine=array(),$left_field,$right_field)
    {
        //获取数据库字段数据类型,获取数据库字段名称
        /*$dataType = $this->get_data_type(current($temp));
        $fieldArr = array_keys(current($temp));*/

        for($i=0;$i<count($init);$i++){
            for($j=0;$j<count($combine);$j++){
                if($init[$i][$left_field]==$combine[$j][$right_field]){
                    $init[$i] = array_merge($init[$i],$combine[$j]);
                    break 1;
                }

                /*if($j == (count($temp)-1)){
                    foreach($fieldArr as $value){

                    }
                }*/
            }
        }

        return $init;
    }

    /*
     * 在组合数据后进行map查询
     * @param map array like array('field','type','data')
     * */
    public function search($map,$data)
    {
        $ret = $data;

        if(count($map) < 3)
            return $ret;

        $len   = count($ret);
        $field = $map['field'];
        $aim   = $map['data'];
        $type  = $map['type'];

        switch($type)
        {
            case 'EQ'://等于
                for($i=0;$i<$len;$i++){
                    if($ret[$i][$field] != $aim)
                        unset($ret[$i]);
                }
                break;
            case 'NEQ'://不等于
                for($i=0;$i<$len;$i++){
                    if($ret[$i][$field] == $aim)
                        unset($ret[$i]);
                }
                break;
            case 'GT'://大于
                for($i=0;$i<$len;$i++){
                    if($ret[$i][$field] <= $aim)
                        unset($ret[$i]);
                }
                break;
            case 'EGT'://大于等于
                for($i=0;$i<$len;$i++){
                    if($ret[$i][$field] < $aim)
                        unset($ret[$i]);
                }
                break;
            case 'LT'://小于
                for($i=0;$i<$len;$i++){
                    if($ret[$i][$field] >= $aim)
                        unset($ret[$i]);
                }
                break;
            case 'ELT'://小于等于
                for($i=0;$i<$len;$i++){
                    if($ret[$i][$field] > $aim)
                        unset($ret[$i]);
                }
                break;
            case 'LIKE'://SQL LIKE
                $push = array();
                for($i=0;$i<$len;$i++){
                    if(strstr($ret[$i][$field],$aim))
                        array_push($push,$ret[$i]);
                }
                $ret = $push;
                break;
            default:
                break;
        }

        return $ret;
    }

    /*
     * 获取数据类型
     * @param arr array
     * @return ret array
     * */
    protected function get_data_type($arr)
    {
        $ret =  array();
        foreach($arr as $value){
            array_push($ret,gettype($value));
        }

        return $ret;
    }

    /*
     * 获取initModel原始数据
     * @param initModel array
     * @param ret       array
     * */
    public function get_init_model_data($map,$Page,$initModel=array())
    {
        if($initModel['table_name'] == ''){
            $Model = D($initModel['model_name']);
            if($Page == 0){
                $ret   = $Model->where($map)->field($initModel['data_field'])->select();
            }
            else{
                $ret   = $Model->where($map)->field($initModel['data_field'])->limit($Page->firstRow.','.$Page->listRows)->select();
            }
        }
        else{
            $Model = D($initModel['model_name']);
            if($Page == 0){

                $ret   = $Model->table($initModel['table_name'])->where($map)->field($initModel['data_field'])->select();
            }
            else{
                $ret   = $Model->table($initModel['table_name'])->where($map)->field($initModel['data_field'])->limit($Page->firstRow.','.$Page->listRows)->select();
            }
        }
        //$sql = $Model->getLastSql();
        return $ret;
    }

    /*
     * 跨库连接多个join连接
     * */
    public function cross_multi_join($map=array(),$Page,$initModel,$joinModelArr=array())
    {
        $ret = $this->get_init_model_data($map,$Page,$initModel);
        foreach($joinModelArr as $key=>$value){
            $ret = $this->cross_single_join($ret,$joinModelArr[$key]);
        }

        $ret = $this->search($map,$ret);

        return $ret;
    }
}
