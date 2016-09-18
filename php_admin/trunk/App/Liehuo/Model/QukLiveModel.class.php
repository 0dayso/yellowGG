<?php
namespace Liehuo\Model;
use \Common\Model\UtilModel;

class QukLiveModel extends PublicModel
{

  const API_USER = 'huanyu123';
  const API_PASS = '999999';
  const API_ROOT = 'http://cloud.quklive.com/cloud/services/';

  protected $errno = 0;
  protected $error = '';
  protected static $datas = [];

  protected $autoCheckFields = false;
  protected $redis_config    = 'redis_live';

  public function __construct()
  {
    self::$datas['token'] = $this->get_user_token();
  }

  public function get_user_token()
  {
    if(!$tok = self::$datas['token'])
    {
      if(!$tok = $this->get_redis()->get('php_string_livetoken'))
      {
        $dat = $this->user_login();
        if(isset($dat['value']['token']))
        {
          $tok = $dat['value']['token'];
        }
      }
      else self::$datas['token'] = $tok;
    }
    return $tok;
  }

  public function set_user_token($tok = '')
  {
    self::$datas['token'] = $tok;
    return $this->get_redis()->setEx('php_string_livetoken',60 * 60 * 24 * 30,$tok);
  }

  public function user_login($usr = self::API_USER,$pwd = self::API_PASS)
  {
    $dat = is_array($usr) ? $usr : ['userName' => $usr,'password' => $pwd];
    $ret = $this->get_api('user/login',$dat);
    isset($ret['value']['token'])  && $this->set_user_token($ret['value']['token']);
    isset($ret['value']['appKey']) && self::$datas['appKey'] = $ret['value']['appKey'];
    return $ret;
  }

  public function user_refresh_token($dat = true)
  {
    $dat === true && $dat = $this->get_user_token();
    $dat = is_array($dat) ? $dat : ['token' => $dat];
    $ret = $this->get_api('user/refreshToken',$dat);
    if(isset($ret['value']['token'])) $this->set_user_token($ret['value']['token']);
    else $ret = $this->user_login();
    rlog([date('Y-m-d H:i:s'),'new' => $ret,'old' => $dat],'quklive_refresh_token');
    return $ret;
  }

  // 删除
  public function impromptu_delete($id = 0)
  {
    $dat =
    [
      'id'    => $id,
      'token' => $this->get_user_token(),
    ];
    $ret = $this->get_api('impromptu/delete',$dat);
    return $ret;
  }

  // 禁播
  public function impromptu_ban($id = 0)
  {
    $dat =
    [
      'id'    => $id,
      'token' => $this->get_user_token(),
    ];
    $ret = $this->get_api('impromptu/ban',$dat);
    return $ret;
  }


  public function get_api($api = '',$dat = [])
  {
    $dat = array_merge([],$dat ?: []);
    is_string($dat) || $dat = json_encode($dat);
    $jss = UtilModel::http(self::API_ROOT.$api,$dat,'POST',
    [
      'Content-Type' => 'application/json;charset=UTF-8',
    ]);
    $ret = json_decode($jss,true);
    isset($ret['code']) && $this->errno = $ret['code'];
    isset($ret['msg'])  && $this->error = $ret['msg'];
    //die(json_encode(compact('api','dat','jss','ret')));
    return $ret;
  }

}