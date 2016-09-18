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
      <link rel="stylesheet" href="/Public/css/app.comm.css">
<style>
* { margin:0;padding:0;list-style-type:none; }
img,a { border:0; }
.piccon { height:20px;margin:20px 0 0 0px; }
.piccon li { float:left;padding:0 0px; }
#preview { position:absolute;border:1px solid #ccc;background:#333;padding:5px;display:none;color:#fff; }
/*覆盖层*/
#overlay { position:absolute;background-color: rgb(119,119,119);opacity: 0.5;cursor: pointer;width:100%;height: 100%;top: 0px;left: 0px }
/*弹出层*/
#wrap { position:absolute;background-color: rgba(18,18,18,0.73);width: 100%;height:50%;/*top: 10%;*/ }
.xubox_layer { top: 20px; }
.pic-rotate { -webkit-transform: rotate(0deg);-webkit-transform-origin: 50% 50%;  }
.row-fluid img { height:85%; }
.tab-content { padding-top:10px; }
.user-info th { text-align:right; }
form.form-inline { display:inline-block; }
</style>
<?php $uid = (int)$data['item']['uid']; $dat = $data['item'] ?: []; $acc = $data['user_account'] ?: []; $loc = $data['user_location'] ?: []; ?>
<div id="content">
  <div class="container-fluid">
    <div class="widget-box">
      <div class="widget-title">
        <ul id="myTab" class="nav nav-tabs">
          <li><a href="#tab1" data-toggle="tab" class="tab-user-info">用户资料</a></li>
          <li><a href="#tab2" data-toggle="tab" class="tab-user-feed">头像/相册 <span class="badge"><?php echo count($data['album']) + count($data['avatar_history']); ?></span></a></li>
          <li><a href="<?php echo U('chat_logs?uid='.$uid);?>" target="_blank">聊天记录</a></li>
          <li><a href="<?php echo U('live/index?state=-1&uid='.$uid);?>" target="_blank">直播记录</a></li>
          <li><a href="#tab-user-closure" data-toggle="tab">警告降权</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              订单记录
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="<?php echo U('order_list?uid='.$uid);?>" target="_blank">现金订单</a></li>
              <li><a href="<?php echo U('order_diamond_list?uid='.$uid);?>" target="_blank">钻石订单</a></li>
              <li><a href="<?php echo U('order_list?ver=1&uid='.$uid);?>" target="_blank">现金订单(旧)</a></li>
            </ul>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              账户流水
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="<?php echo U('glamour_record?uid='.$uid);?>" target="_blank">魅力明细</a></li>
              <li><a href="<?php echo U('diamond_record?uid='.$uid);?>" target="_blank">钻石明细</a></li>
              <!--li><a href="<?php echo U('fee_record?uid='.$uid);?>" target="_blank">收支明细</a></li-->
            </ul>
          </li>
          <li><a href="<?php echo U('cash_list?uid='.$uid);?>" target="_blank">提现记录</a></li>
          <!--li><a href="<?php echo U('like_list?uid='.$uid);?>" target="_blank">赞/超赞记录</a></li-->
          <li><a href="<?php echo U('snap_chat/logs?sender='.$uid);?>" target="_blank">瞬间记录</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
              互动记录
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="<?php echo U('like_list?uid='.$uid);?>" target="_blank">送赞记录</a></li>
              <li><a href="<?php echo U('like_list?oid='.$uid);?>" target="_blank">被赞记录</a></li>
              <li><a href="<?php echo U('match_list?uid='.$uid);?>" target="_blank">匹配列表</a></li>
              <li><a href="<?php echo U('black_list?uid='.$uid);?>" target="_blank">解除匹配</a></li>
              <li><a href="<?php echo U('slide_list?uid='.$uid);?>" target="_blank">滑动记录</a></li>
              <li><a href="<?php echo U('recommend_list?uid='.$uid);?>" target="_blank">推荐列表</a></li>
              <li><a href="<?php echo U('dislikeme_list?uid='.$uid);?>" target="_blank">不喜欢TA</a></li>
              <li><a href="<?php echo U('coupon_list?uid='.$uid);?>" target="_blank">礼物记录</a></li>
              <li><a href="<?php echo U('activity/lucky_bags?uid='.$uid);?>" target="_blank">烈火福袋</a></li>
              <li><a href="<?php echo U('live/follows?uid='.$uid);?>" target="_blank">关注列表</a></li>
              <li><a href="<?php echo U('live/follows?oid='.$uid);?>" target="_blank">粉丝列表</a></li>
              <li><a href="<?php echo U('live/gift_records?uid='.$uid);?>" target="_blank">直播送礼</a></li>
              <li><a href="<?php echo U('live/gift_records?oid='.$uid);?>" target="_blank">直播收礼</a></li>
              <li><a href="<?php echo U('live/task_records?uid='.$uid);?>" target="_blank">任务完成记录</a></li>
            </ul>
          </li>
          <li><a href="<?php echo U('oper_logs?uid='.$uid);?>" target="_blank">操作日志</a></li>
        </ul>
      </div>

      <div class="widget-content">
        <div id="myTabContent" class="tab-content">
          <div class="tab-pane fade in active" id="tab1">
            <table class="table table-bordered user-info">
              <col width="110">
              <tr>
                <th>用户ID</th>
                <td>
                  <?php echo ($data['item']['uid']); ?>
                  <b class="label label-danger"><?php echo boolval($data['user_account']['vip_level'] && $data['user_account']['vip_valid_end'] >= NOW_TIME) ? 'v' : ''; echo ($data['user_account']['glory_grade'] ?: ''); ?></b>
                  <b class="label label-danger"><?php echo ($data['contract_types'][$data['live_host']['contract_type']]['attrs']['name']); ?></b>
                  <div class="pull-right">
