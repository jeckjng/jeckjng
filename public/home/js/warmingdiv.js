var width = document.body.clientWidth;
var height = window.screen.height;

var realwidth = (width-645)/2;
 var realheight = (height-380)/2-100;
 
 
/*
document.writeln("    <div id=\'index-Bomb-box2\' tabindex=\'-1\' class=\'ui-dialog ui-widget ui-widget-content ui-corner-all ui-draggable\' role=\'dialog\' aria-labelledby=\'ui-dialog-title-js-notice-pop\' style=\'outline: 0px;margin-left:auto; margin-right:auto;  width: 645px; height: 380px; display: block; z-index: 1002;position:fixed;top:"+realheight+"px;left:"+realwidth+"px;overflow:hidden;\'>");
document.writeln("      <div class=\'ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix\' style=\'cursor:default;\'>");
document.writeln("        <span class=\'ui-dialog-title\' id=\'ui-dialog-title-js-notice-pop\'>重大通知，请看完本消息（否则将永久丢失本站）</span>");
document.writeln("        <a class=\'ui-dialog-titlebar-close ui-corner-all\' role=\'button\' onclick=\'hideBomb();\' style=\'cursor:pointer;\'>");
 

document.writeln("          X</a>");
document.writeln("      </div>");
document.writeln(" <div class=\'ui-dialog-content ui-widget-content\' id=\'js-notice-pop\' style=\'width: auto; height: 254.28px; min-height: 0px;\' scrolltop=\'0\' scrollleft=\'0\'>");
document.writeln("<span style=\'color:red;\'>AV天堂</span> 提示大家，最近查封严重，<span style=\'color:red;\'>老域名</span>即将作废，请务必记住以下最新域名 ↓<br>");
document.writeln("<p><a href=\'http://www.0011avtt.com\' target=\'_blank\'>www.0011avtt.com</a></p>");
document.writeln("<p><a href=\'http://www.0022avtt.com\' target=\'_blank\'>www.0022avtt.com</a> </p>");
document.writeln("<p><a href=\'http://www.0033avtt.com\' target=\'_blank\'>www.0033avtt.com</a></p>");
document.writeln("<p><a href=\'http://www.0044avtt.com\' target=\'_blank\'>www.0044avtt.com</a> </p>");
document.writeln("<p><a href=\'http://www.0055avtt.com\' target=\'_blank\'>www.0055avtt.com</a> </p>");
document.writeln("<p><a href=\'http://www.0066avtt.com\' target=\'_blank\'>www.0066avtt.com</a></p>");
document.writeln("<p><a href=\'http://www.0077avtt.com\' target=\'_blank\'>www.0077avtt.com</a> </p>");
document.writeln("<p><a href=\'http://www.0088avtt.com\' target=\'_blank\'>www.0088avtt.com</a></p>");
document.writeln("<p><a href=\'http://www.0099avtt.com\' target=\'_blank\'>www.0099avtt.com</a></p>");
 document.writeln(" ");
document.writeln("</div>");
document.writeln("      <div class=\'ui-dialog-buttonpane ui-widget-content ui-helper-clearfix\'>");
document.writeln("        <div class=\'ui-dialog-buttonset\' onclick=\'hideBomb();\'>");
document.writeln("          <button class=\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\' role=\'button\' aria-disabled=\'false\' type=\'button\'>");
document.writeln("            <span class=\'ui-button-text\'>关闭</span></button>");
document.writeln("        </div>");
document.writeln("      </div>");
document.writeln("    </div>");*/


function hideBomb() {
  document.getElementById('index-Bomb-box2').style.display='none';
}