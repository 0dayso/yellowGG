<?php
namespace Common\Model;

class AuthModel extends CommonModel
{

  protected $autoCheckFields = false;//虚拟模型

  public $auth_id     = 0;//用户ID
  public $auth_rules  = array();//所有权限
  public $auth_access = array();//用户权限

  public $model_auth_rule   = 'AdminAuthRule';
  public $model_auth_access = 'AdminAuthAccess';

  public $session_auth_rules  = 'Auth-rules-list';
  public $session_auth_access = 'Auth-access-list';

  public function _initialize()
  {
    $this->auth();
    $this->auth_rules = $this->get_auth_rules();
  }

  // 检查用户权限
  // D('Auth')->reset()->auth(1/*uid*/)->check('login');
  public function check($name,$id = true)
  {
    $name = strtolower($name);
    $id === true && $id = $this->auth_id;
    $id == $this->auth_id || $this->auth($id);
    $ret = false;
    if($name == '')                                    $ret = true;
    elseif(!array_key_exists($name,$this->auth_rules)) $ret = true;
    elseif(array_key_exists($name,$this->auth_access)) $ret = true;
    //die(json_encode(['id' => $id,'name' => $name,$this->auth_access,$this->auth_rules]));
    return $ret;
  }

  // 切换用户
  public function auth($id = true)
  {
    $id === true && $id = $_SESSION[C('USER_AUTH_KEY') ?: 'Auth-id'];
    $this->auth_id     = (int)$id;
    $this->auth_access = $this->get_auth_access($this->auth_id);
    return $this;
  }

  // 清除用户权限
  public function clear($id = true)
  {
    $key = $this->session_auth_access;
    if($id === true)
    {
      unset($_SESSION[$key]);
      unset($_SESSION[$this->session_auth_rules]);
    }
    else unset($_SESSION[$key][$id]);
    return $this;
  }

  // 重置用户权限
  public function reset($id = true)
  {
    $this->clear($id);
    $this->_initialize();
    return $this;
  }

  // 获取用户权限
  public function get_auth_access($id = 0)
  {
    $key = $this->session_auth_access;
    if(isset($_SESSION[$key][$id]) && is_array($_SESSION[$key][$id])) $arr = $_SESSION[$key][$id];
    else
    {
      $arr = $this->get_auth_access_list($id);
      $arr = array_change_key_case($arr,CASE_LOWER);
      $_SESSION[$key][$id] = $arr;
    }
    return $arr;
  }

  public function get_auth_access_list($id = 0)
  {
    $arr = D($this->model_auth_access)->alias('a')
      ->field(array('a.auth_id','r.id','r.name','r.title','r.condition'))
      ->join('left join '.D($this->model_auth_rule)->getTableName().' r on r.id = a.rule_id')
      ->where(array('a.auth_id' => $id,'r.status' => 1))->select();
    if($arr && $keys = array_column($arr,'name')) $arr = array_combine($keys,$arr);
    return $arr;
  }

  // 获取所有权限
  public function get_auth_rules()
  {
    $key = $this->session_auth_rules;
    if(isset($_SESSION[$key]) && is_array($_SESSION[$key])) $arr = $_SESSION[$key];
    else
    {
      $arr = $this->get_auth_rules_list();
      $arr = array_change_key_case($arr,CASE_LOWER);
      $_SESSION[$key] = $arr;
    }
    return $arr;
  }

  public function get_auth_rules_list()
  {
    $arr = D($this->model_auth_rule)
      ->field(array('id','name','title','condition'))
      ->where(array('status' => 1))->select();
    if($arr && $keys = array_column($arr,'name')) $arr = array_combine($keys,$arr);
    return $arr;
  }

}