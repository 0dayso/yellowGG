<?php
namespace Common\Model;
use Think\Model;

class CommonModel extends Model
{

  public static $redis_instances;
  protected $redis_config;

  protected static $_instance;
  public static function Instance()
  {
    $args = func_get_args();
    $model = get_called_class();
    $key = md5($model.'|'.json_encode($args));
    if(!isset(self::$_instance[$key]) || !is_subclass_of(self::$_instance[$key],$model))
    {
      $obj = new \ReflectionClass($model);
      self::$_instance[$key] = $obj->newInstanceArgs($args);
    }
    return self::$_instance[$key];
  }


  // 列表分页
  public function plist($page = 20,$map = array())
  {
    $map && $this->where($map);
    $opt = $this->options;
    if(isset($opt['group']) && $opt['group'])
    {
      $cnt = M('','',$this->connection)->table($this->buildSql())->alias('t')->count();
    }
    else
    {
      $alias = $opt['alias'] ? ($opt['alias'].'.') : '';
      $cnt = $this->count($alias.($this->getPk() ?: ''));
    }
    $opt && $this->options = array_merge($this->options,$opt);
    $pag = is_object($page) ? $page : new \Think\Page($cnt,$page);
    $this->limit($pag->firstRow.','.$pag->listRows);
    $this->pager = $pag;
    return $this;
  }

  // 获取列表
  public function lists($map = array(),$ord = array())
  {
    $map && $this->where($map);//用数组添加
    $ord && $this->order($ord);
    $arr = $this->select();
    return $arr;
  }

  // 获取列表 以某个字段(主键)为数组的键
  public function klist($key = true,$map = array(),$ord = array())
  {
    $key === true && $key = $this->getPk() ?: 'id';
    $arr = $this->lists($map,$ord) ?: array();
    if($arr && $key) $arr = array_combine(array_column($arr,$key) ?: array(),$arr);
    return $arr;
  }

  // 自动完成 字符串
  public function auto_str($str = '')
  {
    return (string)$str;
  }

  // 自动完成 字串
  public function auto_trim($str = '')
  {
    return (string)trim($this->auto_str($str));
  }

  // 自动完成 整数
  public function auto_int($num = 0)
  {
    return (int)$num;
  }

  // 自动完成 时间
  public function auto_time($str = 0)
  {
    return is_numeric($str) ? (int)$str : strtotime($str);
  }

  // 自动完成 附加属性
  public function auto_attrs($attrs = array())
  {
    return $attrs && is_array($attrs) ? json_encode($attrs,JSON_UNESCAPED_UNICODE) : '';
  }

  public function attr2array($attr)
  {
    return (is_string($attr) ? json_decode($attr,true) : $attr) ?: [];
  }

  public function attr2array_row($arr = array(),$fields = array('attrs'))
  {
    if(!$fields) return $arr;
    if(!is_array($arr)) return array();
    $fields = is_array($fields) ? $fields : preg_split('/\s*,\s*/',$fields);
    foreach($fields as $f)
    {
      $arr[$f] = $this->attr2array($arr[$f]);
    }
    return $arr;
  }

  public function attr2array_all($arr = array(),$fields = array('attrs'))
  {
    if(!$fields) return $arr;
    if(!is_array($arr)) return array();
    $nar = array();
    foreach($arr as $k => $v)
    {
      $nar[$k] = $this->attr2array_row($v,$fields);
    }
    return $nar;
  }

  // 自动完成 关联IDs
  public function auto_join_ids($ids = [])
  {
    if(!is_array($ids) && trim((string)$ids) === '') return '';
    $ids = (array)$ids;
    array_unshift($ids,'');
    array_push($ids,'');
    return implode(',',$ids);
  }

  public function get_join_ids($ids = '')
  {
    $ret = [];
    if(is_array($ids)) $ret = $ids;
    elseif(is_string($ids))
    {
      $ids = trim($ids,', \t\n\r\0\x0B');
      $ret = $ids == '' ? [] : explode(',',$ids);
    }
    return $ret;
  }


  // 自动处理并验证某个字段
  public function auto_field($field = '',$data = '',$type = self::MODEL_BOTH)
  {
    if(!trim($field)) return false;
    if(is_object($data)) $data = get_object_vars($data);
    $dat = array($field => $data);
    $dat = $this->auto_operation($dat,$type);
    if(!$this->autoValidation($dat,$type)) return false;
    return $dat[$field];
  }

  // ThinkPHP 自动完成 public
  public function auto_operation($data = [],$type = self::MODEL_BOTH)
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
      foreach($_auto as $auto)
      {
        // 填充因子定义格式
        // array('field','填充内容','填充条件','附加规则',[额外参数])
        if(empty($auto[2])) $auto[2] = self::MODEL_INSERT; // 默认为新增的时候自动填充
        if($type == $auto[2] || $auto[2] == self::MODEL_BOTH)
        {
          if(empty($auto[3])) $auto[3] = 'string';
          switch(trim($auto[3]))
          {
            case 'function': // 使用函数进行填充 字段的值作为参数
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
            case 'field':  // 用其它字段的值进行填充
              $data[$auto[0]] = $data[$auto[1]];
              break;
            case 'ignore': // 为空忽略
              if($auto[1]===$data[$auto[0]]) unset($data[$auto[0]]);
              break;
            case 'string':
            default: // 默认作为字符串填充
              $data[$auto[0]] = $auto[1];
          }
          if(isset($data[$auto[0]]) && false === $data[$auto[0]]) unset($data[$auto[0]]);
        }
      }
    }
    return $data;
  }

  public function set_table($table = '')
  {
    $this->table($table);
    $this->trueTableName = $this->options['table'];
    return $this;
  }

  // 软删除
  public function soft_del($map = [])
  {
    $ret = false;
    $_pk = $this->getPk();
    if(is_numeric($map) && !is_array($pk)) $map = [$_pk => $map];
    if($map) $ret = $this->where($map)->setField('delete_time',NOW_TIME);
    return $ret;
  }

  public function get_last_sql()
  {
    return $this->db->getLastSql();
  }


  public function get_redis($cfg = '')
  {
    return $this->new_redis($cfg);
  }

  // 实例化Redis
  public function new_redis($cfg = '')
  {
    $cfg || $cfg = $this->redis_config ?: 'redis_default';
    $key = is_string($cfg) ? $cfg : md5(serialize($key));
    if(isset(self::$redis_instances[$key]) && self::$redis_instances[$key])
    {
      $rds = self::$redis_instances[$key];
    }
    else
    {
      is_string($cfg) && $cfg = C($cfg);
      $rds = new \Think\Cache\Driver\Redis($cfg);
      if($rds && isset($cfg['password'])) $rds->auth($cfg['password']);
      self::$redis_instances[$key] = $rds;
    }
    return $rds;
  }

}