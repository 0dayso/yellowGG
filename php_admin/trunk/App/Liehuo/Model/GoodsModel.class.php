<?php
namespace Liehuo\Model;

class GoodsModel extends CjDatadwModel
{

  protected $redis_config = 'redis_user';

  public $redis_goods_discount = 'php_goods_discount';


  // 钻石商品
  public static $goods_diamond = [
      1001 => [
          //'name'        => '',
          'status'      => 0,//禁用
          'price'       => 3.00,
          'amount'      => 30,
          //'amount_give' => 0,
          //'give_first'  => 30,//首充赠送
          //'discount'    => '首充送30个',
          'iap_id'      => 'com.heymiao.miao.Diamonds1001.1',
          'pic'         => 'http://static.chujianapp.com/icon/diamond_1.png'
      ],
      1009 => [
          'price'       => 6.00,
          'amount'      => 60,
          'pic'         => 'http://static.chujianapp.com/icon/diamond_2.png'
      ],
      1007 => [
          'status'      => 0,//禁用
          'price'       => 12.00,
          'amount'      => 120,
          'iap_id'      => 'com.heymiao.miao.Diamonds1007.1',
          'pic'         => 'http://static.chujianapp.com/icon/diamond_2.png'
      ],
      1008 => [
          'price'       => 30.00,
          'amount'      => 300,
          'iap_id'      => 'com.heymiao.miao.Diamonds1008.1',
          'pic'         => 'http://static.chujianapp.com/icon/diamond_2.png'
      ],
      1002 => [
          'status'      => 0,//禁用
          'price'       => 45.00,
          'amount'      => 450,
          'iap_id'      => 'com.heymiao.miao.Diamonds1002.1',
          'pic'         => 'http://static.chujianapp.com/icon/diamond_2.png'
      ],
      1003 => [
          'price'       => 98.00,
          'amount'      => 980,
          'pic'         => 'http://static.chujianapp.com/icon/diamond_3.png'
      ],
      1004 => [
          'status'      => 0,//禁用
          'price'       => 198.00,
          'amount'      => 1980,
          'pic'         => 'http://static.chujianapp.com/icon/diamond_4.png'
      ],
      1010 => [
          'price'       => 298.00,
          'amount'      => 2980,
          'pic'         => 'http://static.chujianapp.com/icon/diamond_4.png'
      ],
      1005 => [
          'price'       => 588.00,
          'amount'      => 5880,
          'pic'         => 'http://static.chujianapp.com/icon/diamond_5.png'
      ],
      1006 => [
          'price'       => 1098.00,
          'amount'      => 10980,
          'pic'         => 'http://static.chujianapp.com/icon/diamond_6.png'
      ],
      1011 => [
          'price'       => 2098.00,
          'amount'      => 20980,
          'pic'         => 'http://static.chujianapp.com/icon/diamond_6.png'
      ],
  ];

  // 兑换钻石
  public static $goods_diamond_exchange = [
      1101 => [
          //'name'    => '',
          'glamour' => 100,
          'amount'  => 6,
      ],
      1102 => [
          'glamour' => 500,
          'amount'  => 30,
      ],
      1103 => [
          'glamour' => 1000,
          'amount'  => 60,
      ],
      1104 => [
          'glamour' => 5000,
          'amount'  => 300,
      ],
      1105 => [
          'glamour' => 10000,
          'amount'  => 600,
      ],
      1106 => [
          'glamour' => 50000,
          'amount'  => 3000,
      ],
  ];

  /*
   * 会员充值列表
   * name        商品名称
   * diamond     钻石个数（价格）
   * get_results 会员有效天数
   * describe    商品描述
   * discount    价格折扣描述
   * goods_type  1会员类型商品
   *
   * */
  const GOODS_VIP_1  = 901;
  const GOODS_VIP_3  = 902;
  const GOODS_VIP_6  = 904;
  const GOODS_VIP_12 = 903;

