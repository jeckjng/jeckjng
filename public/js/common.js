;(function () {
    //全局ajax处理
    $.ajaxSetup({
        complete: function (jqXHR) {},
        data: {
        },
        error: function (jqXHR, textStatus, errorThrown) {
            //请求失败处理
        }
    });

    if ($.browser.msie) {
        //ie 都不缓存
        $.ajaxSetup({
            cache: false
        });
    }

    //不支持placeholder浏览器下对placeholder进行处理
    if (document.createElement('input').placeholder !== '') {
        $('[placeholder]').focus(function () {
            var input = $(this);
            if (input.val() == input.attr('placeholder')) {
                input.val('');
                input.removeClass('placeholder');
            }
        }).blur(function () {
            var input = $(this);
            if (input.val() == '' || input.val() == input.attr('placeholder')) {
                input.addClass('placeholder');
                input.val(input.attr('placeholder'));
            }
        }).blur().parents('form').submit(function () {
            $(this).find('[placeholder]').each(function () {
                var input = $(this);
                if (input.val() == input.attr('placeholder')) {
                    input.val('');
                }
            });
        });
    }

    // 所有加了dialog类名的a链接，自动弹出它的href
	if ($('a.js-dialog').length) {
		Wind.use('artDialog', 'iframeTools', function() {
			$('.js-dialog').on('click', function(e) {
				e.preventDefault();
				var $this = $(this);
				art.dialog.open($(this).prop('href'), {
					close : function() {
						$this.focus(); // 关闭时让触发弹窗的元素获取焦点
						return true;
					},
					title : $this.prop('title')
				});
			}).attr('role', 'button');

		});
	}

    // 所有的ajax form提交,由于大多业务逻辑都是一样的，故统一处理
    var ajaxForm_list = $('form.js-ajax-form');
    if (ajaxForm_list.length) {
        Wind.use('ajaxForm', 'artDialog', function () {
            if ($.browser.msie) {
                //ie8及以下，表单中只有一个可见的input:text时，会整个页面会跳转提交
                ajaxForm_list.on('submit', function (e) {
                    //表单中只有一个可见的input:text时，enter提交无效
                    e.preventDefault();
                });
            }

            $('button.js-ajax-submit').on('click', function (e) {
                e.preventDefault();
                /*var btn = $(this).find('button.js-ajax-submit'),
					form = $(this);*/
                var btn = $(this),
                    form = btn.parents('form.js-ajax-form');

                var opt = form_data_option(btn.data('option'));
                if(opt.confirm){ // 如果需要二次确认就 custconfirm
                    custconfirm(opt.msg,function () {
                        if(btn.data("loading")){
                            return;
                        }

                        //批量操作 判断选项
                        if (btn.data('subcheck')) {
                            btn.parent().find('span').remove();
                            if (form.find('input.js-check:checked').length) {
                                var msg = btn.data('msg');
                                if (msg) {
                                    art.dialog({
                                        id: 'warning',
                                        icon: 'warning',
                                        content: btn.data('msg'),
                                        cancelVal: '关闭',
                                        cancel: function () {
                                            //btn.data('subcheck', false);
                                            //btn.click();
                                        },
                                        ok: function () {
                                            btn.data('subcheck', false);
                                            btn.click();
                                        }
                                    });
                                } else {
                                    btn.data('subcheck', false);
                                    btn.click();
                                }

                            } else {
                                $('<span class="tips_error">请至少选择一项</span>').appendTo(btn.parent()).fadeIn('fast');
                            }
                            return false;
                        }

                        //ie处理placeholder提交问题
                        if ($.browser.msie) {
                            form.find('[placeholder]').each(function () {
                                var input = $(this);
                                if (input.val() == input.attr('placeholder')) {
                                    input.val('');
                                }
                            });
                        }

                        var waittime = opt.waittime ? opt.waittime : 0;
                        form.ajaxSubmit({
                            url: btn.data('action') ? btn.data('action') : form.attr('action'), //按钮上是否自定义提交地址(多按钮情况)
                            dataType: 'json',
                            beforeSubmit: function (arr, $form, options) {

                                btn.data("loading",true);
                                var text = btn.text();

                                //按钮文案、状态修改
                                btn.text(text + '中...').prop('disabled', true).addClass('disabled');
                            },
                            success: function (data, statusText, xhr, $form) {
                                waittime = waittime ? waittime : (data.wait ? Number(data.wait)*1000 : 3000);
                                var text = btn.text();

                                //按钮文案、状态修改
                                btn.removeClass('disabled').text(text.replace('中...', '')).parent().find('span').remove();
                                if (data.state === 'success') {
                                    // $('<span class="tips_success">' + data.info + '</span>').appendTo(btn.parent()).fadeIn('slow').delay(3000).fadeOut(function () {
                                    // });
                                    custshowmsg(data.info,waittime);
                                } else if (data.state === 'fail') {
                                    var $verify_img=form.find(".verify_img");
                                    if($verify_img.length){
                                        $verify_img.attr("src",$verify_img.attr("src")+"&refresh="+Math.random());
                                    }

                                    var $verify_input=form.find("[name='verify']");
                                    $verify_input.val("");

                                    // $('<span class="tips_error">' + data.info + '</span>').appendTo(btn.parent()).fadeIn('fast');
                                    custalert(data.info);
                                    btn.removeProp('disabled').removeClass('disabled');
                                }

                                setTimeout(function(){
                                    if (data.referer) {
                                        //返回带跳转地址
                                        if(window.parent.art){
                                            //iframe弹出页
                                            window.parent.location.href = data.referer;
                                        }else{
                                            window.location.href = data.referer;
                                        }
                                    } else {
                                        if (data.state === 'success') {
                                            if(window.parent.art){
                                                reloadPage(window.parent);
                                            }else{
                                                //刷新当前页
                                                reloadPage(window);
                                            }
                                        }
                                    }
                                },waittime)
                            },
                            complete: function(){
                                btn.data("loading",false);
                            }
                        });
                    });
                }else{  // 如果不需要二次确认就直接执行
                    if(btn.data("loading")){
                        return;
                    }

                    //批量操作 判断选项
                    if (btn.data('subcheck')) {
                        btn.parent().find('span').remove();
                        if (form.find('input.js-check:checked').length) {
                            var msg = btn.data('msg');
                            if (msg) {
                                art.dialog({
                                    id: 'warning',
                                    icon: 'warning',
                                    content: btn.data('msg'),
                                    cancelVal: '关闭',
                                    cancel: function () {
                                        //btn.data('subcheck', false);
                                        //btn.click();
                                    },
                                    ok: function () {
                                        btn.data('subcheck', false);
                                        btn.click();
                                    }
                                });
                            } else {
                                btn.data('subcheck', false);
                                btn.click();
                            }

                        } else {
                            $('<span class="tips_error">请至少选择一项</span>').appendTo(btn.parent()).fadeIn('fast');
                        }
                        return false;
                    }

                    //ie处理placeholder提交问题
                    if ($.browser.msie) {
                        form.find('[placeholder]').each(function () {
                            var input = $(this);
                            if (input.val() == input.attr('placeholder')) {
                                input.val('');
                            }
                        });
                    }

                    var waittime = opt.waittime ? opt.waittime : 0;
                    form.ajaxSubmit({
                        url: btn.data('action') ? btn.data('action') : form.attr('action'), //按钮上是否自定义提交地址(多按钮情况)
                        dataType: 'json',
                        beforeSubmit: function (arr, $form, options) {

                            btn.data("loading",true);
                            var text = btn.text();

                            //按钮文案、状态修改
                            btn.text(text + '中...').prop('disabled', true).addClass('disabled');
                        },
                        success: function (data, statusText, xhr, $form) {
                            waittime = waittime ? waittime : (data.wait ? Number(data.wait)*1000 : 3000);
                            var text = btn.text();

                            //按钮文案、状态修改
                            btn.removeClass('disabled').text(text.replace('中...', '')).parent().find('span').remove();
                            if (data.state === 'success') {
                                // $('<span class="tips_success">' + data.info + '</span>').appendTo(btn.parent()).fadeIn('slow').delay(3000).fadeOut(function () {
                                // });
                                custshowmsg(data.info,waittime);
                            } else if (data.state === 'fail') {
                                var $verify_img=form.find(".verify_img");
                                if($verify_img.length){
                                    $verify_img.attr("src",$verify_img.attr("src")+"&refresh="+Math.random());
                                }

                                var $verify_input=form.find("[name='verify']");
                                $verify_input.val("");

                                // $('<span class="tips_error">' + data.info + '</span>').appendTo(btn.parent()).fadeIn('fast');
                                custalert(data.info);
                                btn.removeProp('disabled').removeClass('disabled');
                            }

                            setTimeout(function(){
                                if (data.referer) {
                                    //返回带跳转地址
                                    if(window.parent.art){
                                        //iframe弹出页
                                        window.parent.location.href = data.referer;
                                    }else{
                                        window.location.href = data.referer;
                                    }
                                } else {
                                    if (data.state === 'success') {
                                        if(window.parent.art){
                                            reloadPage(window.parent);
                                        }else{
                                            //刷新当前页
                                            reloadPage(window);
                                        }
                                    }
                                }
                            },waittime)
                        },
                        complete: function(){
                            btn.data("loading",false);
                        }
                    });
                }
            });
        });
    }

    /*
    * 新定义 js ajax form 提交
    * */
    $('form.cust-js-ajax-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $(this).find("button.cust-js-ajax-submit");
        var opt = form_data_option(btn.data('option'));
        var waittime = opt.waittime ? opt.waittime : 0;

        Wind.use('ajaxForm', 'artDialog',function () {
            if(opt.confirm){ // 如果需要二次确认就 custconfirm
                custconfirm(opt.msg,function () {
                    form.ajaxSubmit({
                        url: form.attr('action'), //按钮上是否自定义提交地址(多按钮情况)
                        dataType: 'json',
                        beforeSubmit: function (arr, $form, options) {
                            btn.data("loading",true);
                            //按钮文案、状态修改
                            btn.text(btn.text() + '中...').prop('disabled', true).addClass('disabled');
                        },
                        success: function (data, statusText, xhr, $form) {
                            waittime = waittime ? waittime : (data.wait ? Number(data.wait)*1000 : 3000);
                            //按钮文案、状态修改
                            btn.removeClass('disabled').text(btn.text().replace('中...', '')).parent().find('span').remove();
                            if (data.state === 'success') {
                                custshowmsg(data.info,waittime);
                                setTimeout(function(){
                                    if (data.referer) {
                                        if(data.referer == 'preload'){
                                            parent.location.reload();
                                        }else{
                                            if(window.parent.art){
                                                window.parent.location.href = data.referer;
                                            }else{
                                                window.location.href = data.referer;
                                            }
                                        }
                                    }else{
                                        if(window.parent.art){
                                            parent.location.reload();
                                        }else{
                                            window.location.reload();
                                        }
                                    }
                                },waittime)
                            } else if (data.state === 'fail') {
                                btn.removeProp('disabled').removeClass('disabled');
                                custalert(data.info);
                            }
                        },
                        complete: function(){ btn.data("loading",false); }
                    });
                });
            }else{  // 如果不需要二次确认就直接执行
                form.ajaxSubmit({
                    url: form.data('action') ? form.data('action') : form.attr('action'), //按钮上是否自定义提交地址(多按钮情况)
                    dataType: 'json',
                    beforeSubmit: function (arr, $form, options) {
                        btn.data("loading",true);
                        //按钮文案、状态修改
                        btn.text(btn.text() + '中...').prop('disabled', true).addClass('disabled');
                    },
                    success: function (data, statusText, xhr, $form) {
                        waittime = waittime ? waittime : (data.wait ? Number(data.wait)*1000 : 3000);
                        //按钮文案、状态修改
                        btn.removeClass('disabled').text(btn.text().replace('中...', '')).parent().find('span').remove();
                        if (data.state === 'success') {
                            custshowmsg(data.info,waittime);
                            setTimeout(function(){
                                if (data.referer) {
                                    if(data.referer == 'preload'){
                                        parent.location.reload();
                                    }else {
                                        if (window.parent.art) {
                                            window.parent.location.href = data.referer;
                                        } else {
                                            window.location.href = data.referer;
                                        }
                                    }
                                }else{
                                    if(window.parent.art){
                                        parent.location.reload();
                                    }else{
                                        window.location.reload();
                                    }
                                }
                            },waittime)
                        } else if (data.state === 'fail') {
                            btn.removeProp('disabled').removeClass('disabled');
                            custalert(data.info);
                        }
                    },
                    complete: function(){ btn.data("loading",false); }
                });
            }
        })
    })

    //dialog弹窗内的关闭方法
    $('#js-dialog_close').on('click', function (e) {
        e.preventDefault();
        try{
            art.dialog.close();
        }catch(err){
            Wind.use('artDialog','iframeTools',function(){
                art.dialog.close();
            });
        };
    });

    //所有的删除操作，删除数据后刷新页面
    if ($('a.js-ajax-delete').length) {
        Wind.use('artDialog', function () {
            $('.js-ajax-delete').on('click', function (e) {
                e.preventDefault();
                var $_this = this,
                    $this = $($_this),
                    href = $this.prop('href'),
                    msg = $this.data('msg');
                art.dialog({
                    title: false,
                    icon: 'question',
                    content: '确定要删除吗？',
                    follow: $_this,
                    close: function () {
                        $_this.focus();; //关闭时让触发弹窗的元素获取焦点
                        return true;
                    },
                    okVal:"确定",
                    ok: function () {
                    	
                        $.getJSON(href).done(function (data) {
                            if (data.state === 'success') {
                                if (data.referer) {
																		
                                    location.href = data.referer;
                                } else {
                                    reloadPage(window);
                                }
                            } else if (data.state === 'fail') {
                                //art.dialog.alert(data.info);
                            	//alert(data.info);//暂时处理方案
				art.dialog({   
					content: data.info,
					icon: 'warning',  
					ok: function () {   
						this.title(data.info);   
						return true;   
					}
				}); 
                            }
                        });
                    },
                    cancelVal: '关闭',
                    cancel: true
                });
            });

        });
    }
    
    
    if ($('a.js-ajax-dialog-btn').length) {
        Wind.use('artDialog', function () {
            $('.js-ajax-dialog-btn').on('click', function (e) {
                e.preventDefault();
                var $_this = this,
                    $this = $($_this),
                    href = $this.prop('href'),
                    msg = $this.data('msg');
                if(!msg){
                	msg="您确定要进行此操作吗？";
                }
                art.dialog({
                    title: false,
                    icon: 'question',
                    content: msg,
                    follow: $_this,
                    close: function () {
                        $_this.focus();; //关闭时让触发弹窗的元素获取焦点
                        return true;
                    },
                    ok: function () {
												if(href.indexOf("backup")>=0){
													 $.showLoading("处理中...");
												}
                        $.getJSON(href).done(function (data) {
                            if (data.state === 'success') {
                                if (data.referer) {
																		art.dialog({   
																		content: data.info,
																		icon: 'succeed',
																		ok: function () {   
																				location.href = data.referer;
																				return true;   
																				}
																		}); 
                                   
                                } else {	
																	art.dialog({   
																		content: data.info,
																		icon: 'succeed',
																		ok: function () {   
																				reloadPage(window); 
																				return true;   
																				}
																		}); 
                                }
                            } else if (data.state === 'fail') {
                                //art.dialog.alert(data.info);
				art.dialog({   
					content: data.info,
					icon: 'warning',
					ok: function () {   
						this.title(data.info);   
						return true;   
					}
				}); 
                            }
                        });
                    },
                    cancelVal: '关闭',
                    cancel: true
                });
            });

        });
    }

    /*复选框全选(支持多个，纵横双控全选)。
     *实例：版块编辑-权限相关（双控），验证机制-验证策略（单控）
     *说明：
     *	"js-check"的"data-xid"对应其左侧"js-check-all"的"data-checklist"；
     *	"js-check"的"data-yid"对应其上方"js-check-all"的"data-checklist"；
     *	全选框的"data-direction"代表其控制的全选方向(x或y)；
     *	"js-check-wrap"同一块全选操作区域的父标签class，多个调用考虑
     */

    if ($('.js-check-wrap').length) {
        var total_check_all = $('input.js-check-all');

        //遍历所有全选框
        $.each(total_check_all, function () {
            var check_all = $(this),
                check_items;

            //分组各纵横项
            var check_all_direction = check_all.data('direction');
            check_items = $('input.js-check[data-' + check_all_direction + 'id="' + check_all.data('checklist') + '"]');
            
            //点击全选框
            check_all.change(function (e) {
                var check_wrap = check_all.parents('.js-check-wrap'); //当前操作区域所有复选框的父标签（重用考虑）

                if ($(this).attr('checked')) {
                    //全选状态
                    check_items.attr('checked', true);

                    //所有项都被选中
                    if (check_wrap.find('input.js-check').length === check_wrap.find('input.js-check:checked').length) {
                        check_wrap.find(total_check_all).attr('checked', true);
                    }

                } else {
                    //非全选状态
                    check_items.removeAttr('checked');
                    
                    check_wrap.find(total_check_all).removeAttr('checked');

                    //另一方向的全选框取消全选状态
                    var direction_invert = check_all_direction === 'x' ? 'y' : 'x';
                    check_wrap.find($('input.js-check-all[data-direction="' + direction_invert + '"]')).removeAttr('checked');
                }

            });

            //点击非全选时判断是否全部勾选
            check_items.change(function () {

                if ($(this).attr('checked')) {

                    if (check_items.filter(':checked').length === check_items.length) {
                        //已选择和未选择的复选框数相等
                        check_all.attr('checked', true);
                    }

                } else {
                    check_all.removeAttr('checked');
                }

            });


        });

    }

    //日期选择器
    var dateInput = $("input.js-date")
    if (dateInput.length) {
        Wind.use('datePicker', function () {
            dateInput.datePicker();
        });
    }

    //日期+时间选择器
    var dateTimeInput = $("input.js-datetime");
    if (dateTimeInput.length) {
        Wind.use('datePicker', function () {
            dateTimeInput.datePicker({
                time: true
            });
        });
    }

    //tab
    var tabs_nav = $('ul.js-tabs-nav');
    if (tabs_nav.length) {
        Wind.use('tabs', function () {
            tabs_nav.tabs('.js-tabs-content > div');
        });
    }

})();

