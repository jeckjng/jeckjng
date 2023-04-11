$(document).ready(function(){
    // 搜索
    $("input").keydown(function(e){
        if(e.keyCode == 13){
            search();
        }
    });
    // 顶部广告条
    /*var $header = $("#header_box");
    if($header.length > 0){
        var header_strHtml = "";
        header_strHtml += "<div class=\"wrap mt20 clearfix\"><ul>";


 
        header_strHtml += "<li><a href=\'http://3.3311722.com:250/211471705\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/0hwcYibc46hibGnH3z1mu86y5qU9wPt2NYFpfh6UibQkAnPmjc4faQKvDK8cINq7sdtU7q8VCRm03o/0\'/><\/a><\/li>";
        header_strHtml += "<li><a href=\'http://3.3311722.com:250/211471705\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/0hwcYibc46hibGnH3z1mu86y5qU9wPt2NYFpfh6UibQkAnPmjc4faQKvDK8cINq7sdtU7q8VCRm03o/0\'/><\/a><\/li>";
        header_strHtml += "<li><a href=\'http://3.3311722.com:250/211471705\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/0hwcYibc46hibGnH3z1mu86y5qU9wPt2NYFpfh6UibQkAnPmjc4faQKvDK8cINq7sdtU7q8VCRm03o/0\'/><\/a><\/li>";
        header_strHtml += "<li><a href=\'http://3.3311722.com:250/211471705\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/0hwcYibc46hibGnH3z1mu86y5qU9wPt2NYFpfh6UibQkAnPmjc4faQKvDK8cINq7sdtU7q8VCRm03o/0\'/><\/a><\/li>";




        header_strHtml += "<li><a href=\'http://3.3311722.com:250/211471705\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/0hwcYibc46hibGnH3z1mu86y5qU9wPt2NYFpfh6UibQkAlDERa1qbmicqvIgSem6DOK9sTI6Bw8NnGk/0\'/><\/a><\/li>";
        header_strHtml += "<li><a href=\'http://3.3311722.com:250/211471705\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/0hwcYibc46hibGnH3z1mu86y5qU9wPt2NYFpfh6UibQkAlDERa1qbmicqvIgSem6DOK9sTI6Bw8NnGk/0\'/><\/a><\/li>";
        header_strHtml += "<li><a href=\'http://3.3311722.com:250/211471705\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/0hwcYibc46hibGnH3z1mu86y5qU9wPt2NYFpfh6UibQkAlDERa1qbmicqvIgSem6DOK9sTI6Bw8NnGk/0\'/><\/a><\/li>";
        header_strHtml += "<li><a href=\'http://3.3311722.com:250/211471705\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/0hwcYibc46hibGnH3z1mu86y5qU9wPt2NYFpfh6UibQkAlDERa1qbmicqvIgSem6DOK9sTI6Bw8NnGk/0\'/><\/a><\/li>";





        header_strHtml += "<li><a href=\'http://934.d2mzb.com:934/9315\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/PiajxSqBRaEJV1qNadEsBvC3o1y2gGSRVskbZ9BfeHSQUHYMzPKJw92NbXmnolXEG7ApoEdGRfxQ/0\'/><\/a><\/li>";
        header_strHtml += "<li><a href=\'http://934.d2mzb.com:934/9315\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/PiajxSqBRaEJV1qNadEsBvC3o1y2gGSRVskbZ9BfeHSQUHYMzPKJw92NbXmnolXEG7ApoEdGRfxQ/0\'/><\/a><\/li>";
        header_strHtml += "<li><a href=\'http://934.d2mzb.com:934/9315\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/PiajxSqBRaEJV1qNadEsBvC3o1y2gGSRVskbZ9BfeHSQUHYMzPKJw92NbXmnolXEG7ApoEdGRfxQ/0\'/><\/a><\/li>";
        header_strHtml += "<li><a href=\'http://934.d2mzb.com:934/9315\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/PiajxSqBRaEJV1qNadEsBvC3o1y2gGSRVskbZ9BfeHSQUHYMzPKJw92NbXmnolXEG7ApoEdGRfxQ/0\'/><\/a><\/li>";




        header_strHtml += "<li><a href=\'http://934.d2mzb.com:934/9315\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/frjIACiczz1gUGlvia25gTib9nqBF3npcEib144LoMXoKKkNEtZ0t6ObKicZhbTcWVW1n7veJjEqicOPg/0\'/><\/a><\/li>";
        header_strHtml += "<li><a href=\'http://934.d2mzb.com:934/9315\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/frjIACiczz1gUGlvia25gTib9nqBF3npcEib144LoMXoKKkNEtZ0t6ObKicZhbTcWVW1n7veJjEqicOPg/0\'/><\/a><\/li>";
        header_strHtml += "<li><a href=\'http://934.d2mzb.com:934/9315\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/frjIACiczz1gUGlvia25gTib9nqBF3npcEib144LoMXoKKkNEtZ0t6ObKicZhbTcWVW1n7veJjEqicOPg/0\'/><\/a><\/li>";
        header_strHtml += "<li><a href=\'http://934.d2mzb.com:934/9315\' target=\'_blank\'><img width='100%' height='40' src=\'https://p.qlogo.cn/qqmail_head/frjIACiczz1gUGlvia25gTib9nqBF3npcEib144LoMXoKKkNEtZ0t6ObKicZhbTcWVW1n7veJjEqicOPg/0\'/><\/a><\/li>";


 



 



 


 


        header_strHtml += "<\/ul><\/div>";
        $header.prepend(header_strHtml);
    }*/
    //header下面的广告条
    var $top = $("#top_box");
    if($top.length > 0){
        var top_strHtml = "";
        top_strHtml += "<div class=\"wrap mt20 clearfix\"><ul>";
        
       top_strHtml += "<\/ul><\/div>";
        $top.prepend(top_strHtml);
    }
	
	
	/*
    //center广告条
    var $top = $("#center_box");
    if($top.length > 0){
        var top_strHtml = "";
        top_strHtml += "<div class=\"wrap mt20 clearfix\" style=\"height:100px;margin:10px 0;\"><ul>";
        top_strHtml += "<li><a href=\'http://news.738a.cn\' target=\'_blank\'><img width='100%' height='100' src=\'http://wx3.sinaimg.cn/large/006g8uW3gy1fw6eaxnpkgg30hs05kwhd.gif\'/><\/a><\/li>";

       top_strHtml += "<\/ul><\/div>";
        $top.prepend(top_strHtml);
    }
	*/
	
    // 底部广告条
    var $footer = $("#footer_box");
    if($footer.length > 0){
        var footer_strHtml = "";
        footer_strHtml += "<div class=\"wrap mt20 clearfix\"><ul>";
  //   footer_strHtml += "<li><a href=\'http://200.dns383.com/111.html\' target=\'_blank\'><img width='100%' height='60' //src=\'http://100.dns383.com/cc/1.gif\'/><\/a><\/li>";

       
        footer_strHtml += "<\/ul><\/div>";
        $footer.prepend(footer_strHtml);
    }
    // 图片懒加载
  //  if($('img').length > 0){
   //     $('img').lazyload();
  //  }
/*if ($('.lazy').length > 0) {
        $('.lazy').lazyload();
    }
	
  var $player = $("#player-container");
  var oft = $player.offset().top;
  $(window).scroll(function(){
    var sct = $(window).scrollTop();
    var $body = $('body');
    if(sct >= oft){
      $body.addClass('fixed');
      $body.css({
        paddingTop: $player.height() + 'px'
      });
    }else{
      $body.removeClass('fixed');
      $body.css({
        paddingTop: '0'
      });
    }
  })*/
	
	
	
});

// 搜索功能  http://localhost/search.php?keyword=aaaa&page=3
function search(){
    var index = {form: "/Index/pchome/video_name/"}
    location.href=index.form+$('#iserach').val();
};