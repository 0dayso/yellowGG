<?php
namespace ChuChai\Model;

class ShopAttrModel extends PublicModel
{

  public $attrs =
  [
    'private' =>
    [
      'name' => '私密性',
      'type' => 'radio',
      'subs' => [101001 => '普通',101002 => '很好',101003 => '极佳'],
    ],
    'price' =>
    [
      'name' => '人均',
      'type' => 'radio',
      'subs' => [102001 => '100以下',102002 => '100-200',102003 => '200-400',102004 => '400以上'],
    ],
    'style' =>
    [
      'name' => '场面',
      'type' => 'radio',
      'subs' => [103001 => '普通',103002 => '很好',103003 => '极佳'],
    ],
    'envir' =>
    [
      'name' => '环境',
      'type' => 'radio',
      'subs' => [104001 => '普通',104002 => '很好',104003 => '极佳'],
    ],
    'server' =>
    [
      'name' => '服务',
      'type' => 'radio',
      'subs' => [105001 => '普通',105002 => '很好',105003 => '极佳'],
    ],
    'cooking' =>
    [
      'name' => '菜系',
      'type' => 'radio',
      'subs' => [106001 => '中餐',106002 => '西餐',106003 => '下午茶'],
    ],
    'traffic' =>
    [
      'name' => '交通',
      'type' => 'radio',
      'subs' => [107001 => '普通',107002 => '很好',107003 => '极佳'],
    ],
    'reserve' =>
    [
      'name' => '预定',
      'type' => 'radio',
      'subs' => [108001 => '普通',108002 => '很好',108003 => '极佳'],
    ],
    'invoice' =>
    [
      'name' => '发票',
      'type' => 'radio',
      'subs' => [109001 => '普通',109002 => '很好',109003 => '极佳'],
    ],
    'wifi-pwd' =>
    [
      'name' => 'Wifi密码',
      'type' => 'input',
      'placeholder' => '无线网密码',
    ],
  ];

  public function __construct()
  {
    parent::__construct();
  }

  public function get_attrs($sid = 0)
  {
    $arr = [];
    $lst = $this->where(['sid' => $sid])->select() ?: [];
    foreach($lst as $v)
    {
      $arr[$v['attr']][] = $v['value'];
    }
    return $arr;
  }

  public function set_attrs($sid = 0,$arr = [])
  {
    $ret = false;
    $lst = [];
    foreach($arr ?: [] as $k => $a)
    {
      foreach((array)$a as $v)
      {
        if($v === '') continue;
        $lst[] =
        [
          'sid'   => $sid,
          'attr'  => $k,
          'value' => $v,
        ];
      }
    }
    //die(json_encode(compact('lst')));
    $this->where(['sid' => $sid])->delete();
    if($lst) $ret = $this->addAll(array_values($lst));
    //die($this->getLastSql());
    return $ret;
  }

}