//重新刷新页面，使用location.reload()有可能导致重新提交
function reloadPage(win) {
    var location = win.location;
    location.href = location.pathname + location.search;
}

/**
 * 页面跳转
 * @param url
 */
function redirect(url) {
    location.href = url;
}

/**
 * 读取cookie
 * @param name
 * @returns
 */
function getCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1, c.length);
		}
		if (c.indexOf(nameEQ) == 0) {
			return c.substring(nameEQ.length, c.length);
		}
	}
	

	return null;
}

// 设置cookie
function setCookie(name, value, days) {
    var argc = setCookie.arguments.length;
    var argv = setCookie.arguments;
    var secure = (argc > 5) ? argv[5] : false;
    var expire = new Date();
    if(days==null || days==0) days=1;
    expire.setTime(expire.getTime() + 3600000*24*days);
    document.cookie = name + "=" + escape(value) + ("; path=/") + ((secure == true) ? "; secure" : "") + ";expires="+expire.toGMTString();
}

/**
 * 打开iframe式的窗口对话框
 * @param url
 * @param title
 * @param options
 */
function open_iframe_dialog(url, title, options) {
	var params = {
		title : title,
		lock : true,
		opacity : 0,
		width : "95%"
	};
	params = options ? $.extend(params, options) : params;
	Wind.use('artDialog', 'iframeTools', function() {
		art.dialog.open(url, params);
	});
}

