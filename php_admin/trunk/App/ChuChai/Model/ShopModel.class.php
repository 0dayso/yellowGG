<?php
namespace ChuChai\Model;

class ShopModel extends PublicModel
{

  // 自动完成
  protected $_auto =
  [
    ['name','trim',self::MODEL_BOTH,'function'],
    ['create_time','time',self::MODEL_INSERT,'function'],
  ];

  public function __construct()
  {
    parent::__construct();
  }

  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $alias_sub = $alias ?: $this->getTableName().'.';
    $map = [];
    if($sid = (int)$arr['sid']) $map[$alias.'sid'] = $sid;
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$alias.'create_time']) || $map[$alias.'create_time'] = [];
      $map[$alias.'create_time'][] = ['egt',strtotime(date('Y-m-d 00:00:00',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$alias.'create_time']) || $map[$alias.'create_time'] = [];
      $map[$alias.'create_time'][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($mid = (int)$arr['model_id']) $map[$alias.'model_id'] = $mid;
    $map[$alias.'delete_time'] = isset($arr['deleted']) && $arr['deleted'] ? ['egt',1] : 0;
    if(is_array($arr['attrs']) && $arr['attrs'])
    {
      $wls = [];
      $whe =
      [
        'sid' => ['exp','= '.$alias_sub.'sid'],
      ];
      $fls = D('Field')->get_fields((int)$arr['model_id']) ?: [];
      foreach($arr['attrs'] as $k => $v)
      {
        if(isset($fls[$k])) $wls[] = '(`field_id` = '.$k.' and `value` = \''.$v.'\')';
      }
      if($wls)
      {
        $whe['_string'] = implode(' or ',$wls);
        $sql = D('ShopField')->field('sid,count(id) as cnt')->where($whe)->group('sid')->having('cnt >= '.count($wls))->buildSql();
        $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
      }
    }
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $map['_complex'] =
      [
        '_logic' => 'or',
      ];
      $field = trim($arr['search_field']);
      if(!$field || $field == 'name')        $map['_complex'][$alias.'name']        = ['like','%'.$kwd.'%'];
      if(!$field || $field == 'description') $map['_complex'][$alias.'description'] = ['like','%'.$kwd.'%'];
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'sid') $map['_complex'][$alias.'sid'] = ['like','%'.$kwd.'%'];
      }
    }
    return $map;
  }

}