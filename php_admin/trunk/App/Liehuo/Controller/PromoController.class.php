<?php
namespace Liehuo\Controller;

class PromoController extends PublicController
{

  public function index()
  {
    die;
  }

  public function mass_send()
  {
    isset($_REQUEST['stime']) || $_REQUEST['stime'] = date('Y-m-d',strtotime('-3 days'));
    $aid = (int)$_REQUEST['article_id'];
    $dat = [];
    $dat['packages'] = D('Stat')->get_package_list('','id desc') ?: [];
    $dat['versions'] = array_unique(array_column($dat['packages'],'pkg_version'));
    if($aid >= 1)
    {
      $mod = D('Article');
      $dat['article'] = $mod->find($aid);
      if(!$dat['article']) $this->error('文章不存在');
      $dat['article'] = $mod->complete_fields($dat['article']);
      $dat['article']['article_link'] = $mod->get_url($aid);
      $_REQUEST['send_type'] = 'article';
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function mass_send_count()
  {
    $dat = [];
    $ids = $this->get_mass_send_uids();
    $dat['count'] = count($ids);
    $dat['today_active_count'] = (int)$this->today_active_count;
    $dat['users'] = array_slice($ids,0,10);
    $this->success($dat);
  }

  public function mass_send_post()
  {
    $typ = $_REQUEST['send_type'];
    $msg =
    [
      'target'      => 2,
      'text_type'   => 1,
      'chat_type'   => 2,
      'msg_type'    => 7,
      'msg_content' => '',
      'valid_begin' => NOW_TIME,
      'valid_end'   => strtotime('+1 days'),
    ];
    $log = '';
    // 图文消息
    if($typ == 'graphic')
    {
      $con = $_POST['content'];
      $img = $_POST['image'];
      if(!$con) $this->error('消息内容不能为空');
      if(!$img) $this->error('图片路径不能为空');
      $msg['text_type']   = 100;
      $msg['msg_content'] = json_encode(
      [
        'type'   => $msg['msg_type'],
        'status' => 1,
        'hint'   => $con,
        'text'   => json_encode(
        [
          'comment'              => $con,
          'sessionThumbImageUrl' => $img,
        ]),
      ],JSON_UNESCAPED_UNICODE);
      $log = '图文消息：'.$con;
    }
    // 运营消息
    elseif($typ == 'article')
    {
      $tit = $_POST['title'];
      $img = $_POST['thumb'];
      $des = $_POST['desc'];
      $lnk = $_POST['link'];
      if(!$tit) $this->error('标题不能为空');
      if(!$img) $this->error('图片不能为空');
      if(!$des) $this->error('描述不能为空');
      if(!$lnk) $this->error('链接不能为空');
      $msg['text_type']   = 100;
      $msg['msg_type']    = 8;
      $msg['msg_content'] = json_encode(
      [
        'type'   => $msg['msg_type'],
        'status' => 1,
        'hint'   => $tit,
        'text'   => json_encode(
        [
          [
            'title' => $tit,
            'desc'  => $des,
            'link'  => $lnk,
            'thumb' => $img,
          ],
        ]),
      ],JSON_UNESCAPED_UNICODE);
      $log = '运营消息：'.$tit;
    }
    // 文本消息
    else
    {
      $con = $_POST['content'];
      if(!$con) $this->error('消息内容不能为空');
      $msg['msg_content'] = json_encode(
      [
        'type'   => $msg['msg_type'],
        'status' => 1,
        'text'   => $con,
      ],JSON_UNESCAPED_UNICODE);
      $log = $con;
    }
    $ids = $this->get_mass_send_uids();
    if(!$ids) $this->error('请选择要发送用户');
    else
    {
      //die(json_encode(compact('ids','msg')));
      $mod = D('TblSystemMsg');
      $mid = $mod->insertMsg($msg,$ids);
      $ret = $mod->sendMessage($mid);
      $cnt = count($ids ?: []);
      D('MassSend')->log(
      [
        'text'       => $msg['msg_content'],
        'texttype'   => $msg['text_type'],
        'msgtype'    => $msg['msg_type'],
        'send_num'   => $cnt,
        'active_num' => (int)$this->today_active_count,
        'time'       => date('Y-m-d H:i:s'),
      ]);
      D('OperLog')->log('msg_send_bat',
      [
        '发送人数' => $cnt,
        '活跃人数' => $this->today_active_count,
        '话术'     => $log,
      ]);
    }
    $this->success('发送成功');
  }

  protected function get_mass_send_uids()
  {
    $start_time = microtime(true);
    $typ = trim($_REQUEST['model']);
    $_REQUEST['stime'] = date('Y-m-d 00:00:00',$_REQUEST['stime'] ? strtotime($_REQUEST['stime']) : NOW_TIME);
    $_REQUEST['etime'] = date('Y-m-d 23:59:59',$_REQUEST['etime'] ? strtotime($_REQUEST['etime']) : NOW_TIME);
    $stime = strtotime($_REQUEST['stime']);
    $etime = strtotime($_REQUEST['etime']);
    $ids = [];
    if(in_array($typ,['UserBase','LocationBase','version']))
    {
      $typ == 'version' && $typ = 'LocationBase';
      $mod = D($typ);
      $map = $mod->get_filters(true);
      //if(!$map) $this->error('请选择筛选条件');
      $ids = $mod->field('uid')->where($map)->getField('uid',true);
      $sql = $mod->get_last_sql();
      $ids && $this->today_active_count = (int)D('LocationBase')->where(
      [
        'update_time' => ['egt',strtotime(date('Y-m-d'))],
        'uid'         => ['exp','in ('.$sql.')'],
      ])->count('uid');
      @header('debug-last-map: '.json_encode($map));
    }
    elseif(in_array($typ,['active']))
    {
      $rds = D('UserBase')->get_redis();
      $ids = $rds->zRangeByScore('php_active',$stime,$etime);
      $ids && $this->today_active_count = (int)$rds->zCount('php_active',strtotime(date('Y-m-d')),NOW_TIME + 1);
    }
    else
    {
      $str = $_REQUEST['uids'];
      $ids = [];
      if(preg_match_all('/\b(\d{4,11})\b/',$str,$arr)) $ids = $arr[1];
      elseif($str) $ids = explode(',',$str);
      $ids = array_unique($ids ?: []);
      //$ids[] = 12200022;
      $ids && $this->today_active_count = (int)D('LocationBase')->where(
      [
        'update_time' => ['egt',strtotime(date('Y-m-d'))],
        'uid'         => ['in',array_values($ids)],
      ])->count('uid');
    }
    $mod && @header('debug-last-sql: '.$mod->get_last_sql());
    rlog([date('H:i:s'),
    [
      'type'    => $typ,
      'map'     => $map,
      'sql'     => $sql,
      'count'   => count($ids ?: []),
      'Runtime' => round(microtime(true) - $start_time,6),
    ]],'mass_send_get_uids',86400 * 15);
    return $ids;
  }


  // 线下推广用户数据
  public function offline_data()
  {
    $dat = [];
    $dat['pkgs'] = D('Stat')->get_package_list('','id desc');
    if($file = $_FILES['file'] ?: [])
    {
      $dat['file'] = $file;
      if(preg_match('/([a-z]+\d+)/i',$file['name'],$arr))
      {
        $dat['serial'] = $arr[1];
      }
      $txt = $dat['txt'] = file_get_contents($file['tmp_name']);
      if(preg_match_all('/\b(\d{4,11})\b/',$txt,$arr))
      {
        $dat['phones'] = $arr[1] ?: [];
        $dat['phones'] = array_unique($dat['phones']);
      }
    }
    if($dat['phones'])
    {
      $map =
      [
        'phone'    => ['exp','regexp \'[0-9]{0,7}('.implode('|',$dat['phones']).')$\''],
        //'reg_time' => ['egt',strtotime(date('Y-m-d',strtotime('-30 days')))],
      ];
      $mod = D('UserBase');
      $uls = $mod->get_offline_users($map) ?: [];
      foreach($uls as $v)
      {
        $v['active_time'] = $mod->get_active_time($v['uid']);
        $dat['devices'][$v['device_id']][] = $v['uid'];
        $dat['list'][] = $v;
      }
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function offline_data_save()
  {
    $mod = D('Stat');
    $ids = (array)$_REQUEST['ids'];
    $pkg = (int)$_REQUEST['pkg_id'];
    $ser = trim($_REQUEST['serial']);
    $dat = [];
    if(!$ids)     $this->error('请选择记录');
    elseif(!$pkg) $this->error('请选择安装包');
    elseif(!preg_match('/^[a-z]+\d+$/i',$ser)) $this->error('广告序列号错误');
    elseif(!$adv = $mod->get_adver($pkg,$ser)) $this->error($mod->getError() ?: '获取广告失败');
    {
      $usr = D('UserBase');
      $map = ['uid' => ['in',$ids]];
      $dat['list'] = $usr->where($map)->select();
      //$ret = $usr->where($map)->limit(count($ids))->save(['pkg_channel' => $adv['ch_serial']]);
      //$mod->set_user_adver_channel($ids,$adv);
      //$mod->import_offline_daily_count($ids);
      $ret = $mod->import_offline_daily_user($ids,$adv);
      $mod->update_offline_daily_user();
      $dat['isok'] = !!$ret;
      D('OperLog')->log('promo_offline',
      [
        '渠道序列号' => $ser,
        '安装包ID'   => $pkg,
        '影响用户数' => (int)$ret,
      ]);
    }
    $this->data = $dat;
    $this->display('offline_data');
  }


  // 街拍导入
  public function snap_import()
  {
    $dat = [];
    if($files = $_FILES['imgs'] ?: [])
    {
      $msg = D('Message');
      $lst = [];
      foreach($files['tmp_name'] as $i => $tmp)
      {
        $onm = $files['name'][$i];
        if(!preg_match('/^\s*image\//i',$files['type'][$i])) continue;
        preg_match('/\b(\d{4,11})\b/i',$onm,$arr);
        $tel = $arr[1];
        if(!$tel) continue;
        $fnm = $msg->make_file_name($onm);
        if(!isset($lst[$tel]))
        {
          //$ret = json_decode('{"status":200}');
          $ret = $this->aliyup('im-image',$tmp,$fnm);
          if($ret)
          {
            $lst[$tel] =
            [
              'phone'    => $tel,
              'name'     => $onm,
              'tmp_name' => $tmp,
              'new_name' => $fnm,
            ];
            $msg->add_upload_histoty($fnm);
          }
        }
      }
      if($lst)
      {
        $dat['phones'] = array_keys($lst);
        $dat['phones'] = array_unique($dat['phones']);
        $pls = array_combine($dat['phones'],$dat['phones']);
        $mod = D('UserBase');
        $rds = $mod->get_redis();
        $uls = $mod->get_offline_users(
        [
          'phone'    => ['exp','regexp \'[0-9]{0,7}('.implode('|',$dat['phones']).')$\''],
          'reg_time' => ['egt',strtotime(date('Y-m-d',strtotime('-7 days')))],
        ]);
        if($uls) foreach($uls as $v)
        {
          foreach($pls as $p)
          {
            if(!preg_match('/'.$p.'$/',$v['phone'])) continue;
            else
            {
              $v['photo']    = $lst[$p];
              $v['resource'] = $v['photo']['new_name'];
              //unset($pls[$p]);//会导致统一号码匹配到的多个人只有一个人有图片
            }
            $v['is_sent'] = !!$_SESSION['promo-snap-history'][$v['uid']];
          }
          $v['active_time'] = $rds->zScore('php_active',$v['uid']);
          $dat['list'][] = $v;
        }
      }
      //die(json_encode($dat));
    }
    $this->data = $dat;
    $this->display();
  }

  public function snap_send()
  {
    $ids = (array)$_REQUEST['ids'];
    $res = (array)$_REQUEST['resource'];
    $ret = [];
    $num = 0;
    if(!$ids) $this->error('请选择记录');
    foreach($res as $uid => $fnm)
    {
      if(!$ids[$uid]) continue;
      $msg = D('Message');
      $src = $msg->image_url_root.$fnm;
      $thu = $src.'@640w';
      $ret[$uid] = $msg->set_feedback()->add_image($uid,$src,$thu);
      $ret[$uid] === false || $num++;
      $_SESSION['promo-snap-history'][$uid] = $ret[$uid];
    }
    D('OperLog')->log('promo_snap',
    [
      '街拍照片群发',
      '数量' => (int)$num,
    ]);
    //die(json_encode(compact('ids','res','ret')));
    $this->success('发送完成！成功'.$num.'条');
  }

}