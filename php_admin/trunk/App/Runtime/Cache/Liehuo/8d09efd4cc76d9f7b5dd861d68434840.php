<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="renderer" content="webkit">
<title>初见后台</title>
<meta name="keywords" content="初见">
<!-- <link rel="stylesheet" href="/Public/js/webuploader/webuploader.css" />
<link rel='stylesheet' href='/Public/cms/justifiedgallery.min.css' type='text/css' media='all' />
<link rel='stylesheet' href='/Public/cms/swipebox.css' type='text/css' media='screen' />
<link rel='stylesheet' href='/Public/cms/colorbox.css' type='text/css' media='screen' /> 
<link rel="stylesheet" href="/Public/datepicker/css/datepicker.css" />-->
<link rel="stylesheet" href="/Public/css/emoji.css" />
<link href="/Public/js/plugins/jquery.jqplot.min.css" rel="stylesheet">
<link href="/Public/css/update/bootstrap.min.css?v=3.3.0" rel="stylesheet">
<link href="/Public/css/update/font-awesome.css?v=4.3.0" rel="stylesheet">
<link href="/Public/css/update/custom.css" rel="stylesheet">
<link href="/Public/css/update/animate.css" rel="stylesheet">
<link href="/Public/css/update/style.css?v=2.1.0" rel="stylesheet">
<link rel="stylesheet" href="/Public/css/emoji.css" />
<!-- Mainly scripts -->
<script src="/Public/js/update/jquery-2.1.1.min.js"></script>
<script src="/Public/js/update/jquery-ui-1.10.4.min.js"></script>
<script src="/Public/js/update/bootstrap.min.js"></script>
<script src="/Public/js/update/jquery.metisMenu.js"></script>
<script src="/Public/js/update/jquery.slimscroll.min.js"></script>
<!-- Custom and plugin javascript -->
<script src="/Public/js/update/hplus.js"></script>
<script src="/Public/js/update/pace.min.js"></script>
<!-- iCheck -->
<script src="/Public/js/update/icheck.min.js"></script>
<!--弹出框-->
<script src="/Public/layer/layer.min.js"></script>
</head>
<body class=" pace-done">
<div class="pace  pace-inactive"><div class="pace-progress" data-progress-text="100%" data-progress="99" style="width: 100%;">
  <div class="pace-progress-inner"></div>