/**
 * 打开地图对话框
 * 
 * @param url
 * @param title
 * @param options
 * @param callback
 */
function open_map_dialog(url, title, options, callback) {

	var params = {
		title : title,
		lock : true,
		opacity : 0,
		width : "95%",
		height : 400,
		ok : function() {
			if (callback) {
				var d = this.iframe.contentWindow;
				var lng = $("#lng_input", d.document).val();
				var lat = $("#lat_input", d.document).val();
				var address = {};
				address.address = $("#address_input", d.document).val();
				address.province = $("#province_input", d.document).val();
				address.city = $("#city_input", d.document).val();
				address.district = $("#district_input", d.document).val();
				callback.apply(this, [ lng, lat, address ]);
			}
		}
	};
	params = options ? $.extend(params, options) : params;
	Wind.use('artDialog', 'iframeTools', function() {
		art.dialog.open(url, params);
	});
	
}

/*
* 把数据处理成对象，例如：{confirm:true,msg:保存,waittime:1000}
* */
function form_data_option(str){
    var dataarr = {};
    if(!str){
        return dataarr;
    }
    str = str.split('{')[1].split('}')[0];
    $.each(str.split(','),function (i,v){
        var arr = v.split(':');
        dataarr[$.trim(arr[0])] = $.trim(arr[1]);
    })
    return dataarr;
}

