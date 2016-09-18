<?php
/**
 * User: Anl
 */

namespace Cms\Controller;

class UserGradeController extends PublicController
{

  public function index()
  {
    $mod = D(CONTROLLER_NAME);
    $map = $dat = array();
    $map['status'] = 0;
    if($grade = (int)$_REQUEST['grade']) $map['grade'] = $grade;
    $dat['list'] = $mod->plist(C('ITEMS_PER_PAGE') ?: 20,$map)->lists() ?: array();//C('ITEMS_PER_PAGE')
    $dat['list'] += array(
      array(
        'uid'        => 225601,
        'grade'      => 1,
        'score'      => 10,
        'sex'        => 0,
        'login_time' => time() - 1000,
        'album_cnt'  => 3,
        'tag_data'   => '',
        'album_data' => '["ff/6b/img_2805e0b82cd0f283185507d8a78035a0.jpg","ff/6b/img_bafc9c4348110f5cf2fb1b82eb902197.jpg","ff/6b/img_6ef7263058121ac9bd2ec61614fda46f.jpg","ff/6b/img_159cd9447e63f9fdfe42968d9f984796.jpg"]',
      ),
      array(
        'uid'        => 225626,
        'grade'      => 2,
        'score'      => 20,
        'sex'        => 1,
        'login_time' => time() - 2222,
        'album_cnt'  => 5,
        'tag_data'   => '',
        'album_data' => '["ff/6b/img_6ef7263058121ac9bd2ec61614fda46f.jpg","ff/6b/img_159cd9447e63f9fdfe42968d9f984796.jpg","ff/6b/img_bafc9c4348110f5cf2fb1b82eb902197.jpg","ff/6b/img_2805e0b82cd0f283185507d8a78035a0.jpg"]',
      ),
    );
    $dat['tag_class'] = A('Search','Event')->tagclass();
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->data = $dat;
    $this->recommend = (array)$_REQUEST['recommend'];
    //die(json_encode($dat));
    $this->display();
  }

  // 待入库
  public function recommend()
  {
    $mod = D(CONTROLLER_NAME);
    if(IS_POST)
    {
      $arr = $this->get_recommend_data_byform();
      foreach($arr['where'] ?: array() as $i => $map)
      {
        $row = $arr['data'][$i];
        $row['status'] = 1;
        $mod->update_single_item($map,$row);
      }
    }
    // 获取列表
    $map = $dat = array();
    $map['status'] = 1;
    if($grade = (int)$_REQUEST['grade']) $map['grade'] = $grade;
    $dat['list'] = $mod->plist(C('ITEMS_PER_PAGE') ?: 20,$map)->lists() ?: array();//C('ITEMS_PER_PAGE')
    $dat['tag_class'] = A('Search','Event')->tagclass();
    $this->page = $dat['page_html'] = $mod->pager->show();
    $this->data = $dat;
    $this->recommend = (array)$_REQUEST['recommend'];
    //die(json_encode(array('data' => $arr)));
    $this->display();
  }

  // 确认入库
  public function recommend2()
  {
    $mod = D(CONTROLLER_NAME);
    $arr = $this->get_recommend_data_byform();
    foreach($arr['where'] ?: array() as $i => $map)
    {
      $row = $arr['data'][$i];
      $row['status'] = 2;
      $mod->update_single_item($map,$row);
    }
    //die(json_encode(array('data' => $arr)));
    $this->success('操作成功',U('recommend'));
  }

  // 获取推荐入库表单数据
  protected function get_recommend_data_byform($rcm = array())
  {
    $rcm || $rcm = $this->recommend = (array)$_REQUEST['recommend'];
    $map = $dat = array();
    foreach($rcm['list'] ?: array() as $uid => $v)
    {
      $row = $v ?: array();
      $map[] = array('uid' => $uid);
      $dat[] = array('status' => 1,'recommend_data' => json_encode($row));
    }
    return array('where' => $map,'data' => $dat);
  }

}