<?php if($data['live_manager']): ?><a href="<?php echo U('Live/manager_set',['uid' => $data['item']['uid'],'del' => 1]);?>" class="btn btn-warning btn-sm ajax-with-msg">移出直播管理</a>
<?php else: ?>
                    <a href="<?php echo U('Live/manager_set',['uid' => $data['item']['uid']]);?>" class="btn btn-warning btn-sm ajax-with-msg">加入直播管理</a><?php endif; ?>
                    <a data-rtmp="rtmp://live.hkstv.hk.lxdns.com/live/hks" class="btn btn-primary btn-sm act-live-play hide">直播</a>
                    <a href="<?php echo U('del_user_cache?uid='.$uid);?>" class="btn btn-danger btn-sm">清除缓存</a>
                  </div>
                </td>
              </tr>
              <tr>
                <th>昵称</th>
                <td>
                  <label>
                    <input type="checkbox" name="clear_info[nickname]" value="1">
                  </label>
                  <a class="editable-disabled required clear-field-key"
                     data-type="text"
                     data-name="nickname"
                     data-pk="<?php echo ($data['item']['uid']); ?>"
                     data-url="<?php echo U('set_field');?>"
                     data-title="请输入新昵称："><?php echo htmlspecialchars($data['item']['nickname']);?></a>
<?php if($data['item']['nickname']): ?><a href="<?php echo U('del_field?field=nickname&uid='.$uid);?>" target="_blank" class="act-text-clear btn btn-danger btn-sm pull-right">清空</a><?php endif; ?>
                </td>
              </tr>
              <tr>
                <th>手机号</th>
                <td>
                  <a class="editable required"
                     data-type="text"
                     data-name="phone"
                     data-pk="<?php echo ($data['item']['uid']); ?>"
                     data-url="<?php echo U('set_field');?>"
                     data-title="请输入新手机号："
                     data-validate="^1[34578]\d{9}$"><?php echo ($data['item']['phone']); ?></a>
<?php if($data['item']['qq_open_id']): ?>&nbsp;<i class="fa fa-lg fa-qq" style="color:#1BE;"></i><?php endif; ?>
<?php if($data['item']['wx_open_id']): ?>&nbsp;<i class="fa fa-lg fa-weixin" style="color:#0A0;"></i><?php endif; ?>
                </td>
              </tr>
              <tr>
                <th>性别</th>
                <td>
                  <?php $sex = $data['item']['sex']; ?>
                  <a class="editable"
                     data-type="select"
                     data-name="sex"
                     data-pk="<?php echo ($data['item']['uid']); ?>"
                     data-url="<?php echo U('set_field');?>"
                     data-title="请选择性别："
                     data-value="<?php echo ($data['item']['sex']); ?>"
                     data-source="[{value:0,text:'男'},{value:1,text:'女'}]"><?php echo (C("USER_SEX_IS.$sex")); ?></a>
                </td>
              </tr>
              <tr>
                <th>生日</th>
                <td>
                  <?php echo ($data['item']['birthday'] ?: '-'); ?> &nbsp;
                  <?php if($birt = strtotime($data['item']['birthday'])) { echo (int)((NOW_TIME - $birt) / 60 / 60 / 24 / 365).'岁'; } ?>
                </td>
              </tr>
              <tr>
                <th>设置密码</th>
                <td>
                  <form action="<?php echo U('set_field');?>" method="POST" class="form-inline">
                    <input type="hidden" name="uid" value="<?php echo ($data['item']['uid']); ?>">
                    <div class="form-group">
                      <label></label>
                      <input type="hidden" name="name" value="password">
                      <input type="text" name="value" placeholder="设置新密码" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-danger">确定</button>
                  </form>
                </td>
              </tr>
              <tr>
                <th>个人简介</th>
                <td>
                  <label>
                    <input type="checkbox" name="clear_info[description]" value="1">
                    <?php echo htmlspecialchars($data['item']['description'] ?: '-');?>
                  </label>
<?php if($data['item']['description']): ?><a href="<?php echo U('del_field?field=description&uid='.$uid);?>" target="_blank" class="act-text-clear btn btn-danger btn-sm pull-right">清空</a><?php endif; ?>
                </td>
              </tr>
              <tr>
                <th>注册渠道</th>
                <td>
                  <?php echo ($data['item']['pkg_channel'] ?: '-'); ?> &nbsp;
                  <?php echo ($data['item']['device']); ?> &nbsp;
                  <?php echo ($data['item']['device_model']); ?> &nbsp;
                  <?php echo ($data['item']['device_version']); ?>
                </td>
              </tr>
              <tr>
                <th>设备ID</th>
                <td>
                  <a href="<?php echo U('device_list?device_id='.$data['item']['device_id']);?>" target="_blank"><?php echo ($data['item']['device_id']); ?></a>
                  <a href="<?php echo U('index');?>?device_id=<?php echo ($data['item']['device_id']); ?>" target="_blank" class="label label-default"><?php echo ($data['item']['device_count']); ?></a>