/*
* 新定义 展示消息
* */
function custshowmsg(msg,time){
    msg = msg ? msg : '';
    time = time>0 ? time : 3000;
    var html =	'<div id="cust-custshowmsg" style="top: 50px;z-index: 19891015;position:fixed;">' +
        '<div class="cust-custshowmsg-content" style="margin: 0;border-radius: 3px;font-weight: 400;position: relative;color: white;background: rgba(0, 0, 0, 0.5);padding: 15px 30px;font-size: 14px;word-break: break-all;">'+msg+'</div>' +
        '</div>';
    $("body").children('#cust-custshowmsg').remove();
    $("body").append(html);

    var left = (document.body.clientWidth-$(".cust-custshowmsg-content").width())/2;
    $(".cust-custshowmsg-content").css('left',left);

    setTimeout(function (){
        $("body").children('#cust-custshowmsg').remove();
    },time);
}

/*
* 新定义 alert弹框
* */
function custalert(msg,func){
    msg = msg ? msg : '';
    var html = '<style type="text/css" >' +
        '.cust-alert-dialog-btn span{height: 28px;line-height: 28px;margin: 5px 5px 0;padding: 6px 15px;border-radius: 2px;font-weight: 400;cursor: pointer;}' +
        '.cust-alert-dialog-btn .cust-alert-dialog-btn0{border: 1px solid #1E9FFF;background-color: #1E9FFF;color: #fff;}' +
        '.cust-alert-dialog-btn .cust-alert-dialog-btn0:hover{background-color: #4BAFFA;}' +
        '</style>'+
        '<div id="cust-alert">' +
        '<div class="cust-alert-shade" style="width:100%; height: 100%;left: 0;top: 0;position:fixed;z-index:19891014;background: rgba(0, 0, 0, 0.3);"></div>'+
        '<div class="cust-alert-dialog" style="min-width: 260px;top:50px;left: 50px;z-index: 19891015;position:fixed;background: #fff;font: 14px Helvetica Neue,Helvetica,PingFang SC,Tahoma,Arial,sans-serif;color: #23262e;border-radius: 2px;">' +
        '<div class="cust-alert-dialog-content" style="position: relative;padding: 20px;line-height: 24px;font-size: 14px;word-break: break-all;">'+msg+'</div>' +
        '<div class="cust-alert-dialog-btn" style="text-align: right;padding: 0 15px 12px;">' +
        '<span class="cust-alert-dialog-btn0">确定</span>' +
        '</div>' +
        '</div>' +
        '</div>';
    $("body").children('#cust-alert').remove();
    $("body").append(html);

    var left = (document.body.clientWidth-$(".cust-alert-dialog").width())/2;
    $(".cust-alert-dialog").css('left',left);

    $(".cust-alert-dialog-btn0").on('click',function(){
        $('#cust-alert').remove();
        if(func){
            func();
        }
    });
}

/*
* 新定义 confirm确认是否操作
* */
function custconfirm(msg,func,fullmsg){
    var title = msg ? (fullmsg== true ? msg : '您确定要'+msg+'吗？') : '您确定要执行此操作吗？';
    var html = '<style type="text/css" >' +
                    '.cust-confirm-dialog-btn span{height: 28px;line-height: 28px;margin: 5px 5px 0;padding: 6px 15px;border-radius: 2px;font-weight: 400;cursor: pointer;}' +
                    '.cust-confirm-dialog-btn .cust-confirm-dialog-btn0{border: 1px solid #1E9FFF;background-color: #1E9FFF;color: #fff;}' +
                    '.cust-confirm-dialog-btn .cust-confirm-dialog-btn1{border: 1px solid #dedede;background-color: #fff;color: #333;}' +
                    '.cust-confirm-dialog-btn .cust-confirm-dialog-btn0:hover{background-color: #4BAFFA;}' +
                    '.cust-confirm-dialog-btn .cust-confirm-dialog-btn1:hover{color: rgba(51, 51, 51, 0.85);}' +
                '</style>'+
                '<div id="cust-confirm">' +
                    '<div class="cust-confirm-shade" style="width:100%; height: 100%;left: 0;top: 0;position:fixed;z-index:19891014;background: rgba(0, 0, 0, 0.3);"></div>'+
                    '<div class="cust-confirm-dialog" style="min-width: 260px;top:50px;left: 50px;z-index: 19891015;position:fixed;background: #fff;font: 14px Helvetica Neue,Helvetica,PingFang SC,Tahoma,Arial,sans-serif;color: #23262e;border-radius: 2px;">' +
                        '<div class="cust-confirm-dialog-content" style="position: relative;padding: 20px;line-height: 24px;font-size: 14px;word-break: break-all;">'+title+'</div>' +
                        '<div class="cust-confirm-dialog-btn" style="text-align: right;padding: 0 15px 12px;">' +
                            '<span class="cust-confirm-dialog-btn0">确定</span>' +
                            '<span class="cust-confirm-dialog-btn1">取消</span>' +
                        '</div>' +
                    '</div>' +
                '</div>';
    $("body").children('#cust-confirm').remove();
    $("body").append(html);

    var left = (document.body.clientWidth-$(".cust-confirm-dialog").width())/2;
    $(".cust-confirm-dialog").css('left',left);

    $(".cust-confirm-dialog-btn0").on('click',function(){
        $('#cust-confirm').remove();
        if(func){
            func();
        }
    });
    $(".cust-confirm-dialog-btn1").on('click',function(){
        $('#cust-confirm').remove();
    });
}

