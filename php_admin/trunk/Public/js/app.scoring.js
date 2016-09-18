// JS For chujian By Alone

(function()
{

  if(!window.Scoring) return false;
  window.Scoring || (window.Scoring = {});
  Scoring.route  || (Scoring.route  = {});
  Scoring.option || (Scoring.option = {});
  window.feed_img_root = Scoring.option.feed_img_root;
  window.feed_timeout  = Scoring.option.feed_timeout;
  window.feed_timeout_highlight = Scoring.option.feed_timeout_highlight;
  window.ajax_interval = Scoring.option.ajax_interval;

  $('body')
  //刷新列表
  .on('renew','.list-scoring',function()
  {
    var box = $(this);
    $.ajax(
    {
      url:Scoring.route.query,
      data:{ajax:'1'},
      dataType:'json'
    })
    .done(function(data)
    {
      var dat = data.data || {},
          lst = dat.list || [],
          arr = {},
          ntc = 0;
      if(data.ret)
      {
        data.msg && console.error(data.msg);
        return false;
      }
      $('.scoring-count').text(dat.count || '-');
      $('.scoring-total').text(dat.total || '-');
      $.each(lst,function(i,v)
      {
        var row = box.find('[data-id="' + v.id + '"]');
        v.id || (v.id = v.feed_id);
        if(v.id)
        {
          v.img = (/^(https?:)?\/\//i.test($.trim(v.resource)) ? '' : window.feed_img_root) + v.resource;
          v.img += (/\?/.test(v.img) ? '&' : '?') + '_=' + (new Date).getTime();
          arr[v.id] = v;
          if(row.length < 1 && (parseInt(moment().format('X')) - (list_delete[v.id] || 0) > feed_timeout))
          {
            row = $(template('tpl-scoring-item',v)).appendTo(box);
            if(window.Notification && ntc < 12)
            {
              var ntf = new Notification('新的打分请求！',{icon:v.img});
              ntc++;
              ntf.onshow = function()
              {
                ntf.close && setTimeout(function(){ ntf.close();ntc--; },5000);
              };
              ntf.onclick = function()
              {
                window.focus();
                $('html').trigger('scroll-scoring');
              };
            }
            row.find('.scoring-shitu').trigger('shitu');
          }
          else if(row.length >= 1 && !row.is('[data-resource="' + v.resource + '"]'))
          {
            row.attr('data-resource',v.resource).find('img.resource').attr('src',v.img);
          }
        }
      });
      box.find('[data-id]:not([data-id=""])').each(function(i)
      {
        var row = $(this),
            _id = row.attr('data-id');
        if(_id && !arr[_id]) row.trigger('row-remove');
      });
      box.find('.scoring-timeout').trigger('timeout');
      box.filter('.animated').removeClass('animated fadeInRightBig');
      box.find('.scoring-deled').remove();
    })
    .fail(function()
    {
      console.error('error');
    });
    return false;
  })
  // 禁止打分
  .on('disabled','[data-id]',function()
  {
    var row = $(this).addClass('disabled');
    row.find('.act-scoring,.act-scoring-quick').addClass('disabled');
    row.find('input[name="score"]').attr('disabled',true);
    return false;
  })
  // 超时删除
  .on('row-remove','[data-id]',function()
  {
    var row = $(this),
        _id = parseInt(row.attr('data-id')) || 0;
    if(row.data('deling')) return false;
    row.data('deling',1).trigger('disabled').attr('data-id','');
    if(!Scoring.option.ajax_real_time) row.add(row.next('.scoring-blank')).addClass('scoring-deled');
    else row.delay(3000).animate({opacity:.05},500,function()
    {
      row.addClass('animated fadeOutLeftBig');
      setTimeout(function(){ row.add(row.next('.scoring-blank')).remove(); },300);
    })
    .find('img').delay(3000).animate({height:30},500);
    if(_id)
    {
      //list_delete[_id] = parseInt(moment().format('X')) || 1;
      if(0/*已禁用*/) $.get(Scoring.route.del,{ajax:1,id:_id})
      .done(function(data)
      {
        if(data.ret) delete list_delete[_id];
        console.info('del:' + _id + ' ' + (data.msg || '') + ' at ' + moment().format('YYYY-MM-DD HH:mm:ss'));
      })
      .fail(function()
      {
        delete list_delete[_id];
      });
    }
    return false;
  })

  // 响应时间
  .on('timeout','.scoring-timeout',function()
  {
    var the = $(this),
        row = the.parents('[data-id]:first'),
        tim = parseInt(the.attr('data-time')) || 0,
        sec = parseInt(the.attr('data-timeout')) || 0,
        ter = false;
    if(!the.data('hasTimeOut'))
    {
      ter = setInterval(function()
      {
        var css = '';
        sec++;
        if(row.is('.success,.disabled'))
        {
          clearInterval(ter);
          return false;
        }
        sec >= 30 * feed_timeout_highlight && (css = 'primary');
        sec >= 40 * feed_timeout_highlight && (css = 'warning');
        sec >= 50 * feed_timeout_highlight && (css = 'danger');
        // 自动打分
        if(sec >= 60 * feed_timeout_highlight && 0)
        {
          row.find('input[name="score"]').val(7.7);
          row.find('.act-scoring').trigger('click');
        }
        the.html(sec).addClass('label-' + css);
        row.addClass(css);
        sec > feed_timeout && row.trigger('row-remove');
      },1000);
      the.data('hasTimeOut',1);
    }
    return false;
  })

  // 检查盗图
  .on('shitu','.scoring-shitu',function()
  {
    var the = $(this),
        row = the.parents('[data-id][data-resource]:first'),
        res = row.attr('data-resource');
    console.log(res);
    res && $.ajax(
    {
      url:Scoring.route.shitu,
      data:{resource:res},
      dataType:'json'
    })
    .done(function(data)
    {
      if(data.ret) the.addClass('label-danger').text(data.msg);
    });
    return false;
  })

  // 打分操作
  .on('click','.act-scoring',function()
  {
    var btn = $(this),
        row = btn.parents('[data-id]:first'),
        ipt = row.find('input[name="score"]'),
        qbs = btn.add(row.find('.act-scoring-quick')),
        _id = parseInt(row.attr('data-id')) || 0,
        sco = parseFloat(ipt.val()) || 0,
        res = row.attr('data-resource') || '',
        ter = row.find('.scoring-timeout'),
        tmo = parseInt(ter.text() - ter.attr('data-timeout')) || 0,
        lay = -1;
    sco < 0  && (sco = 0);
    sco > 10 && (sco = 10);
    ipt.val(sco);
    if(btn.hasClass('disabled') || ipt.is('[disabled]')) return false;
    (function(fun)
    {
      if(sco == 0 || sco == 10) lay = layer.confirm('确定打【' + sco + '】分？',fun);
      else fun();
    })
    (function()
    {
      lay >= 0 && layer.close(lay);lay = -1;
      qbs.addClass('disabled');
      ipt.attr('disabled',true);
      $.ajax(
      {
        url:Scoring.route.save,
        data:{ajax:'1',id:_id,score:sco,resource:res,timeout:tmo},
        type:'POST',
        dataType:'json'
      })
      .done(function(data)
      {
        var dat = data.data || {};
        if(data.ret)
        {
          //data.msg && layer.msg(data.msg,1);
          data.msg && require(['messenger-future'],function()
          {
            Messenger().post(
            {
              type:data.ret == 0 ? 'success' : 'error',//info
              message:data.msg
            });
          });
          qbs.removeClass('disabled');
          ipt.removeAttr('disabled');
          return false;
        }
        // 打分成功
        row.removeClass('warning danger').addClass('success');
        setTimeout(function(){ row.trigger('row-remove'); },1000 * 3);
      })
      .fail(function()
      {
        qbs.removeClass('disabled');
        ipt.removeAttr('disabled');
        console.error('error');
      });
    });
  })
  .on('keypress','input[name="score"]',function(e)
  {
    e.keyCode == 13 && $(this).parents('[data-id]:first').find('.act-scoring').trigger('click');
  })
  // 快捷打分
  .on('click','.act-scoring-quick[data-score]',function()
  {
    var the = $(this),
        sco = the.data('score'),
        row = the.parents('[data-id]:first');
    if(the.is('.score-rand'))
    {
      sco = parseInt(sco) || 0;
      sco = parseInt(Math.random() * 10 + 10 * sco) / 10;
    }
    row.find('input[name="score"]:enabled').val(sco);
    row.find('.act-scoring:not(.disabled,[disabled])').click();
  })

  // 暂停/开启接收新分配
  .on('click','.scoring-stop-assign',function()
  {
    var the = $(this),
        url = the.attr('data-href') || the.attr('href'),
        val = the.attr('aria-pressed') == 'true' ? 0 : 1;
    $.get(url,{stop:val});
  })

  // 按性别分配
  .on('change','select[data-ajax]',function()
  {
    var the = $(this),
        url = the.attr('data-ajax'),
        key = the.attr('name'),
        val = the.val(),
        dat = {};
    dat[key] = val;
    $.get(url,dat)
    .done(function()
    {
      $('.list-scoring').trigger('renew');
    });
  });

  // 请求列表
  (function(fn)
  {
    window.list_delete || (window.list_delete = {});
    fn();
    setInterval(fn,ajax_interval);
    if(window.Notification && Notification.permission != 'granted') Notification.requestPermission();
  })
  (function()
  {
    var box = $('.list-scoring');
    if(Scoring.option.ajax_real_time) box.trigger('renew');
    else if(!box.find('[data-id][data-resource]:not(.scoring-deled)').length)
    {
      box.trigger('renew');
    }
  });

  $('html')
  .on('scroll-scoring',function()
  {
    $('html,body').animate({scrollTop:$('#scoring-box').offset().top || 0},1000);
  })
  .trigger('scroll-scoring');

  setTimeout(function()
  {
    $('.scoring-demo').trigger('row-remove');
  },1000 * 10);

  window.define && define.amd && define('app-scoring',['app-comm'],function(){return Scoring;});

})();