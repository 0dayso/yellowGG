<?php
namespace Yanzhi\Controller;

class UserBaseController extends PublicController
{

  public $ret = array('ret' => 0,'msg' => '','data' => array());

  public function index()
  {
    $mod = D(CONTROLLER_NAME)->alias('u');
    $dat = array();
    $map = $mod->get_filters(true/*表别名*/);//筛选搜索
    // 获取列表
    $dat['list'] = $mod->plist(100,$map)//C('ITEMS_PER_PAGE')
      ->field(array('u.*','l.update_time,l.latest_feed_time'))
      ->join('left join '.D('LocationBase')->getTableName().' l on l.uid = u.uid')
      ->lists('','u.reg_time desc,u.uid desc');
    $dat['list'] = $mod->format_nickname_all($dat['list']);
    $dat['user_types'] = $mod->user_types ?: array();
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->pager = $mod->pager;
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  // 用户详情
  public function view()
  {
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    $mod = D(CONTROLLER_NAME);
    $dat = array();
    if(!$dat['item'] = $mod->find($uid))
    {
      $this->error('对象不存在!');
    }
    else
    {
      $dat['item'] = $mod->attr2array_row($dat['item']);
      $dat['user_types'] = $mod->user_types ?: array();
      $dat['user_location'] = D('LocationBase')->alias('l')
        ->field(array('l.*','c.province','c.city'))
        ->join('left join cj_city_base c on c.id = l.city_id')
        ->where(array('l.uid' => $uid))->find();
      // 用户动态
      $fed = D('FeedBase');
      $dat['feeds'] = $fed->plist(100,array('uid' => $uid))->lists('','create_time desc');
      $dat['feeds_pager'] = $fed->pager;
      // 封禁历史
      $acc = D('AccusationBaseLog');
      $dat['accusation_logs']    = $acc->lists(array('oid' => $uid),'create_time desc');
      $dat['accusation_states']  = $acc->accusation_states;
      $dat['accusation_reasons'] = $acc->accusation_reasons;
      $dat['accusation_admins']  = $acc->get_accusation_admins($dat['accusation_logs'],'aid,nickname');
      // 被举报记录
      $rep = D('ReportBase');
      $dat['reports']        = $rep->lists(array('offender_uid' => $uid),'dtime desc');
      $dat['report_types']   = $rep->report_types;
      $dat['report_status']  = $rep->report_status;
      $dat['report_reasons'] = $rep->report_reasons;
    }
    $this->data = $dat;
    $this->display();
  }

  // 获取所有聊天记录 html
  public function chat_logs()
  {
    $_REQUEST['day'] || $_REQUEST['day'] = 1;
    $_REQUEST['stime'] || $_REQUEST['stime'] = date('Y-m-d',strtotime('-'.((int)$_REQUEST['day'] - 1).' days'));
    $dat = $this->get_chat_log_data();
    $this->display();
  }

  // 获取所有聊天记录 json
  public function get_chat_log_all()
  {
    $dat = $this->get_chat_log_data();
    $this->ajaxReturn($this->ret);
  }

  // 获取用户聊天记录 json
  public function get_chat_log()
  {
    $dat = $this->get_chat_log_data();
    $this->ajaxReturn($this->ret);
  }

  // 获取聊天记录 array
  protected function get_chat_log_data($where = array())
  {
    $sday = (int)$_REQUEST['day'];
    $eday = 0;
    $map = array();
    if($uid = (int)$_REQUEST['uid'])
    {
      $map = array(
        '_complex' => array(
          '_logic' => 'or',
          'sender' => $uid,
          'recver' => $uid,
        ),
      );
    }
    if($sender = (int)$_REQUEST['sender']) $map['sender'] = $sender;
    if($recver = (int)$_REQUEST['recver']) $map['recver'] = $recver;
    if($texttype = (int)$_REQUEST['texttype']) $map['texttype'] = $texttype;
    if($_REQUEST['stime'] && $stime = strtotime($_REQUEST['stime']))
    {
      $sday = (int)((time() - $stime) / 24 / 60 / 60);
      $map['time'][] = array('egt',date('Y-m-d 00:00:00',$stime));
    }
    if($_REQUEST['etime'] && $etime = strtotime($_REQUEST['etime']))
    {
      $eday = (int)((time() - $etime) / 24 / 60 / 60);
      $map['time'][] = array('elt',date('Y-m-d 23:59:59',$etime));
    }
    if($kwd = trim($_REQUEST['kwd'])) $map['text'] = array('like','%'.$kwd.'%');
    $map = array_merge($map,$where);
    $dat = array();
    $mod = D('ChatLogBase');
    $dat['list'] = $mod->get_chat_log_union($map,$sday,$eday,true)->plist(100)->select() ?: array();
    $this->pager = $mod->pager;
    //is_array($dat['list']) && $dat['list'] = array_reverse($dat['list']);
    is_array($dat['list']) && $dat['list'] = $mod->format_text_all($dat['list']);
    $this->data = $dat;
    $this->ret['data'] = $dat;
    return $dat;
  }

  // 单个字段修改
  public function set_field()
  {
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'] ?: (int)$_REQUEST['pk'];
    $key = I('request.name');
    $val = I('request.value');
    $map = array('uid' => $id);
    $mod = D(CONTROLLER_NAME);
    if($id < 1)
    {
      $this->error('ID错误');
    }
    elseif(!$key)
    {
      $this->error('参数错误');
    }
    elseif(!$old = $mod->find($id))
    {
      $this->error('对象不存在');
    }

    // 设置昵称
    elseif($key == 'nickname')
    {
      if(!$val)
      {
        $this->error('昵称不能为空');
      }
      elseif(false === $mod->where($map)->setField($key,$val))
      {
        $this->error('保存失败');
      }
      else
      {
        // im...
      }
    }

    // 设置手机号
    elseif($key == 'phone')
    {
      if(!($val && preg_match('/^1[34578]\d{9}$/i',$val)))
      {
        $this->error('手机号格式错误');
      }
      elseif($val == $old['phone'])
      {
        // 未改动
      }
      elseif($val != $old['phone'] && $mod->where(array('phone' => $val))->count('uid') > 0)
      {
        $this->error('手机号已存在');
      }
      elseif(false === $mod->where($map)->setField($key,$val))
      {
        $this->error('保存失败');
      }
      else
      {
        // im...
      }
    }

    // 设置性别
    elseif($key == 'sex')
    {
      $val = (int)$val;
      if(false === $mod->auto_field($key,$val))
      {
        $this->error($mod->getError() ?: '数据错误');
      }
      elseif(false === $mod->where($map)->setField($key,$val))
      {
        $this->error('保存失败');
      }
      else
      {
        D('LocationBase')->where($map)->setField($key,$val);
      }
    }

    // 设置密码
    elseif($key == 'password')
    {
      if(!$val)
      {
        $this->error('密码不能为空');
      }
      elseif(false === $mod->where($map)->setField($key,$mod->password_encrypt($val)))
      {
        $this->error('保存失败');
      }
      else
      {
        $mod->del_user_token($id);
      }
    }
    // 删除用户缓存
    $mod->del_user_cache($id);
    $this->success('保存成功');
  }

  // 封禁日志
  public function accusation_logs()
  {
    $mod = D('AccusationBaseLog');
    $dat['list'] = $mod->plist(C('ITEMS_PER_PAGE'),$map)->lists('','create_time desc');//C('ITEMS_PER_PAGE')
    $this->page = $dat['page_html'] = $mod->pager->show();
    $dat['accusation_states']  = $mod->accusation_states;
    $dat['accusation_reasons'] = $mod->accusation_reasons;
    $dat['accusation_admins']  = $mod->get_accusation_admins($dat['list'],'aid,nickname');
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  // 封禁用户
  public function closure()
  {
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    $rid = (int)$_REQUEST['report_id'];
    $status = (int)$_REQUEST['status'];
    $reason = trim($_REQUEST['reason']);
    $remark = I('request.remark');
    $acc = D('AccusationBaseLog');
    $dat = array();
    if($rid >= 1)
    {
      $rep = D('ReportBase');
      $dat['report'] = $rep->find($rid) ?: array();
      $rid = (int)$dat['report']['id'];
      if($rid) $uid = (int)$dat['report']['offender_uid'];
    }
    $log = array(
      'report_id'   => $rid,
      'uid'         => (int)$dat['report']['uid'],
      'oid'         => $uid,//被封禁人ID
      'aid'         => (int)$_SESSION['authId'],
      'status'      => $status,//封禁状态
      'reason'      => $reason,
      'remark'      => $remark,
      'create_time' => time(),
    );
    $rpt = array(
      'status' => 2,//举报已处理并封禁
      'reason' => $reason,
      'remark' => $remark,
      'atime'  => time(),
    );
    if($uid < 1)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '用户ID错误';
    }
    // 拒绝受理
    elseif($status == 1)
    {
      $rpt['status'] = 3;
    }
    // 已处理不封禁
    elseif($status == 6)
    {
      $rpt['status'] = 2;
    }
    elseif(!array_key_exists($reason,$acc->accusation_reasons))
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '理由错误';
    }
    else
    {
      $usr = D(CONTROLLER_NAME);
      $this->ret = array_merge($this->ret,$usr->closure($uid,$status) ?: array());
      //die(json_encode($this->ret));
    }
    if(!$this->ret['ret'])
    {
      if(false === $acc->add($log))
      {
        $this->ret['ret'] = 1;
        $this->ret['msg'] = '写入Log失败';
      }
      if($rid >= 1 && $dat['report']['status'] == 0)
      {
        // 举报已处理
        $rep->where(array('id' => $rid))->save($rpt);
      }
    }
    $this->ret['ret'] ? $this->error($this->ret['msg']) : $this->success($this->ret['msg']);
  }