/*
* 新定义 弹出iframe页面
* */
$(".cust-iframe-pop").on('click',function (event){
    event.preventDefault();
    var href = $(this).attr('href');
    var title = $(this).attr('title');
    var opt = form_data_option($(this).attr('data-iframe'));
    var width = opt.width ? opt.width : '400px';
    var height = opt.height ? opt.height : '350px';
    var top = opt.top ? opt.top : '50px';
    var left = opt.left ? opt.left : '50px';
    var background = opt.background ? opt.background : 'rgba(0,0,0,0.1)';

    var html = '<div id="cust-js-iframe" style="display:none;">' +
        '<style type="text/css" >' +
        '.cust-iframe-shade{width:100%; height: 100%;left: 0;top: 0;position:fixed;z-index:19891014; background: '+background+';}' +
        '.cust-iframe-page{width: '+width+';height: '+height+';top:'+top+';left:'+left+' !important;z-index:19891015;position:fixed;background: #fff;}' +
        '</style>' +
        '<div class="cust-iframe-shade" ></div>' +
        '<div class="cust-iframe-page">' +
        '<iframe width="100%" height="100%" src="'+href+'" name="x-cust-iframe" class="x-cust-iframe" id="x-cust-iframe" style="border-width: 0px;"></iframe>' +
        '</div>' +
        '</div>';
    $("body").children('#cust-js-iframe').remove();
    $("body").append(html);
    var left = (document.body.clientWidth-$(".cust-iframe-page").width())/2;
    $(".cust-iframe-page").css('left',left);
    $('#cust-js-iframe').toggle();
})

/*
* 新定义 js ajax请求
* */
$(".cust-js-ajax").on('click',function (event){
    event.preventDefault();
    var href = $(this).attr('href');
    custconfirm($(this).attr('confirm'),function (){
        $.get(href, {}, function(res) {
            var waittime = waittime ? waittime : (res.wait ? Number(res.wait)*1000 : 2000);
            if(res.status == 1){
                if(res.info.msg){
                    custshowmsg(res.info.msg)
                }else{
                    custshowmsg(res.info)
                }
                setTimeout(function (){
                    if(res.referer){
                        location.href = res.referer;
                    }else{
                        location.reload();
                    }
                },waittime)
            }else{
                if(res.info.msg){
                    custalert(res.info.msg)
                }else{
                    custalert(res.info)
                }
            }
        });
    },true)
})

/*
* 新定义 图片上传
* */
window.CUST_UPLOAD = function() {
    $('.cust-upload-img').off('click')
    $('.cust-upload-img').on('click', function(event) {
        event.preventDefault();
        var name = $(this).data('name') ? $(this).data('name') : '';
        var timeNum = $(this).data('timenum') ? $(this).data('timenum') : '';
        var showimgclass = $(this).data('showimgclass') ? $(this).data('showimgclass') : '';
        var progress = $(this).data('progress') ? $(this).data('progress') : 0;
        var accept = $(this).attr('data-accept') ? $(this).attr('data-accept') : 'image/*';
        var filename = $(this).data('filename') ? $(this).data('filename') : 'image';
        var max_size = $(this).data('max_size') ? $(this).data('max_size') : 30;
        var cust_change_file_func = $(this).data('cust_change_file_func') ? $(this).data('cust_change_file_func') : 'cust_change_img_file';
        if(name == ''){
            custalert('请加上data-name属性')
            return false;
        }
        var html = '<form method="post" class="cust-img-js-ajax-form" action="'+$(this).data('url')+'" style="display: none;">' +
            '<input type="file" id="file" name="'+filename+'" data-name="'+name+'" data-showimgclass="'+showimgclass+'" data-progress="'+progress+'" data-max_size="'+max_size+'" accept="'+accept+'" data-timenum1="'+ timeNum +'" onChange="'+cust_change_file_func+'(this)">' +
            '<button type="submit" class="cust-img-js-ajax-submit" ></button>' +
            '</form>';
        $("body").children('form.cust-img-js-ajax-form').remove();
        $("body").append(html);
        $("form.cust-img-js-ajax-form").children("input[name='"+filename+"']").trigger('click');
    })
};
window.CUST_UPLOAD();
function cust_change_img_file(obj){
    Wind.use('ajaxForm',function () {
        var form = $(obj).parent();
        $("body").children('#cust-progress-content').remove();
        is_finish = false;
        form.ajaxSubmit({
            url: form.attr('action'), //按钮上是否自定义提交地址(多按钮情况)
            dataType: 'json',
            beforeSubmit: function (arr, $form, options) {
                // console.log(arr[0]['value']['size'])
                // console.log($form)
                // console.log(options)
                var max_size = parseInt($(obj).attr('data-max_size'));
                // console.log('max_size:'+max_size)
                if($(obj).attr('name')=='image' && arr[0]['value']['size'] > (1024*1024*max_size)){
                    custalert('文件大小不能大于 '+max_size+'M')
                    return false;
                }
            },
            success: function (data, statusText, xhr, $form) {
                $("body").children('#cust-progress-content').remove();
                is_finish = true;
                if(data.status=='1'){
                    var timeNum = $(obj).data('timenum1') ? $(obj).data('timenum1') : '';
                    var name = $(obj).data('name');
                    var $input = $("input[data-timenum='"+timeNum+"']")[0] && $("input[data-timenum='"+timeNum+"']") || $("input[name='"+name+"']:not(input[data-timenum])");
                    $input.val(data.info);
                    if($(obj).data('showimgclass')){
                        var showimgclass = $(obj).data('showimgclass');
                        var showimgclassNum = $("img[data-showimgclassnum="+ timeNum +"]")[0];
                        var $img = showimgclassNum && $(showimgclassNum) || $("."+showimgclass + ":not(img[data-showimgclassnum])");
                        $img.attr('src','');
                        $img.attr('src',data.info);
                        var changefunc = $img.attr('data-changefunc');
                        if(changefunc && changefunc.length > 0){
                            eval(changefunc+'($("."+showimgclass))');
                        }
                    }
                    custshowmsg('上传成功')
                }
                $("form.cust-img-js-ajax-form").remove();
            },
            xhr: function(){
                is_finish = true;
                if($(obj).data('progress')=='1'){
                    var html = '<div id="cust-progress-content" style="top: 50px;z-index: 19891015;position:fixed;width:550px;height:14px;line-height:14px;padding:0;margin:0;">' +
                        '<div class="cust-progress-line" style="display:inline-block;width:402px;height:14px;line-height:14px;background-color:#fbfbfb;padding:0;margin:0;border:1px solid #777777;border-radius:7px;">' +
                        '<p class="cust-progress-rate" style="background-color:#73c944;height:12px;line-height:12px;width:0;padding:0;margin:1px;border-radius:5px;"></p>' +
                        '</div>' +
                        '<div style="display:inline-block;height:14px;line-height:14px;padding:0;margin:0;color: black;">' +
                        '&nbsp;<span class="cust-progress-percent" style="height:14px;line-height:14px;padding:0;margin:0;">0</span>' +
                        '<span class="cust-progress-symbol" style="height:14px;line-height:14px;padding:0;margin:0;">%</span>' +
                        '</div>' +
                        '</div>'
                    $("body").children('#cust-progress-content').remove();
                    $("body").append(html);
                    var left = (document.body.clientWidth-$("#cust-progress-content").width())/2;
                    $("#cust-progress-content").css('left',left);

                    var xhr = $.ajaxSettings.xhr();
                    if(onprogress && xhr.upload) {
                        xhr.upload.addEventListener("progress" , onprogress, false);
                        return xhr;
                    }
                }else{
                    var xhr = $.ajaxSettings.xhr();
                    if(onprogress && xhr.upload) {
                        xhr.upload.addEventListener("progress" , onprogress, false);
                        return xhr;
                    }
                }
            },
            complete: function () {},
            error: function (context, xhr, e){
                is_finish = true;
                custalert('上传失败')
                $("body").children('#cust-progress-content').remove();
                $("form.cust-img-js-ajax-form").remove();
            }
        });
    })
}

