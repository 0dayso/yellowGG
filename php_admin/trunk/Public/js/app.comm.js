// JS For chujian By Alone

(function(fun)
{
  var cdn = '//cdn.bootcss.com/',
      cfg =
  {
    waitSeconds:60 * 10,
    paths:
    {
      'jquery'               : [cdn + 'jquery/1.11.1/jquery.min','http://apps.bdimg.com/libs/jquery/1.11.1/jquery.min'],
      'json2'                : cdn + 'json2/20150503/json2.min',
      'require-css'          : cdn + 'require-css/0.1.8/css.min',
      'bootstrap'            : cdn + 'twitter-bootstrap/3.3.4/js/bootstrap.min',
      'bootstrap-css'        : cdn + 'twitter-bootstrap/3.3.4/css/bootstrap.min',
      'vue'                  : cdn + 'vue/1.0.22/vue.min',
      'moment'               : cdn + 'moment.js/2.11.2/moment.min',
      'moment-zh'            : cdn + 'moment.js/2.11.2/locale/zh-cn',
      'bs-datetimepicker'    : cdn + 'bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min',
      'bs-datetimepicker-css': cdn + 'bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min',
      'bs-daterangepicker'   : cdn + 'bootstrap-daterangepicker/2.1.19/daterangepicker.min',
      'bs-colorpicker'       : cdn + 'bootstrap-colorpicker/2.3.3/js/bootstrap-colorpicker.min',
      'bs-colorpicker-css'   : cdn + 'bootstrap-colorpicker/2.3.3/css/bootstrap-colorpicker.min',
      'animate-css'          : cdn + 'animate.css/3.3.0/animate.min',
      'font-awesome'         : cdn + 'font-awesome/4.3.0/css/font-awesome.min',
      'nprogress'            : cdn + 'nprogress/0.2.0/nprogress.min',
      'ckeditor'             : cdn + 'ckeditor/4.5.9/ckeditor',
      'ckeditor-jquery'      : cdn + 'ckeditor/4.5.9/adapters/jquery',
      'messenger'            : cdn + 'messenger/1.4.0/js/messenger.min',
      'messenger-future'     : cdn + 'messenger/1.4.0/js/messenger-theme-future',
      'messenger-css'        : cdn + 'messenger/1.4.0/css/messenger',
      'messenger-future-css' : cdn + 'messenger/1.4.0/css/messenger-theme-future',
      'highcharts'           : cdn + 'highcharts/4.1.7/highcharts',
      'video-js'             : cdn + 'video.js/5.10.1/video.min',
      'video-js-css'         : cdn + 'video.js/5.10.1/video-js.min',
      'video-js-hls'         : cdn + 'videojs-contrib-hls/2.1.1/videojs-contrib-hls',
      'jquery-jplayer'       : cdn + 'jplayer/2.9.2/jplayer/jquery.jplayer.min',
      'jquery-qrcode'        : cdn + 'jquery.qrcode/1.0/jquery.qrcode.min',
      'jquery-tablesorter'   : cdn + 'jquery.tablesorter/2.25.5/js/jquery.tablesorter.min',
      'jquery-sortable'      : cdn + 'jquery-sortable/0.9.13/jquery-sortable-min',
      'sortable'             : cdn + 'Sortable/1.4.2/Sortable.min',
      'webui-popover'        : cdn + 'webui-popover/1.2.7/jquery.webui-popover.min',
      'fullcalendar'         : cdn + 'fullcalendar/2.9.0/fullcalendar.min',
      'fullcalendar-zh'      : cdn + 'fullcalendar/2.9.0/lang/zh-cn',
      'fc-scheduler'         : '//fullcalendar.io/js/fullcalendar-scheduler-1.3.2/scheduler.min',
      'typeahead-bundle'     : cdn + 'typeahead.js/0.11.1/typeahead.bundle.min',
      'bootstrap3-typeahead' : cdn + 'bootstrap-3-typeahead/4.0.0-alpha/bootstrap3-typeahead.min',
      'blueimp-fileupload'   : cdn + 'blueimp-file-upload/9.10.4/jquery.fileupload',
      'jquery.ui.widget'     : cdn + 'blueimp-file-upload/9.10.4/vendor/jquery.ui.widget',
      'baidu-map'            : '//api.map.baidu.com/getscript?v=2.0&ak=33a7eecb43e5bf39954e55daf4015926&t=' + (new Date).getTime(),
      'art-template'         : '/Public/js/artTemplate-v3.0.0',
      'app-comm'             : '/Public/js/app.comm',
      'app-scoring'          : '/Public/js/app.scoring'
    },
    shim:
    {
      'json2'                : {exports:'JSON'},
      'bs-datetimepicker'    : {deps:['jquery','moment-zh','css!bs-datetimepicker-css'],exports:'jQuery.fn.datetimepicker'},
      'bs-daterangepicker'   : {deps:['jquery','moment','css!bs-daterangepicker'],exports:'jQuery.fn.daterangepicker'},
      'bs-colorpicker'       : {deps:['jquery','css!bs-colorpicker-css'],exports:'jQuery.fn.colorpicker'},
      'nprogress'            : {deps:['css!nprogress'],exports:'NProgress'},
      'ckeditor'             : {exports:'CKEDITOR'},
      'ckeditor-jquery'      : {deps:['ckeditor','jquery'],exports:'jQuery.fn.ckeditor'},
      'messenger'            : {deps:['jquery','css!messenger-css'],exports:'Messenger'},
      'messenger-future'     : {deps:['messenger','css!messenger-future-css'],exports:'Messenger'},
      'highcharts'           : {deps:['jquery'],exports:'jQuery.fn.highcharts'},
      'video-js'             : {deps:['css!video-js-css'],exports:'videojs'},
      'video-js-hls'         : {deps:['video-js'],exports:'videojs'},
      'webui-popover'        : {deps:['jquery','css!webui-popover'],exports:'jQuery.fn.webuiPopover'},
      'fullcalendar-zh'      : {deps:['fullcalendar','css!fullcalendar'],exports:'jQuery.fullCalendar'},
      'fc-scheduler'         : {deps:['fullcalendar-zh','css!fc-scheduler'],exports:'jQuery.fullCalendar.schedulerVersion'},
      'baidu-map'            : {exports:'BMap'},
      'art-template'         : {exports:'template'}
    },
    map:
    {
      '*' : {'css' : 'require-css'}
    }
  };

  // - Ajax Cache
  $.ajaxSetup({cache:true});

  $.heredoc || ($.heredoc = function(fun)
  {
     return fun.toString().split('\n').slice(1,-1).join('\n');
  });

  //window.jQuery ? jQuery(fun) : fun();
  if(window.require)
  {
    require(cfg);
    jQuery(fun);
  }
  else $.getScript(cdn + 'require.js/2.2.0/require.min.js')
  .done(function()
  {
    require(cfg);
    jQuery(fun);
  });
})

