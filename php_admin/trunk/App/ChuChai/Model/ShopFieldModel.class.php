<?php
namespace ChuChai\Model;

class ShopFieldModel extends PublicModel
{

  public function __construct()
  {
    parent::__construct();
  }

  public function get_fields($sid = 0)
  {
    $arr = [];
    $lst = $this->where(['sid' => $sid])->select() ?: [];
    foreach($lst as $v)
    {
      $arr[$v['field_id']]['value'][] = $v['value'];
      if($adt = $this->attr2array($v['attrs'])) $arr[$v['field_id']]['attrs'] = $adt;
    }
    return $arr;
  }

  public function set_fields($sid = 0,$arr = [])
  {
    $ret = false;
    $lst = [];
    foreach($arr ?: [] as $k => $a)
    {
      if(isset($a['attrs']))
      {
        $lst[] =
        [
          'sid'      => $sid,
          'field_id' => $k,
          'value'    => '',
          'attrs'    => $this->auto_attrs($a['attrs']),
        ];
        continue;
      }
      foreach((array)$a as $v)
      {
        if($v === '') continue;
        $lst[] =
        [
          'sid'      => $sid,
          'field_id' => $k,
          'value'    => $v,
          'attrs'    => '',
        ];
      }
    }
    //die(json_encode(compact('lst')));
    $num = $this->where(['sid' => $sid])->delete();
    if($lst) $ret = $this->addAll(array_values($lst));
    $sql = $this->getLastSql();
    //rlog(compact('num','lst','sql'),'shop_fields_save',86400);
    //die($sql);
    return $ret;
  }

}