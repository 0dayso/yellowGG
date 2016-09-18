<?php
namespace Liehuo\Controller;

class SettingController extends PublicController
{

  public $resource_root_url = 'http://static.chujianapp.com/';

  public function sensitive_words()
  {
    $mod = D('SensitiveWords');
    $dat = [];
    $dat['list'] = $mod->plist(100)->lists();//C('ITEMS_PER_PAGE')
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $this->pager->show();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function sensitive_words_save()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D('SensitiveWords');
    $dat = $mod->create();
    $dat['type'] = (int)$dat['type'];
    if($dat['word'] == '') $this->error('敏感词不能为空');
    elseif($dat === false)
    {
      $err = $mod->getError();
      $this->error($err);
    }
    $cnt = $mod->where(array('word' => $dat['word']))->count('id');
    // add
    if($isadd = $id < 1)
    {
      if($cnt > 0) $this->error('敏感词已存在');
      else $id = (int)$mod->add($dat);
    }
    // edit
    elseif(!$old = $mod->find($id))                  $this->error('对象不存在');
    elseif($dat['word'] != $old['word'] && $cnt > 0) $this->error('敏感词已存在');
    else
    {
      if($mod->where(array('id' => $id))->save($dat) === false) $this->error('保存失败');
    }
    if($id < 1)
    {
      $this->error('保存失败');
    }
    else
    {
      $mod->update_cache();
      if($isadd) $mod->sync_words('add',$dat['word']);
      else
      {
        $mod->sync_words('del',$old['word']);
        $mod->sync_words('add',$dat['word']);
      }
      $this->success('保存成功',U('sensitive_words'));
    }
  }