  // 封禁解除
  public function unclosure()
  {
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    if($uid < 1)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '用户ID错误';
    }
    else
    {
      $usr = D(CONTROLLER_NAME);
      $this->ret = array_merge($this->ret,$usr->closure($uid,1) ?: array());
    }
    if(!$this->ret['ret'])
    {
      $arr = array(
        'report_id'   => 0,
        'uid'         => 0,
        'oid'         => $uid,//被封禁人ID
        'aid'         => (int)$_SESSION['authId'],
        'status'      => 6,
        'reason'      => 5,
        'remark'      => '解除封禁',
        'create_time' => time(),
      );
      if(false === D('AccusationBaseLog')->add($arr))
      {
        $this->ret['ret'] = 1;
        $this->ret['msg'] = '写入Log失败';
      }
    }
    $this->ret['ret'] ? $this->error($this->ret['msg']) : $this->success($this->ret['msg']);
  }


  // 运营账号 添加
  public function virtual_add()
  {
    $key = 'virtual-user-feeds';
    $fed = D('FeedBase');
    $dat = array();
    if($files = $_FILES['feeds'] ?: array())
    {
      foreach($files['tmp_name'] as $i => $tmp)
      {
        if(!preg_match('/^\s*image\//i',$files['type'][$i])) continue;
        $fnm = $fed->get_file_name_oss($files['name'][$i]);
        //$ret = json_decode('{"status":200}');
        $ret = $this->aliyup($fed->feed_oss_bucket,$fnm,$tmp);
        if($ret->status == 200)
        {
          $feed = array(
            'uid'         => 0,
            'resource'    => $fnm,
            'text'        => '',
            'share'       => 0,
            'create_time' => time(),
          );
          if($fid = (int)$fed->add($feed))//rand(1,9999) ?: 
          {
            $feed['id'] = $fid;
            $dat['list'][$fid] = array_merge($feed,array(
              'nickname' => '',
              'sex'      => (int)$_REQUEST['sex'],
            ));
          }
        }
      }
    }
    $dat['list'] = ($dat['list'] ?: array()) + (session($key) ?: array());
    session($key,$dat['list']);
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function virtual_del($feedId = 0)
  {
    $key = 'virtual-user-feeds';
    $old = session($key) ?: array();
    $fid = (int)($feedId ?: $_REQUEST['feed_id']);
    if(isset($old[$fid])) unset($old[$fid]);
    session($key,$old);
    //die(json_encode(session($key)));
    $feedId === 0 && $this->success('操作成功');
  }

  public function virtual_user_reg()
  {
    $mod = D(CONTROLLER_NAME);
    $fid = (int)$_REQUEST['feed_id'];
    $dat = $mod->create();
    if($dat === false)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = $mod->getError().' ('.$fid.')';
    }
    elseif(!$dat['password'] && 0)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '密码不能为空 ('.$fid.')';
    }
    else
    {
      $dat['feed_id'] = $fid;
      //die(json_encode(['data' => $dat,$mod]));
      $jss = $this->http($mod->api_reg,$dat,'POST');
      $arr = json_decode($jss,true) ?: array();
      if($uid = (int)$arr['uid'])
      {
        $mod->where(array('uid' => $uid))->setField('type',3);//设置为运营账号
        //$mod->set_user_location($uid,array('sex' => $dat['sex'],'token' => $arr['token']));//不上报位置
        $this->ret['msg'] = '用户注册成功，ID:'.$uid;
        $this->virtual_del($fid);
      }
      elseif(isset($arr['errno']))
      {
        $this->ret['ret'] = $arr['errno'];
        $this->ret['msg'] = $arr['error'];
      }
    }
    $this->ret['ret'] ? $this->error($this->ret['msg']) : $this->success($this->ret['msg']);
  }

  // 经纪人添加
  public function broker_add()
  {
    $this->display();
  }

  public function broker_reg()
  {
    $mod = D(CONTROLLER_NAME);
    $dat = $mod->create();
    if($dat === false)
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = $mod->getError();
    }
    elseif(!$dat['password'])
    {
      $this->ret['ret'] = 1;
      $this->ret['msg'] = '密码不能为空';
    }
    else
    {
      //die(json_encode(['data' => $dat,$mod]));
      $jss = $this->http($mod->api_reg,$dat,'POST');
      $arr = json_decode($jss,true) ?: array();
      if($uid = (int)$arr['uid'])
      {
        $mod->where(array('uid' => $uid))->setField('type',4);//设置为经纪人
        $mod->set_user_location($uid,array('sex' => $dat['sex'],'token' => $arr['token']));//上报位置
        $this->ret['msg'] = '用户注册成功，ID:'.$uid;
      }
      elseif(isset($arr['errno']))
      {
        $this->ret['ret'] = $arr['errno'];
        $this->ret['msg'] = $arr['error'];
      }
    }
    $this->ret['ret'] ? $this->error($this->ret['msg']) : $this->success($this->ret['msg']);
  }

  // 保存用户附加属性
  public function save_attrs()
  {
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    $mod = D(CONTROLLER_NAME);
    $dat = $mod->auto_field('attrs',(array)$_REQUEST['attrs']);
    if($dat === false)
    {
      $this->error($mod->getError() ?: '数据错误');
    }
    elseif(false === $mod->where(array('uid' => $uid))->setField('attrs',$dat))
    {
      $this->error('保存失败');
    }
    $this->success('操作成功');
  }


  public function get_user_token()
  {
    $uid = $id = (int)$_REQUEST['uid'] ?: (int)$_REQUEST['id'];
    die('Token:'.D(CONTROLLER_NAME)->get_user_token($uid));
  }

}