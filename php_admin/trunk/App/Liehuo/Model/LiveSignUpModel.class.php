<?php
namespace Liehuo\Model;

class LiveSignUpModel extends CjAdminModel
{

  const TYPE_NONE     = 0;
  const TYPE_YANZHI   = 1;
  const TYPE_CAIYI    = 2;
  const TYPE_LIAOTIAN = 3;
  const TYPE_HULIAO   = 4;
  const TYPE_TESHU    = 5;

  public $types =
  [
    self::TYPE_YANZHI   => '颜值',
    self::TYPE_CAIYI    => '才艺',
    self::TYPE_LIAOTIAN => '聊天',
    self::TYPE_HULIAO   => '互撩',
  ];

  const STYLE_GAOXIAO = 1;
  const STYLE_QIPA    = 2;
  const STYLE_MAIMENG = 3;
  const STYLE_XINGGAN = 4;

  public $styles =
  [
    self::STYLE_GAOXIAO => '搞笑',
    self::STYLE_QIPA    => '奇葩',
    self::STYLE_MAIMENG => '卖萌',
    self::STYLE_XINGGAN => '性感',
  ];

  public function __construct()
  {
    parent::__construct();
  }

}