  public function sensitive_words_del()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D('SensitiveWords');
    if(!$old = $mod->find($id))          $this->error('对象不存在');
    elseif(!$mod->limit(1)->delete($id)) $this->error('操作失败');
    else
    {
      $mod->update_cache();
      $mod->sync_words('del',$old['word']);
    }
    $this->success('操作成功',U('sensitive_words'));
  }


  // APP闪屏配置
  /*
  redis -h redisauth.chujianapp.com
  zAdd php_launch_images
  [
    {
      "title"       : '主题',
      "start_time"  : 1444444444,//开始时间
      "end_time"    : 1444455555,//结束时间
      "image"       : 'http://..',
      "update_time" : 1444455555,
      "create_time" : 1444444444,
    }
  ]
  */
  public function launch_images()
  {
    $id  = trim($_REQUEST['id']);
    $mod = D('AppLaunch');
    $dat['list'] = $mod->get_list(['desc' => 1]);
    $id && isset($dat['list'][$id]) && $dat['item'] = $dat['list'][$id] ?: [];
    $this->data = $dat;
    //die(json_encode(compact('dat','arr')));
    $this->display();
  }

  public function launch_save()
  {
    $id  = trim($_REQUEST['id']);
    $isadd = !$id;
    $dat =
    [
      'title'       => trim($_REQUEST['title']),
      'start_time'  => is_numeric($_REQUEST['start_time']) ? (int)$_REQUEST['start_time'] : (int)strtotime($_REQUEST['start_time']),
      'end_time'    => is_numeric($_REQUEST['end_time'])   ? (int)$_REQUEST['end_time']   : (int)strtotime($_REQUEST['end_time']),
      'type'        => (int)$_REQUEST['type'],
      'image'       => trim($_REQUEST['image']),
      'video'       => trim($_REQUEST['video']),
      'status'      => (int)$_REQUEST['status'],
      'update_time' => time(),
    ];
    if(!$dat['title']) $this->error('主题不能为空');
    if($dat['type'] == '0' && !$dat['image']) $this->error('图片路径不能为空');
    if($dat['type'] == '1' && !$dat['video']) $this->error('视频路径不能为空');
    $ret = D('AppLaunch')->zSave($dat,$id,$dat['end_time']);
    //die(json_encode(compact('dat')));
    if(!$ret) $this->error('操作失败');
    $this->success('操作成功',U('launch_images'));
  }

  public function launch_del()
  {
    $id  = trim($_REQUEST['id']);
    if(!D('AppLaunch')->zRem($id))
    {
      $this->error('操作失败');
    }
    $this->success('操作成功');
  }


  // 会员介绍页Banner
  public function vip_banners()
  {
    $id  = trim($_REQUEST['id']);
    $mod = D('AppVipBanner');
    $dat['list'] = $mod->get_list(['desc' => 1]);
    $id && isset($dat['list'][$id]) && $dat['item'] = $dat['list'][$id] ?: [];
    $this->data = $dat;
    //die(json_encode(compact('dat','arr')));
    $this->display();
  }

  public function vip_banner_save()
  {
    $id  = trim($_REQUEST['id']);
    $dat =
    [
      'title'      => trim($_REQUEST['title']),
      'start_time' => is_numeric($_REQUEST['start_time']) ? (int)$_REQUEST['start_time'] : (int)strtotime($_REQUEST['start_time']),
      'end_time'   => is_numeric($_REQUEST['end_time'])   ? (int)$_REQUEST['end_time']   : (int)strtotime($_REQUEST['end_time']),
      'image'      => trim($_REQUEST['image']),
      'image_sm'   => trim($_REQUEST['image_sm']),
      'lt_icon'    => trim($_REQUEST['lt_icon']),
      'link'       => trim($_REQUEST['link']),
      'status'     => (int)$_REQUEST['status'],
    ];
    if(!$dat['title']) $this->error('主题不能为空');
    if(!$dat['image'] || !$dat['image_sm']) $this->error('图片路径不能为空');
    $ret = D('AppVipBanner')->zSave($dat,$id,$dat['end_time']);
    //die(json_encode(compact('dat')));
    if(!$ret) $this->error('操作失败');
    $this->success('操作成功',U('vip_banners'));
  }

  public function vip_banner_del()
  {
    $id  = trim($_REQUEST['id']);
    if(!D('AppVipBanner')->zRem($id))
    {
      $this->error('操作失败');
    }
    $this->success('操作成功');
  }


  // 直播Banner
  public function live_banners()
  {
    $id  = trim($_REQUEST['id']);
    $mod = D('AppLiveBanner');
    $dat['list'] = $mod->get_list(['desc' => 1]);
    $id && isset($dat['list'][$id]) && $dat['item'] = $dat['list'][$id] ?: [];
    $this->data = $dat;
    //die(json_encode(compact('dat','arr')));
    $this->display();
  }

  public function live_banner_save()
  {
    $id  = trim($_REQUEST['id']);
    $dat =
    [
      'title'      => trim($_REQUEST['title']),
      'start_time' => is_numeric($_REQUEST['start_time']) ? (int)$_REQUEST['start_time'] : (int)strtotime($_REQUEST['start_time']),
      'end_time'   => is_numeric($_REQUEST['end_time'])   ? (int)$_REQUEST['end_time']   : (int)strtotime($_REQUEST['end_time']),
      'image'      => trim($_REQUEST['image']),
      'link'       => trim($_REQUEST['link']),
      'status'     => (int)$_REQUEST['status'],
    ];
    if(!$dat['title']) $this->error('主题不能为空');
    if(!$dat['image']) $this->error('图片路径不能为空');
    $ret = D('AppLiveBanner')->zSave($dat,$id,$dat['end_time']);
    //die(json_encode(compact('dat')));
    if(!$ret) $this->error('操作失败');
    $this->success('操作成功',U('live_banners'));
  }

  public function live_banner_del()
  {
    $id  = trim($_REQUEST['id']);
    if(!D('AppLiveBanner')->zRem($id))
    {
      $this->error('操作失败');
    }
    $this->success('操作成功');
  }


  // 任务列表
  public function tasks()
  {
    $id  = (int)$_REQUEST['id'];
    $mod = D('Task');
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->klist('id','','sort desc,end_time desc,create_time desc,id desc');
    $this->pager = $mod->pager;
    $id && $dat['item'] = $mod->find($id);
    $dat['list'] = $mod->attr2array_all($dat['list']);
    if($dat['item'])
    {
      $dat['item'] = $mod->attr2array_row($dat['item']);
      //unset($dat['list'][$id]);
      //array_unshift($dat['list'],$dat['item']);
    }
    $dat['types'] = $mod->types ?: [];
    $this->data = $dat;
    $this->display();
  }

  public function task_save()
  {
    $id  = (int)$_REQUEST['id'];
    $mod = D('Task');
    $dat = $mod->create();
    $isadd = $id < 1;
    if($dat === false) $this->error($mod->getError() ?: '参数错误');
    elseif(!$dat['title']) $this->error('名称不能为空');
    elseif(!$dat['attrs']) $this->error('参数错误');
    else
    {
      unset($dat['id']);
      if($isadd)
      {
        $dat['create_time'] = time();
        $id = (int)$mod->add($dat);
      }
      else
      {
        if(!$mod->where(['id' => $id])->save($dat)) $id = 0;
      }
      if($id < 1) $this->error('操作失败');
      else
      {
        $dat['id'] = $id;
        $mod->update_cache();
        D('OperLog')->log('task',
        [
          $isadd ? '新增任务' : '编辑任务',
          'ID'   => $id,
          '名称' => $dat['title'],
        ]);
      }
    }
    $this->success('操作成功',U('tasks'));
  }

  public function task_del()
  {
    $id  = trim($_REQUEST['id']);
    $mod = D('Task');
    if(false === $mod->soft_del($id))
    {
      $this->error('操作失败');
    }
    $mod->update_cache();
    $this->success('操作成功',U('tasks'));
  }


  // 游戏列表
  public function games()
  {
    $id  = (int)$_REQUEST['id'];
    $mod = D('AppGame');
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->klist('id','','end_time desc,create_time desc,id desc');
    $this->pager = $mod->pager;
    $id && $dat['item'] = $mod->find($id);
    $dat['list'] = $mod->attr2array_all($dat['list']);
    if($dat['item'])
    {
      unset($dat['list'][$id]);
      $dat['item'] = $mod->attr2array_row($dat['item']);
      array_unshift($dat['list'],$dat['item']);
    }
    isset($dat['item']['attrs']['list']) || $dat['item']['attrs']['list'] = array_fill(0,8,[]);
    $dat['types'] = $mod->types ?: [];
    $this->data = $dat;
    $this->display();
  }

  public function game_save()
  {
    $id  = (int)$_REQUEST['id'];
    $mod = D('AppGame');
    $dat = $mod->create();
    $adt = (array)$_REQUEST['attrs'];
    $isadd = $id < 1;
    if($dat === false) $this->error($mod->getError() ?: '参数错误');
    elseif(!$dat['attrs']) $this->error('参数错误');
    elseif(!$adt['list'])  $this->error('参数错误');
    elseif(array_sum(array_column((array)$adt['list'],'probability') ?: []) != 100) $this->error('概率之和必须为100');
    elseif(!(int)$adt['raise_expire']) $this->error('筹集时间错误');
    else
    {
      unset($dat['id']);
      if($isadd)
      {
        $dat['create_time'] = time();
        $id = (int)$mod->add($dat);
      }
      else
      {
        if(!$mod->where(['id' => $id])->save($dat)) $id = 0;
      }
      if($id < 1) $this->error('操作失败');
      else
      {
        $dat['id'] = $id;
        $mod->update_cache();
        D('OperLog')->log('game',
        [
          $isadd ? '新增游戏' : '编辑游戏',
          'ID'   => $id,
          '名称' => $dat['title'],
        ]);
      }
    }
    $this->success('操作成功');
  }


  // 渠道广告配置
  public function adver_list()
  {
    $mod = D('Adver');
    $dat = [];
    $dat['list'] = $mod->plist($this->page_size)->lists();
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $this->pager->show();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function adver_save()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D('Adver');
    $dat = $mod->create();
    $dat['thumb_times'] = (int)$dat['thumb_times'];
    if($dat['thumb_times'] <= 1) $this->error('倍数错误');
    if($dat['ch_serial'] == '')  $this->error('渠道标识不能为空');
    elseif($dat === false)
    {
      $err = $mod->getError();
      $this->error($err);
    }
    $cnt = $mod->where(array('ch_serial' => $dat['ch_serial']))->count('id');
    // add
    if($isadd = $id < 1)
    {
      if($cnt > 0) $this->error('标识已存在');
      else $id = (int)$mod->add($dat);
    }
    // edit
    elseif(!$old = $mod->find($id))                            $this->error('对象不存在');
    elseif($dat['ch_serial'] != $old['ch_serial'] && $cnt > 0) $this->error('标识已存在');
    elseif($mod->where(array('id' => $id))->save($dat) === false)
    {
      $this->error('保存失败');
    }
    if($id < 1)
    {
      $this->error('保存失败');
    }
    else
    {
      $mod->del_cache();
    }
    $this->success('保存成功',U('adver_list'));
  }

  public function adver_del()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D('Adver');
    if(!$mod->limit(1)->delete($id)) $this->error('操作失败');
    else
    {
      $mod->del_cache();
    }
    $this->success('操作成功',U('adver_list'));
  }


  // 正在审核的版本
  public function version_audit()
  {
    $dat['version_ios'] = D('AppCfg')->get_redis()->get('php_app_version_ios');
    $this->data = $dat;
    $this->display();
  }

  public function version_audit_save()
  {
    $rds = D('AppCfg')->get_redis();
    $rds->setEx('php_app_version_ios',60 * 60 * 24 * 30,trim($_REQUEST['version_ios']));
    $this->success('操作成功',U('version_audit'));
  }


  // 上传图片
  public function upload()
  {
    $typ = trim($_REQUEST['type']);
    $res = $_FILES['file'];
    if(!$res) $this->error('请选择文件');
    elseif(!in_array($typ,['images','voices','videos']))
    {
      $this->error('参数错误');
    }
    else
    {
      $mod = D('Resource');
      $fnm = $this->make_file_name($res['name'],$typ);
      //$ret = $this->aliyup('cjstatic',$fnm,$res['tmp_name']);
      $ret = $mod->oss_upload('cjstatic',$res['tmp_name'],$fnm);
      if($ret)
      {
        $this->success(
        [
          'filename' => $fnm,
          'resource' => $this->resource_root_url.$fnm,
          'result'   => is_object($ret) ? get_object_vars($ret) : $ret,
        ]);
      }
    }
    $this->error('操作失败');
  }

  protected function make_file_name($filename = '',$dir = '')
  {
    $ext = strrchr($filename,'.');
    $path = date('Ym').'/';
    $dir && $path = $dir.'/'.$path;
    $path .= md5(uniqid(rand(),true)).$ext;
    return $path;
  }

}