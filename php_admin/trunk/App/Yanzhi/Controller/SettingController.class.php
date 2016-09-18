<?php
namespace Yanzhi\Controller;

class SettingController extends PublicController
{

  public function sensitive_words()
  {
    $mod = D('SensitiveWords');
    $dat = array();
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
      $this->success('保存成功',U('sensitive_words'));
    }
  }

  public function sensitive_words_del()
  {
    $id = (int)$_REQUEST['id'];
    $mod = D('SensitiveWords');
    if(!$mod->limit(1)->delete($id)) $this->error('操作失败');
    else $this->success('操作成功',U('sensitive_words'));
  }

}