  public static $goods_vips = [
      self::GOODS_VIP_1 => [
          'name'        => '1个月',
          'diamond'     => 580,
          'price'       => 58,
          'get_results' => 31,
          'describe'    => '',
          'discount'    => '',
          'goods_type'  => 0,
          'recommended' => 0,
          'sort_by'     => 1,
      ],
      self::GOODS_VIP_3 => [
          'name'        => '3个月',
          'diamond'     => 680,
          'price'       => 68,
          'get_results' => 93,
          'describe'    => '',
          'discount'    => '',
          'goods_type'  => 0,
          'recommended' => 0,
          'sort_by'     => 2,
      ],

      self::GOODS_VIP_6 => [
          'name'        => '6个月',
          'diamond'     => 1080,
          'price'       => 108,
          'get_results' => 186,
          'describe'    => '',
          'discount'    => '',
          'goods_type'  => 0,
          'recommended' => 0,
          'sort_by'     => 3,
      ],

      self::GOODS_VIP_12 => [
          'name'        => '12个月',
          'diamond'     => 1080,
          'price'       => 108,
          'get_results' => 372,
          'describe'    => '',
          'discount'    => '',
          'goods_type'  => 0,
          'recommended' => 1,
          'sort_by'     => 4,
      ],
  ];

  /*
   * 喜欢购买列表
   * name        商品名称
   * diamond     钻石个数（价格）
   * get_results 喜欢个数
   * describe    商品描述
   * discount    价格折扣描述
   * goods_type  1会员类型商品
   * */
  const GOODS_LIKE_MAX = 100;//最多可购买

  public static $goods_like = [
      701 => [
          'name'        => '100个喜欢',
          'diamond'     => 60,
          'get_results' => 100,
          'describe'    => '',
          'discount'    => '',
          'goods_type'  => 0,
      ],
      702 => [
          'name'        => '100个喜欢',
          'diamond'     => 30,
          'get_results' => 100,
          'describe'    => '',
          'discount'    => '会员尊享5折优惠',
          'goods_type'  => 1,
      ],
      703 => [
          'name'        => '10个喜欢',
          'diamond'     => 0,
          'get_results' => 10,
          'describe'    => '',
          'discount'    => '分享获得10个喜欢',
          'goods_type'  => 2,
          'share_url'   => 'http://app.chujianapp.com/60.php?p=202&a=fst20',
          'share_pic'   => 'http://static.chujianapp.com/icon/share_thumb.png',
          'share_title' => '朋友圈疯传，已玩疯',
          'share_desc'  => '全是帅哥美女的高颜值刷脸社交，这个APP有毒',
      ]
  ];

  /*
   * 超喜欢购买列表
   * name        商品名称
   * diamond     钻石个数（价格）
   * get_results 超喜欢个数
   * describe    商品描述
   * discount    价格折扣描述
   * goods_type  1会员类型商品
   * */
  const SUPER_LIKE_MAX = 6;

  public static $goods_super_like = [
      801 => [
          'name'        => '6个超喜欢',
          'diamond'     => 60,
          'get_results' => 6,
          'describe'    => '多送5个超喜欢',
          'discount'    => '',
          'goods_type'  => 0,
      ],
      802 => [
          'name'        => '6个超喜欢',
          'diamond'     => 30,
          'get_results' => 6,
          'describe'    => '多送5个超喜欢',
          'discount'    => '会员尊享5折优惠',
          'goods_type'  => 1,
      ],
      803 => [
          'name'        => '1个超喜欢',
          'diamond'     => 0,
          'get_results' => 1,
          'describe'    => '',
          'discount'    => '分享获得1个超喜欢',
          'goods_type'  => 2,
          'share_url'   => 'http://app.chujianapp.com/60.php?p=202',
          'share_pic'   => 'http://static.chujianapp.com/icon/share_thumb.png',
          'share_title' => '朋友圈疯传，已玩疯',
          'share_desc'  => '全是帅哥美女的高颜值刷脸社交，这个APP有毒',
      ]
  ];