// 上传视频到java
function cust_change_file_to_java(obj){
    Wind.use('ajaxForm',function () {
        var form = $(obj).parent();
        $("body").children('#cust-progress-content').remove();
        is_finish = false;
        form.ajaxSubmit({
            url: form.attr('action'), //按钮上是否自定义提交地址(多按钮情况)
            dataType: 'json',
            beforeSubmit: function (arr, $form, options) {
                // console.log(arr[0]['value']['size'])
                // console.log($form)
                // console.log(options)
                var max_size = parseInt($(obj).attr('data-max_size'));
                // console.log('max_size:'+max_size)
                if($(obj).attr('name')=='image' && arr[0]['value']['size'] > (1024*1024*max_size)){
                    custalert('文件大小不能大于 '+max_size+'M')
                    return false;
                }
            },
            success: function (data, statusText, xhr, $form) {
                $("body").children('#cust-progress-content').remove();
                is_finish = true;
                if(data.code == 200){
                    var timeNum = $(obj).data('timenum1') ? $(obj).data('timenum1') : '';
                    var name = $(obj).data('name');
                    var $input = $("input[data-timenum='"+timeNum+"']")[0] && $("input[data-timenum='"+timeNum+"']") || $("input[name='"+name+"']:not(input[data-timenum])");
                    $input.val(data.data.fileStoreKey);
                    if($(obj).data('showimgclass')){
                        var showimgclass = $(obj).data('showimgclass');
                        var showimgclassNum = $("img[data-showimgclassnum="+ timeNum +"]")[0];
                        var $img = showimgclassNum && $(showimgclassNum) || $("."+showimgclass + ":not(img[data-showimgclassnum])");
                        $img.attr('src','');
                        $img.attr('src',data.data.fileStoreKey);
                        var changefunc = $img.attr('data-changefunc');
                        if(changefunc && changefunc.length > 0){
                            eval(changefunc+'($("."+showimgclass))');
                        }
                    }
                    custshowmsg('上传成功')
                }else{
                    if(data.msg){
                        custshowmsg(data.msg)
                    }else{
                        custshowmsg(data.info)
                    }
                }
                $("form.cust-img-js-ajax-form").remove();
            },
            xhr: function(){
                is_finish = true;
                if($(obj).data('progress')=='1'){
                    var html = '<div id="cust-progress-content" style="top: 50px;z-index: 19891015;position:fixed;width:550px;height:14px;line-height:14px;padding:0;margin:0;">' +
                        '<div class="cust-progress-line" style="display:inline-block;width:402px;height:14px;line-height:14px;background-color:#fbfbfb;padding:0;margin:0;border:1px solid #777777;border-radius:7px;">' +
                        '<p class="cust-progress-rate" style="background-color:#73c944;height:12px;line-height:12px;width:0;padding:0;margin:1px;border-radius:5px;"></p>' +
                        '</div>' +
                        '<div style="display:inline-block;height:14px;line-height:14px;padding:0;margin:0;color: black;">' +
                        '&nbsp;<span class="cust-progress-percent" style="height:14px;line-height:14px;padding:0;margin:0;">0</span>' +
                        '<span class="cust-progress-symbol" style="height:14px;line-height:14px;padding:0;margin:0;">%</span>' +
                        '</div>' +
                        '</div>'
                    $("body").children('#cust-progress-content').remove();
                    $("body").append(html);
                    var left = (document.body.clientWidth-$("#cust-progress-content").width())/2;
                    $("#cust-progress-content").css('left',left);

                    var xhr = $.ajaxSettings.xhr();
                    if(onprogress && xhr.upload) {
                        xhr.upload.addEventListener("progress" , onprogress, false);
                        return xhr;
                    }
                }else{
                    var xhr = $.ajaxSettings.xhr();
                    if(onprogress && xhr.upload) {
                        xhr.upload.addEventListener("progress" , onprogress, false);
                        return xhr;
                    }
                }
            },
            complete: function () {},
            error: function (context, xhr, e){
                is_finish = true;
                custalert('上传失败')
                $("body").children('#cust-progress-content').remove();
                $("form.cust-img-js-ajax-form").remove();
            }
        });
    })
}

