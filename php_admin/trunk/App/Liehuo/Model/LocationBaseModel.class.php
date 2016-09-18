<?php
namespace Liehuo\Model;

class LocationBaseModel extends CjDatadwModel
{

  public $provinces = ['上海','北京','天津','重庆','安徽','福建','甘肃','广东','广西','贵州','海南','河北','河南','黑龙江','湖北','湖南','吉林','江苏','江西','辽宁','内蒙古','宁夏','青海','山东','山西','陕西','四川','西藏','新疆','云南','浙江','香港','澳门','台湾'];

  // 获取搜索筛选条件
  //   $alias 表别名，为true时自动获取
  public function get_filters($alias = '',$arr = [])
  {
    is_array($arr) && $arr || $arr = $_REQUEST ?: [];
    $alias === true && $alias = $this->options['alias'] ?: $this->getTableName();
    $alias = $alias ? ($alias.'.') : '';
    $map = [];
    $time_type = 'update_time';
    if($arr['stime'] && $stime = strtotime($arr['stime']))
    {
      is_array($map[$time_type]) || $map[$time_type] = [];
      $map[$time_type][] = ['egt',strtotime(date('Y-m-d',$stime))];
    }
    if($arr['etime'] && $etime = strtotime($arr['etime']))
    {
      is_array($map[$time_type]) || $map[$time_type] = [];
      $map[$time_type][] = ['elt',strtotime(date('Y-m-d 23:59:59',$etime))];
    }
    if($arr['app_type']    != '') $map[$alias.'app_type']    = $arr['app_type'] ? 1 : 0;
    if($arr['app_version'] != '') $map[$alias.'app_version'] = $arr['app_version'];
    if($arr['sex'] != '')
    {
      $sex = (int)$arr['sex'] == 0 ? 0 : 1;
      $map[$alias.'sex'] = $sex;
    }
    if($arr['score_rank'] != '')
    {
      $whe =
      [
        'uid' => ['exp','= '.$alias.'uid'],
      ];
      $arr['score_rank'] == 'fail' && $whe['score'] = [/*['egt',0],*/['elt',5.99]];
      $arr['score_rank'] == 'pass' && $whe['score'] = [['egt',6],['elt',10]];
      if($whe['score'])
      {
        $sql = D('UserBase')->field('uid')->where($whe)->buildSql();
        $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
      }
    }
    if($arr['has_all_info'] != '')
    {
      $whe =
      [
        'uid'     => ['exp','= '.$alias.'uid'],
        '_string' => $arr['has_all_info']
          ? "(description != '' and home != '0' and job_haunt != '0' and interest != '0' and `character` != '0')"
          : "(description = '' and home = '0' and job_haunt = '0' and interest = '0' and `character` = '0')",
      ];
      $sql = D('UserBase')->field('uid')->where($whe)->buildSql();
      $map['_string'] .= ($map['_string'] ? ' and ' : '').'exists '.$sql;
    }
    if($kwd = trim(urldecode($arr['kwd'])))
    {
      $_REQUEST['kwd'] = $_GET['kwd'] = $kwd;
      $field = trim($arr['search_field']);
      $map['_complex'] =
      [
        '_logic' => 'or',
      ];
      if(!$field || $field == 'province') $map['_complex'][$alias.'province'] = ['like','%'.$kwd.'%'];
      if(!$field || $field == 'city')     $map['_complex'][$alias.'city']     = ['like','%'.$kwd.'%'];
      if(!$field || $field == 'area')     $map['_complex'][$alias.'area']     = ['like','%'.$kwd.'%'];
      if(preg_match('/^\d+$/i',$kwd))
      {
        if(!$field || $field == 'uid')   $map['_complex'][$alias.'uid']   = $kwd;
      }
      if(count($map['_complex']) == 1) unset($map['_complex']);
    }
    return $map;
  }

  public function get_by_list($arr = [],$fields = false,$field_pk = 'uid')
  {
    $dat = [];
    if($ids = array_unique(array_column($arr ?: [],$field_pk)) ?: [])
    {
      if($fields) $this->field($fields);
      $dat = $this->klist('uid',['uid' => ['in',$ids]]) ?: [];
    }
    return $dat;
  }

  public function location_update($uid = 0,$loc = array())
  {
    if(!$uid) return false;
    $loc = array_merge(array(),$loc);
    return $this->where(array('uid' => $uid))->save($loc);
  }

}