  /*
   * 礼物列表
   * 礼物id   id;
   * 价格         price;
     键盘预览图  pic;
     动画       preview;
     提示词集合  guide_words;
     名称          name
   *
   * */
  public static $goods_gift = [
      2010 => [
          'id'          => 2010,
          'name'        => '愚人节纪念(特惠1折)',
          'diamond'     => 30,
          'pic'         => 'http://static.chujianapp.com/gift/gift_013_icon.png?v=6',
          'background'  => 'http://static.chujianapp.com/gift/gift_013_bg.jpg?v=6',
          'background_color' => 'ffc000',
          'preview'     => 'http://static.chujianapp.com/gift/gift_013_fg.png?v=6',
          'guide_words' => [
              '可乐被我摇过了，喷到你了吗？',
              '送你瓶可乐，喝前先摇一摇',
              '愚人节快乐..!!',
              '愚人节，应景要不要愚弄一下？',
              '送你一瓶可乐，我帮你打开了..'
          ]
      ],
      2011 => [
          'id'          => 2011,
          'name'        => '"污",约吗(特惠1折)',
          'diamond'     => 30,
          'pic'         => 'http://static.chujianapp.com/gift/gift_014_icon.png?v=1',
          'background'  => 'http://static.chujianapp.com/gift/gift_014_bg.jpg?v=1',
          'background_color' => '155700',
          'preview'     => 'http://static.chujianapp.com/gift/gift_014_fg.png?v=1',
          'guide_words' => [
              '想不想革命的友谊升华一下',
              '少年，可否一战',
              '一个人寂寞空虚冷，那么俩个人呢',
              '想拴住你的人，还有你的心',
              '拥有你最好的方式，是不是这样'
          ]
      ],
      2012 => [
          'id'          => 2012,
          'name'        => '春季去踏青(特惠1折)',
          'diamond'     => 30,
          'pic'         => 'http://static.chujianapp.com/gift/gift_012_icon.png?v=6',
          'background'  => 'http://static.chujianapp.com/gift/gift_012_bg.jpg?v=6',
          'background_color' => '604850',
          'preview'     => 'http://static.chujianapp.com/gift/gift_012_fg.png?v=6',
          'guide_words' => [
              '十里春风，不如遇见你',
              '你那么美，赏个春游行不行',
              '很想知道和你踏青是什么感觉',
              '真想陪你一起去踏青',
              '你就是我的春天'
          ]
      ],
      2001 => [
          'id'          => 2001,
          'name'        => '书',
          'diamond'     => 300,
          'pic'         => 'http://static.chujianapp.com/gift/gift_001_icon.png?v=5',
          'background'  => 'http://static.chujianapp.com/gift/gift_001_bg.jpg?v=5',
          'background_color' => '1c37af',
          'preview'     => 'http://static.chujianapp.com/gift/gift_001_fg.png?v=5',
          'guide_words' => [
              '想约你一起看书~',
              '书中的颜如玉都不如你惊艳！',
              '你是一本我想要读懂的书。',
              '你是一本我想读一生的书。',
              '我把爱藏在书中角落，找到了吗？'
          ]
      ],
      2002 => [
          'id'          => 2002,
          'name'        => '音乐',
          'diamond'     => 500,
          'pic'         => 'http://static.chujianapp.com/gift/gift_002_icon.png?v=5',
          'background'  => 'http://static.chujianapp.com/gift/gift_002_bg.jpg?v=5',
          'background_color' => '36335f',
          'preview'     => 'http://static.chujianapp.com/gift/gift_002_fg.png?v=5',
          'guide_words' => [
              '想约你一起听音乐~',
              '我的音乐MV主角是你~',
              '音乐让原本素不相识的我们相遇~',
              '你的魅力超越音乐势不可挡！',
              '音乐传递爱意，它说：我爱你。',
          ]
      ],
      2003 => [
          'id'          => 2003,
          'name'        => '电影',
          'diamond'     => 500,
          'pic'         => 'http://static.chujianapp.com/gift/gift_003_icon.png?v=5',
          'background'  => 'http://static.chujianapp.com/gift/gift_003_bg.jpg?v=5',
          'background_color' => '0be6cb',
          'preview'     => 'http://static.chujianapp.com/gift/gift_003_fg.png?v=5',
          'guide_words' => [
              '想约你去看电影~',
              '爱在午夜降临前，如此妙不可言~',
              '我想和你演绎爱情电影~',
              '和我一起看真爱至上吧！',
              '我希望自己的爱情电影主角是你！',
          ]
      ],
      2004 => [
          'id'          => 2004,
          'name'        => '美食',
          'diamond'     => 500,
          'pic'         => 'http://static.chujianapp.com/gift/gift_004_icon.png?v=5',
          'background'  => 'http://static.chujianapp.com/gift/gift_004_bg.jpg?v=5',
          'background_color' => 'f28f20',
          'preview'     => 'http://static.chujianapp.com/gift/gift_004_fg.png?v=5',
          'guide_words' => [
              '想与你一起分享美食~',
              '唯有爱与美食不可辜负~',
              '吃货的世界只有吃货才懂。',
              '和你共享美食是我的追求！',
              '美食已上桌，共进晚餐吗？',
          ]
      ],
      2005 => [
          'id'          => 2005,
          'name'        => '游戏',
          'diamond'     => 500,
          'pic'         => 'http://static.chujianapp.com/gift/gift_005_icon.png?v=5',
          'background'  => 'http://static.chujianapp.com/gift/gift_005_bg.jpg?v=5',
          'background_color' => '491348',
          'preview'     => 'http://static.chujianapp.com/gift/gift_005_fg.png?v=5',
          'guide_words' => [
              '这场爱情游戏，我认真了，你呢？',
              '一起在游戏世界里打怪升级吧！',
              '想成为你的游戏手柄，被你掌控！',
              '陪你打游戏是对你最深情的告白！',
              '想约你一起打游戏~',
          ]
      ],
      2006 => [
          'id'          => 2006,
          'name'        => '宠物',
          'diamond'     => 500,
          'pic'         => 'http://static.chujianapp.com/gift/gift_006_icon.png?v=5',
          'background'  => 'http://static.chujianapp.com/gift/gift_006_bg.jpg?v=5',
          'background_color' => 'c56169',
          'preview'     => 'http://static.chujianapp.com/gift/gift_006_fg.png?v=5',
          'guide_words' => [
              '想送你的爱宠一份礼物~',
              '愿你的宠物健康成长！',
              '我想和你的宠物争夺你的爱！',
              '请给我机会来爱你的萌宠吧！',
              '你愿意和我一起养只爱的宠物吗？',
          ]
      ],
      2007 => [
          'id'          => 2007,
          'name'        => '运动',
          'diamond'     => 500,
          'pic'         => 'http://static.chujianapp.com/gift/gift_007_icon.png?v=5',
          'background'  => 'http://static.chujianapp.com/gift/gift_007_bg.jpg?v=5',
          'background_color' => '90c35a',
          'preview'     => 'http://static.chujianapp.com/gift/gift_007_fg.png?v=5',
          'guide_words' => [
              '想约你一起去打球~',
              '球拍我都有，球友就差一个你咯！',
              '只要你想运动，我愿奉陪到底！',
              '你愿意和我一起去打球吗？',
              '你爱的运动我都喜欢！',
          ]
      ],
      2008 => [
          'id'          => 2008,
          'name'        => '品牌',
          'diamond'     => 4880,
          'pic'         => 'http://static.chujianapp.com/gift/gift_008_icon.png?v=5',
          'background'  => 'http://static.chujianapp.com/gift/gift_008_bg.jpg?v=5',
          'background_color' => '000000',
          'preview'     => 'http://static.chujianapp.com/gift/gift_008_fg.png?v=5',
          'guide_words' => [
              '想陪你逛街送你爱的品牌~',
              'Dior再好也比不过你！',
              'PRADA远不如你的美腻~',
              '你就像是奢侈品，做梦都想拥有。',
              '对你爱不完，想为你买买买！',
          ]
      ],
      2009 => [
          'id'          => 2009,
          'name'        => '旅行',
          'diamond'     => 6180,
          'pic'         => 'http://static.chujianapp.com/gift/gift_009_icon.png?v=5',
          'background'  => 'http://static.chujianapp.com/gift/gift_009_bg.jpg?v=5',
          'background_color' => 'f6cad1',
          'preview'     => 'http://static.chujianapp.com/gift/gift_009_fg.png?v=5',
          'guide_words' => [
              '想约你一起去旅行~',
              '世界那么大，和我去看看吗？',
              '机票已备好，只差一个你！',
              '来一场说走就走的旅行吗？',
              '背上行囊，一起去旅游吧！',
          ]
      ],
  ];


