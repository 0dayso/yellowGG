<?php
namespace Liehuo\Controller;
use Think\Controller;

class NavigationController extends Controller
{

  public function __construct()
  {
    parent::__construct();

    // 路径导航
    $this->breadcrumbs =
    [
      'Common/index'   => '管理后台',
      'UserBase/index' =>
      [
        'name' => '用户管理',
        'subs' =>
        [
          'UserBase/view' =>
          [
            'name' => '用户详情',
            'link' => true,
          ],
          'UserBase/punished'    => '警告/封禁',
          'UserBase/device_list' => '设备封禁',
          'ReportBase/index'     => '举报处理',
        ],
      ],
      'Content/manage' =>
      [
        'name' => '内容管理',
        'link' => '#',
        'subs' =>
        [
          'UserBase/avatar_list'         => function()
          {
            $ret = '相册管理';
            if($_REQUEST['type'] == '3') $ret = '视频管理';
            return $ret;
          },
          'UserBase/text_modify_request' => '文字审核',
          'Scoring/index'                =>
          [
            'name' => '用户评级',
            'subs' =>
            [
              'Scoring/logs' => '打分记录',
            ],
          ],
        ],
      ],
      'Operating/tools' =>
      [
        'name' => '运营工具',
        'link' => '#',
        'subs' =>
        [
          'ReportBase/index'   => '举报处理',
          'UserBase/cash_list' => '提现处理',
          'Data/monit'         =>
          [
            'name' => '数据监控',
            'link' => '#',
            'subs' =>
            [
              'UserBase/chat_logs'  => '聊天监控',
              'UserBase/order_list' => '购买监控',
              'UserBase/oper_logs'  => '操作日志',
            ],
          ],
        ],
      ],
      'Analy/index' =>
      [
        'name' => '统计报表',
        'link' => '#',
        'subs' =>
        [
          'Analy/adver_stat'  => '推广数据',
          'Analy/daily_analy' => '总体行为',
          'Analy/daily_thumb' => '点赞分布',
          'Analy/subject'     =>
          [
            'name' => '分析专题',
            'link' => '#',
            'subs' =>
            [
              'Analy/been_slide'            => '曝光分布',
              'Analy/been_thumb'            => '被赞分布',
              'Analy/match_rate'            => '匹配分布',
              'Analy/stat_retention'        =>
              [
                'name' => '次留分析',
                'subs' =>
                [
                  'Analy/stat_retention_detail' =>
                  [
                    'name' => function()
                    {
                      $ret = '次留分析';
                      if($_REQUEST['type'] == 'slide')      $ret = '滑动';
                      if($_REQUEST['type'] == 'thumb')      $ret = '点赞';
                      if($_REQUEST['type'] == 'been_slide') $ret = '曝光';
                      if($_REQUEST['type'] == 'been_thumb') $ret = '被赞';
                      if($_REQUEST['type'] == 'match')      $ret = '匹配';
                      if($_REQUEST['type'] == 'chat')       $ret = '聊天';
                      return $ret;
                    },
                    'link' => true,
                  ],
                ],
              ],
              'Analy/hourly_slide'          => '每时滑动',
            ],
          ],
        ],
      ],
      // 示例
      'Test/1' =>
      [
        'name' => 'Test 1',
        'link' => '#',
        'subs' =>
        [
          'Test/2' =>
          [
            'name' => 'Test 2',
            // 支持闭包
            'link' => function()
            {
              return '##';
            },
            'subs' =>
            [
              'Test/3' =>
              [
                'name' => function()
                {
                  return $_REQUEST['name'] ?: 'Test 3';
                },
                'link' => '#',
                'subs' =>
                [
                  'Test/4' =>
                  [
                    'name' => 'Test 4',
                    'link' => true,//链接自动添加GET参数
                    'subs' =>
                    [
                      'Test/test_breadcrumbs' => 'Test ok',
                    ],
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
    ];
    $this->breadcrumb_list = $this->get_breadcrumb_list();
  }

  // 获取当前路径导航
  public function getNavPath($key = true,&$dat = [])
  {
    $key === true && $key = CONTROLLER_NAME.'/'.ACTION_NAME;
    $lst = $this->breadcrumb_list;
    if(isset($lst[$key]))
    {
      $v = $lst[$key] ?: [];
      $nam = $v['name'];
      $nam instanceof \Closure && $nam = $nam();
      $lnk = isset($v['link']) ? $v['link'] : $key;
      $lnk === '#' && $lnk = false;
      if($lnk instanceof \Closure) $url = $lnk();
      elseif($lnk === true)  $url = U($key,$_GET);
      elseif($lnk === false) $url = '#';
      else                   $url = U($lnk);
      array_unshift($dat,
      [
        'title' => $nam,
        'url'   => $url,
      ]);
      isset($lst[$v['parent']]) && $this->getNavPath($v['parent'],$dat);
    }
    return $dat;
  }

  // 获取一维导航数据 递归
  public function get_breadcrumb_list($arr = true,&$dat = [],$parent = false)
  {
    $arr === true && $arr = $this->breadcrumbs;
    foreach($arr ?: [] as $k => $v)
    {
      is_array($v) || $v = ['name' => $v];
      $parent && $v['parent'] = $parent;
      $subs = $v['subs'];
      unset($v['subs']);
      $dat[$k] = $v;
      is_array($subs) && $subs && $this->get_breadcrumb_list($subs,$dat,$k);
    }
    return $dat;
  }

}