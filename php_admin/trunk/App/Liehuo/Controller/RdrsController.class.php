<?php
namespace Liehuo\Controller;

class RdrsController extends PublicController
{

  public function __construct()
  {
    parent::__construct();
  }


  public function channel_list()
  {
    $mod = D('Stat')->set_table('__CHANNEL__');
    $dat['list'] = $mod->plist($this->page_size)->lists();
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $this->pager->show();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function channel_save()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D('Stat')->set_table('__CHANNEL__');
    $dat = $mod->create();
    $dat['ch_sums'] = round($dat['ch_sums'],1);
    if($dat['ch_name']   == '') $this->error('名称不能为空');
    if($dat['ch_serial'] == '') $this->error('命名规范不能为空');
    elseif($dat === false)
    {
      $err = $mod->getError();
      $this->error($err);
    }
    $cnt = $mod->where(['ch_serial' => $dat['ch_serial']])->count('id');
    // add
    if($isadd = $id < 1)
    {
      unset($dat['id']);
      $dat['ch_time'] = time();
      if($cnt > 0) $this->error('标识已存在');
      else $id = (int)$mod->add($dat);
    }
    // edit
    elseif(!$old = $mod->find($id))                            $this->error('对象不存在');
    elseif($dat['ch_serial'] != $old['ch_serial'] && $cnt > 0) $this->error('标识已存在');
    elseif($mod->where(['id' => $id])->limit(1)->save($dat) === false)
    {
      $this->error('操作失败');
    }
    if($id < 1)
    {
      $this->error('操作失败');
    }
    $this->success('操作成功',U('channel_list'));
  }

