<?php
namespace Liehuo\Controller;

class ArticleController extends PublicController
{

  public function __construct()
  {
    parent::__construct();
  }

  public function index()
  {
    $mod = D(CONTROLLER_NAME);
    $map = ['delete_time' => 0];
    $dat['list'] = $mod->plist($this->page_size,$map)->lists('','create_time desc,id desc');
    $dat['list'] = $mod->complete_list($dat['list']);
    $this->pager = $mod->pager;
    $this->page = $dat['page_html'] = $this->pager->show();
    $this->data = $dat;
    //die(json_encode($dat));
    $this->display();
  }

  public function view()
  {
    layout(false);
    $id  = (int)$_REQUEST['id'];
    @header('Location: http://src.chujianapp.com/article/?id='.$id);
    $mod = D(CONTROLLER_NAME);
    $dat = [];
    if($id < 1) $this->error('对象不存在');
    else
    {
      $dat['item'] = $mod->find($id);
      $dat['item'] = $mod->complete_fields($dat['item']);
    }
    $this->data = $dat;
    $this->display();
  }

  public function edit()
  {
    $id  = (int)$_REQUEST['id'];
    $mod = D(CONTROLLER_NAME);
    $dat = [];
    if($id >= 1)
    {
      $dat['item'] = $mod->find($id);
      $dat['item'] = $mod->complete_fields($dat['item']);
    }
    $this->data = $dat;
    $this->display();
  }

  public function save()
  {
    $id  = (int)$_REQUEST['id'];
    $mod = D(CONTROLLER_NAME);
    $dat = $mod->create();
    $isadd = $id < 1;
    if(!$dat['title'])       $this->error('标题不能为空');
    elseif(!$dat['content']) $this->error('内容不能为空');
    elseif(!$dat['thumb'])   $this->error('缩率图不能为空');
    elseif($dat === false)   $this->error($mod->getError() ?: '参数错误');
    else
    {
      unset($dat['id']);
      is_string($dat['publish_time']) && $dat['publish_time'] = strtotime($dat['publish_time']);
      if($isadd)
      {
        $dat['create_time'] = time();
        $id = (int)$mod->add($dat);
      }
      else
      {
        $dat['update_time'] = time();
        if(!$mod->where(['id' => $id])->save($dat)) $id = 0;
      }
      if($id < 1) $this->error('操作失败');
      else
      {
        $dat['id'] = $id;
        $this->publish($dat);
        D('OperLog')->log('article',
        [
          $isadd ? '新增文章' : '编辑文章',
          '文章ID' => $id,
          '标题'   => $dat['title'],
        ]);
      }
    }
    $this->success('操作成功');
  }


  // 将文章发布到阿里云
  protected function publish($dat = [])
  {
    $id = (int)$dat['id'];
    $htm = '';
    if($id)
    {
      $dat = D(CONTROLLER_NAME)->complete_fields($dat);
      $this->data = ['item' => $dat];
      layout(false);
      $htm = $this->fetch('view');
      $fnm = 'article/content/'.$id.'.html';
      import('Org.AliyunOss.sdk');
      $oss = new \ALIOSS();
      $oss->delete_object('cjstatic',$fnm);
      $ret = $oss->upload_file_by_content('cjstatic',$fnm,
      [
        'content' => $dat['content'],
        'Content-Encoding' => 'UTF-8',
        'Expires' => date('Y-m-d H:i:s',strtotime('1 days')),
        //'Cache-Control' => 'no-cache',
      ]);
    }
    return $dat;
  }

  public function del()
  {
    $id  = (int)$_REQUEST['id'];
    $mod = D(CONTROLLER_NAME);
    $ret = $mod->where(['id' => $id])->save(['delete_time' => time()]);
    if($ret)
    {
      D('OperLog')->log('article',
      [
        '删除文章',
        '文章ID' => $id,
      ]);
    }
    $ret ? $this->success('操作成功') : $this->error('操作失败');
  }

  public function upload()
  {
    $res = $_FILES['file'];
    if(!$res) $this->error('请选择文件');
    else
    {
      $fnm = $this->make_file_name($res['name'],'article/image');
      $ret = D('Resource')->oss_upload('cjstatic',$res['tmp_name'],$fnm);
      if($ret)
      {
        $this->success(
        [
          'filename' => $fnm,
          'resource' => D(CONTROLLER_NAME)->resource_root_url.$fnm,
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