(function($)
{
// JQ

  (function($)
  {
    // 正则筛选
    $.expr[':'].regexp = function(ele,i,sel)
    {
      var pat = (sel || [])[3] || '',
          arr = /^\/([^]+)\/([a-z]*)$/i.exec(pat) || [],
          exp = arr[1] || '',
          mod = arr[2] || '',
          reg = mod ? new RegExp(exp,mod) : new RegExp(pat);
      //window[pat] || console.log([pat,arr,exp,reg]),window[pat] = 1;
      return (ele.innerHTML || '').match(reg) ? true : false;
    };
    $.fn.regexp || ($.fn.regexp = function(reg)
    {
      var ret = $();
      if($.type(reg) == 'regexp') this.each(function()
      {
        if((this.innerHTML || '').match(reg)) ret.push(this);
      });
      else ret = this.filter(':regexp(' + reg + ')');
      return this.pushStack(ret);
    });
  })(jQuery);

  window.define && define.amd && define('jquery',[],function(){return jQuery;});
  window.body = $('body:first');

  $(document)
  .on('artTemplate.ready',function()
  {
    window.template && (function()
    {
      template.config('openTag','<$');
      template.config('closeTag','$>');
      template.helper('_G',window);
      template.helper('$',window.jQuery);
      template.helper('$G',function(key){ return eval(key); });
    })();
  })
  .trigger('artTemplate.ready')

  // RequireJS 加载完成
  .trigger('require.ready');

  // ThinkPHP入口文件
  window.url_index = location.pathname.replace(/(.*?\.php)\b.*/i,'$1');

  body
  .on('click','.check-all:checkbox',function()
  {
    var the = $(this),
        fid = $.trim(the.attr('form') || '') || the.parents('form:first').attr('id'),
        frm = fid ? $('#' + fid).filter('form') : the.parents('form:first'),
        tar = the.data('target'),
        obj = frm.find(':checkbox');
    if(tar) obj = $(tar).filter(':checkbox');
    else if(fid) obj = obj.add($('[form="' + fid + '"]:checkbox'));
    obj.filter(':enabled').not(this).prop('checked',this.checked);
  })

  // 查看全图
  .on('click','img.zoom,.xubox_bigimg img',function()
  {
    var the = $(this),
        src = the.attr('data-src') || the.attr('src'),
        url = window.url_index;
    src && window.open(url + '/index/show_img_full?src=' + encodeURIComponent(src));
    return false;
  })

  .on('click','a.ajax-with-msg',function()
  {
    var the = $(this),
        url = the.data('href') || the.attr('href');
    $.ajax(
    {
      url:url
      ,data:{ajax:1}
      ,dataType:'json'
    })
    .done(function(data,textStatus,jqXHR)
    {
      var dat = data.data || {};
      data.info && require(['messenger-future'],function()
      {
        Messenger().post(
        {
          type:data.status == 1 ? 'success' : 'error',
          message:data.info
        });
      });
      dat.set_href && the.attr('href',dat.set_href);
      dat.set_text && the.text(dat.set_text);
    })
    .fail(function(jqXHR,textStatus,errorThrown)
    {
      console.log('ajax error',url,arguments);
    });
    return false;
  })

  .on('submit','form.ajax-with-msg',function()
  {
    var the = $(this),
        url = the.data('action') || the.attr('action'),
        dat = the.serializeArray();
    dat.push({name:'ajax',value:1});
    $.ajax(
    {
      url:url
      ,type:the.attr('method')
      ,data:dat
      ,dataType:'json'
    })
    .done(function(data,textStatus,jqXHR)
    {
      var dat = data.data || {};
      data.info && require(['messenger-future'],function()
      {
        Messenger().post(
        {
          type:data.status == 1 ? 'success' : 'error',
          message:data.info
        });
      });
    })
    .fail(function(jqXHR,textStatus,errorThrown)
    {
      console.log('ajax error',url,arguments);
    });
    return false;
  })

  // 动态删除
  .on('click','.act-feed-del',function()
  {
    var obj = $(this),
        url = obj.attr('data-href') || obj.attr('href'),
        box = $('#modal-feed-del');
    if(box.length < 1) return confirm('真的要这么做吗？');
    box.modal().find('form:first').attr('action',url);
    $(window).trigger('resize');
    return false;
  })
  // 照片删除
  .on('click','.act-avatar-del',function()
  {
    var obj = $(this),
        url = obj.attr('data-href') || obj.attr('href'),
        res = obj.attr('data-resource'),
        box = $('#modal-avatar-del');
    if(box.length < 1) return confirm('真的要这么做吗？');
    box.modal()
    .find('form:first').attr('action',url)
    .find('[name="resource"]').val(res)
    .end().find('[name="resource[]"]').remove();
    $(window).trigger('resize');
    return false;
  })
  // 照片批量删除
  .on('click','.act-avatar-del-bat',function()
  {
    var obj = $(this),
        url = obj.attr('data-href') || obj.attr('href'),
        box = $('#modal-avatar-del'),
        frm = box.find('form:first');
    if(box.length < 1) return confirm('真的要这么做吗？');
    box.modal();
    frm.attr('action',url).find('[name="resource"],[name="resource[]"]').remove();
    $('.list-album [name="resource[]"]:checked:enabled').each(function(i)
    {
      var the = $(this);
      frm.append($('<input>').attr({type:'hidden',name:the.attr('name')}).val(the.val()));
    });
    $(window).trigger('resize');
    return false;
  })
  // 字段清空
  .on('click','.act-modal-confirm,.act-text-clear',function()
  {
    var obj = $(this),
        url = obj.attr('data-href') || obj.attr('href'),
        box = $('#modal-confirm');
    if(box.length < 1) return confirm('真的要这么做吗？');
    box.modal()
    .find('form:first').attr('action',url);
    $(window).trigger('resize');
    return false;
  })
  // 资料批量清空
  .on('click','.act-text-clear-bat',function()
  {
    var obj = $(this),
        url = obj.attr('data-href') || obj.attr('href'),
        box = $('#modal-confirm'),
        frm = box.find('form:first');
    if(box.length < 1) return confirm('真的要这么做吗？');
    box.modal();
    frm.attr('action',url).find('[name^="clear_info["]').remove();
    $('.user-info [name^="clear_info["]:checked:enabled').each(function(i)
    {
      var the = $(this);
      frm.append($('<input>').attr({type:'hidden',name:the.attr('name')}).val(the.val()));
    });
    $(window).trigger('resize');
    return false;
  })
  // 文字/动态审核
  .on('click','.act-feed-audit,.act-user-field-pass',function()
  {
    var obj = $(this),
        url = obj.attr('data-href') || obj.attr('href');
    url && $.ajax(
    {
      url:url,
      data:{ajax:1},
      dataType:'json'
    })
    .done(function(data)
    {
      //data.info && alert(data.info);
      data.info && require(['messenger-future'],function()
      {
        Messenger().post(
        {
          type:data.status == 1 ? 'success' : 'error',//info
          message:data.info
        });
      });
      if(data.status == 1)
      {
        obj.parents('tr:first').find(':checkbox').prop('checked',false).attr('disabled',true);
        obj.remove();
      }
    });
    return false;
  })
  .on('click','.act-feed-audit-all',function()
  {
    var all = $('.list-feed .act-feed-audit');
    if(all.length < 1) $(this).remove();
    else if(confirm('确定要这么做吗？')) all.trigger('click');
    return false;
  })
  .on('click','.act-user-field-pass-all',function()
  {
    var all = $('.act-user-field-pass');
    if(all.length < 1) $(this).remove();
    else if(confirm('确定要这么做吗？')) all.trigger('click');
    return false;
  })

  // 图片上传
  .on('click','.image-upload-comm',function()
  {
    var the = $(this),
        tar = the.data('target') || '[name="thumb"]',
        box = the.parents('.form-group:first'),
        ipt = box.find(tar),
        old = ipt.val() || '';
    require(['jquery','blueimp-fileupload','messenger-future'],function($)
    {
      the.fileupload(
      {
        dataType:'json',
        formData:{ajax:1},
        change:function()
        {
          the.attr('disabled',true);
          ipt.val('');
        },
        done:function(e,ret)
        {
          var data = ret.result || {},
              tip = data.info || data.message || '';
          tip && Messenger().post(
          {
            type:data.status == 1 ? 'success' : 'error',//info
            message:tip,
            showCloseButton:true
          });
          if(data.status && data.data)
          {
            var dat = data.data || {};
            ipt.val(dat.resource || dat.filename).trigger('change');
          }
          else ipt.val(old);
          the.removeAttr('disabled');
        },
        fail:function()
        {
          the.removeAttr('disabled');
          ipt.val(old);
        }
      });
    });
  })

  // 语音播放
  .on('click','.jplayer-voice',function()
  {
    var the = $(this),
        $jp = $('#jPlayer'),
        src = the.attr('data-src') || the.attr('src') || the.attr('href');
    if(src) require(['jquery-jplayer'],function()
    {
      if(!$jp.length)
      {
        $jp = $('<div/>').attr({id:'jPlayer'}).appendTo('body').jPlayer(
        {
          swfPath  : '//cdn.bootcss.com/jplayer/2.9.2/jplayer/jquery.jplayer.swf',
          supplied : 'amr,mp3'
        });
      }
      $jp.jPlayer('setMedia',{amr:src,mp3:src}).jPlayer('play');
    });
    return false;
  })

  // 在线直播
  .on('click','.act-live-play',function()
  {
    var the = $(this),
        src = the.data('rtmp')   || '',
        hdl = the.data('hdl')    || '',
        hls = the.data('hls')    || '',
        rcd = the.data('record') || '',
        mod = $('#modal-comm').modal(),
        box = mod.find('.modal-body').empty(),
        ele = $('<video/>').addClass('video-js vjs-default-skin').appendTo(box);
    src && ele.append('<source type="rtmp/mp4" src="' + src + '">');
    hdl && ele.append('<source type="video/x-flv" src="' + hdl + '">');
    hls && ele.append('<source type="application/x-mpegURL" src="' + hls + '">');
    rcd && ele.append('<source type="application/x-mpegURL" src="' + rcd + '">');
    if(window.videojs)
    {
      typeof define === 'function' && define.amd
      && define('video-js-css',[],function(){ return videojs; })
      && define('video-js',[],function(){ return videojs; });
    }
    require(['video-js'/*,'video-js-hls'*/],function(videojs)
    {
      videojs.options.flash.swf = '//cdn.bootcss.com/video.js/5.10.1/video-js.swf';
      window.videojs || (window.videojs = videojs);
      videojs(ele[0],
      {
        width:536,
        controls:true,
        autoplay:true,
        preload:'auto'
      });
    });
    return false;
  })

  // 嵌入iframe
  .on('click','.act-modal-iframe',function()
  {
    var the = $(this),
        src = the.data('src')  || '',
        siz = the.data('size') || '16by9',
        mod = $('#modal-comm').modal(),
        box = mod.find('.modal-body').empty(),
        ele = $('<iframe/>').addClass('embed-responsive-item')
              .appendTo($('<div/>').addClass('embed-responsive embed-responsive-' + siz).appendTo(box));
    ele.attr('src',src);
    return false;
  })

  .on('hidden.bs.modal','.modal-auto-destroy',function()
  {
    $(this).find('.modal-body').empty();
  });


  (function()
  {
    body
    .on('change','.filter-fields',function()
    {
      var the = $(this),
          typ = the.data('type') || 'filter',
          val = the.val(),
          frm = $(the.prop('form') || the.parents('form:first')),
          fls = frm.find('[data-' + typ + ']'),
          sls = val == '' ? $() : fls.filter('[data-' + typ + '~="' + val + '"]');
      fls.hide().find(':enabled').add(fls.filter(':enabled')).attr('disabled',true);
      sls.stop().slideDown(500).find(':disabled').add(sls.filter(':disabled')).removeAttr('disabled');
    });
    $('.filter-fields:enabled').change().eq(1).change();
  })();

  // 手机验证码
  window.sayHi = function sayHi()
  {
    var val = $(this).val();
    if(!val || val.length != 11) return false;
    $.post(window.url_index + '/search/phonecode_base',{phone:val},function(data)
    {
      $('#top-search').val('' + data);
    });
  }
  $('#top-search').off('keyup').on('keyup',sayHi);

  // bootstrap tab 自动切换
  // url#tab // url?tab=tab
  $('.nav-tabs').length && (function()
  {
    var box = $('.nav-tabs'),
        tab = (/[?&]tab=([^&]+)/i.exec(location.search) || [])[1]
            || (/\/tab\/([^\/\\?&]+)/i.exec(location.pathname) || [])[1]
            || (location.hash || '').replace(/^#+\/?/,'');
    box.find('> :first-child [data-toggle~="tab"]').click();
    tab && box.find('[data-toggle~="tab"][href="#' + tab + '"],.' + tab).eq(0).click();
  })();

  // 分页样式
  (function(fun)
  {
    body.on('reload','.pagination',fun);
    $('.pagination').trigger('reload');
  })
  (function()
  {
    var box = $(this);
    box.find('.num,.prev,.next,.first,.last,.end').not('.btn')
      .addClass('btn btn-white')
      .filter('.prev').html('<i class="fa fa-chevron-left"></i>').end()
      .filter('.next').html('<i class="fa fa-chevron-right"></i>').end();
    box.find('.current:not(.btn)').addClass('btn btn-primary active');
    return false;
  });

  // 工具提示
  (function()
  {
    var tps = $('.tip');
    tps.length && tps.tooltip();
  })();

  // 弹框提示
  (function()
  {
    $('.popover-hover').each(function()
    {
      var the = $(this),
          con = the.data('content') || the.html();
      if($.trim(the.text()).length > 10) the.addClass('text-overflow').popover(
      {
        content:con,
        html:true,
        trigger:'hover focus',
        delay:{hide:300}
      });
    });
  })();

  // 头像弹框
  (function()
  {
    body.on('mouseenter focus','.popover-avatar',function()
    {
      var the = $(this),
          uid = parseInt(the.data('uid')) || parseInt($.trim(the.text())) || parseInt((/uid[=\/](\d+)/i.exec(the.attr('href')) || [])[1]),
          pla = the.data('placement') || 'right';
      if(uid) the.popover(
      {
        content:'<div style="width:200px;height:200px;"><img src="' + window.url_index + '/user_base/avatar?uid=' + uid + '" style="max-width:200px;max-height:200px;"></div>',
        html:true,
        trigger:'hover focus',
        placement:pla,
        animation:true,
        delay:{hide:300}
      }).popover('show');
      return false;
    });
  })();

  // 卡片弹框
  (function()
  {
    body
    .on('mouseenter focus','.popover-with-ajax',function()
    {
      var the = $(this),
          url = the.data('url') || the.data('href') || the.attr('href'),
          uid = parseInt(the.data('uid')) || parseInt($.trim(the.text())) || parseInt((/uid[=\/](\d+)/i.exec(url) || [])[1]),
          tpl = the.data('tpl') || 'tpl-popover-comm',
          pla = the.data('placement') || 'auto';
      if(the.data('has-popover')) the.webuiPopover('show');
      else require(['art-template','webui-popover'],function(art)
      {
        window.template || (window.template = art);
        $(document).trigger('artTemplate.ready');
        the.webuiPopover(
        {
          type:'async',
          url:url,
          content:function(data)
          {
            var dat = data.data || {};
            //console.log(arguments);
            if($('#' + tpl).length)
            {
              the.data('has-popover',1);
              return template(tpl,dat);
            }
          },
          trigger:'hover',
          placement:pla,
          animation:'pop'
        }).webuiPopover('show');
      });
    })
    .on('mouseenter focus','.popover-with-data',function()
    {
      var the = $(this),
          dat = the.data() || {},
          uid = parseInt(the.data('uid')) || parseInt($.trim(the.text())) || parseInt((/uid[=\/](\d+)/i.exec(the.attr('href')) || [])[1]),
          tpl = the.data('tpl') || 'tpl-popover-comm',
          pla = the.data('placement') || 'auto';
      if(the.data('has-popover')) the.webuiPopover('show');
      else require(['art-template','webui-popover'],function(art)
      {
        window.template || (window.template = art);
        $(document).trigger('artTemplate.ready');
        the.webuiPopover(
        {
          type:'html',
          content:function()
          {
            if($('#' + tpl).length)
            {
              the.data('has-popover',1);
              return template(tpl,dat);
            }
          },
          trigger:'hover',
          placement:pla,
          animation:'pop'
        }).webuiPopover('show');
      });
    });
  })();

  // 表格排序
  (function()
  {
    var tbs = $('.tablesorter');
    tbs.length && require(['jquery-tablesorter'],function()
    {
      tbs.tablesorter();
    });
  })();

  // 图片列表自动对齐
  $('.col-auto-height').length && $(window)
  .resize(function()
  {
    var obj = $(this),
        rtm = parseInt(obj.data('resize-times')) || 0,
        lst = $('.col-auto-height').height('auto').removeClass('col-last'),
        pre = $(),
        mlt = 0,
        mht = 0;
    lst.each(function(i)
    {
      var the = $(this),
          lft = the.offset().left || 0,
          hgt = the.height() || 0,
          pht = pre.height() || 0;
      if(lft <= mlt && pre.length && mht > 0)
      {
        pre.height(mht + (mht >= pht ? 1 : 0)).addClass('col-last');
        mlt = 0;
        mht = 0;
        lft = the.offset().left || 0;
        hgt = the.height() || hgt;
      }
      if(lft > mlt) mlt = lft;
      if(hgt > mht) mht = hgt;
      pre = the;
    });
    rtm < 10 && setTimeout(function(){ obj.data('resize-times',++rtm).trigger('resize'); },rtm < 5 ? 2000 : 5000);
  })
  .trigger('resize');

  // 日期时间
  (function(fun)
  {
    var obj = $('input.date-time'),
        rng = $('.date-time-ranges');
    if(obj.length) require(['bs-datetimepicker'],function()
    {
      window.moment || (window.moment = require('moment'));
      obj.each(fun);
    });
    if(rng.length) require(['moment'],function(moment)
    {
      var rls = [],
          rgs =
          {
            '今天':[moment().startOf('day'),moment().endOf('day')],
            '昨天':[moment().subtract(1,'days').startOf('day'),moment().subtract(1,'days').endOf('day')],
            '前天':[moment().subtract(2,'days').startOf('day'),moment().subtract(2,'days').endOf('day')],
            '近3天':[moment().subtract(2,'days').startOf('day'),moment().endOf('day')],
            '前3天':[moment().subtract(5,'days').startOf('day'),moment().subtract(3,'days').endOf('day')],
            '近7天':[moment().subtract(6,'days').startOf('day'),moment().endOf('day')],
            '近15天':[moment().subtract(14,'days').startOf('day'),moment().endOf('day')],
            '近30天':[moment().subtract(29,'days').startOf('day'),moment().endOf('day')],
            '本月':[moment().startOf('month'),''],
            '上月':[moment().subtract(1,'month').startOf('month'),moment().subtract(1,'month').endOf('month')],
            '今年':[moment().startOf('year'),''],
            '所有':['','']
          };
      $.each(rgs,function(k,v)
      {
        var stm = v[0] ? v[0].format('YYYY-MM-DD HH:mm:ss') : '',
            etm = v[1] ? v[1].format('YYYY-MM-DD HH:mm:ss') : '';
        rls.push('<li><a href="#" data-stime="' + stm + '" data-etime="' + etm + '">' + k + '</a></li>');
      });
      rng.each(function()
      {
        var the = $(this);
        the.append(rls.join(''));
      });
      rng.on('click','li a',function(e)
      {
        var the = $(this);
        obj.filter('[name="stime"]').val(the.data('stime') || '');
        obj.filter('[name="etime"]').val(the.data('etime') || '');
        e.preventDefault();
      });
    });
  })
  (function()
  {
    var ipt = $(this),
        fmt = ipt.data('format') || 'YYYY-MM-DD HH:mm:ss',
        frm = ipt.parents('form:first');
    ipt.datetimepicker(
    {
      locale:'zh-cn',
      format:fmt
    });
  });

  // 日期范围
  (function(fun)
  {
    var obj = $('input.date-range');
    if(obj.length) require(['bs-daterangepicker'],function()
    {
      window.moment || (window.moment = require('moment'));
      obj.each(fun);
    });
  })
  (function()
  {
    var ipt = $(this),
        frm = ipt.parents('form:first'),
        sti = $.trim(ipt.data('stime')),
        eti = $.trim(ipt.data('etime')),
        lmt = parseInt(ipt.data('limit')),
        sep = ' 到 ';
    sti = sti ? moment(sti) : moment('1970-01-01');//moment().subtract(29,'days')
    eti = eti ? moment(eti) : moment();
    ipt.val(sti + sep + eti);
    ipt.daterangepicker(
    {
      startDate:sti,
      endDate:eti,
      dateLimit:lmt ? {days:lmt} : false,
      format:'YYYY-MM-DD',
      separator:sep,
      showDropdowns:true,
      //opens:'left',
      ranges:
      {
        '今天':[moment(),moment()],
        '昨天':[moment().subtract(1,'days'),moment().subtract(1,'days')],
        '前天':[moment().subtract(2,'days'),moment().subtract(2,'days')],
        '近3天':[moment().subtract(2,'days'),moment()],
        '前3天':[moment().subtract(5,'days'),moment().subtract(3,'days')],
        '近7天':[moment().subtract(6,'days'),moment()],
        '近15天':[moment().subtract(14,'days'),moment()],
        '近30天':[moment().subtract(29,'days'),moment()],
        '本月':[moment().startOf('month'),moment()],
        '今年':[moment().startOf('year'),moment()],
        '所有':[moment('1970-01-01'),moment()]
      },
      locale:
      {
        format:'YYYY-MM-DD',
        separator:sep,
        cancelLabel:'关闭',
        applyLabel:'确认',
        daysOfWeek:["日","一","二","三","四","五","六"],
        monthNames:['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'],
        fromLabel:'',
        toLabel:'',
        customRangeLabel:'自定义'
      }
    },
    function(stime,etime)
    {
      var stm = stime.format('YYYY-MM-DD'),
          etm = etime.format('YYYY-MM-DD'),
          url = location.href;
      url = url.replace(/[se]time=[^&]*&?/gi,'');
      url = url.replace(/\/[se]time\/[^\/?&]*&?/gi,'');
      url = url.replace(/[?&]+$/,'');
      url += (url.indexOf('?') < 0 ? '?' : '&') + 'stime=' + stm + '&etime=' + etm;
      //location.href = url;
      frm.find('[name="stime"]').val(stm);
      frm.find('[name="etime"]').val(etm);
    });
  });

  // 颜色盘
  (function(fun)
  {
    var obj = $('.color-picker');
    if(obj.length) require(['bs-colorpicker'],function()
    {
      obj.colorpicker(
      {
        format:'hex'
      })
      .on('changeColor',function(e)
      {
        $(this).css('border-color',e.color.toHex());
      });
    });
  })();

  (function()
  {
    body
    .on('auto-rowspan','table,tbody',function()
    {
      var cls = '.auto-rowspan',
          cli = '.auto-rowspan-identic',
          trs = $(this).find('tr > ' + cls).parent(':visible');
      if(trs.length < 1) return false;
      trs.children(cls + ',' + cli).removeClass('auto-rowspan-ok').show().filter('[rowspan]').attr('rowspan',1);
      trs.eq(0).children(cls).each(function(idx)
      {
        var ptr = $(),
            ptd = $(),
            tdp = $(),
            rsp = 1,
            exp = false;
        trs.add($('<tr/>')).each(function(i)
        {
          var row = $(this),
              tds = row.children(cls),
              tdc = tds.eq(idx),
              tdp = ptd.eq(idx),
              tip = ptr.children(cli),
              tic = row.children(cli);
          exp = tdc.html() == tdp.html();
          if(tip.length > 0) tip.each(function(i)
          {
            var tdp = $(this),
                tdc = tic.eq(i);
            exp = exp && tdc.html() == tdp.html();
          });
          if(exp)
          {
            rsp++;
            tdc.hide();
            tic.filter(':not(.auto-rowspan-ok)').hide();
          }
          else
          {
            tdp.add(tip).addClass('auto-rowspan-ok');
            ptr = row;
            ptd = ptr.children(cls);
            tdp = ptd.eq(idx);
            tip = ptr.children(cli);
            rsp = 1;
          }
          tdp.attr('rowspan',rsp);
          tip.filter(':not(.auto-rowspan-ok)').attr('rowspan',rsp);
          //if(i == trs.length - 1 && exp) tdp.add(tip).addClass('auto-rowspan-ok');//last
        });
      });
      return false;
    })
    .on('auto-rowspan2','table,tbody',function()
    {
      $pNode = '';
      $eNode = '';
      $cNode = '';
      $pCount = 0;
      $pSize  = 1;
      $exist = 0;
      $(this).find('tr > .auto-rowspan').parent(':visible').each(function()
      {
        $pCount = $pCount + 1;
        //console.log($pCount);
        $eNode = $(this);
        $tTime  = $(this).find('td').eq(0).html();
        $tState = $(this).find('td > img').eq(0).attr('alt');
        if($pCount == 1)
        {
          $pNode = $(this);
        }
        $nNode = $eNode.next(':visible');
        $nTime  = $nNode.find('td').eq(0).html();
        $nState = $nNode.find('td > img').eq(0).attr('alt');
        if($tTime == $nTime && $tState == $nState )
        {
          $pSize = $pSize +1;
          $exist = 1;
        }
        else
        {
          if($exist)
          {
            $exist = 0;
            $pNode.find('td').eq(0).attr('rowspan',$pSize);
            $pNode.find('td').eq(1).attr('rowspan',$pSize);
            for($pSize;$pSize > 1;$pSize--)
            {
              $cNode = $pNode.next(':visible');
              $cNode.find('td').eq(0).hide();
              $cNode.find('td').eq(1).hide();
              $pNode = $cNode;
            }
          }
          $pSize  = 1;
          $pCount = 0;
        }
      });
      return false;
    });
    $('.an-auto-rowspan').trigger('auto-rowspan');
  })();

  window.define && define.amd && define('app-comm',['jquery'],function(){return body;});

  (function(fun)
  {
    setTimeout(fun,1000 * 1);
    setInterval(fun,1000 * 60);
  })
  (function()
  {
    0 && $('script[src^="http"],script[src^="//"]').each(function(i)
    {
      var src = $.trim($(this).attr('src'));
      if(!/^\s*(https?:)?\/\/[^\/?#]*\b(chujianapp\.com|cdn\.bootcss\.com|qq\.com|baidu\.com|bdimg\.com|sogou\.com|fullcalendar\.io")\b/.test(src))
      {
        alert('页面可能被黑客篡改，请联系后台管理员！！！\n' + [src,location.href].join('\n'));
        return false;
      }
    });
  });

// JQ
});