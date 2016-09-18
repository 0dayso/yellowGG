<?php
namespace ChuChai\Controller;
use ChuChai\Model;

class CommonController extends \Liehuo\Controller\CommonController
{

  public $resource_root_url = 'http://static.chujianapp.com/';


  // 上传图片
  public function upload()
  {
    $typ = trim($_REQUEST['type']);
    $res = $_FILES['file'];
    if(!$res) $this->error('请选择文件');
    elseif(!in_array($typ,['images','article']))
    {
      $this->error('参数错误');
    }
    else
    {
      $mod = D('Liehuo/Resource');
      $fnm = $this->make_file_name($res['name'],$typ);
      $fnm = 'chuchai/'.$fnm;
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

  // 上传图片 ckeditor
  public function upload_cke()
  {
    $typ = trim($_REQUEST['type']);
    $res = $_FILES['upload'];
    $err = '';
    $dat = ['uploaded' => 0];
    if(!$res) $err = '请选择文件';
    elseif(!in_array($typ,['images','article']))
    {
      $err = '参数错误';
    }
    else
    {
      $mod = D('Liehuo/Resource');
      $fnm = $this->make_file_name($res['name'],$typ);
      $fnm = 'chuchai/'.$fnm;
      //$ret = $this->aliyup('cjstatic',$fnm,$res['tmp_name']);
      $ret = $mod->oss_upload('cjstatic',$res['tmp_name'],$fnm);
      if($ret)
      {
        $dat =
        [
          'uploaded' => 1,
          'fileName' => $fnm,
          'url'      => $this->resource_root_url.$fnm,
        ];
      }
    }
    if(isset($_REQUEST['CKEditorFuncNum']))
    {
      if($dat) die("<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction(".(int)$_REQUEST['CKEditorFuncNum'].",'".$dat['url']."');</script>");
      die;
    }
    if($err) $dat['error']['message'] = $err ?: '操作失败';
    $this->ajaxReturn($dat);
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