function onprogress(evt){
    //通过事件对象侦查
    //该匿名函数表达式大概0.05-0.1秒执行一次
    // evt.loaded;  //已经上传大小情况
    //evt.total; 附件总大小
    var loaded = evt.loaded;
    var tot = evt.total;
    var per = Math.floor(100*loaded/tot);  //已经上传的百分比
    per = per - 0.1;
    $('#cust-progress-content').find('.cust-progress-rate').css('width',(per*400/100));
    $('#cust-progress-content').find('.cust-progress-percent').text(per);
    $('#cust-progress-content').find('.cust-progress-symbol').html('%&nbsp;&nbsp;');
    if(is_finish === true){
    }else{
        custshowmsg('处理中。。。')
    }
    if(per>=100){
        setTimeout(function (){
            // $("body").children('#cust-progress-content').remove();
        },3000);
    }
}

/*
* 判断是否为数字或带小数点的数
* */
function check_isnumber(obj,msg) {
    var value = obj.value;
    // var ex = /^\.\d+$/;
    var ex = /^(-)?\d+(\.\d+)?$/;
    if (!ex.test(value)) {
        var label = $(obj).parent().parent().children("label").text();
        msg = msg ? '【'+msg+'】请设置正确的值' : ( label ? '【'+label+'】请设置正确的值' : '请设置正确的值');
        custalert(msg,function (){
            if($(obj).attr('data-oval')){
                $(obj).val($(obj).attr('data-oval'));
            }else{
                $(obj).val('');
            }
        });
    }
    return false;
}

/*
* 判断是否为整数或者4位小数
* */
function checkIntegerOr4Decimal(obj, msg='', clear=false) {
    var value = obj.value;
    var label = $(obj).parent().parent().children("label").text();

    var reg = /^([1-9]\d*|0)((\.)|(\.\d{1})|(\.\d{2})|(\.\d{3})|(\.\d{4}))?$/
    if(value.indexOf('-') === 0){
        arr = value.split('-');
        if(arr.length > 2){
            msg = msg ? '【'+msg+'】请设置正确的值' : ( label ? '【'+label+'】请设置正确的值' : '请设置正确的值');
            custalert(msg,function (){
                if(!clear){
                    return ;
                }
                if($(obj).attr('data-oval')){
                    $(obj).val($(obj).attr('data-oval'));
                }else{
                    $(obj).val('');
                }
            });
            return ;
        }
        value = arr[1];
        if(value === ''){
            return ;
        }
    }
    if (!reg.test(value)) {
        msg = msg ? '【'+msg+'】请设置正确的值' : ( label ? '【'+label+'】请设置正确的值' : '请设置正确的值');
        custalert(msg,function (){
            if(!clear){
                return ;
            }
            if($(obj).attr('data-oval')){
                $(obj).val($(obj).attr('data-oval'));
            }else{
                $(obj).val('');
            }
        });
    }
    return ;
}

/*
* 新定义 loading
* */
function custloading(status=true){
    if(status===true){
        var top = (window.innerHeight - 100)/2;
        var html =	'<div id="cust-loading" style="top: '+top+'px;z-index: 19891015;position:fixed;">' +
            '<div class="cust-loading-content" style="margin: 0;border-radius: 3px;font-weight: 400;position: relative;width: 100px;height: 26px;color: white;background: rgba(0, 0, 0, 0.5);font-size: 14px;word-break: break-all;">'+
            '<i style="position: absolute;top: 0;left: 0;display: inline-block;width: 26px;height: 26px;background: url(/public/images/loading.gif) no-repeat center;"></i>' +
            '<span style="margin-left: 26px;font-family: \'Microsoft Yahei\',verdana;font-size: 12px;font-weight: bold;line-height: 26px;">正在加载...</span>' +
            '</div>' +
            '</div>';
        $("body").children('#cust-loading').remove();
        $("body").append(html);

        var left = (document.body.clientWidth-$(".cust-loading-content").width())/2;
        $(".cust-loading-content").css('left',left);

        setTimeout(function (){
            $("body").children('#cust-loading').remove();
        },60000);
    }else{
        $("body").children('#cust-loading').remove();
    }
}

/*
* 保留num位小数
* */
function savedecimal(obj,num=2) {
    var value = obj.value;
    var arr = value.split('.');
    var new_val = arr[0].replace(/[^0-9]+/,'')+'.'+(arr[1].replace(/[^0-9]+/,'')).substr(0,num);
    obj.value = new_val;
}

/*
* 新定义 色块选择
* */
$('.cust-color-select').on('focus', function(event) {
    event.preventDefault();
    var colorlist = $(this).attr('data-colorlist');
    if(!colorlist){
        custalert('请传入色块列表数据')
        return false;
    }
    var list = JSON.parse(colorlist);
    var name = $(this).attr('name') ? $(this).attr('name') : '';
    var blockid = $(this).attr('data-blockid') ? $(this).attr('data-blockid') : '';
    var width = $(this).attr('data-width') ? $(this).attr('data-width') : 200;

    var top = $(this).offset().top - $(document).scrollTop() + $(this).outerHeight() + 2;
    var left = $(this).offset().left - $(document).scrollLeft();

    var base = 'cust-color-select';
    var box_id = base+'-'+name;

    box_id_scroll = true;

    var html =  '<div id="'+box_id+'">' +
                '<div class="'+base+'-shade" style="width:100%; height: 100%;left: 0;top: 0;position:fixed;z-index:19891014;background: rgba(0, 0, 0, 0.0);" onclick="cust_color_colose(this,\''+box_id+'\')"></div>'+
                '<div class="'+base+'-dialog" style="top:'+top+'px;left: '+left+'px;z-index: 19891015;position:fixed;background: #fff;font: 14px Helvetica Neue,Helvetica,PingFang SC,Tahoma,Arial,sans-serif;color: #23262e;border-radius: 2px;">'+
                '<div class="'+base+'-dialog-content" style="position: relative;width: '+width+'px !important;min-height: 200px !important;border: 2px solid lightgrey !important;border-radius: 4px !important;">';
    $.each(list,function (i,v) {
        html += '<span style="padding: 0px 15px 15px 14px;background-color: '+v.val+';border: 1px solid white;" data-val="'+v.val+'" onclick="cust_color_click(this,\''+name+'\',\''+blockid+'\')"></span>';
    });

    html += '</div>' +
            '</div>' +
            '</div>' +
            '</div>';

    $("body").children('#'+box_id).remove();
    $("body").append(html);

    $(window).scroll(function () {
        if(box_id_scroll !== true){
            return ;
        }
        var thisobj = $("[name='"+name+"']");
        var newtop = $(thisobj).offset().top - $(document).scrollTop() + $(thisobj).outerHeight() + 2;
        $("body").children('#'+box_id).children('.'+base+'-dialog').css('top',newtop);
    });
})