</div>
<div class="pace-activity"></div></div>
  <div id="wrapper">
    <nav class="navbar-default navbar-static-side" role="navigation">
      <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">

          <li class="nav-header">
            <div class="dropdown profile-element"> <span>
              <img alt="image" class="img-circle" src="/Public/js/update/chujian.png" width="64" height="64">
               </span>
              <a data-metisMenu="metisMenu" class="dropdown-toggle" href="javascript:void(0)">
                <span class="clear" style="margin-top:4px">
                  <span class=" m-t-xs"><strong class="font-bold"><?php echo $_SESSION['nickname']; ?></strong></span>
                  <span class="text-muted text-xs ">
                    管理员 <b class="caret"></b>
                  </span>
                </span>
              </a>
              <ul class="dropdown-menu animated fadeInRight m-t-xs">
                <li>
                  <a href="javascript:void(0)" style="display:block;overflow:hidden;float:right">
                    <i class="fa fa-power-off close-power" style="float:right;width:4px" ></i>
                  </a>
                </li>
                <li><a href="/index.php/admin_info/index" target="_blank">我的个人信息</a></li>
                <!--li><a href="/index.php/admin_task/index">我的任务</a></li-->
                <li class="divider"></li>
                <li><a href="<?php echo U('common/logout');?>">安全退出</a></li>
              </ul>
            </div>
            <div class="logo-element">H+</div>
          </li>

          <li>
            <a href="javascript:void(0)">
              <i class="fa fa-users"></i>
              <span class="nav-label">用户管理</span>
              <span class="fa arrow"></span>
            </a>
            <ul class="nav nav-second-level collapse">
              <li><a class="auto-openli" href="<?php echo U('user_base/index');?>">用户列表</a></li>
              <li><a class="auto-openli" href="<?php echo U('user_base/punished');?>">警告/封禁列表</a></li>
              <li><a class="auto-openli" href="<?php echo U('user_base/device_list');?>">设备封禁</a></li>
              <!--li><a class="auto-openli" href="<?php echo U('user_base/accusation_logs');?>">警告记录</a></li-->
            </ul>
          </li>

          <li>
            <a href="javascript:void(0)">
              <i class="fa fa-photo"></i>
              <span class="nav-label">内容管理</span>
              <span class="fa arrow"></span>
            </a>
            <ul class="nav nav-second-level collapse">
              <li><a class="auto-openli" href="<?php echo U('scoring/index');?>">用户评级</a></li>
              <li><a class="auto-openli" href="<?php echo U('user_base/avatar_list?audited=0&filter=unscored');?>">照片审核</a></li>
              <li><a class="auto-openli" href="<?php echo U('user_base/avatar_list?audited=0&type=3');?>">视频审核</a></li>
              <!--li><a class="auto-openli" href="<?php echo U('user_base/avatar_list?filter=scored');?>">打分记录</a></li-->
              <li><a class="auto-openli" href="<?php echo U('user_base/text_modify_request?operation=0');?>">文字审核</a></li>
              <!--li><a class="auto-openli" href="<?php echo U('user_base/text_repeat');?>">重复聊天</a></li-->
              <li><a class="auto-openli" href="<?php echo U('article/index');?>">文章管理</a></li>
            </ul>
          </li>

          <li>
            <a href="javascript:void(0)">
              <i class="fa fa-puzzle-piece"></i>
              <span class="nav-label">运营工具</span>
              <span class="fa arrow"></span>
            </a>
            <ul class="nav nav-second-level collapse">
              <li><a class="auto-openli" href="<?php echo U('promo/mass_send');?>" data-href="<?php echo U('search/adminsend');?>">群发消息</a></li>
              <!--li><a class="auto-openli" href="<?php echo U('feedback/unreads');?>">意见反馈</a></li-->
              <li><a class="auto-openli" href="<?php echo U('report_base/index?status=0');?>">举报处理</a></li>
              <li><a class="auto-openli" href="<?php echo U('user_base/cash_list');?>">提现处理</a></li>
              <!--li><a class="auto-openli" href="<?php echo U('setting/sensitive_words');?>">敏感词库</a></li-->
              <li>
                <a href="javascript:void(0)">数据监控<span class="fa arrow"></span></a>
                <ul class="nav nav-third-level collapse">
                  <li><a class="auto-openli" href="<?php echo U('user_base/chat_logs');?>">聊天监控</a></li>
                  <li><a class="auto-openli" href="<?php echo U('user_base/order_list');?>">充值监控</a></li>
                  <li><a class="auto-openli" href="<?php echo U('user_base/order_diamond_list');?>">购买监控</a></li>
                  <!--li><a class="auto-openli" href="<?php echo U('user_base/order_list?ver=1');?>">购买监控(旧版)</a></li-->
                  <!--li><a class="auto-openli" href="<?php echo U('user_base/fee_record');?>">收支监控</a></li-->
                  <li><a class="auto-openli" href="<?php echo U('user_base/coupon_list?type=2');?>">金星超赞</a></li>
                  <li><a class="auto-openli" href="<?php echo U('user_base/coupon_list?type=3');?>">礼物记录</a></li>
                  <!--li><a class="auto-openli" href="<?php echo U('activity/lucky_bags');?>">烈火福袋</a></li-->
                  <li><a class="auto-openli" href="<?php echo U('user_base/oper_logs');?>">操作日志</a></li>
                  <li><a class="auto-openli" href="<?php echo U('scoring/logs');?>">打分团记录</a></li>
                  <!--li><a class="auto-openli" href="<?php echo U('activity/pubs?activity_id=1001');?>">红包分享活动</a></li-->
                </ul>
              </li>
              <li><a class="auto-openli" href="<?php echo U('user_base/give_bat');?>">批量奖励</a></li>
              <li>
                <a href="javascript:void(0)">APP配置<span class="fa arrow"></span></a>
                <ul class="nav nav-third-level collapse">
                  <li><a class="auto-openli" href="<?php echo U('goods/discount_list');?>">付费配置</a></li>
                  <li><a class="auto-openli" href="<?php echo U('setting/launch_images');?>">闪屏配置</a></li>
                  <li><a class="auto-openli" href="<?php echo U('setting/vip_banners');?>">会员Banner</a></li>
                  <!--li><a class="auto-openli" href="<?php echo U('setting/adver_list');?>">渠道管理</a></li-->
                </ul>
              </li>
              <li>
                <a href="javascript:void(0)">运营管理<span class="fa arrow"></span></a>
                <ul class="nav nav-third-level collapse">
                  <li><a class="auto-openli" href="<?php echo U('Robot/virtuals');?>">瞬间运营账号</a></li>
                  <li class="hide"><a class="auto-openli" href="<?php echo U('user_base/virtual_users?type=1');?>">瞬间运营账号</a></li>
                  <li><a class="auto-openli" href="<?php echo U('snap_chat/logs');?>">瞬间管理/发送</a></li>
                  <li><a class="auto-openli" href="<?php echo U('robot/user');?>">陪聊运营账号</a></li>
                  <li><a class="auto-openli" href="<?php echo U('robot/index');?>">陪聊内容设置</a></li>
                </ul>
              </li>
            </ul>
          </li>

          <li>
            <a href="javascript:void(0)">
              <i class="fa fa-video-camera"></i>
              <span class="nav-label">直播管理</span>
              <span class="fa arrow"></span>
            </a>
            <ul class="nav nav-second-level collapse">
              <li><a class="auto-openli" href="<?php echo U('live/index');?>">直播监控</a></li>
              <li><a class="auto-openli" href="<?php echo U('live/hots');?>">热门直播</a></li>
              <li><a class="auto-openli" href="<?php echo U('live/hosts');?>">主播库</a></li>
              <li><a class="auto-openli" href="<?php echo U('live/apply_list');?>">主播申请</a></li>
              <li><a class="auto-openli" href="<?php echo U('live/notices');?>">公屏公告</a></li>
              <li><a class="auto-openli" href="<?php echo U('live/chat_logs');?>">直播聊天</a></li>
              <li><a class="auto-openli" href="<?php echo U('live/game_records');?>">直播游戏</a></li>
              <li>
                <a href="javascript:void(0)">直播配置<span class="fa arrow"></span></a>
                <ul class="nav nav-third-level collapse">
                  <li><a class="auto-openli" href="<?php echo U('setting/tasks');?>">任务配置</a></li>
                  <li><a class="auto-openli" href="<?php echo U('setting/games');?>">游戏配置</a></li>
                  <li><a class="auto-openli" href="<?php echo U('live/add_robot');?>">添加机器人</a></li>
                  <li><a class="auto-openli" href="<?php echo U('live/robot_config');?>">机器人策略</a></li>
                  <li><a class="auto-openli" href="<?php echo U('setting/live_banners');?>">Banner配置</a></li>
                  <!--li><a class="auto-openli" href="<?php echo U('live/contract_types');?>">签约类型</a></li-->
                </ul>
              </li>
            </ul>
          </li>

          <li>
            <a href="javascript:void(0)">
              <i class="fa fa-paper-plane"></i>
              <span class="nav-label">推广工具</span>
              <span class="fa arrow"></span>
            </a>
            <ul class="nav nav-second-level collapse">
              <li><a class="auto-openli" href="<?php echo U('Promo/offline_data');?>">地推用户导入</a></li>
              <li><a class="auto-openli" href="<?php echo U('Promo/snap_import');?>">街拍照片群发</a></li>
              <li>
                <a href="javascript:void(0)">渠道广告配置<span class="fa arrow"></span></a>
                <ul class="nav nav-third-level collapse">
                  <li><a class="auto-openli" href="<?php echo U('rdrs/channel_list');?>">渠道设置</a></li>
                  <!--li><a class="auto-openli" href="<?php echo U('rdrs/adver_list');?>">广告设置</a></li-->
                  <li><a class="auto-openli" href="<?php echo U('rdrs/package_list');?>">包版本设置</a></li>
                  <li><a class="auto-openli" href="<?php echo U('setting/version_audit');?>">当前版本</a></li>
                  <li><a class="auto-openli" href="<?php echo U('rdrs/market_list');?>">投放金额</a></li>
                </ul>
              </li>
            </ul>
          </li>

          <li>
            <a href="javascript:void(0)">
              <i class="fa fa-bar-chart-o"></i>
              <span class="nav-label">数据报表</span>
              <span class="fa arrow"></span>
            </a>
            <ul class="nav nav-second-level collapse">
              <li><a class="auto-openli" href="<?php echo U('analy/score_quality');?>">用户质量</a></li>
              <!--li><a class="auto-openli" href="<?php echo U('analy/score_record');?>">评分转化</a></li-->
              <li><a class="auto-openli" href="<?php echo U('user_base/black_list');?>">解除关系</a></li>
              <li><a class="auto-openli" href="<?php echo U('analy/operation');?>">客户服务</a></li>
              <li><a class="auto-openli" href="<?php echo U('analy/adver_stat');?>">推广数据</a></li>
              <li><a class="auto-openli" href="<?php echo U('analy/adver_daily');?>">手机版</a></li>
              <li>
                <a href="javascript:void(0)">付费数据<span class="fa arrow"></span></a>
                <ul class="nav nav-third-level collapse">
                  <li><a class="auto-openli" href="<?php echo U('analy/daily_income');?>">盈收数据</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/stat_pay_base');?>">基础数据</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/stat_pay_first');?>">首付时间</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/stat_pay_frequency');?>">付费频率</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/stat_pay_trace');?>">付费追踪</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/stat_pay_amount');?>">付费金额</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/stat_consume_frequency');?>">充值消耗</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/stat_pay_vip_path');?>">会员购买路径</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/stat_pay_visit');?>">会员购买来源</a></li>
                </ul>
              </li>
              <li>
                <a href="javascript:void(0)">直播数据<span class="fa arrow"></span></a>
                <ul class="nav nav-third-level collapse">
                  <li><a class="auto-openli" href="<?php echo U('analy/live_guest_source');?>">观众来源</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/live_host_daily');?>">主播日常</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/live_gift_daily');?>">礼物数据</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/live_guest_daily');?>">每日访客</a></li>
                </ul>
              </li>
              <li>
                <a href="javascript:void(0)">行为数据<span class="fa arrow"></span></a>
                <ul class="nav nav-third-level collapse">
                  <li><a class="auto-openli" href="<?php echo U('analy/daily_analy');?>">总体行为</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/daily_thumb');?>">点赞分布</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/been_thumb');?>">被赞分布</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/hourly_slide');?>">每时滑动</a></li>
                </ul>
              </li>
              <li>
                <a href="javascript:void(0)">分析专题<span class="fa arrow"></span></a>
                <ul class="nav nav-third-level collapse">
                  <li><a class="auto-openli" href="<?php echo U('analy/been_slide');?>">曝光分布</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/match_rate');?>">匹配分布</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/daily_chat');?>">聊天分布</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/stat_retention');?>">次留分析</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/daily_userinfo');?>">资料完整度</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/daily_userinfo_modify');?>">资料填写率</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/stat_users_visit');?>">查看资料率</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/stat_scoring_time');?>">打分平均耗时</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/stat_scoring_count');?>">打分耗时分布</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/stat_border_visit');?>">会员边框</a></li>
                  <li><a class="auto-openli" href="<?php echo U('analy/stat_im_active');?>">抽奖活动</a></li>
                </ul>
              </li>
            </ul>
          </li>

          <li>
            <a href="javascript:void(0)"><i class="fa fa-desktop"></i>
              <span class="nav-label">系统工具</span>
              <span class="fa arrow"></span>
            </a>
            <ul class="nav nav-second-level collapse">
              <li>
                <a href="javascript:void(0)">后台管理<span class="fa arrow"></span></a>
                <ul class="nav nav-third-level collapse">
                  <li><a class="auto-openli" href="<?php echo U('admin/index');?>">管理员列表</a></li>
                  <li><a class="auto-openli" href="<?php echo U('admin/auth_rule');?>">管理员权限</a></li>
                </ul>
              </li>
              <li><a class="auto-openli" href="<?php echo U('prevention/index');?>">业务预警</a></li>
            </ul>
          </li>

        </ul>

      </div>
    </nav>

    <div id="page-wrapper" class="gray-bg dashbard-1">
      <div class="row border-bottom">
        <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
          <div class="navbar-header">
            <a class="navbar-minimalize minimalize-styl-2 btn btn-primary" data-href="<?php echo U('common/index');?>"><i class="fa fa-bars"></i> </a>
            <div class="navbar-form-custom"   >
              <div class="form-group">
                <input type="text" placeholder="输入手机号查看验证码！" onPaste="var e=this; setTimeout(function(){ sayHi(e.value); }, 4);" value="" class="form-control"   id="top-search">
              </div>
            </div>
            <script>
              function sayHi(ev){
                $.post("<?php echo U('search/phonecode');?>",{phone:ev}, function (data) {
                  $('#top-search').val('');
                  $('#top-search').val(data);
                });
              }
              $("#top-search").keyup(function(){
                var leng = $("#top-search").val().length;
                var p = $(this).val();
                if(leng== 11){
                  $.post("<?php echo U('search/phonecode');?>",{phone:p}, function (data) {
                    $('#top-search').val('');
                    $('#top-search').val(data);
                  });
                }
              });
            </script>
          </div>
          <ul class="nav navbar-top-links navbar-right">
            <li>
              <span class="m-r-sm text-muted welcome-message"><a href="javascript:void(0)" title="返回首页"><i class="fa fa-home"></i></a>欢迎使用初见后台</span>
            </li>
            
            <li class="dropdown" style="display:none;">
              <a class="dropdown-toggle count-info" data-toggle="dropdown" href="javascript:void(0)">
                <i class="fa fa-bell"></i>
                <?php $mnum = $mycount['certificate_car_count']+$mycount['certificate_video_count']+$mycount['accusation_count']+$mycount['tag']; ?>
                <?php if($mnum > 0): ?><span class="label label-primary"><?php echo ($mnum); ?></span><?php endif; ?>
              </a>
              <ul class="dropdown-menu dropdown-alerts">
                <li>
                  <a href="<?php echo U('certificate_car/index',array('menu'=>'admin_task','type'=>'unprocessed'));?>">
                    <div>
                      <i class="fa fa-car fa-fw"></i> <?php echo ($mycount["certificate_car_count"]); ?>条未读消息
                      <span class="pull-right text-muted small">查看</span>
                    </div>
                  </a>
                </li>
                <li class="divider"></li>
                <li>
                  <a href="<?php echo U('certificate_video/index',array('menu'=>'admin_task','type'=>'unprocessed'));?>">
                    <div>
                      <i class="fa fa-video-camera fa-fw"></i> <?php echo ($mycount["certificate_video_count"]); ?>条未读消息
                      <span class="pull-right text-muted small">查看</span>
                    </div>
                  </a>
                </li>
                <li class="divider"></li>
                <li>
                  <a href="<?php echo U('accusation/index',array('menu'=>'admin_task','type'=>'unprocessed'));?>">
                    <div>
                      <i class="fa fa-exclamation-triangle fa-fw"></i> <?php echo ($mycount["accusation_count"]); ?>条未读消息
                      <span class="pull-right text-muted small">查看</span>
                    </div>
                  </a>
                </li>
                <li class="divider"></li>
                <li>
                  <a href="<?php echo U('search/mytag',array('menu'=>'admin_task'));?>">
                    <div>
                      <i class="fa fa-tag fa-fw"></i> <?php echo ($mycount["tag"]); ?>条未读消息
                      <span class="pull-right text-muted small">查看</span>
                    </div>
                  </a>
                </li>
                 
              </ul>
            </li>

            <li>
              <a href="<?php echo U('common/logout');?>"><i class="fa fa-sign-out"></i> 退出</a>
            </li>
          </ul>

        </nav>
      </div>


      <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
          <h2>导航栏</h2>
          <ol class="breadcrumb">
            <?php if(is_array($nav_path)): $i = 0; $__LIST__ = $nav_path;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li>
              <a href="<?php echo ($vo["url"]); ?>"><?php echo ($vo["title"]); ?></a>
            </li><?php endforeach; endif; else: echo "" ;endif; ?>
          </ol>
        </div>
      </div>



      <div class="wrapper wrapper-content animated fadeInRight">
       
  
            </div>
        <div class="footer">
            <div class="pull-right">
                By：<a href="http://www.zi-han.net/" target="_blank">zihan's blog</a>
            </div>
            <div>
                <strong>Copyright</strong> H+ © 2014
            </div>
        </div>

    </div>