  const LIVE_GIFT_CHEST = 3008;//宝箱

  /*
   * 直播礼物
   * */
  public static $goods_gift_live = [
      3001 => [
          'name'     => '玫瑰',
          'diamond'  => 1,
          'pic'      => 'http://static.chujianapp.com/images/201605/c6a500600d6ffafcabec09a005ded921.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3003 => [
          'name'     => '香蕉',
          'diamond'  => 3,
          'pic'      => 'http://static.chujianapp.com/images/201605/d909401609d50a2b920e87f1178a7f88.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3004 => [
          'name'     => '小皮鞭',
          'diamond'  => 5,
          'pic'      => 'http://static.chujianapp.com/images/201605/8eb48a08b05499013589918624b294ce.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3005 => [
          'name'     => '棒棒糖',
          'diamond'  => 10,
          'pic'      => 'http://static.chujianapp.com/images/201605/88f96bebce35a8d9f7459139f20fcf54.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3006 => [
          'name'     => '小金人',
          'diamond'  => 365,
          'pic'      => 'http://static.chujianapp.com/images/201605/d7f3af2a90f43818690e03aac5d2e859.png',
          'is_combo' => 0,
          'animate'  => 'goldman',
      ],
      3007 => [
          'name'     => '1314',
          'diamond'  => 1314,
          'pic'      => 'http://static.chujianapp.com/images/201605/fde378f126b67abb81a99cc55bf008b0.png',
          'is_combo' => 0,
          'animate'  => '1314',
      ],
      3008 => [
          'name'     => '流星雨',
          'diamond'  => 12888,
          'pic'      => 'http://static.chujianapp.com/images/201605/e47e52ae7ecb7883ed50a466fa9494d5.png',
          'is_combo' => 0,
          'animate'  => 'fireworks',
      ],
      3009 => [
          'name'     => '烈火羽毛',
          'diamond'  => 1,
          'pic'      => 'http://static.chujianapp.com/images/201605/6e09082d1b1adaa5205a604064586dc0.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3002 => [
          'name'     => '么么哒',
          'diamond'  => 2,
          'pic'      => 'http://static.chujianapp.com/images/201605/7db18d071b19b08006bafc20adebbb2f.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3011 => [
          'name'     => '肥皂',
          'diamond'  => 5,
          'pic'      => 'http://static.chujianapp.com/images/201605/be2c731948c3ea1fd8fe30976f6407d7.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3012 => [
          'name'     => '再来一次',
          'diamond'  => 16,
          'pic'      => 'http://static.chujianapp.com/images/201605/d1d1dc67beb4318fa5c31e96a078bd34.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3013 => [
          'name'     => '暴力熊',
          'diamond'  => 88,
          'pic'      => 'http://static.chujianapp.com/images/201605/9c5c7d07d722a6875dbd2454bab7cd45.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3014 => [
          'name'     => '皇冠',
          'diamond'  => 288,
          'pic'      => 'http://static.chujianapp.com/images/201605/1b1270039c8d4852b671a1416923c6ed.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3015 => [
          'name'     => '666',
          'diamond'  => 666,
          'pic'      => 'http://static.chujianapp.com/images/201605/4e4db58c79187c848b9798d8629662dd.png?err',
          'is_combo' => 1,
          'animate'  => '666',
      ],
      3016 => [
          'name'     => '法拉利',
          'diamond'  => 1800,
          'pic'      => 'http://static.chujianapp.com/images/201605/bb8516ca877b8197f70988be141abce1.png',
          'is_combo' => 0,
          'animate'  => 'car',
      ],
      3010 => [
          'name'     => '干杯',
          'diamond'  => 2,
          'pic'      => 'http://static.chujianapp.com/images/201605/898e159bf016453d8d007a7d97167394.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3018 => [
          'name'     => '麻辣烫',
          'diamond'  => 6,
          'pic'      => 'http://static.chujianapp.com/images/201605/f3ad25aadb0a80ded1b4d6ce51039327.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3017 => [
          'name'     => '爽歪歪',
          'diamond'  => 10,
          'pic'      => 'http://static.chujianapp.com/images/201605/8fe1763323d33fc84ff06d9175c8784e.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3019 => [
          'name'     => '钻戒',
          'diamond'  => 168,
          'pic'      => 'http://static.chujianapp.com/images/201605/4744c977146a5f11b22a08f8a85582bf.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3020 => [
          'name'     => '飞机',
          'diamond'  => 3000,
          'pic'      => 'http://static.chujianapp.com/images/201605/0cbe1d98f1d6c9147f523e8ae2f36d94.png',
          'is_combo' => 0,
          'animate'  => 'plane',
      ],
      3801 => [
          'name'     => '药',
          'diamond'  => 2,
          'pic'      => 'http://static.chujianapp.com/images/201605/0cbe1d98f1d6c9147f523e8ae2f36d94.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3802 => [
          'name'     => '雪糕',
          'diamond'  => 4,
          'pic'      => 'http://static.chujianapp.com/images/201605/0cbe1d98f1d6c9147f523e8ae2f36d94.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3803 => [
          'name'     => '甜甜圈',
          'diamond'  => 10,
          'pic'      => 'http://static.chujianapp.com/images/201605/0cbe1d98f1d6c9147f523e8ae2f36d94.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
      3804 => [
          'name'     => 'KJ制服',
          'diamond'  => 98,
          'pic'      => 'http://static.chujianapp.com/images/201605/0cbe1d98f1d6c9147f523e8ae2f36d94.png',
          'is_combo' => 1,
          'animate'  => 'batter',
      ],
  ];

  const GOLD_LIKE = 300;

  public static $goods_gold_like = [
      self::GOLD_LIKE => [
          'name'    => '一见倾心',
          'diamond' => 300,
          'glamour' => 9000,
      ],
  ];

  const LUCKY_BAG = 600;//福袋ID

  const BROADCAST = 1100;//广播、喇叭

  public static $goods_broadcast = [
      self::BROADCAST => [
          'name'    => '广播',
          'diamond' => 888,
      ],
  ];

  const HOT_TICKET = 3800;//热门票

  public static $goods_hot_ticket = [
      self::HOT_TICKET => [
          'name'     => '热门票',
          'diamond'  => 1000,
          'duration' => 300,//热门置顶时长 秒
      ],
  ];


  /*
   * 直播进房特效
   * */
  const LIVE_EFFECT_DEFAULT = 4001;

  public static $goods_live_effects = [
      4001 => [
          'name'    => '默认特效',
          'text'    => '进入房间',
          'icon'    => 'http://static.chujianapp.com/images/201608/dec630ca7a5acd416ba3b9fd7f534440.png',
          'image'   => '',
          'diamond' => 0,
          'glory_limit' => 13,
      ],
      4002 => [
          'name'    => '小黄鸭',
          'text'    => '骑着小黄鸭挥手，萌傻帅啊..',
          'icon'    => 'http://static.chujianapp.com/images/201608/de94fc780dcb0808f473b7773c5f19ba.png',
          'image'   => 'http://static.chujianapp.com/images/201608/5baa2eb1a669f2ab68caf601d85c3f1b.png',
          'diamond' => 1500,
          'glory_limit' => 13,
      ],
      4003 => [
          'name'    => '白马王子',
          'text'    => '骑着白马缓缓袭来，快快让道..',
          'icon'    => 'http://static.chujianapp.com/images/201608/1af1b3234998387935ed5e8e12d14884.png',
          'image'   => 'http://static.chujianapp.com/images/201608/9c61e5019dc14d68fcb0ee9b07a79d59.png',
          'diamond' => 5000,
          'glory_limit' => 16,
      ],
      4004 => [
          'name'    => 'Boss座驾宝马',
          'text'    => '开着宝马进入，Boss风范有没有..',
          'icon'    => 'http://static.chujianapp.com/images/201608/62ab3b02843c3b50b8303be2b7e13cb7.png',
          'image'   => 'http://static.chujianapp.com/images/201608/0379c808586ee08a855ee2db6eae303f.png',
          'diamond' => 1000,
          'glory_limit' => 16,
      ],
      4005 => [
          'name'    => '保时捷卡宴',
          'text'    => '开着保时捷进入，有认识我的吗？',
          'icon'    => 'http://static.chujianapp.com/images/201608/f90c18b5436ba2a945a9d4b3ac372ffc.png',
          'image'   => 'http://static.chujianapp.com/images/201608/f2565b4238d89b6e90b946b3abd9357e.png',
          'diamond' => 3000,
          'glory_limit' => 16,
      ],
      4006 => [
          'name'    => '藤原家的AE86',
          'text'    => '极速漂移过弯，闪现在大家眼前..',
          'icon'    => 'http://static.chujianapp.com/images/201608/27cacec6b0b73a32f928b6f49bb679f8.png',
          'image'   => 'http://static.chujianapp.com/images/201608/7e90f0026e615a64a596f998b56f6f7a.png',
          'diamond' => 8600,
          'glory_limit' => 16,
      ],
      4007 => [
          'name'    => '布加迪限量超跑',
          'text'    => '开着布加迪进入，请仰望..',
          'icon'    => 'http://static.chujianapp.com/images/201608/473c352ed39fe1bb63738945bf93cc28.png',
          'image'   => 'http://static.chujianapp.com/images/201608/cb8f8ced6a403ecd3a4a759fb9218656.png',
          'diamond' => 12000,
          'glory_limit' => 21,
      ],
      4008 => [
          'name'    => '时光机',
          'text'    => '开着时光机进入，请仰望..',
          'icon'    => 'http://static.chujianapp.com/images/201608/43849445185cab931a4949cd602f54da.png',
          'image'   => 'http://static.chujianapp.com/images/201608/51ab43a1eb19771d496cd2173f932633.png',
          'vibrate' => 1,
          'diamond' => 12000,
          'glory_limit' => 25,
      ],
  ];



  /*
   * 获取商品的优惠信息
   * */
  public function getDiscountByGoods($gid = 0,$dat = [])
  {
      $lst = $this->getDiscounts() ?: [];
      $dls = $lst[$gid] ?: [];
      foreach($dls as $v)
      {
          $stm = (int)$v['discount_stime'];
          $etm = (int)$v['discount_etime'];
          if($stm <= NOWTIME && $etm >= NOWTIME)
          {
              $dat = array_merge($dat ?: [],$v);
              break;
          }
      }
      return $dat ?: [];
  }

  /*
   * 获取优惠活动列表
   * */
  public function getDiscounts()
  {
      if(!is_array($this->goods_discount))
      {
          $arr = $this->getRedis()->zRange($this->redis_goods_discount,NOWTIME - 1,NOWTIME + 60 * 60 * 24) ?: [];
          if($arr)
          {
              $this->goods_discount = [];
              foreach($arr as $v)
              {
                  $row = json_decode($v,true);
                  if(!$row) continue;
                  $gid = (int)$row['goods_id'];
                  if(!$gid) continue;
                  $this->goods_discount[$gid][] = $row;
              }
          }
      }
      return $this->goods_discount ?: [];
  }


  // 获取折扣信息
  public function get_discount($id = '')
  {
    $key = 'php_goods_discount_'.$id;
    $rds = $this->get_redis();
    $dat = $rds->get($key);
    is_string($dat) && $dat = json_decode($dat,true) ?: [];
    return $dat;
  }

  // 获取折扣信息
  public function get_discounts($ids = [])
  {
    $dat = [];
    $ids = array_values($ids ?: []);
    $kys = array_map(function($v)
    {
      return 'php_goods_discount_'.$v;
    },$ids ?: []);
    if($kys)
    {
      $rds = $this->get_redis();
      foreach($rds->mGet($kys) ?: [] as $i => $v)
      {
        $dat[$ids[$i]] = is_string($v) ? json_decode($v,true) : $v;
      }
    }
    return $dat;
  }

  // 保存折扣信息
  public function set_discount($dat = [])
  {
    $dat['id'] || $dat['id'] = md5(uniqid(rand(),true).rand());
    $key = 'php_goods_discount_'.$dat['id'];
    $rds = $this->get_redis();
    $ret = $rds->set($key,is_string($dat) ? $dat : json_encode($dat));
    if($ret)
    {
      $rds->zAdd($this->redis_goods_discount,$dat['discount_etime'],$dat['id']);
      $ret = $dat;
    }
    return $ret;
  }

  // 删除折扣信息
  public function del_discount($id = '')
  {
    $key = 'php_goods_discount_'.$id;
    $rds = $this->get_redis();
    $ret = $rds->zRem($this->redis_goods_discount,$id);
    $ret = $rds->del($key) || $ret;
    return $ret;
  }

}