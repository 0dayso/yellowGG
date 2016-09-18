<?php
namespace Liehuo\Model;

class AccountBaseModel extends CjDatadwModel
{

  protected $redis_config = 'redis_user';

  public function __construct()
  {
    parent::__construct();
    $this->redis_expire = 60 * 60 * 24 * 7;
  }

  /*
   * 获取账户数据 包括余额
   * */
  public function get_data($uid = 0)
  {
    $dat = $this->get_cache($uid);
    if(!$dat)
    {
      $dat = $this->find($uid);
      if($dat) $this->set_cache($uid,$dat);
    }
    return $dat;
  }

  public function get_users_account($uls = [],$fields = false)
  {
    $ret = [];
    if($ids = array_unique(array_column($uls ?: [],'uid')))
    {
      if($fields) $this->field($fields);
      $ret = $this->where(['uid' => ['in',$ids]])->klist('uid') ?: [];
    }
    return $this->vip_users($ret);
  }

  public function vip_users($arr = [])
  {
    return array_map(function($v)
    {
      $v['vip_level'] = $this->check_vip($v['vip_level'],$v['vip_valid_end']);
      return $v;
    },$arr ?: []);
  }

  // 检查会员是否到期
  public function check_vip($vip,$end = 0)
  {
    $vip = (int)$vip;
    if($end < time()) $vip = 0;
    return $vip;
  }

  // 设置会员
  public function set_vip_days($uid = 0,$days = 0)
  {
    return D('RpcApi')->call('Account/setVipDays',
    [
      'uid'  => (int)$uid,
      'days' => (int)$days,
    ]);
  }

  // 设置会员 v1.7
  public function set_vip_v7($uid = 0,$days = 0,$vip = 1)
  {
    $ret = false;
    $now = time();
    $tim = 60 * 60 * 24 * (int)$days;
    if($old = $this->where(['uid' => $uid])->find())
    {
      $dat = [];
      if($this->check_vip($old['vip_level'],$old['vip_valid_end']))
      {
        $dat = [
          'vip_valid_end' => $old['vip_valid_end'] + $tim,
        ];
      }
      else
      {
        $dat = [
          'vip_level'       => $vip,
          'vip_valid_begin' => $now,
          'vip_valid_end'   => $now + $tim,
        ];
      }
      $ret = $this->account_update($uid,$dat);
      if($ret)
      {
        $inf = D('Goods')->goods_vips ?: [];
        if(isset($dat['vip_level']) && isset($inf[$dat['vip_level']]))
        {
          $inf = $inf[$dat['vip_level']];
          D('UserZan')->set_zan_surp($uid,$inf['like_times']);
        }
        $ret = array_merge($old,$dat);
      }
    }
    return $ret;
  }

  // 增加钻石
  public function set_diamond_inc($uid = 0,$num = 0)
  {
    return D('RpcApi')->call('Account/setDiamondInc',
    [
      'uid'     => (int)$uid,
      'diamond' => (int)$num,
    ]);
  }

  /*
   * 增加用户魅力值 RPC
   * $fee    float 负数为支出
   * $record array 魅力明细
   * @return float 余额或false
   * */
  public function set_glamour_inc($uid = 0,$num = 0,$record = [])
  {
    return D('RpcApi')->call('Account/setGlamourInc',
    [
      'uid'     => (int)$uid,
      'glamour' => (int)$num,
      'record'  => $record,
    ]);
  }

  /*
   * 增加用户账户余额
   * $fee    float 负数为支出
   * $record array 账务明细
   * @return float 余额或false
   * */
  public function set_balance_inc($uid = 0,$fee = 0,$record = [])
  {
    $ret = false;
    $fee = (float)$fee;
    $acc = $this->get_data($uid);
    $balance = (float)$acc['balance'];
    if(!$acc)
    {
      $this->error = '获取账户信息失败';
    }
    else
    {
      if($this->where(['uid' => $uid])->limit(1)->setField('balance',['exp','balance + '.$fee]) === false)
      {
        $this->error = '更新余额失败';
      }
      else
      {
        $ret = $balance + $fee;
        $acc['balance'] = $ret;
        $this->set_cache_bykey($uid,'balance',$ret);
        if(is_array($record)) D('FeeRecord')->add_record(array_merge([
          'uid'     => $uid,
          'fee'     => $fee,
          'balance' => $ret,
        ],$record));
      }
    }
    return $ret;
  }

