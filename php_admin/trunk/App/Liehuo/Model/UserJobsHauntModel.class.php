<?php
namespace Liehuo\Model;

class UserJobsHauntModel extends CjDatadwModel
{

  public $types =
  [
    1 => '职业',
    2 => '常出没地',
    3 => '性格',
  ];

  public function get_job_haunt_by_user($str = '')
  {
    $dat = [];
    if($str && preg_match('/\bj:(?<jid>\d*),\bh:(?<hid>\d*)/i',$str,$arr))
    {
      $dat['job']      = $this->where(['id' => $arr['jid']])->getField('name');
      $dat['haunt']    = $this->where(['id' => $arr['hid']])->getField('name');
      $dat['job_id']   = $arr['jid'];
      $dat['haunt_id'] = $arr['hid'];
    }
    return $dat;
  }

  public function get_character_by_ids($ids = [])
  {
    $dat = [];
    if($ids)
    {
      $arr = $this->where(['id' => ['in',$ids]])->select();
      foreach($arr ?: [] as $v)
      {
        $dat[$v['id']] = $v;
      }
    }
    return $dat;
  }

}