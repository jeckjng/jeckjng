<?php
$str = 'rtmp://120.25.106.132:1935/live/21789_1597913898';

// 零 limit
print_r(substr($str,strrpos($str,'/')+1));

// 正的 limit
print_r(substr($str,0,strrpos($str,'/')+1));
