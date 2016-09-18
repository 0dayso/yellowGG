<?php
namespace Yanzhi\Model;

class FeedScoreBox9Model extends PublicModel
{

  public static $score_box9_list = array();

  public function get_score_box9($score = 0)
  {
    $this->score_box9_list || $this->score_box9_list = $this->where(array('status' => 1))->order('id')->klist() ?: array();
    $lst = array();
    foreach($this->score_box9_list as $v)
    {
      $id = (int)$v['id'];
      $sco = $id / 10;
      $v['score'] = $sco;
      if($sco > $score && $sco < $score + 1) $lst[$id] = $v;
    }
    return $lst;
  }

  public function set_score_box9($sco = 0,$res = '')
  {
    is_array($res) && $res = $res['resource'] ?: '';
    if(!$res) return false;
    $id = (int)(round((float)$sco,1) * 10);
    if($ret = $this->where(array('id' => $id,'status' => 1))->setField('resource',$res))
    {
      isset($this->score_box9_list[$id]['resource']) && $this->score_box9_list[$id]['resource'] = $res;
    }
    return $ret;
  }

}