  public function channel_del()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D('Stat')->set_table('__CHANNEL__');
    if(!$mod->where(['id' => $id])->limit(1)->setField('ch_del',1))
    {
      $this->error('操作失败');
    }
    $this->success('操作成功',U('channel_list'));
  }


  public function adver_list()
  {
    $mod = D('Stat')->set_table('__ADVER__');
    $dat['list'] = $mod->plist($this->page_size)->lists();
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $this->pager->show();
    $dat['channels'] = $mod->get_channel_list() ?: [];
    $dat['packages'] = $mod->get_package_list() ?: [];
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function adver_save()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D('Stat')->set_table('__ADVER__');
    $dat = $mod->create();
    $dat['ch_id']  = (int)$dat['ch_id'];
    $dat['pkg_id'] = (int)$dat['pkg_id'];
    //if($dat['name']      == '') $this->error('名称不能为空');
    if($dat['ch_serial'] == '') $this->error('标识不能为空');
    elseif($dat === false)
    {
      $err = $mod->getError();
      $this->error($err);
    }
    $cnt = $mod->where(['ch_serial' => $dat['ch_serial']])->count('id');
    // add
    if($isadd = $id < 1)
    {
      unset($dat['id']);
      if($cnt > 0) $this->error('标识已存在');
      else $id = (int)$mod->add($dat);
    }
    // edit
    elseif(!$old = $mod->find($id))                            $this->error('对象不存在');
    elseif($dat['ch_serial'] != $old['ch_serial'] && $cnt > 0) $this->error('标识已存在');
    elseif($mod->where(['id' => $id])->limit(1)->save($dat) === false)
    {
      $this->error('操作失败');
    }
    if($id < 1)
    {
      $this->error('操作失败');
    }
    $this->success('操作成功',U('adver_list'));
  }

  public function adver_del()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D('Stat')->set_table('__ADVER__');
    if(!$mod->where(['id' => $id])->limit(1)->setField('del',1))
    {
      $this->error('操作失败');
    }
    $this->success('操作成功',U('adver_list'));
  }


  public function package_list()
  {
    $mod = D('Stat')->set_table('__PACKAGE__');
    $dat['list'] = $mod->plist($this->page_size)->lists();
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $this->pager->show();
    $dat['devices'] = $mod->devices ?: [];
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function package_save()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D('Stat')->set_table('__PACKAGE__');
    $dat = $mod->create();
    $dat['pkg_device'] = (int)$dat['pkg_device'];
    if($dat['pkg_name']    == '')                 $this->error('名称不能为空');
    if($dat['pkg_version'] == '')                 $this->error('版本不能为空');
    if(!isset($mod->devices[$dat['pkg_device']])) $this->error('所属设备错误');
    elseif($dat === false)
    {
      $err = $mod->getError();
      $this->error($err);
    }
    // add
    if($isadd = $id < 1)
    {
      unset($dat['id']);
      $dat['pkg_time'] = time();
      $id = (int)$mod->add($dat);
    }
    // edit
    elseif($mod->where(['id' => $id])->limit(1)->save($dat) === false)
    {
      $this->error('操作失败');
    }
    if($id < 1)
    {
      $this->error('操作失败');
    }
    $this->success('操作成功',U('package_list'));
  }

  public function package_del()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D('Stat')->set_table('__PACKAGE__');
    if(!$mod->where(['id' => $id])->limit(1)->setField('pkg_del',1))
    {
      $this->error('操作失败');
    }
    $this->success('操作成功',U('package_list'));
  }


  public function market_list()
  {
    $stm = strtotime($_REQUEST['stime']);
    $stm || $stm = strtotime('-6 days');
    $etm = $stm + 60 * 60 * 24 * 7 - 1;
    $map =
    [
      'ma_date' =>
      [
        ['egt',date('Y-m-d',$stm)],
        ['elt',date('Y-m-d',$etm)],
      ],
    ];
    $mod = D('Stat')->set_table('__MARKET__');
    $dat['list'] = $mod->lists($map,'id');
    $dat['stime'] = $stm;
    $dat['etime'] = $etm;
    $dat['devices']  = $mod->devices ?: [];
    $dat['channels'] = $mod->get_channel_list() ?: [];
    foreach($dat['list'] ?: [] as $v)
    {
      $dat['sums'][$v['ma_date']][$v['ch_id']] = $v;
    }
    for($i = 0;$i < 10;$i++)
    {
      $tim = strtotime('+'.$i.' days',$stm);
      if($tim > $etm) break;
      $dat['dates'][] = date('Y-m-d',$tim);
    }
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function market_save()
  {
    $all = (array)$_REQUEST['sums'];
    $sdt = $dts = $chs = [];
    foreach($all ?: [] as $date => $vd)
    {
      foreach($vd ?: [] as $cid => $v)
      {
        $cid = (int)$cid;
        if($cid && $v['android'] != '' && $v['ios'] != '')
        {
          $dts[$date] = $date;
          $chs[$cid]  = $cid;
          $sdt[$date][$cid] = $v;
        }
      }
    }
    $mod = D('Stat')->set_table('__MARKET__');
    $els = $mod->klist('id',
    [
      'ma_date' => ['in',array_keys($dts)],
      'ch_id'   => ['in',array_keys($chs)],
    ]) ?: [];
    $edt = [];
    foreach($els as $id => $v)
    {
      $edt[$v['ma_date']][$v['ch_id']] = $v;
    }
    $adt = [];
    foreach($sdt ?: [] as $date => $vd)
    {
      foreach($vd ?: [] as $cid => $v)
      {
        $cid = (int)$cid;
        $row = ['android' => round($v['android'],2),'ios' => round($v['ios'],2)];
        $row['ma_sums'] = $row['android'] + $row['ios'];
        $old = $edt[$date][$cid];
        if($old['id']) $mod->where(['id' => $old['id']])->save($row);
        else $adt[] = array_merge($row,
        [
          'ma_date' => $date,
          'ch_id'   => $cid,
        ]);
      }
    }
    if($adt) $mod->addAll($adt);
    $this->success('操作成功');
  }

}