<?php
namespace Liehuo\Model;

class MassSendModel extends CjAdminLogModel
{

  public function log($dat = [])
  {
    $dat = array_merge(
    [
      'text'     => '',
      'texttype' => 1,
      'msgtype'  => 7,
      'send_num' => 0,
      'time'     => date('Y-m-d H:i:s'),
    ],$dat ?: []);
    $id = $this->add($dat) && $dat['id'] = $id;
    return $id ? $dat : $id;
  }

}