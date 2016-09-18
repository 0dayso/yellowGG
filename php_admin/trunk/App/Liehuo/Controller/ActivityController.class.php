<?php
namespace Liehuo\Controller;

class ActivityController extends PublicController
{

  public function __construct()
  {
    parent::__construct();
  }

  public function index()
  {
    $mod = D(CONTROLLER_NAME);
    $dat['list'] = $mod->plist($this->page_size)->lists('','create_time desc,id desc');
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $this->pager->show();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function pubs()
  {
    $mod = D(CONTROLLER_NAME);
    $ord =
    [
      's.cnt_subs'    => 'desc',
      'p.create_time' => 'desc',
      'p.id'          => 'desc',
    ];
    $sql = $mod->table('__ACTIVITY_SUB__')->field(
    [
      'pub_key',
      'count(id)'        => 'cnt_subs',
      'max(finish_time)' => 'last_finish',
    ])->group('pub_key')->buildSql();
    $map = $mod->set_table('__ACTIVITY_PUB__')->alias('p')->get_filters();
    $dat['list'] = $mod->field('p.*,s.cnt_subs,s.last_finish')
      ->join('left join '.$sql.' s on s.pub_key = p.pub_key')
      ->plist($this->page_size,$map)
      ->lists('',$ord);
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $this->pager->show();
    $map = $mod->alias('s')->get_filters();
    $dat['cnt_subs'] = $mod->table('__ACTIVITY_SUB__')->where($map)->count('id');
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function subs()
  {
    $mod = D(CONTROLLER_NAME)->set_table('__ACTIVITY_SUB__');
    $umd = D('UserBase');
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','create_time desc,id desc');
    $dat['list'] = array_map(function($v) use($umd)
    {
      $v['active_time'] = $umd->get_active_time($v['uid']);
      return $v;
    },$dat['list'] ?: []);
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $this->pager->show();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }


  // 福袋
  public function lucky_bags()
  {
    $mod = D('LuckyBag');
    $ord =
    [
      'p.create_time' => 'desc',
      's.cnt_subs'    => 'desc',
      'p.id'          => 'desc',
    ];
    $sql = $mod->table('__LUCKY_BAG_SUB__')->field(
    [
      'pub_id',
      'count(id)'        => 'cnt_subs',
      'sum(glamour)'     => 'cnt_glamour',
      'max(create_time)' => 'last_time',
    ])->group('pub_id')->buildSql();
    $map = $mod->set_table('__LUCKY_BAG_PUB__')->alias('p')->get_filters();
    $dat['list'] = $mod->field('p.*,s.cnt_subs,s.cnt_glamour,s.last_time')
      ->join('left join '.$sql.' s on s.pub_id = p.id')
      ->plist($this->page_size,$map)
      ->lists('',$ord);
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $this->pager->show();
    $map = $mod->alias('s')->get_filters();
    //$dat['cnt_subs'] = $mod->table('__LUCKY_BAG_SUB__')->where($map)->count('id');
    $dat['cnt_user'] = $mod->where($map)->count('distinct uid');
    $dat['users'] = D('UserBase')->get_users_account($dat['list']);
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function lucky_bag_subs()
  {
    $mod = D('LuckyBag')->set_table('__LUCKY_BAG_SUB__');
    $umd = D('UserBase');
    $map = $mod->get_filters();
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','create_time desc,id desc');
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $this->pager->show();
    $dat['list'] = array_map(function($v) use($umd)
    {
      $v['active_time'] = $umd->get_active_time($v['uid']);
      return $v;
    },$dat['list'] ?: []);
    $dat['cnt_glamour'] = $mod->table('__LUCKY_BAG_SUB__')->where($map)->sum('glamour');
    $dat['avg_glamour'] = $mod->table('__LUCKY_BAG_SUB__')->where($map)->avg('glamour');
    $dat['users'] = D('UserBase')->get_users_account($dat['list']);
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

}