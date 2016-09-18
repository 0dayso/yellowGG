<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/9
 * Time: 13:10
 */

namespace Cms\Model;

use Think\Model;
class PublicModel extends Model{

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


  // 列表分页
  public function plist($page = 20,$map = array())
  {
    if($map) $this->where($map);
    $opt = $this->options;
    $pag = is_object($page) ? $page : new \Think\Page($this->count($this->getPk() ?: ''),$page);
    $this->limit($pag->firstRow.','.$pag->listRows);
    $this->pager = $pag;
    if($opt) $this->options = array_merge($opt,$this->options);
    return $this;
  }

  public function lists($map = array(),$ord = array())
  {
    if($map) $this->where($map);
    if($ord) $this->order($ord);
    $arr = $this->select();
    return $arr;
  }

  // 获取列表 以某个字段(id)为数组键
  public function klist($key = 'id',$map = array(),$ord = array())
  {
    $arr = $this->lists($map,$ord) ?: array();
    if($arr && $key) $arr = array_combine(array_column($arr,$key),$arr);
    return $arr;
  }

  // 自动处理并验证某个字段
  public function auto_field($field = '',$data = '',$type='')
  {
    if(!trim($field)) return false;
    if(is_object($data)) $data = get_object_vars($data);
    $type || $type = self::MODEL_BOTH;
    $dat = array($field => $data);
    $dat = $this->auto_operation($dat,$type);
    if(!$this->autoValidation($dat,$type)) return false;
    return $dat[$field];
  }

  // ThinkPHP 自动完成 public
  public function auto_operation(&$data,$type)
  {
    if(!empty($this->options['auto']))
    {
      $_auto = $this->options['auto'];
      unset($this->options['auto']);
    }
    elseif(!empty($this->_auto))
    {
      $_auto = $this->_auto;
    }
    // 自动填充
    if(isset($_auto))
    {
      foreach ($_auto as $auto)
      {
        // 填充因子定义格式
        // array('field','填充内容','填充条件','附加规则',[额外参数])
        if(empty($auto[2])) $auto[2] =  self::MODEL_INSERT; // 默认为新增的时候自动填充
        if( $type == $auto[2] || $auto[2] == self::MODEL_BOTH)
        {
          if(empty($auto[3])) $auto[3] =  'string';
          switch(trim($auto[3]))
          {
            case 'function':    //  使用函数进行填充 字段的值作为参数
            case 'callback': // 使用回调方法
              $args = isset($auto[4])?(array)$auto[4]:array();
              if(isset($data[$auto[0]]))
              {
                array_unshift($args,$data[$auto[0]]);
              }
              if('function'==$auto[3])
              {
                $data[$auto[0]] = call_user_func_array($auto[1], $args);
              }
              else
              {
                $data[$auto[0]] = call_user_func_array(array(&$this,$auto[1]), $args);
              }
              break;
            case 'field':    // 用其它字段的值进行填充
              $data[$auto[0]] = $data[$auto[1]];
              break;
            case 'ignore': // 为空忽略
              if($auto[1]===$data[$auto[0]]) unset($data[$auto[0]]);
              break;
            case 'string':
            default: // 默认作为字符串填充
              $data[$auto[0]] = $auto[1];
          }
          if(isset($data[$auto[0]]) && false === $data[$auto[0]] ) unset($data[$auto[0]]);
        }
      }
    }
    return $data;
  }

}