</div>



<div class="jvectormap-label"></div><div class="theme-config">
    <div class="theme-config-box">
        <div class="spin-icon">
            <i class="fa fa-cog fa-spin"></i>
        </div>
        <div class="skin-setttings">
            <div class="title">主题设置</div>
            <div class="setings-item">
                <span>
                        收起左侧菜单
                    </span>

                <div class="switch">
                    <div class="onoffswitch">
                        <input type="checkbox" name="collapsemenu" class="onoffswitch-checkbox" id="collapsemenu">
                        <label class="onoffswitch-label" for="collapsemenu">
                            <span class="onoffswitch-inner"></span>
                            <span class="onoffswitch-switch"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="setings-item">
                <span>
                        固定侧边栏
                    </span>

                <div class="switch">
                    <div class="onoffswitch">
                        <input type="checkbox" name="fixedsidebar" class="onoffswitch-checkbox" id="fixedsidebar">
                        <label class="onoffswitch-label" for="fixedsidebar">
                            <span class="onoffswitch-inner"></span>
                            <span class="onoffswitch-switch"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="setings-item">
                <span>
                        固定顶部
                    </span>

                <div class="switch">
                    <div class="onoffswitch">
                        <input type="checkbox" name="fixednavbar" class="onoffswitch-checkbox" id="fixednavbar">
                        <label class="onoffswitch-label" for="fixednavbar">
                            <span class="onoffswitch-inner"></span>
                            <span class="onoffswitch-switch"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="setings-item">
                <span>
                        固定宽度
                    </span>

                <div class="switch">
                    <div class="onoffswitch">
                        <input type="checkbox" name="boxedlayout" class="onoffswitch-checkbox" id="boxedlayout">
                        <label class="onoffswitch-label" for="boxedlayout">
                            <span class="onoffswitch-inner"></span>
                            <span class="onoffswitch-switch"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="setings-item">
                <span>
                        固定底部
                    </span>

                <div class="switch">
                    <div class="onoffswitch">
                        <input type="checkbox" name="fixedfooter" class="onoffswitch-checkbox" id="fixedfooter">
                        <label class="onoffswitch-label" for="fixedfooter">
                            <span class="onoffswitch-inner"></span>
                            <span class="onoffswitch-switch"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="setings-item">
                <span>
                        RTL模式
                    </span>

                <div class="switch">
                    <div class="onoffswitch">
                        <input type="checkbox" name="RTLmode" class="onoffswitch-checkbox" id="RTLmode">
                        <label class="onoffswitch-label" for="RTLmode">
                            <span class="onoffswitch-inner"></span>
                            <span class="onoffswitch-switch"></span>
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<div style="width:420px; height:260px; padding:20px; border:1px solid #ccc; background-color:#eee;display:none" id="new-order">
    <p>有新用户了，赶紧看看吧！</p>
