<?php
namespace Promo\Controller;
use Liehuo\Model;

class IndexController extends PublicController
{

  public function __construct()
  {
    parent::__construct();
  }

  public function index()
  {
    $phone = trim(I('request.phone'));
    if($phone)
    {
      if(!preg_match('/^\d{5,11}$/',$phone)) $this->error('手机号格式错误且至少输入6位');
      $mod = D('Liehuo/UserBase');
      $dat['user'] = $mod->field('password',true)->where(
      [
        'reg_time' => ['egt',strtotime('-2 days')],
        'phone'    => ['like','%'.$phone.'%'],
      ])->limit(10)->klist('uid');
      $dat['user'] = array_map(function($v) use($mod)
      {
        $v['albums'] = json_decode($v['album'],true) ?: [];
        $v['active_time'] = $mod->get_active_time($v['uid']);
        return $v;
      },$dat['user'] ?: []);
      if($ids = array_keys($dat['user'] ?: []))
      {
        //var_dump(D('Liehuo/Stat')->set_table('__DAILY_ANALYSIS_DATA__'));die;
        $dat['stat'] = D('Liehuo/Stat')->set_table('__DAILY_ANALYSIS_DATA__')//__DT_ANALYSIS_DATA__
        ->field(
        [
          'uid',
          'sum(day_nope_num)'       => 'nope_num',
          'sum(day_free_thumb_num)' => 'free_thumb_num',
          'sum(day_pay_thumb_num)'  => 'pay_thumb_num',
          'sum(day_free_like_num)'  => 'free_like_num',
          'sum(day_pay_like_num)'   => 'pay_like_num',
          'sum(day_free_match_num)' => 'free_match_num',
          'sum(day_pay_match_num)'  => 'pay_match_num',
        ])
        ->where(['uid' => ['in',$ids]])
        ->group('uid')
        ->klist('uid');
        $dat['location'] = D('Liehuo/LocationBase')->where(['uid' => ['in',$ids]])->klist('uid');
      }
      $this->data = $dat;
      //die(json_encode(compact('dat')));
    }
    $this->display();
  }

}