<?php if($data['auth_data']['device_id'] && $data['auth_data']['device_id'] != $data['item']['device_id']): ?><br>
                  <a href="<?php echo U('device_list?device_id='.$data['auth_data']['device_id']);?>" target="_blank"><?php echo ($data['auth_data']['device_id']); ?></a><?php endif; ?>
                </td>
              </tr>
              <tr>
                <th>注册时间</th>
                <td><?php echo ($data['item']['reg_time'] ? date('Y-m-d H:i:s',$data['item']['reg_time']) : '-'); ?></td>
              </tr>
              <tr>
                <th>最后登录</th>
                <td><?php echo ($data['user_location']['update_time'] ? date('Y-m-d H:i:s',$data['user_location']['update_time']) : '-'); ?></td>
              </tr>
              <tr>
                <th>活跃时间</th>
                <td><?php echo ($data['item']['active_time'] ? date('Y-m-d H:i:s',$data['item']['active_time']) : '-'); ?></td>
              </tr>
              <tr>
                <th>登录城市</th>
                <td>
                  <?php echo ($data['user_location']['province']); ?> <?php echo ($data['user_location']['city']); ?> <?php echo ($data['user_location']['area']); ?>
                  （<?php echo ($data['user_location']['lat']); ?>,<?php echo ($data['user_location']['lng']); ?>）
                </td>
              </tr>
              <tr>
                <th>APP版本</th>
                <td><?php echo ($data['user_location']['app_version']); ?></td>
              </tr>
              <tr>
                <th>家乡</th>
                <td>
                  <?php echo ($data['home_city']['province'] ?: '-'); ?> <?php echo ($data['home_city']['city'] ?: '-'); ?>
                </td>
              </tr>
<?php if($data['job_haunt']['haunt']): ?><tr>
                <th>常出没地</th>
                <td>
                  <label>
                    <input type="checkbox" name="clear_info[haunt]" value="<?php echo ($data['job_haunt']['haunt_id']); ?>">
                    <?php echo ($data['job_haunt']['haunt'] ?: '-'); ?>
                  </label>
                </td>
              </tr><?php endif; ?>
<?php if($data['job_haunt']['job']): ?><tr>
                <th>职业</th>
                <td>
                  <label>
                    <input type="checkbox" name="clear_info[job]" value="<?php echo ($data['job_haunt']['job_id']); ?>">
                    <?php echo ($data['job_haunt']['job'] ?: '-'); ?>
                  </label>
                </td>
              </tr><?php endif; ?>
<?php if($data['character']): ?><tr>
                <th>性格</th>
                <td>