</div>
<script>

    //var int=self.setInterval("clock()",2000);
    function clock()
    {
        var t =new Date();
        $('#new-order > p').html(t);
        var pageii = $.layer({
            type: 1,
            title: false,
            area: ['auto', 'auto'],
            border: [0], //去掉默认边框
            shade: [0], //去掉遮罩
            closeBtn: [0, false], //去掉默认关闭按钮
            shift: 'right-bottom',
            page: {
                dom: "#new-order",
            }
        });

    }






    // 顶部菜单固定
    $('#fixednavbar').click(function () {
        if ($('#fixednavbar').is(':checked')) {
            $(".navbar-static-top").removeClass('navbar-static-top').addClass('navbar-fixed-top');
            $("body").removeClass('boxed-layout');
            $("body").addClass('fixed-nav');
            $('#boxedlayout').prop('checked', false);
        } else {
            $(".navbar-fixed-top").removeClass('navbar-fixed-top').addClass('navbar-static-top');
            $("body").removeClass('fixed-nav');
        }
    });

    // 左侧菜单固定
    $('#fixedsidebar').click(function () {
        if ($('#fixedsidebar').is(':checked')) {
            $("body").addClass('fixed-sidebar');
            $('.sidebar-collapse').slimScroll({
                height: '100%',
                railOpacity: 0.9
            });
        } else {
            $('.sidebar-collapse').slimscroll({
                destroy: true
            });
            $('.sidebar-collapse').attr('style', '');
            $("body").removeClass('fixed-sidebar');
        }
    });

    // 收起左侧菜单
    $('#collapsemenu').click(function () {
        if ($('#collapsemenu').is(':checked')) {
            $("body").addClass('mini-navbar');
            SmoothlyMenu();
        } else {
            $("body").removeClass('mini-navbar');
            SmoothlyMenu();
        }
    });

    // 自适应宽度
    $('#boxedlayout').click(function () {
        if ($('#boxedlayout').is(':checked')) {
            $("body").addClass('boxed-layout');
            $('#fixednavbar').prop('checked', false);
            $(".navbar-fixed-top").removeClass('navbar-fixed-top').addClass('navbar-static-top');
            $("body").removeClass('fixed-nav');
            $(".footer").removeClass('fixed');
            $('#fixedfooter').prop('checked', false);
        } else {
            $("body").removeClass('boxed-layout');
        }
    });

    // 底部版权固定
    $('#fixedfooter').click(function () {
        if ($('#fixedfooter').is(':checked')) {
            $('#boxedlayout').prop('checked', false);
            $("body").removeClass('boxed-layout');
            $(".footer").addClass('fixed');
        } else {
            $(".footer").removeClass('fixed');
        }
    });

    // RTL模式
    $('#RTLmode').click(function () {
        if ($('#RTLmode').is(':checked')) {
            $('head').append('<link href="/Public/css/update/bootstrap-rtl.css" id="rtl-mode" rel="stylesheet">');
            $('body').addClass('rtls');
        } else {
            $('#rtl-mode').remove();
            $('body').removeClass('rtls');
        }
    });




</script>

<style>
    .fixed-nav .slimScrollDiv #side-menu {
        padding-bottom: 60px;
    }
</style></body></html>