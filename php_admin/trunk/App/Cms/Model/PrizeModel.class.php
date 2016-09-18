<?php
/**
 * Created by PhpStorm.
 * User: zsj
 * Date: 2015/03/18
 * Time: 11:52
 */

namespace Cms\Model;

 
use Cms\Model;
class PrizeModel extends PublicModel{

    /*
     * 插入单条记录
     * @param data   array   array(field=>value)
     * */
    public function insert_single_item($data)
    {
        $Model = D($this->name);
        return $Model->data($data)->add();
    }

    public function getlist($where){

    	return D($this->name)->get_multi_items($where);
    }




}



?>