  /*
   * 减少用户账户余额
   * $fee    float 负数为收入
   * $record array 账务明细
   * @return float 余额或false
   * */
  public function set_balance_dec($uid = 0,$fee = 0,$record = [])
  {
    $ret = false;
    $fee = (float)$fee;
    $acc = $this->get_data($uid);
    $balance = (float)$acc['balance'];
    if(!$acc)
    {
      $this->error = '获取账户信息失败';
    }
    elseif($fee > $balance)
    {
      $this->error = '账户余额不足';
    }
    else
    {
      if($this->where(['uid' => $uid])->limit(1)->setField('balance',['exp','balance - '.$fee]) === false)
      {
        $this->error = '更新余额失败';
      }
      else
      {
        $ret = $balance - $fee;
        $acc['balance'] = $ret;
        $this->set_cache_bykey($uid,'balance',$ret);
        if(is_array($record)) D('FeeRecord')->add_record(array_merge([
          'uid'     => $uid,
          'fee'     => 0 - $fee,
          'balance' => $ret,
        ],$record));
      }
    }
    return $ret;
  }

  /*
   * 设置账户字段 同时更新缓存
   * */
  public function set_field($uid = 0,$key,$val)
  {
    $ret = $this->where(['uid' => $uid])->limit(1)->save([$key => $val]);
    if($ret) $this->set_cache_bykey($uid,$key,$val);
    return $ret;
  }

  /*
   * 增加字段值
   * */
  public function set_inc($uid = 0,$field,$step = 1)
  {
    $ret = $this->where(['uid' => $uid])->limit(1)->setField($field,['exp',$field.' + '.$step]);
    if($ret)
    {
      $acc = $this->get_data($uid);
      $val = $acc[$field] + $step;
      $this->set_cache_bykey($uid,$field,$val);
    }
    return $ret;
  }

  /*
   * 减少字段值
   * */
  public function set_dec($uid = 0,$field,$step = 1)
  {
    $ret = $this->where(['uid' => $uid])->limit(1)->setField($field,['exp',$field.' - '.$step]);
    if($ret)
    {
      $acc = $this->get_data($uid);
      $val = $acc[$field] - $step;
      $this->set_cache_bykey($uid,$field,$val);
    }
    return $ret;
  }

  // 用户账户更新
  public function account_update($uid = 0,$dat = [])
  {
    if(!$uid) return false;
    $dat = array_merge([],$dat ?: []);
    $ret = $this->where(['uid' => $uid])->limit(1)->save($dat);
    if($ret) $this->del_cache($uid);
    return $ret;
  }


  public function get_cache($uid = 0)
  {
    return $this->get_redis()->hGetAll('php_account_'.$uid);
  }

  public function set_cache($uid = 0,$data = [],$ex = true)
  {
    $ret = $this->get_redis()->hMset('php_account_'.$uid,$data);
    if($ret && $ex)
    {
      $ex = $ex === true ? $this->redis_expire : $ex;
      $this->get_redis()->expire('php_account_'.$uid,(int)$ex);
    }
    return $ret;
  }

  public function set_cache_bykey($uid = 0,$key = '',$val = '')
  {
    return $this->get_redis()->hSet('php_account_'.$uid,$key,$val);
  }

  public function del_cache($uid = 0)
  {
    return $this->get_redis()->del('php_account_'.$uid);
  }


  /*
  * 获取日常操作次数
  * 如：提现次数、支付密码错误次数...
  * */
  public function get_daily_bykey($uid = 0,$key = '')
  {
    $ret = false;
    $rky = 'php_user_daily_'.$uid;
    $arr = $this->get_redis()->hGetAll($rky) ?: [];
    if(isset($arr[$key]) && date('Y-m-d') == date('Y-m-d',$arr[$key.'_last_time']))
    {
      $ret = $arr[$key];
    }
    else $this->get_redis()->hDel($rky,$key);
    return $ret;
  }

  public function set_daily_inc($uid = 0,$key = '',$step = 1)
  {
    $rky = 'php_user_daily_'.$uid;
    $val = (float)$this->get_daily_bykey($key);
    $val += $step;
    $ret = $this->get_redis()->hMset($rky,
    [
      $key              => $val,
      $key.'_last_time' => time(),
    ]);
    if($ret) $this->get_redis()->expire($rky,60 * 60 * 24 * 7);
    //rlog(compact('key','uid','val'),'user_daily_times');
    return $ret;
  }

}