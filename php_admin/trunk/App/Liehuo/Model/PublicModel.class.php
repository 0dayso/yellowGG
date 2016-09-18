<?php
namespace Liehuo\Model;
use Common\Model\CommonModel;

class PublicModel extends CommonModel
{

    // 设置用户ID
    public function set_user($uid = 0)
    {
      $this->uid = (int)$uid;
      return $this;
    }

    /*
     * 获取单条记录
     * @param map    array   查询条件
     * @param field  string  多个或者单个字段名组成的字符串
     * */
    public function get_single_item($map,$field=null){
        if($field == null){
            $Model = D($this->name);
            $ret = $Model->where($map)->find();
        }
        else{
            $Model = D($this->name);
            $ret = $Model->where($map)->field($field)->find();
        }

        return $ret;
    }

    /*
     * 获取多条记录
     * @param map    array   查询条件
     * @param field  string  多个或者单个字段名组成的字符串
     * */
    public function get_multi_items($map,$field=null)
    {
        if($field == null){
            $Model = D($this->name);
            $ret = $Model->where($map)->select();
        }
        else{
            $Model = D($this->name);
            $ret = $Model->where($map)->field($field)->select();
        }

        return $ret;
    }

    /*
     * 获取单条记录某个字段内容
     * @param map    array   查询条件
     * @param field  string  单个字段名
     * @return value/null
     * */
    public function get_single_field_value($map,$field)
    {
        $Model = D($this->name);
        $item = $Model->where($map)->field($field)->find();

        if(isset($item[$field]))
            return current($item);
        else
            return null;
    }

    /*
     * 获取items数量
     * @param map array 查询条件
     * @return ret int
     * */
    public function get_count($map)
    {
        $Model = D($this->name);
        $ret = $Model->where($map)->count();

        return $ret;
    }

    /*
     * 更新单条记录
     * @param map    array   查询条件
     * @param data   array   array(field=>value)
     * */
    public function update_single_item($map,$data)
    {
        $Model = D($this->name);
        $Model->where($map)->data($data)->save();
    }

    /*
     * 更新多条记录
     * @param map    array   查询条件
     * @param data   array   array(0=>array,1=>array,...)
     * */
    public function update_multi_items($map,$data)
    {
        $Model = D($this->name);
        $Model->where($map)->data($data)->save();
    }

    /*
     * 插入单条记录
     * @param data   array   array(field=>value)
     * */
    public function insert_single_item($data)
    {
        $Model = D($this->name);
        $Model->data($data)->add();
    }

    /*
     * 插入单条记录
     * @param data   array   array(0=>array,1=>array)
     * 注意：data最好在循环里以items[]=array形式添加
     * */
    public function insert_multi_items($data)
    {
        $Model = D($this->name);
        $Model->addAll($data);
    }

    /*
     * 删除单条记录
     * @param map   array/string 最好是string
     * */
    public function delete_single_item($map)
    {
        $Model = D($this->name);
        $Model->where($map)->delete();
    }

    /*
     * 插入单条记录
     * @param map   array(field=>array(IN,array))
     * */
    public function delete_multi_items($map)
    {
        $Model = D($this->name);
        $Model->where($map)->delete();
    }

    /*
     * 限制获取记录的数量
     * */
    public function get_limit_items($map,$limit,$field=null)
    {
        $Model = D($this->name);
        if($field == null){
            $ret = $Model->where($map)->limit($limit)->select();
        }
        else{
            $ret = $Model->where($map)->field($field)->limit($limit)->select();
        }

        return $ret;
    }

    /*
     * 清空数据表
     * */
    public function truncate_table()
    {
        $Model = D($this->name);
        $sql   = "TRUNCATE ".$this->trueTableName;
        $count = $Model->count();
        if($count>0)
            $Model->execute($sql);
    }

    // 查询单条
    public function search($where='',$field=''){
        return D($this->name)->field($field)->where($where)->find();
    }
    // 查询多条
    public function searchs($where='',$field=''){
        return D($this->name)->field($field)->where($where)->select();
    }

    // 自动设置排序
    public function auto_sort_set($id = 0,$field = 'sort')
    {
      if($id >= 1 && $field && trim($_REQUEST[$field]) === '')
      {
        $sort = $id * 10;
        $this->where(['id' => $id])->setField($field,$sort);
        return $sort;
      }
      else return false;
    }

}