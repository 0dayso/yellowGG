<?php
namespace Liehuo\Model;

class UserCityModel extends CjDatadwModel
{

  public function get_by_user($str = '')
  {
    $dat = [];
    if($str && preg_match('/\bp:(?<pid>\d*),\bc:(?<cid>\d*)/i',$str,$arr))
    {
      $dat['province'] = $this->table('__USER_PROVINCE__')->where(['id' => $arr['pid']])->getField('province');
      $dat['city']     = $this->where(['id' => $arr['cid']])->getField('city');
    }
    return $dat;
  }

}