// 色块选择关闭
function cust_color_colose(obj){
    $(obj).parent().remove()
    box_id_scroll = false;
}
// 色块点击选中
function cust_color_click(obj,name,blockid){
    var val = $(obj).attr('data-val');
    if(name){
        $("[name='"+name+"']").val(val);
    }
    if(blockid){
        $("#"+blockid).css('background-color',val);
    }
}

/*
    * 新定义 筛选列，是否有隐藏的，如果有则隐藏不显示
    * */
$('.cust-filter-column').on('click', function(event) {
    event.preventDefault();
    var table_id = $(this).attr('data-table_id');
    if(!table_id){
        custalert('data-table_id 不存在')
        return
    }
    var page_name = $(this).attr('data-page_name') ? $(this).attr('data-page_name') : '';
    if(!page_name){
        custalert('data-page_name 不存在')
        return
    }

    var table_obj = $("#"+table_id);

    var width = 'auto';
    var height = 'auto';
    var top = 15;
    var left = $(this).offset().left + $(this).outerWidth() + 2;

    var base = 'cust-filter-column';
    var md5_page_name = $.md5(page_name);
    var box_id = base + '-' + md5_page_name;

    var html = '<style>label:hover{background-color: #1dccaa;color: white !important;}</style>';
    html += '<div id="'+box_id+'">' +
        '<div class="'+base+'-shade" style="width:100%; height: 100%;left: 0;top: 0;position:fixed;z-index:19891014;background: rgba(0, 0, 0, 0.0);" onclick="cust_color_colose(this,\''+box_id+'\')"></div>'+
        '<div class="'+base+'-dialog" style="top:'+top+'px;left: '+left+'px;z-index: 19891015;position:fixed;background: #fff;font: 14px Helvetica Neue,Helvetica,PingFang SC,Tahoma,Arial,sans-serif;color: #23262e;border-radius: 2px;">'+
        '<div class="'+base+'-dialog-content" style="position: relative;width: '+width+'px !important;min-height: 200px !important;max-height: 500px !important;height: '+height+'px !important;overflow: auto;border: 2px solid lightgrey !important;border-radius: 4px !important;">';

    $(table_obj).children('thead').children('tr').children('th').each(function (index,val){
        var data_field = $(this).attr('data-field');
        var checked_status = '';
        if($(this).hasClass('filter-checked-no')){
            checked_status = '';
        }else{
            checked_status = 'checked';
        }
        html += '<label class="checkbox block" style="line-height: 20px;height: 20px;color:#2c3e50;font-weight: bold;margin-right: 15px;" >'
        html += '<input style="vertical-align: middle;line-height: 20px;height: 20px;margin: 0px 5px 0px 0px;" type="checkbox" onchange="cust_filter_column_checked(this,\''+table_id+'\', \''+md5_page_name+'\')" value="' + data_field + '" name="' + data_field + '"' + checked_status + '>'
        html += '<span style="vertical-align: middle;line-height: 20px;height: 20px;">' + $(this).text() + '</span>';
        html += '</label>';
    })

    html += '</div>' +
        '</div>' +
        '</div>' +
        '</div>';

    $("body").children('#'+box_id).remove();
    $("body").append(html);
})

// 选中要展示或要隐藏的列
function cust_filter_column_checked(obj, table_id, md5_page_name){
    var data_field = $(obj).attr('name');
    var is_checked = $(obj).is(':checked');
    var cust_filter_column_key = "cust-filter-column-" + md5_page_name;
    var checked_no_json = window.localStorage.getItem(cust_filter_column_key);
    var checked_no_list = [];
    if(checked_no_json){
        checked_no_list = JSON.parse(checked_no_json);
    }
    if(is_checked == true){
        $('#'+table_id).children('thead').find("[data-field='" + data_field + "']").removeClass('filter-checked-no').addClass('filter-checked-yes').css('display', '');
        $('#'+table_id).children('tbody').find("[data-field='" + data_field + "']").removeClass('filter-checked-no').addClass('filter-checked-yes').css('display', '');
        var index = checked_no_list.indexOf(data_field);
        if(index > -1){
            checked_no_list.splice(index,1)
        }
    }else{
        $('#'+table_id).children('thead').find("[data-field='" + data_field + "']").addClass('filter-checked-no').removeClass('filter-checked-yes').css('display', 'none');
        $('#'+table_id).children('tbody').find("[data-field='" + data_field + "']").addClass('filter-checked-no').removeClass('filter-checked-yes').css('display', 'none');
        if(checked_no_list.indexOf(data_field) == -1){
            checked_no_list.push(data_field)
        }
    }
    window.localStorage.setItem(cust_filter_column_key, JSON.stringify(checked_no_list));
}

/*
* 自定义时间按钮点击筛选
* */
$(document).on('click','.cust-time-select', function(){
    $(".cust-time-select").removeClass('bg-color-white').removeClass('color-black').addClass('bg-color-white').addClass('color-black');
    $("#start_time").val($(this).attr('data-time_start'));
    $("#end_time").val($(this).attr('data-time_end'));
    $("#time_type").val($(this).attr('data-time_type'));
});

/*
* 手动输入，改变时间，让自定义时间按钮失去选择
* */
$(document).on('click','#start_time,#end_time', function(){
    $(".cust-time-select").removeClass('bg-color-white').removeClass('color-black').addClass('bg-color-white').addClass('color-black');
    $("#time_type").val('');
});

/*
* 根据时区获取对应的时间
* */
function getLocalTime(i){
    var d = new Date();
    var len = d.getTime();
    //本地时间与UTC时间的时间偏移差
    var offset = d.getTimezoneOffset() * 60000;
    //得到现在的UTC时间，各时区UTC时间相同
    var utcTime = len + offset;
    //得到时区标准时间
    return new Date(utcTime + 3600000 * i).toLocaleString();

    //得到UTC时间戳
    // return new Date(utcTime).getTime();
    //得到时区时间戳
    // return new Date(utcTime + 3600000 * i).getTime();
}