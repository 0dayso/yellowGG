<?php
namespace ChuChai\Model;

class FieldModel extends PublicModel
{

  const TYPE_TEXT       = 'text';
  const TYPE_NUMBER     = 'number';
  const TYPE_TEXTAREA   = 'textarea';
  const TYPE_URL        = 'url';
  const TYPE_SELECT     = 'select';
  const TYPE_RADIO      = 'radio';
  const TYPE_CHECKBOX   = 'checkbox';
  const TYPE_EMAIL      = 'email';
  const TYPE_IMAGE      = 'image';
  const TYPE_DATE       = 'date';
  const TYPE_TIME       = 'time';
  const TYPE_DATETIME   = 'datetime';
  const TYPE_RANGE      = 'range';
  const TYPE_ILLUST     = 'illust';//图文
  const TYPE_GOODS      = 'goods';//商品
  const TYPE_ROOMS      = 'rooms';//包间
  const TYPE_BSHOURS    = 'bshours';//营业时间

  public $types =
  [
    self::TYPE_TEXT     =>
    [
      'name' => '字符串',
      'tag'  => 'input',
      'attr' => ['type' => 'text'],
    ],
    self::TYPE_NUMBER   =>
    [
      'name' => '数值',
      'tag'  => 'input',
      'attr' => ['type' => 'number'],
    ],
    self::TYPE_TEXTAREA =>
    [
      'name' => '长文本',
      'tag'  => 'textarea',
      'attr' => ['type' => ''],
    ],
    self::TYPE_URL      =>
    [
      'name' => '超级链接',
      'tag'  => 'input',
      'attr' => ['type' => 'url'],
      'hide' => 1,
    ],
    self::TYPE_SELECT   =>
    [
      'name' => '下拉选择',
      'tag'  => 'select',
      'attr' => ['type' => ''],
      'hide' => 1,
    ],
    self::TYPE_RADIO    =>
    [
      'name' => '单选框',
      'tag'  => 'input',
      'attr' => ['type' => 'radio'],
    ],
    self::TYPE_CHECKBOX =>
    [
      'name' => '多选框',
      'tag'  => 'input',
      'attr' => ['type' => 'checkbox'],
    ],
    self::TYPE_EMAIL    =>
    [
      'name' => '电子邮件',
      'tag'  => 'input',
      'attr' => ['type' => 'email'],
      'hide' => 1,
    ],
    self::TYPE_IMAGE    =>
    [
      'name' => '上传图片',
      'tag'  => 'input',
      'attr' => ['type' => 'file'],
    ],
    self::TYPE_DATE     =>
    [
      'name' => '日期',
      'tag'  => 'input',
      'attr' => ['type' => 'date'],
      'hide' => 1,
    ],
    self::TYPE_TIME     =>
    [
      'name' => '时间',
      'tag'  => 'input',
      'attr' => ['type' => 'time'],
    ],
    self::TYPE_DATETIME =>
    [
      'name' => '日期时间',
      'tag'  => 'input',
      'attr' => ['type' => 'datetime'],
      'hide' => 1,
    ],
    self::TYPE_RANGE    =>
    [
      'name' => '范围',
      'tag'  => 'input',
      'attr' => ['type' => 'range'],
      'hide' => 1,
    ],
    self::TYPE_ILLUST   =>
    [
      'name' => '图文',
      'tag'  => 'illust',
      'attr' => ['type' => ''],
    ],
    self::TYPE_GOODS    =>
    [
      'name' => '商品',
      'tag'  => 'goods',
      'attr' => ['type' => ''],
    ],
    self::TYPE_ROOMS    =>
    [
      'name' => '包间',
      'tag'  => 'rooms',
      'attr' => ['type' => ''],
      'hide' => 1,
    ],
    self::TYPE_BSHOURS  =>
    [
      'name' => '营业时间',
      'tag'  => 'bshours',
      'attr' => ['type' => ''],
    ],
  ];

  protected static $datas = [];

  // 自动完成
  protected $_auto =
  [
    ['sort','auto_int',self::MODEL_BOTH,'callback'],
    ['name','trim',self::MODEL_BOTH,'function'],
    ['model_id','auto_int',self::MODEL_BOTH,'callback'],
    ['model_ids','auto_join_ids',self::MODEL_BOTH,'callback'],
    ['attrs','auto_attrs',self::MODEL_BOTH,'callback'],
    ['create_time','time',self::MODEL_INSERT,'function'],
  ];

  public function __construct()
  {
    parent::__construct();
  }


  public function get_fields($map = [])
  {
    is_array($map) || $map =
    [
      '_complex' =>
      [
        '_logic'    => 'or',
        //'model_id'  => (int)$map,
        'model_ids' => ['like','%,'.$map.',%'],
      ],
    ];
    $map['delete_time'] = 0;
    $key = md5(json_encode($map));
    if(!isset(self::$datas[$key]))
    {
      $lst = $this->klist(true,$map,'sort,create_time,id');
      $lst = $this->complete_attrs($lst);
      self::$datas[$key] = $lst;
    }
    return self::$datas[$key];
  }

  public function get_fields_bykey($map = [])
  {
    $arr = $this->get_fields($map);
    $lst = [];
    foreach($arr as $v)
    {
      $lst[$v['key']] = $v;
    }
    return $lst;
  }

  public function complete_row($arr = [])
  {
    if(isset($arr['attrs']))
    {
      $arr = $this->attr2array_row($arr);
      isset($arr['attrs']['choices']) && $arr['choices'] = $this->get_choices_data($arr['attrs']['choices']);
    }
    isset($arr['model_ids']) && $arr['model_ids_array'] = $this->get_join_ids($arr['model_ids']);
    return $arr;
  }

  public function complete_all($arr = [])
  {
    return array_map(function($v)
    {
      return $this->complete_row($v);
    },$arr ?: []);
  }

  public function complete_attrs($lst = [])
  {
    return array_map(function($v)
    {
      $v = $this->attr2array_row($v);
      $v['choices'] = $this->get_choices_data($v['attrs']['choices']);
      return $v;
    },$lst ?: []);
  }

  public function get_choices_data($str = '')
  {
    $dat = array();
    if($str && preg_match_all('/([\d.]+)[ \t]*=[ \t]*([^\r\n]+)/isu',$str,$tmp))
    {
      $choices = array();
      if($tmp[0] && is_array($tmp[0])) foreach($tmp[0] as $i => $v)
      {
        $choices[$tmp[1][$i]] = $tmp[2][$i];
      }
      $dat['data'] = $choices;
      $dat['tree'] = $this->get_choices_tree($choices);
    }
    return $dat;
  }

  public function get_choices_tree($arr = array(),$key = '')
  {
    $dat = array();
    if($arr && is_array($arr)) foreach($arr as $k => $v)
    {
      $reg = (string)$key !== '' ? '/^'.$key.'\.[^.\s]+$/i' : '/^[^.\s]+$/i';
      if(preg_match($reg,$k)) $dat[$k] = $this->get_choices_tree($arr,$k);
    }
    return $dat;
  }

}