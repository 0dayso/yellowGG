<?php
namespace Liehuo\Model;

class UserInfoModifyRequestModel extends CjAdminModel
{

  protected $redis_config = 'redis_user';

  public $user_fields = array(
    'nickname'    => '昵称',
    'description' => '个人简介',
    'interest'    => '兴趣',
    'job_haunt_character' => '职业/出没地/性格',
    'msg'         => '聊天',
    'msg_warn'    => '聊天(5位数字)',
  );

  public $operation_status = array(
    0 => '未审查',
    1 => '已审查',
    2 => '已处理',
  );


  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = array())
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: array();
    isset($arr['has_illegal']) || $arr['has_illegal'] = 1;
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = array();
    if($uid = (int)$arr['uid'])  $map[$alias.'uid'] = $uid;
    if($arr['stime'] && $stime = strtotime($_REQUEST['stime'] = $_GET['stime'] = urldecode(urldecode($arr['stime']))))
    {
      is_array($map[$alias.'sub_time']) || $map[$alias.'sub_time'] = [];
      $map[$alias.'sub_time'][] = array('egt',date('Y-m-d H:i:s',$stime));
    }
    if($arr['etime'] && $etime = strtotime($_REQUEST['etime'] = $_GET['etime'] = urldecode(urldecode($arr['etime']))))
    {
      is_array($map[$alias.'sub_time']) || $map[$alias.'sub_time'] = [];
      $map[$alias.'sub_time'][] = array('elt',date('Y-m-d H:i:59',$etime));
    }
    if($arr['field_name'] == 'user_info') $map[$alias.'field_name'] = ['in',['nickname','description','interest','job_haunt_character']];
    elseif($arr['field_name']  != '') $map[$alias.'field_name']  = $arr['field_name'];
    if($arr['operation']   != '') $map[$alias.'operation']   = (int)$arr['operation'];
    if($arr['has_illegal'] != '') $map[$alias.'has_illegal'] = (int)$arr['has_illegal'];
    if($arr['sex'] != '')//性别筛选
    {
      $sex = (int)$arr['sex'] == 0 ? 0 : 1;
      $map[$alias.'sex'] = $sex;
    }
    if($arr['user_type'] != '')
    {
      $typ = (int)$arr['user_type'];
      if($typ == -1)
      {
        $sql = D('AccountBase')->table('chujiandw.__ACCOUNT_BASE__')->field('uid')
          ->where(['uid' => ['exp',' = '.$alias.'uid'],'total_expense' => ['gt',0]])
          ->buildSql();
        $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
      }
      else
      {
        $sql = D('UserBase')->table('chujiandw.__USER_BASE__')->field('uid')
          ->where(['uid' => ['exp',' = '.$alias.'uid'],'type' => $typ])
          ->buildSql();
        $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
      }
    }
    if($kwd = trim($arr['kwd']))
    {
      $map['_complex'] = array(
        '_logic'   => 'or',
        //$alias.'field_value' => [['like','%'.$kwd.'%']],
      );
      foreach(preg_split('/[\s|;,]+/',$kwd) ?: [] as $v)
      {
        $map['_complex'][$alias.'field_value'][] = ['like','%'.$v.'%'];
      }
      $map['_complex'][$alias.'field_value'][] = 'or';
      if(count($map['_complex']) == 1) unset($map['_complex']);
      else unset($map[$alias.'has_illegal']);
    }
    return $map;
  }

  // 格式化 emoji 替换敏感词
  public function format_fields_all($fields = [],$arr = [])
  {
    import('Think.Emoji');
    if(is_string($fields)) $fields = [$fields];
    $wds = [];
    foreach(D('SensitiveWords')->get_multi_items('','word') ?: [] as $v)
    {
      $wds[$v['word']] = '<a class="high-light">'.$v['word'].'</a>';
    }
    $arr = array_map(function($v) use($wds,$fields)
    {
      foreach($fields as $f)
      {
        if(isset($v[$f]))
        {
          $v[$f] = htmlspecialchars($v[$f]);//xss
          $v[$f.'_html'] = emoji_unified_to_html(str_ireplace(array_keys($wds),array_values($wds),$v[$f]));
        }
      }
      return $v;
    },$arr ?: []);
    //die(json_encode(['data' => $arr,$wds]));
    return $arr;
  }

  // 处理
  public function handle($uid = 0,$key = '',$dat = [])
  {
    is_string($dat) && $dat = ['result' => $dat];
    return $this->where(
    [
      'uid'        => $uid,
      'field_name' => $key,
      'operation'  => 0,
    ])->save(array_merge(
    [
      'operation' => 2,//已审核并处理
      'aid'       => (int)$_SESSION[C('USER_AUTH_KEY')],
      'pass_time' => date('Y-m-d H:i:s'),
    ],$dat ?: []));
  }

  // 添加多条
  public function add_all($als = [])
  {
    $ret = false;
    $uls = [];
    if($ids = array_column($als ?: [],'uid'))
    {
      $uls = UserBaseModel::Instance()->get_by_ids($ids);
    }
    $lst = [];
    $now = time();
    foreach($als ?: [] as $v)
    {
      $uid = (int)$v['uid'];
      $val = trim($v['field_value']);
      $usr = $uls[$uid] ?: [];
      if(!$uid) continue;
      if(!$usr) continue;
      if(!$val) continue;
      if($usr['type'] == '2' && $usr['dblocking_time'] > $now) continue;
      $lst[$uid.'-'.$v['field_name'].'-'.$val] = array_merge(
      [
        'uid'         => 0,
        'sex'         => 0,
        'field_name'  => 'msg',
        'field_value' => '',
        'has_illegal' => 0,
        'sub_time'    => date('Y-m-d H:i:s'),
      ],$v ?: []);
    }
    if($lst) $this->addAll(array_values($lst));
    //header('debug-all: '.json_encode(compact('lst','ids')));
    return $ret;
  }

}