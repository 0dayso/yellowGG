<?php
namespace Cms\Model;

class UserScoringGroupRangeModel extends PublicModel
{

  public function lists($map = array(),$ord = array())
  {
    if($map) $this->where($map);
    if($ord) $this->order($ord);
    $this->alias('gr')
         ->field(array('gr.*','g.name' => 'group_name','r.name' => 'range_name','r.work_start','r.work_end'))
         ->join('left join '.D('UserScoringGroup')->getTableName().' g on g.id = gr.group_id')
         ->join('left join '.D('UserScoringRange')->getTableName().' r on r.id = gr.range_id');
    $arr = $this->select();
    return $arr;
  }

  // 保存分组班次关系 多对多
  public function save_group_range($id,$arr)
  {
    $dat = array();
    if(is_string($arr)) $arr = preg_split('/\s*,\s*/',$arr);
    if($id >= 1 && is_array($arr))
    {
      $mod = $this;
      $mod->where(array('group_id' => $id))->delete();
      foreach($arr as $vid)
      {
        $vid = (int)$vid;
        if($vid >= 1) $dat[] = array('group_id' => $id,'range_id' => $vid);
      }
      if($dat)
      {
        $mod->addAll($dat);
      }
    }
    return $dat;
  }

}