<?php if(is_array($data['character'])): $i = 0; $__LIST__ = $data['character'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><label>
                    <span class="label label-default"><input type="checkbox" name="clear_info[character][<?php echo ($v['id']); ?>]" value="1"><?php echo ($v['name']); ?></span> &nbsp;
                  </label><?php endforeach; endif; else: echo "" ;endif; ?>
                </td>
              </tr><?php endif; ?>
              <tr>
                <th>兴趣</th>
                <td>
                  <a href="<?php echo U('del_fields?uid='.$uid);?>" target="_blank" class="act-text-clear-bat btn btn-danger btn-sm pull-right">清空选中资料</a>
<?php if($data['interest']): ?><table class="table table-bordered" style="width:auto;">
                    <tr>
                      <th>分类</th>
                      <td>兴趣</td>
                    </tr>
<?php if(is_array($data['interest_types'])): $i = 0; $__LIST__ = $data['interest_types'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$type): $mod = ($i % 2 );++$i; if($data['interest'][$key]): ?><tr>
                      <th><?php echo ($type); ?></th>
                      <td>
<?php if(is_array($data['interest'][$key])): $i = 0; $__LIST__ = $data['interest'][$key];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><label>
                          <span class="label label-default"><input type="checkbox" name="clear_info[interest][<?php echo ($v['id']); ?>]" value="1"><?php echo ($v['name']); ?></span> &nbsp;
                        </label><?php endforeach; endif; else: echo "" ;endif; ?>
                      </td>
                    </tr><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                  </table>
<?php else: ?>
-<?php endif; ?>
                </td>
              </tr>
              <tr>
                <th>收到赞</th>
                <td>
                  普通赞：<?php echo ($data['user_account']['total_like']); ?> &nbsp;
                  超级赞：<?php echo ($data['user_account']['total_super_like']); ?> &nbsp;
                  金星超赞：<?php echo ($data['user_account']['total_gold_like']); ?>
                </td>
              </tr>
              <tr>
                <th>剩余赞</th>
                <td>
                  <table class="table table-bordered text-center" style="width:auto;">
                    <tr>
                      <td><a href="<?php echo U('zan_logs?uid='.$uid);?>" target="_blank">类型</a></td>
                      <td>合计</td>
                      <td>免费</td>
                      <td>付费</td>
                    </tr>
                    <tr>
                      <td>普通赞</td>
                      <td><a href="<?php echo U('zan_logs?zan_type=like&uid='.$uid);?>" target="_blank"><?php echo ($data['item']['surp_like_times']['zan']['total']); ?></a></td>
                      <td><a href="<?php echo U('zan_logs?zan_type=0&uid='.$uid);?>" target="_blank"><?php echo ($data['item']['surp_like_times']['zan']['free']); ?></a></td>
                      <td><a href="<?php echo U('zan_logs?zan_type=3&uid='.$uid);?>" target="_blank"><?php echo ($data['item']['surp_like_times']['zan']['fees']); ?></a></td>
                    </tr>
                    <tr>
                      <td>超级赞</td>
                      <td><a href="<?php echo U('zan_logs?zan_type=super_like&uid='.$uid);?>" target="_blank"><?php echo ($data['item']['surp_like_times']['super_zan']['total']); ?></a></td>
                      <td><a href="<?php echo U('zan_logs?zan_type=1&uid='.$uid);?>" target="_blank"><?php echo ($data['item']['surp_like_times']['super_zan']['free']); ?></a></td>
                      <td><a href="<?php echo U('zan_logs?zan_type=2&uid='.$uid);?>" target="_blank"><?php echo ($data['item']['surp_like_times']['super_zan']['fees']); ?></a></td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <th>道具仓库</th>
                <td>
                  <table class="table table-bordered text-center" style="width:auto;">
                    <tr>
                      <th>道具名称</th>
<?php if(is_array($data['props'])): $i = 0; $__LIST__ = $data['props'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i; if($gds = $data['goods'][$key] ?: []): ?><td><?php echo ($gds['name']); ?></td><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                    </tr>
                    <tr>
                      <th>剩余数</th>
<?php if(is_array($data['props'])): $i = 0; $__LIST__ = $data['props'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i; if($gds = $data['goods'][$key] ?: []): ?><td><?php echo ($v); ?></td><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                    </tr>
                    <tr>
                      <th>过期时间</th>
<?php if(is_array($data['props'])): $i = 0; $__LIST__ = $data['props'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i; if($gds = $data['goods'][$key] ?: []): ?><td><?php echo date('Y-m-d',$data['props'][$key.'_expire_time']);?></td><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <th>账户</th>
                <td>
                  <table class="table table-bordered text-center" style="width:auto;">
                    <tr>
                      <th>钻石余额</th>
                      <th>历史消费现金</th>
                      <!--th>充值金额</th-->
                      <th>可兑换魅力</th>
                      <th>不可兑换魅力</th>
                      <th>历史收到魅力</th>
                      <th>直播收到魅力</th>
                      <th>荣耀值</th>
                      <th>荣耀等级</th>
                      <th>历史消费钻石</th>
                      <th>历史提现</th>
                      <th>历史提现魅力</th>
                      <th>历史兑换魅力</th>
                      <th>现金余额</th>
                      <th>账户版本</th>
                    </tr>
                    <tr>
                      <td><a href="<?php echo U('diamond_record?uid='.$uid);?>" target="_blank"><?php echo ($data['user_account']['diamond'] ?: '-'); ?></a></td>
                      <td><a href="<?php echo U('order_list?uid='.$uid);?>" target="_blank"><?php echo ($data['user_account']['total_expense'] ?: '-'); ?></a></td>
                      <!--td><?php echo ($data['user_account']['total_charge'] ?: '-'); ?></td-->
                      <td><a href="<?php echo U('glamour_record?uid='.$uid);?>" target="_blank"><?php echo intval($data['user_account']['glamour'] - $data['user_account']['glamour_frozen']) ?: '-';?></a></td>
                      <td><?php echo ($data['user_account']['glamour_frozen'] ?: '-'); ?></td>
                      <td><?php echo ($data['user_account']['total_glamour'] ?: '-'); ?></td>
                      <td><?php echo ($data['user_account']['total_live_glamour'] ?: '-'); ?></td>
                      <td><?php echo ($data['user_account']['glory'] ?: '-'); ?></td>
                      <td><?php echo ($data['user_account']['glory_grade'] ?: '-'); ?></td>
                      <td><a href="<?php echo U('order_diamond_list?uid='.$uid);?>" target="_blank"><?php echo ($data['user_account']['total_expense_diamond'] ?: '-'); ?></a></td>
                      <td><a href="<?php echo U('cash_list?uid='.$uid);?>" target="_blank"><?php echo ($data['user_account']['total_cash'] ?: '-'); ?></a></td>
                      <td><?php echo ($data['user_account']['total_cash_glamour'] ?: '-'); ?></td>
                      <td><?php echo ($data['user_account']['total_exchange_glamour'] ?: '-'); ?></td>
                      <td><?php echo ($data['user_account']['balance'] ?: '-'); ?></td>
                      <td><?php echo ($data['user_account']['account_ver'] ?: '-'); ?></td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <th>VIP状态</th>
                <td>

<?php if($data['user_account']['vip_valid_end'] > NOW_TIME): ?><b class="label label-danger" data-lvl="<?php echo ($data['user_account']['vip_level']); ?>">Vip</b>
                  &nbsp; &nbsp; 剩余<?php echo (int)(($data['user_account']['vip_valid_end'] - NOW_TIME) / (60 * 60 * 24)); ?>天
                  &nbsp; &nbsp; 开通时间：<?php echo date('Y-m-d H:i:s',$data['user_account']['vip_valid_begin']);?>
                  &nbsp; &nbsp; 到期时间：<?php echo date('Y-m-d H:i:s',$data['user_account']['vip_valid_end']);?>
<?php else: ?>
                  -<?php endif; ?>
                </td>
              </tr>
              <tr>
                <th>送赞/VIP</th>
                <td>
                  <form action="<?php echo U('give');?>" method="POST" class="form-inline">
                    <input type="hidden" name="uid" value="<?php echo ($data['item']['uid']); ?>">
                    <div class="form-group">
                      <label><input type="radio" name="type" value="like" class="filter-fields"> 普通赞</label> &nbsp;
                      <label><input type="radio" name="type" value="super_like" class="filter-fields"> 超级赞</label> &nbsp;
                      <label><input type="radio" name="type" value="vip" class="filter-fields"> VIP</label> &nbsp;
                      <label><input type="radio" name="type" value="glamour" class="filter-fields"> 魅力</label> &nbsp;
                      <label><input type="radio" name="type" value="diamond" class="filter-fields"> 钻石</label> &nbsp;
                      <label data-filter="diamond"><input type="checkbox" name="with_glory" value="1"> 赠送荣耀值 &nbsp;</label>
                      <label><input type="radio" name="type" value="prop" class="filter-fields"> 道具</label> &nbsp;
                      <label><input type="radio" name="type" value="live_effect" class="filter-fields"> 进场特效</label> &nbsp;
                    </div>
                    <div class="form-group" data-filter="prop">
                      <select name="goods_id" class="form-control">
                        <option value="<?php echo Liehuo\Model\GoodsModel::BROADCAST;?>">广播</option>
                        <option value="<?php echo Liehuo\Model\GoodsModel::HOT_TICKET;?>">热门票</option>
                      </select>
                    </div>
                    <div class="form-group" data-filter="live_effect">
                      <select name="goods_id" class="form-control">
<?php $arr = Liehuo\Model\GoodsModel::$goods_live_effects ?: []; ?>
<?php if(is_array($arr)): $i = 0; $__LIST__ = $arr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($v['name']); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <input type="text" name="num" placeholder="增加数量/天数" class="form-control">
                    </div>
                    <div class="form-group" data-filter="diamond glamour">
                      <input type="text" name="remark" placeholder="备注，选填" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-danger">确定</button>
                  </form>
                </td>
              </tr>
              <tr>
                <th>用户类型</th>
                <td>
                  <?php echo ($data['user_types'][$data['item']['type']]); ?> &nbsp;
<?php if($data['item']['power']): ?><span class="label label-warning">特权用户</span>
                  <a href="<?php echo U('set_super_user?uid='.$uid.'&power=0');?>" class="btn btn-warning btn-xs" onclick="return confirm('确定这么做？')">解除特权用户</a>
<?php else: ?>
                  <a href="<?php echo U('set_super_user?uid='.$uid.'&power=1');?>" class="btn btn-warning btn-xs" onclick="return confirm('确定这么做？')">开通特权用户</a><?php endif; ?>
                </td>
              </tr>
<?php if(in_array($data['item']['type'],[2,3]) || ($data['item']['dblocking_time'] > NOW_TIME)): ?><tr>
                <th>解封时间</th>
                <td>
                  <?php echo ($data['item']['dblocking_time'] ? date('Y-m-d H:i:s',$data['item']['dblocking_time']) : '-'); ?> &nbsp;
                  <span class="text-danger"><?php echo ($data['accusation_states'][$data['accusation_last']['status']]); ?></span> &nbsp;
                  <a href="<?php echo U('view?uid='.$uid.'&tab=tab-user-closure');?>"
                     data-href="<?php echo U('unclosure?uid='.$uid);?>"
                     class="btn btn-danger btn-xs"
                     data-onclick="return confirm('确定这么做？')">解除</a>
                </td>
              </tr><?php endif; ?>
            </table>
          </div>

          <div class="tab-pane fade" id="tab2">
<?php if($data['album'][0]): ?><h4>主头像</h4>
            <div class="row list-feed">
<?php $score_show = $data['item']['score'] >= 0 ? $data['item']['score'] : ''; $score_css = 'default'; $score_show >= 1 && $score_css = 'primary'; $score_show >= 5 && $score_css = 'success'; $score_show >= 8 && $score_css = 'danger'; $avatar = $data['album'][0]; $res = is_array($avatar) ? $avatar : ['resource' => $avatar]; $src = $res['resource']; ?>
              <div class="feed-item col-xxs-12 col-xs-6 col-sm-4 col-md-3 col-lg-2" data-img="<?php echo ($data['album'][0]); ?>">
                <div class="thumbnail">
<?php if($res['type'] == 3): ?><a><video src="http://feed.chujianapp.com/<?php echo ($src); ?>" poster="http://feed.chujianapp.com/<?php echo ($res['thumb']); ?>" preload="meta" controls></a>
<?php else: ?>
                  <a><img src="http://feed.chujianapp.com/<?php echo ($src); ?>"></a><?php endif; ?>
                  <!--span class="feed-score label label-<?php echo ($score_css); ?>"><?php echo ($score_show); ?></span-->
                  <div class="caption">
<?php if($data['avatars'][$src]['score_time']): ?><p class="text-nowrap"><b>打分时间:</b><?php echo date('Y-m-d H:i:s',$data['avatars'][$src]['score_time']);?></p><?php endif; ?>
                    <p class="text-center">
                      <a href="http://image.baidu.com/n/pc_search?queryImageUrl=http://feed.chujianapp.com/<?php echo ($src); ?>" target="_blank" class="btn btn-sm btn-white">查询盗图</a>
                      <a data-href="<?php echo U('avatar_del?uid='.$uid);?>" data-resource="<?php echo ($src); ?>" class="btn btn-sm btn-danger act-avatar-del" data-onclick="return confirm('这是用户的主头像，确定要删除吗？')">删除</a>
                    </p>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <form action="<?php echo U('set_score');?>" method="POST" class="form-block">
                  <input type="hidden" name="uid" value="<?php echo ($data['item']['uid']); ?>">
                  <div class="form-group">
                    <label>最后一次评分：<?php echo ($data['item']['score'] >= 0 ? $data['item']['score'] : '未评分'); ?></label>
                    <input type="text" name="score" class="form-control" placeholder="修改分值">
                  </div>
                  <div class="form-group">
                    <label>备注：</label>
                    <input type="text" name="remark" class="form-control" placeholder="必填">
                  </div>
                  <div class="form-group">
                    <label>打分团备注：</label>
                    <input type="text" name="remark_scoring" value="<?php echo ($data['remark_scoring']); ?>" class="form-control" placeholder="选填">
                  </div>
                  <div class="form-group">
                    <label>话术：</label>
                    <input type="text" name="msg" class="form-control" placeholder="选填">
                  </div>
                  <button type="submit" class="btn btn-primary">修改分值</button>
                  <a href="<?php echo U('avatar_list?uid='.$uid);?>" target="_blank" class="btn btn-success">更多</a>
                </form>
              </div>
            </div>
<?php unset($data['album'][0]); endif; ?>

<?php if($data['album']): ?><div class="clearfix">
              <h4 class="pull-left">相册</h4>
              <a data-href="<?php echo U('avatar_del?uid='.$uid);?>" class="btn btn-sm btn-danger act-avatar-del-bat pull-left" style="margin-left:10px;">批量删除</a>
            </div>
            <div class="row list-feed list-album">
<?php if(is_array($data['album'])): $i = 0; $__LIST__ = $data['album'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i; $res = is_array($v) ? $v : ['resource' => $v]; $src = $res['resource']; ?>
              <div class="feed-item col-xxs-12 col-xs-6 col-sm-4 col-md-3 col-lg-2 col-auto-height" data-img="<?php echo ($src); ?>">
                <div class="thumbnail">
<?php if($res['type'] == 3): ?><a><video src="http://feed.chujianapp.com/<?php echo ($src); ?>" poster="http://feed.chujianapp.com/<?php echo ($res['thumb']); ?>" preload="meta" controls></a>
<?php else: ?>
                  <a><img src="http://feed.chujianapp.com/<?php echo ($src); ?>"></a><?php endif; ?>
                  <div class="caption">
<?php if($data['avatars'][$src]['score_time']): ?><p class="text-nowrap"><b>打分时间:</b><?php echo date('Y-m-d H:i:s',$data['avatars'][$src]['score_time']);?></p><?php endif; ?>
                    <p class="text-center">
                      <a href="http://image.baidu.com/n/pc_search?queryImageUrl=http://feed.chujianapp.com/<?php echo ($src); ?>" target="_blank" class="btn btn-sm btn-white">查询盗图</a>
                      <a data-href="<?php echo U('avatar_del?uid='.$uid);?>" data-resource="<?php echo ($src); ?>" class="btn btn-sm btn-danger act-avatar-del">删除</a>
                      <label><input type="checkbox" name="resource[]" value="<?php echo ($src); ?>"> 批量删除</label>
                    </p>
                  </div>
                </div>
              </div><?php endforeach; endif; else: echo "" ;endif; ?>
            </div><?php endif; ?>

<?php if($data['avatar_history']): ?><div class="clearfix">
              <h4 class="pull-left">历史照片（用户不再使用/用户自己删除）</h4>
              <a href="<?php echo U('avatar_clear?uid='.$uid);?>" class="btn btn-sm btn-danger pull-left" onclick="return confirm('真的要这么做？')">批量删除</a>
            </div>
            <div class="row list-feed">
<?php if(is_array($data['avatar_history'])): $i = 0; $__LIST__ = $data['avatar_history'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i; if(!$v['in_album']): ?><div class="feed-item col-xxs-12 col-xs-6 col-sm-4 col-md-3 col-lg-2 col-auto-height" data-img="<?php echo ($v['resource']); ?>">
                <div class="thumbnail">
<?php if($v['type'] == 3): ?><a><video src="http://feed.chujianapp.com/<?php echo ($v['resource']); ?>" poster="http://feed.chujianapp.com/<?php echo ($v['thumb']); ?>" preload="meta" controls></a>
<?php else: ?>
            <a><img src="http://feed.chujianapp.com/<?php echo ($v['resource']); ?>"></a><?php endif; ?>
                  <div class="caption">
                    <p class="text-nowrap"><b>创建时间:</b><?php echo date('Y-m-d H:i:s',$v['create_time']);?></p>
<?php if($v['score_time']): ?><p class="text-nowrap"><b>打分时间:</b><?php echo date('Y-m-d H:i:s',$v['score_time']);?></p><?php endif; ?>
<?php if($v['delete_time']): ?><p class="text-nowrap"><b>删除时间:</b><?php echo date('Y-m-d H:i:s',$v['delete_time']);?></p><?php endif; ?>
                  </div>
                </div>
              </div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
            </div><?php endif; ?>
          </div>

          <div class="tab-pane fade" id="tab3">
          </div>

          <div class="tab-pane fade" id="tab-user-chat">
            <div>
              <a class="btn btn-success get-chat-log" data-uid="<?php echo ($data['item']['uid']); ?>" data-day="7">最近7天</a>
              <a class="btn btn-success get-chat-log" data-uid="<?php echo ($data['item']['uid']); ?>" data-day="30">最近30天</a>
            </div>
            <hr>
            <div class="chat-logs"></div>
          </div>

          <div class="tab-pane fade" id="tab-user-closure">
            <form action="<?php echo U('closure');?>" method="POST" class="form-inline" style="width:100%;">
              <input type="hidden" name="report_id" value="<?php echo ($_REQUEST['report_id']); ?>">
              <input type="hidden" name="uid" value="<?php echo ($data['item']['uid']); ?>" placeholder="被举报人ID">
              <div class="form-group">
                <b class="label label-danger"><?php echo ($data['contract_types'][$data['live_host']['contract_type']]['attrs']['name']); ?></b>
              </div>
              <div class="form-group">
                <select name="status" class="form-control accusation-states">
                  <option value="">封禁状态</option>
<?php if(is_array($data['accusation_states'])): $i = 0; $__LIST__ = $data['accusation_states'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($v); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
              </div>
              <div class="form-group hide">
                <select name="reason" class="form-control">
                  <option value="">封禁理由</option>
<?php if(is_array($data['accusation_reasons'])): $i = 0; $__LIST__ = $data['accusation_reasons'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($v); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
              </div>
              <div class="form-group">
                <label>备注：</label>
                <input type="text" name="remark" placeholder="必填" class="form-control">
              </div>
              <button type="submit" class="btn btn-primary">提交</button>
              <div class="form-group">
                <label class="checkbox">
                  <input type="checkbox" name="msg2reporter" value="1" checked>
                  是否给举报人发消息
                </label>
              </div>
              <br><br>
              <textarea name="msg" placeholder="必填，话术将以系统消息发送给用户" rows="1" class="form-control" style="width:100%;"></textarea>
              <hr>
            </form>
            <div class="clearfix">
              <h3 class="pull-left">警告/封禁记录</h3>
              <a href="<?php echo U('report_base/index?oid='.$uid);?>" target="_blank" class="btn btn-primary pull-right">被举报记录</a>
            </div>
            <table class="table table-bordered text-center">
              <thead>
                <tr>
                  <th>ID</th>
                  <!--th>举报记录ID</th-->
                  <!--th>被封禁人ID</th-->
                  <th>封禁状态</th>
                  <!--th>封禁理由</th-->
                  <th>备注</th>
                  <th>话术</th>
                  <th>举报人ID</th>
                  <th>举报时间</th>
                  <th>操作管理员</th>
                  <th>操作时间</th>
                </tr>
              </thead>
              <tbody>
<?php if(is_array($data['accusation_logs'])): $i = 0; $__LIST__ = $data['accusation_logs'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><tr class="gradeX">
                  <td><?php echo ($v['id']); ?></td>
                  <!--td><a class="label label-default"><?php echo ($v['report_id'] ?: ''); ?></a></td-->
                  <!--td><a href="<?php echo U('view?uid='.$v['oid']);?>" target="_blank" class="label label-default"><?php echo ($v['oid']); ?></a></td-->
                  <td><?php echo ($data['accusation_states'][$v['status']]); ?></td>
                  <!--td><?php echo ($data['accusation_reasons'][$v['reason']]); ?></td-->
                  <td>
                    <div class="td-content"><?php echo htmlspecialchars($v['remark']);?></div>
                  </td>
                  <td>
                    <div class="td-content popover-hover"><?php echo htmlspecialchars($v['msg']);?></div>
                  </td>
                  <td><a href="<?php echo U('view?uid='.$v['uid']);?>" target="_blank" class="label label-default"><?php echo ($v['uid'] ?: ''); ?></a></td>
                  <td><?php echo ($v['report_time'] ? date('Y-m-d H:i',$v['report_time']) : ''); ?></td>
                  <td><?php echo ($data['accusation_admins'][$v['aid']]['nickname']); ?></td>
                  <td><?php echo ($v['create_time'] ? date('Y-m-d H:i',$v['create_time']) : ''); ?></td>
                </tr><?php endforeach; endif; else: echo "" ;endif; ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<div id="modal-avatar-del" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="about:blank" method="POST" class="form-block" onsubmit="return confirm('真的要这么做？')">
        <input type="hidden" name="resource">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">删除照片</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>备注：</label>
            <input type="text" name="remark" placeholder="必填" required class="form-control">
          </div>
          <div class="form-group">
            <label>话术：</label>
            <textarea name="msg" placeholder="必填，话术将以系统消息发送给用户" required class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">确定</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div id="modal-confirm" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="about:blank" method="POST" class="form-block" onsubmit="return confirm('真的要这么做？')">
        <input type="hidden" name="resource">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">操作确认</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>备注：</label>
            <input type="text" name="remark" placeholder="必填" required class="form-control">
          </div>
          <div class="form-group">
            <label>话术：</label>
            <textarea name="msg" placeholder="必填，话术将以系统消息发送给用户" required class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">确定</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script type="text/html" id="tpl-chat-log-item">
<$
G = $G('window');
$>
  <div class="media chat-msg<$=is_self ? ' chat-myself' : ''$>" data-id="<$=smsid$>">
    <div class="media-body">
      <p class="media-heading chat-info">
        <small class="chat-time"><$=time$></small>
        <a class="label label-primary"><$=sender$></a> 对
        <a class="label label-primary"><$=recver$></a> 说：
      </p>
      <p class="chat-text">
<$
if(texttype == '2' && text_json && text_json.thumbnailPhotoUrl)
{
$>
        <img src="<$=text_json.thumbnailPhotoUrl$>" data-src="<$=text_json.originPhotoUrl || text_json.thumbnailPhotoUrl$>" class="zoom">
<$
}
else
{
$>
        <$==text_html || text$>
<$
}
$>
      </p>
    </div>
  </div>
</script>

<link rel="stylesheet" href="//cdn.bootcss.com/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css">
<script src="//cdn.bootcss.com/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
<script src="/Public/layer/layer.min.js"></script>
<script src="/Public/layer/extend/layer.ext.js"></script>
<script>window.JSON || document.write('<script src="//cdn.bootcss.com/json2/20150503/json2.min.js"><\/script>');</script>
<script src="/Public/js/artTemplate-v3.0.0.js"></script>
<script src="/Public/js/app.comm.js"></script>
<script>
$(document).on('require.ready',function()
{

  window.template && (function()
  {
    template.config('openTag','<$');
    template.config('closeTag','$>');
    template.helper('$G',function(key){ return eval(key); });
  })();

  // 图片放大
  layer.photosPage(
  {
    parent:'.list-feed',
    title:''
  });

  (function()
  {
    //$.fn.editable.defaults.mode = 'inline';
    $('.editable').editable(
    {
      success : function(ret,val)
      {
        if(ret.status != 1) return ret.info || false;
        else return {newValue:val};
      }
    })
    .filter('.required').editable('option','validate',function(val)
    {
      if(!$.trim(val)) return '该字段不能为空！';
    })
    .end().filter('[data-validate]').editable('option','validate',function(val)
    {
      var the = $(this),
          pat = the.attr('data-validate'),
          arr = /^\/([^]+)\/([a-z]*)$/i.exec(pat) || [];
      if(arr[0]) pat = new RegExp(arr[1],arr[2] || '');
      else       pat = new RegExp(pat);
      if(!pat.test(val)) return '格式错误！';
    });
  })();

  $('body')
  .on('change','select.accusation-states',function()
  {
    var the = $(this),
        val = the.val(),
        hds = {0:1,5:1,6:1,'-2':1,'-1':1},
        ops = {'-3':1},
        sld = hds[val] ? 'slideUp' : 'slideDown',
        tip1 = ops[val] ? '选填' : '必填',
        tip2 = !ops[val] ? '选填' : '必填',
        obj = the.parents('form:first').find('[name="msg"]');
    obj.attr('placeholder',function()
    {
      return obj.attr('placeholder').replace(tip2,tip1);
    }).val('')[sld](500);
  })

  // 获取聊天记录
  .on('click','.get-chat-log',function()
  {
    var the = $(this),
        box = $('.chat-logs').empty(),
        tip = box.find('.tip-msg'),
        lst = $(),
        uid = parseInt(the.attr('data-uid')) || 0,
        day = parseInt(the.attr('data-day')) || 0;
    tip.length || (tip = $('<h3/>').addClass('tip-msg').prependTo(box));
    tip.html('加载中...');
    $.ajax(
    {
      url:'<?php echo U('get_chat_log');?>',
      data:{uid:uid,day:day},
      dataType:'json'
    })
    .done(function(data)
    {
      var dat = data.data || {};
      $.each(dat.list,function(i,v)
      {
        v.is_self = v.sender == uid;
        lst = lst.add($(template('tpl-chat-log-item',v)).appendTo(box));
      });
      if(lst.length) tip.remove();
      else tip.html('暂无记录！');
    });
  });

});
</script>  
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