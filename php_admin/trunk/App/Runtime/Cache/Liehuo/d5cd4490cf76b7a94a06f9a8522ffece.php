<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0">

<link rel="shortcut icon" type="image/x-icon" href="http://res.wx.qq.com/mmbizwap/zh_CN/htmledition/images/icon/common/favicon22c41b.ico">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="format-detection" content="telephone=no">
<title><?php echo ($data['item']['title']); ?></title>
<link rel="stylesheet" href="http://res.wx.qq.com/mmbizwap/zh_CN/htmledition/style/page/appmsg/page_mp_article_improve2a7a3f.css">
<!--[if lt IE 9]>
<link rel="stylesheet" href="http://res.wx.qq.com/mmbizwap/zh_CN/htmledition/style/page/appmsg/page_mp_article_improve_pc2a7a3f.css">
<![endif]-->
<link rel="stylesheet" href="http://res.wx.qq.com/mmbizwap/zh_CN/htmledition/style/page/appmsg/page_mp_article_improve_combo2a7a3f.css">
</head>
<body id="activity-detail" class="zh_CN mm_appmsg" ontouchstart="">

<div id="js_article" class="rich_media">
  
  <div id="js_top_ad_area" class="top_banner">
  </div>
      

  <div class="rich_media_inner">
    <div id="page-content">
      <div id="img-content" class="rich_media_area_primary">
        <h2 class="rich_media_title" id="activity-name">
          <?php echo ($data['item']['title']); ?>
        </h2>

        <div class="rich_media_meta_list">
          <em id="post-date" class="rich_media_meta rich_media_meta_text">2016-02-17</em>
          <a id="post-user" class="rich_media_meta rich_media_meta_link rich_media_meta_nickname" href="javascript:void(0);"></a>
        </div>

        <div id="js_content" class="rich_media_content">
<?php echo ($data['item']['content']); ?>
        </div>

        <div class="rich_media_tool" id="js_toobar3">
          <div id="js_read_area3" class="media_tool_meta tips_global meta_primary" style="display:none;">阅读 <span id="readNum3"></span></div>
          <span style="display:none;" class="media_tool_meta meta_primary tips_global meta_praise" id="like3">
            <i class="icon_praise_gray"></i><span class="praise_num" id="likeNum3"></span>
          </span>
          <a id="js_report_article3" style="display:none;" class="media_tool_meta tips_global meta_extra" href="javascript:void(0);">举报</a>
        </div>

      </div>
    </div>

    <div id="js_pc_qr_code" class="qr_code_pc_outer" style="display:none;">
      <div class="qr_code_pc_inner">
        <div class="qr_code_pc" style="display:none;">
          <img id="js_pc_qr_code_img" class="qr_code_pc_img">
          <p>微信扫一扫<br>关注该公众号</p>
        </div>
      </div>
    </div>

  </div>
</div>
</body>
</html>