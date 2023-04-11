<?php

class Api_Longvideo extends PhalApi_Api {

    public function getRules() {
        //新增
        return array(
            'setVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'title' => array('name' => 'title', 'type' => 'string',  'desc' => '标题'),
                'label' => array('name' => 'label', 'type' => 'string',  'desc' => '标签'),
                'thumb' => array('name' => 'thumb', 'type' => 'string',  'require' => true, 'desc' => '封面图'),
                'hrefcontent' => array('name' => 'hrefcontent', 'type' => 'string',  'require' => true, 'desc' => 'm3u8文件内容'),
                'gifurl' => array('name' => 'gifurl', 'type' => 'string',  'require' => true,'desc' => 'gif地址'),
                //    'videoid' => array('name' => 'videoid', 'type' => 'string',  'require' => false,'desc' => '视频ID'),
            ),
            'setComment' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'default'=>0, 'desc' => '回复的评论UID'),
                'commentid' => array('name' => 'commentid', 'type' => 'int',  'default'=>0,  'desc' => '回复的评论commentid'),
                'parentid' => array('name' => 'parentid', 'type' => 'int',  'default'=>0,  'desc' => '回复的评论ID'),
                'content' => array('name' => 'content', 'type' => 'string',  'default'=>'', 'desc' => '内容'),
                'at_info'=>array('name'=>'at_info','type'=>'string','desc'=>'被@的用户json信息'),
            ),
            'addView' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
                'random_str'=>array('name' => 'random_str', 'type' => 'string', 'require' => true, 'desc' => '加密串'),

            ),
            'addLike' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
            ),
            'addStep' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
            ),

            'addShare' => array(
                'uid' => array('name' => 'uid', 'type' => 'int',  'desc' => '用户ID'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
                'random_str'=>array('name' => 'random_str', 'type' => 'string', 'require' => true, 'desc' => '加密串'),
            ),

            'setBlack' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
            ),

            'addCommentLike' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => false, 'desc' => '用户Token'),
                'commentid' => array('name' => 'commentid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '评论/回复 ID'),
            ),
            'getVideoList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'desc' => '用户ID'),
                'label' => array('name' => 'label', 'type' => 'string', 'desc' => '标签'),
                'classify' => array('name' => 'classify', 'type' => 'string', 'desc' => '分类'),
                'release_time' => array('name' => 'release_time', 'type' => 'string', 'desc' => '发布天数 1：一天 ；2：一周；3：二周：4：一月；5一年'),
                'duration' => array('name' => 'duration', 'type' => 'string', 'desc' => ' 视频时长 1：0-30分钟 ；2：30-60；3：60-90：4：90-120；5: 120-150'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'iscoding'=>array('name' => 'iscoding', 'type' => 'string', 'desc' => '是否打码 1无码 2有码'),
                'watchmax'=>array('name' => 'watchmax', 'type' => 'int', 'desc' => '最多播放，固定值1'),
                'likemax'=>array('name' => 'likemax', 'type' => 'int', 'desc' => '最多点赞，固定值1'),
                'timedesc' =>array('name' => 'timedesc', 'type' => 'int', 'desc' => '最新片源固定值1'),
                'url'=>array('name' => 'url', 'desc' => '前台域名'),
            ),
            'getSearchContent' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'desc' => '用户ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'searchcontent' => array('name' => 'searchcontent', 'type' => 'string', 'desc' => '搜索类容，当type=search 时，必传'),
                'url'=>array('name' => 'url', 'desc' => '前台域名'),
            ),
            'getVideojingxuan' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'desc' => '用户ID'),
                'label' => array('name' => 'label', 'type' => 'string', 'desc' => '标签'),
                'classify' => array('name' => 'classify', 'type' => 'string', 'desc' => '分类'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'iscoding'=>array('name' => 'iscoding', 'type' => 'string', 'desc' => '是否打码 1无码 2有码'),
                'watchmax'=>array('name' => 'watchmax', 'type' => 'int', 'desc' => '最多播放，固定值1'),
                'likemax'=>array('name' => 'likemax', 'type' => 'int', 'desc' => '最多点赞，固定值1'),
            ),
            'getVideobylabel' => array(
                //'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'desc' => '用户ID'),
                'label' => array('name' => 'label', 'type' => 'string', 'desc' => '视频标签.如果为空,按时间倒序返回所有标签分类'),
                'classify' => array('name' => 'classify', 'type' => 'string', 'desc' => '视频分类'),
                'iscoding' => array('name' => 'iscoding', 'type' => 'int', 'desc' => '是否打码'),
                'is_today_recommendation'=> array('name' => 'is_today_recommendation', 'type' => 'string', 'desc' => '是否是今日推荐 1 是 0或不传不处理'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'url'=>array('name' => 'url', 'desc' => '前台域名'),
            ),
            'getVideobylabelnew' => array(
                'label' => array('name' => 'label', 'type' => 'string', 'require' => true,'desc' => '视频标签.如果为空,按时间倒序返回所有标签分类'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
             ),
            'getVideobyclassify' => array(
                'classify' => array('name' => 'classify', 'type' => 'string', 'require' => true,'desc' => '视频分类.如果为空,按时间倒序返回所有标签分类'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
            ),

            'getAttentionVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => false, 'desc' => '用户Token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
            ),
            'getVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
                'is_search' => array('name' => 'is_search', 'type' => 'int',  'desc' => '是否为搜索数据 1：是'),
            ),
            'getComments' => array(
                'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
            ),

            'getReplys' => array(
                'uid' => array('name' => 'uid', 'type' => 'int',  'require' => true, 'desc' => '用户ID'),
                'commentid' => array('name' => 'commentid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '评论ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
            ),

            'getMyVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'status' => array('name' => 'status', 'type' => 'int', 'default'=>0, 'desc' => '0 全部 状态1 待审核 2 审核通过 3 审核不通过'),

            ),
            'del' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => 'token'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
            ),

            'report' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => 'token'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
                'content' => array('name' => 'content', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '举报内容'),
            ),

            'getHomeVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int',  'desc' => '用户ID'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'require' => true, 'desc' => '对方ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
            ),
            'getCreateNonreusableSignature' => array(
                'imgname' => array('name' => 'imgname', 'type' => 'string', 'desc' => '图片名称'),
                'videoname' => array('name' => 'videoname', 'type' => 'string', 'desc' => '视频名称'),
                'folderimg' => array('name' => 'folderimg', 'type' => 'string','desc' => '图片文件夹'),
                'foldervideo' => array('name' => 'foldervideo', 'type' => 'string', 'desc' => '视频文件夹'),
            ),


            'getRecommendVideos'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int',  'desc' => '用户ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'isstart' => array('name' => 'isstart', 'type' => 'int', 'default'=>0, 'desc' => '是否启动App'),
            ),

            'getNearby'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
                'lng' => array('name' => 'lng', 'type' => 'string', 'desc' => '经度值'),
                'lat' => array('name' => 'lat', 'type' => 'string','desc' => '纬度值'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
            ),

            'setVideoLabel'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => 'token'),
                'label' => array('name' => 'label', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '视频标签'),
            ),

            'deleteVideoLabel'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => 'token'),
                'label' => array('name' => 'label', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '视频标签'),
            ),
            'getVideolabel'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'label' => array('name' => 'label', 'type' => 'string', 'require' => false, 'desc' => '标签名称'),
                // 'token' => array('name' => 'token', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => 'token'),
            ),
            'getVideolabelnew'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'label' => array('name' => 'label', 'type' => 'string', 'require' => false, 'desc' => '标签名称'),
                // 'token' => array('name' => 'token', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => 'token'),
            ),
            'getVideohomelabel'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int',  'desc' => '用户ID'),
                'label' => array('name' => 'label', 'type' => 'string', 'require' => false, 'desc' => '标签名称,不传获取全部标签和分类'),
            ),


            'addCollection' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
            ),
            'getCollection' => array(
                'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
            ),
            'setConversion'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
                'is_search'=>array('name' => 'is_search', 'type' => 'string',  'desc' => '是否是搜索 1:是不传或传其他为不是 '),
                'is_record'=>array('name' => 'is_record', 'type' => 'string',  'desc' => ' 1: 返回操作记录不数据 '),

            ),

            'getRanklist'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
           ),
            'getRankhot'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'desc' => '用户ID'),
                'url'=>array('name' => 'url', 'desc' => '前台域名'),
            ),
            'getWatchrecord' => array(
                'uid' => array('name' => 'uid', 'require' => true, 'type' => 'int','desc' => '用户ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'label' => array('name' => 'label',  'require' => true,'type' => 'string','desc' => '视频分类,传空字符表示全部,分类id'),
            ),
            'deleteWatchrecord'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'id' => array('name' => 'id', 'type' => 'string', 'min' => 1,'desc' => 'id值'),
                'isdelete_all' => array('name' => 'isdelete_all', 'type' => 'string',   'desc' => '是否全部删除（1为全部删除，否则为空）'),

                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
            ),
            'watchHistory'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string',  'min' => 1, 'require' => true, 'desc' => 'token'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
            ),
            'getVideoClassify' => array(
                'label' => array('name' => 'label', 'type' => 'string','desc' => '长视频标签'),
            ),
            'downloadVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
            ),
            'getMydownload'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string',  'min' => 1, 'require' => true, 'desc' => 'token'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
            ),
            'delMydownload'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'id' => array('name' => 'id', 'type' => 'string', 'min' => 1,'desc' => 'id值多个用英文逗号拼接 ","'),
                'isdelete_all' => array('name' => 'isdelete_all', 'type' => 'string',   'desc' => '是否全部删除（1为全部删除，否则为空）'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
            ),
            'guessLikeLongVide'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string',  'min' => 1, 'require' => true, 'desc' => 'token'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
            ),
            'getBanner'=>array(
                'label' => array('name' => 'label', 'type' => 'int','desc' => '标签'),
            ),
            'getHotPerformer' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => false, 'desc' => '用户Token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
            ),
            'getHotVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1,  'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户Token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'url'=>array('name' => 'url', 'desc' => '前台域名'),
            ),
            'uploadVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => false, 'desc' => '用户Token'),
              /*  'file' => array('name' => 'file', 'type' => 'file', 'require' => true, 'desc' => '视频文件'),*/
                'title' => array('name' => 'title', 'type' => 'string', 'require' => true, 'desc' => '标题'),
                'label' => array('name' => 'label', 'type' => 'string', 'require' => true, 'desc' => '标签(单选)'),
                'classify' => array('name' => 'classify', 'type' => 'string', 'require' => true, 'desc' => '分类 (单选)'),
                'performer' => array('name' => 'performer', 'type' => 'string',  'desc' => '演员 (单选)'),
                'desc' => array('name' => 'desc', 'type' => 'string', 'desc' => '剧情简介'),
                'region' => array('name' => 'region', 'type' => 'string', 'desc' => '地区'),
                'years' => array('name' => 'years', 'type' => 'string', 'desc' => '年份'),
            ),

            'getperformer' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),

            ),
            'getRandomVideo'=> array( 'url'=>array('name' => 'url', 'desc' => '前台域名'),),
            'buyLongvideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' =>true,'min' => 1, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'require' => true,'desc' => '视频id'),

            ),
            'buyLongvideovip' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' =>true,'min' => 1, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'vip_grade' => array('name' => 'vip_grade', 'type' => 'int', 'require' => true,'desc' => '长视频会员等级'),

            ),
            'buyLongvideovipList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' =>true,'min' => 1, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),

            ),
            'watchVodeo'=> array(
            'uid' => array('name' => 'uid', 'type' => 'int', 'require' =>true,'min' => 1, 'desc' => '用户ID'),
            'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
            'videoid' => array('name' => 'videoid', 'type' => 'int', 'require' => true,'desc' => '视频id'),
            ),
            'getLongvideovip'=> array(

            ),
            'getLongvideosearch'=> array(

            ),
            'getSearchbyuser'=> array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' =>true,'min' => 1, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
            ),
            'delSearchbyuser'=> array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' =>true,'min' => 1, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'search_id' => array('name' => 'search_id', 'type' => 'int', 'require' => true,'desc' => '搜索id'),
            ),
            'updateVideoInfo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'video_id' => array('name' => 'video_id', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
                'file_store_key' => array('name' => 'file_store_key', 'type' => 'string', 'require' => true, 'desc' => '视频key'),
            ),
            'getAllclssifyandlabel' => array(
                
            ),
        );
    }

    /**
     *   获取多个视频列表
     * @desc 用于获取视频详情
     * @return int code 操作码，0表示成功，1000表示视频不存在
     * @return array userinfo  会员信息（头像，昵称等等等）
     * @return array info[0] 视频详情
     * @return object info[0].uid   会员id
     * @return object info[0].title  视频标题
     * @return string info[0].video_id 视频id
     * @return string info[0].href   m3u8地址
     * @return string info[0].label 视频标签
     * @return string info[0].comments 评论数
     * @return string msg 提示信息
     */
    public function getVideoList() {

        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid=$this->uid;
        $p=$this->p;
        if (!empty($uid)){
            $isBan=isBan($this->uid);
            if($isBan=='0'){
                $rs['code'] = 700;
                $rs['msg'] = '该账号已被禁用';
                return $rs;
            }

        }

        $classify=$this->classify;
        $iscoding=$this->iscoding;
        $label = $this->label;
        $watchmax=$this->watchmax;
        $likemax=$this->likemax;
        $release_time =$this->release_time;
        $duration =$this->duration;
        $url = $this->url;
        $timedesc= $this->timedesc;
        $key='longvideoHot_'.$p;
        $info=getcaches($key);


      /*  if(!$info){*/
            $domain = new Domain_Longvideo();
            $info= $domain->getVideoList($uid,$p,$classify,$label,$iscoding,$watchmax,$likemax,$release_time,$duration,$url,$timedesc);

            if($info==10010){
                $rs['code'] = 0;
                $rs['msg'] = "暂无视频列表";
                return $rs;
            }

            setcaches($key,$info,2);
     /*   }*/
        /*print_r('<pre>');
        print_r($info);
        print_r('<pre/>');exit;*/
        $rs['info'] = array_values($info);
        return $rs;
    }


    /**
     *   视频搜索
     * @desc 视频热搜
     * @return int code 操作码，0表示成功，1000表示视频不存在
     * @return array userinfo  会员信息（头像，昵称等等等）
     * @return array info[0] 视频详情
     * @return object info[0].uid   会员id
     * @return object info[0].title  视频标题
     * @return string info[0].video_id 视频id
     * @return string info[0].href   m3u8地址
     * @return string info[0].label 视频标签
     * @return string info[0].comments 评论数
     * @return string msg 提示信息
     */
    public function getSearchContent() {

        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid=$this->uid;
        $p=$this->p;
       /* if (!empty($uid)){
            $isBan=isBan($this->uid);
            if($isBan=='0'){
                $rs['code'] = 700;
                $rs['msg'] = '该账号已被禁用';
                return $rs;
            }
        }*/
        $url = $this->url;
        $searchcontent = $this->searchcontent;




       /* $key='longvideosearch_'.$p;

        $info=getcaches($key);

        if(!$info){*/
            $domain = new Domain_Longvideo();
            $info= $domain->getSearchContent($uid,$p,$searchcontent,$url);

            if($info==10010){
                $rs['code'] = 0;
                $rs['msg'] = "暂无视频列表";
                return $rs;
            }
       /* if(!empty($info)){

            $scorehot = DI()->redis->zScore('rank_longhotsearch_list',$searchcontent);
            if(!$scorehot){
                $rankhot=DI()->redis -> zAdd('rank_longhotsearch_list',1,$searchcontent);
            }else{
                $rankhot=DI()->redis -> zAdd('rank_longhotsearch_list',$scorehot+1,$searchcontent);
            }
        }*/
        /*    setcaches($key,$info,2);
        }
        //观影排行榜 数据更新
        if(!empty($info)){

            $scorehot = DI()->redis->zScore('rank_hotsearch_list',$searchcontent);
            if(!$scorehot){
                $rankhot=DI()->redis -> zAdd('rank_hotsearch_list',1,$searchcontent);
            }else{
                $rankhot=DI()->redis -> zAdd('rank_hotsearch_list',$scorehot+1,$searchcontent);
            }
        }*/

        $rs['info'] = array_values($info);
        return $rs;
    }
    /**
     *   获取首页精选视频
     * @desc 用于获取首页精选视频详情
     * @return int code 操作码，0表示成功，1000表示视频不存在
     * @return array userinfo  会员信息（头像，昵称等等等）
     * @return array info[0] 视频详情
     * @return object info[0].uid   会员id
     * @return object info[0].title  视频标题
     * @return string info[0].video_id 视频id
     * @return string info[0].href   m3u8地址
     * @return string info[0].label 视频标签
     * @return string info[0].comments 评论数
     * @return string msg 提示信息
     */
    public function getVideojingxuan() {

        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid=$this->uid;
        $p=$this->p;
        $isBan=isBan($this->uid);
        $classify=$this->classify;
        $iscoding=$this->iscoding;
        $label=$this->label;
        $watchmax=$this->watchmax;
        $likemax=$this->likemax;

        if($isBan=='0'){
            $rs['code'] = 700;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }

        $key='longvideoHot_'.$p;

       /* $info=getcaches($key);

        if(!$info){*/
            $domain = new Domain_Longvideo();
            $info= $domain->getVideojingxuan($uid,$p,$classify,$label,$iscoding,$watchmax,$likemax);

            if($info==10010){
                $rs['code'] = 0;
                $rs['msg'] = "暂无视频列表";
                return $rs;
            }

            setcaches($key,$info,2);
     /*   }*/
        /*print_r('<pre>');
        print_r($info);
        print_r('<pre/>');exit;*/
        $rs['info'] = $info;
        return $rs;
    }

    /**
     * 长视频评论/回复
     * @desc 用于用户评论/回复 别人视频
     * @return int code 操作码，0表示成功
     * @return array info
     * @return int info[0].isattent 对方是否关注我
     * @return int info[0].u2t 我是否拉黑对方
     * @return int info[0].t2u 对方是否拉黑我
     * @return int info[0].comments 评论总数
     * @return int info[0].replys 回复总数
     * @return string msg 提示信息
     */
    public function setComment() {
        $rs = array('code' => 0, 'msg' => '评论成功', 'info' => array());
        $game_tenant_id=$this->game_tenant_id;
        $uid=$this->uid;
        $token=checkNull($this->token);
        $touid=$this->touid;
        $videoid=$this->videoid;
        $commentid=$this->commentid;
        $parentid=$this->parentid;
        $content=checkNull($this->content);
        if (empty($content)){
            $rs['code'] = 1002;
            $rs['msg'] = '评论内容不能为空';
            return $rs;
        }
        $at_info=$this->at_info;

        //$arr = json_decode($at_info,true);
        if(!$at_info){
            $at_info='';
        }

        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }

        if($touid>0){
            $isattent=isAttention($touid,$uid);
            $u2t = isBlack($uid,$touid);
            $t2u = isBlack($touid,$uid);
            if($t2u==1){
                $rs['code'] = 1000;
                $rs['msg'] = '对方暂时拒绝接收您的消息';
                return $rs;
            }

        }

        if($commentid==0 && $commentid!=$parentid){
            $commentid=$parentid;
        }

        $data=array(
            'uid'=>$uid,
            'touid'=>$touid,
            'videoid'=>$videoid,
            'commentid'=>$commentid,
            'parentid'=>$parentid,
            'content'=>$content,
            'addtime'=>time(),
            'at_info'=>$at_info,
            'tenant_id'=>$game_tenant_id,
            'video_type'=> 2
        );

        $userInfo  = getUserInfo($uid);
        if (!in_array($userInfo['user_type'],[2,5,6,7])){
            $rs['code'] = 1002;
            $rs['msg'] = "当前您是游客登录，请注册账号后再试";
            return $rs;
        }
        /*var_dump($data);
        die;*/

        $domain = new Domain_Longvideo();
        $result = $domain->setComment($data);



        $info=array(
            'isattent'=>'0',
            'u2t'=>'0',
            't2u'=>'0',
            'comments'=>$result['comments'],
            'replys'=>$result['replys'],
        );
        if($touid>0){
            $isattent=isAttention($touid,$uid);
            $u2t = isBlack($uid,$touid);
            $t2u = isBlack($touid,$uid);

            $info['isattent']=(string)$isattent;
            $info['u2t']=(string)$u2t;
            $info['t2u']=(string)$t2u;
        }

        $rs['info'][0]=$info;

        if($parentid!=0){
            $rs['msg']='回复成功';
        }
        return $rs;
    }
    /**
     * 长视频回复列表
     * @desc 根据commentid获取视频评论列表
     * @return int code 操作码，0表示成功
     * @return array info 评论列表
     * @return object info[].userinfo 用户信息
     * @return string info[].datetime 格式后的时间差
     * @return object info[].tocommentinfo 回复的评论的信息
     * @return object info[].tocommentinfo.content 评论内容
     * @return string info[].likes 点赞数
     * @return string info[].islike 是否点赞
     * @return string msg 提示信息
     */
    public function getReplys() {
        $rs = array('code' => 0, 'msg' => '查询成功', 'info' => array());
        $isBan=isBan($this->uid);
        if($isBan=='0'){
            $rs['code'] = 700;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }

        $domain = new Domain_Longvideo();
        $rs['info'] = $domain->getReplys($this->uid,$this->commentid,$this->p);

        return $rs;
    }
    /**
     * 长视频评论列表
     * @desc 根据videoid获取视频评论列表
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].comments 评论总数
     * @return array info[0].commentlist 评论列表
     * @return object info[0].commentlist[].userinfo 用户信息
     * @return string info[0].commentlist[].datetime 格式后的时间差
     * @return string info[0].commentlist[].replys 回复总数
     * @return string info[0].commentlist[].likes 点赞数
     * @return string info[0].commentlist[].islike 是否点赞
     * @return array info[0].commentlist[].replylist 回复列表
     * @return string msg 提示信息
     */
    public function getComments() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $isBan=isBan($this->uid);
        $game_tenant_id=$this->game_tenant_id;
        if($isBan=='0'){
            $rs['code'] = 700;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }

        $domain = new Domain_Longvideo();
        $rs['info'][0] = $domain->getComments($this->uid,$this->videoid,$this->p,$game_tenant_id);

        return $rs;
    }
    /**
     * 长视频详情
     * @desc 用于获取视频详情
     * @return int code 操作码，0表示成功，1000表示视频不存在
     * @return array info 视频详情
     * @return object info.uid   会员id
     * @return string info.video_id 视频id
     * @return string info.href m3u8地址
     * @return string info.label 视频标签
     * @return string info.comments 评论数
     * @return string info.collection 收藏数
     * @return string info.watchtimes 观看数
     * @return string info.is_download 是否下载 1 已下载 0未下载
     * @return string info.is_collection  是否收藏
     * @return string info.is_like 是否喜欢
     * @return string info.is_follow 是否关注
     * @return string info.users_attention  粉丝数量
     * @return string msg 提示信息
     */
    public function getVideo() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        if (!empty($this->uid)){
            $uid=checkNull($this->uid);
        }else{
            $uid = '';
        }

        $videoid=checkNull($this->videoid);
        $is_search=$this->is_search;

        $domain = new Domain_Longvideo();
        $result = $domain->getVideo($uid,$videoid,$is_search);
        if($result==1000){
            $rs['code'] = 1000;
            $rs['msg'] = "视频已删除";
            return $rs;

        }
        $rs['info'][0]=$result;

        return $rs;
    }



    /**
     * 长视频分类列表
     * @desc 用于获取长视频标签详情
     * @return array info[0] 长视频标签详情
     * @return string msg 提示信息
     */
    public function getVideolabel() {
        $rss = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid=$this->uid;
        $label=$this->label;
        $domain = new Domain_Longvideo();
        $result = $domain->getVideolabel($uid,$label);

        $rss['info']=$result;

        return $rss;
    }
    /**
     * 长视频获取标签（新需求）
     * @desc 用于获取长视频标签
     * @return array info[0] 获取长视频标签
     * @return string msg 提示信息
     */
    public function getVideolabelnew() {
        $rss = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid=$this->uid;
        $label=$this->label;
        $domain = new Domain_Longvideo();
        $result = $domain->getVideolabelnew($uid,$label);

        $rss['info']=$result;

        return $rss;
    }

    /**
     * 长视频首页标签
     * @desc 用于获取长视频首页标签详情
     * @return array info[0] 长视频标签详情
     * @return string msg 提示信息
     */
    public function getVideohomelabel() {
        $rss = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid=$this->uid;
        $label=$this->label;
        $domain = new Domain_Longvideo();
        $result = $domain->getVideohomelabel($uid,$label);

        $rss['info']=$result;

        return $rss;
    }

    /**
     *   根据标签获取视频
     * @desc  标签分类视频
     * @return int code 操作码，0表示成功，1000表示视频不存在
     * @return array userinfo  会员信息（头像，昵称等等等）
     * @return array info[0] 视频详情
     * @return object info[0].uid   会员id
     * @return object info[0].title  视频标题
     * @return string info[0].video_id 视频id
     * @return string info[0].href   m3u8地址
     * @return string info[0].label 视频标签
     * @return string info[0].comments 评论数
     * @return string msg 提示信息
     */
    public function getVideobylabel() {

        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $url = $this->url;
        $p=$this->p;
        $label=$this->label;
        $iscoding=$this->iscoding;
        $classify=$this->classify;
        $is_today_recommendation=$this->is_today_recommendation;
        $key='longvideoHot_'.$p;

        $info=getcaches($key);

        if(!$info){
            $domain = new Domain_Longvideo();
            $info= $domain->getVideobylabel($p,$label,$iscoding,$classify,$is_today_recommendation,$url);

            if($info==10010){
                $rs['code'] = 0;
                $rs['msg'] = "暂无视频列表";
                return $rs;
            }

            setcaches($key,$info,2);
        }
		$rs['count'] = $info['count'];
        unset($info['count']);
        $rs['info'] = array_values($info);
        return $rs;
    }
    /**
     *   根据标签获取长视频分类视频（新需求）
     * @desc  标签获取长视频
     * @return int code 操作码，0表示成功，1000表示视频不存在
     * @return array userinfo  会员信息（头像，昵称等等等）
     * @return array info[0] 视频详情
     * @return object info[0].uid   会员id
     * @return object info[0].title  视频标题
     * @return string info[0].video_id 视频id
     * @return string info[0].href   m3u8地址
     * @return string info[0].label 视频标签
     * @return string info[0].comments 评论数
     * @return string msg 提示信息
     */
    public function getVideobylabelnew() {

        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());

        $p=$this->p;
        $label=$this->label;
        $key='longvideonew_'.$label;

        $info=getcaches($key);

        if(!$info){
            $domain = new Domain_Longvideo();
            $info= $domain->getVideobylabelnew($p,$label);

            if($info==10010){
                $rs['code'] = 0;
                $rs['msg'] = "暂无视频列表";
                return $rs;
            }

            setcaches($key,$info,10);
        }
        $rs['count'] = $info['count'];
        unset($info['count']);
        $rs['info'] = array_values($info);
        return $rs;
    }
    /**
     *  获取所有长视频主分类 和 子分类（新需求）
     * @desc  获取所有长视频主分类 和 子分类（
     * @return int
     * @return string info[0].label 视频标签
     * @return string info[0].comments 评论数
     * @return string msg 提示信息
     */
    public function getAllclssifyandlabel() {

        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());

        
        $key='getAllclssifyandlabel';

        $info=getcaches($key);

        if(!$info){
            $domain = new Domain_Longvideo();
            $info= $domain->getAllclssifyandlabel();
            if($info==10010){
                $rs['code'] = 0;
                $rs['msg'] = "无主分类和 子分类";
                return $rs;
            }

            setcaches($key,$info,10);
        }

        $rs['info'] = array_values($info);
        return $rs;
    }
    /**
     *   根据分类获取长视频分类视频（新需求）
     * @desc  分类获取长视频
     * @return int code 操作码，0表示成功，1000表示视频不存在
     * @return array userinfo  会员信息（头像，昵称等等等）
     * @return array info[0] 视频详情
     * @return object info[0].uid   会员id
     * @return object info[0].title  视频标题
     * @return string info[0].video_id 视频id
     * @return string info[0].href   m3u8地址
     * @return string info[0].label 视频标签
     * @return string info[0].comments 评论数
     * @return string msg 提示信息
     */
    public function getVideobyclassify() {

        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());

        $p=$this->p;
        $classify=$this->classify;

        $key='longvideo_byclassifynew_'.$p;

        $info=getcaches($key);

        if(!$info){
            $domain = new Domain_Longvideo();
            $info= $domain->getVideobyclassify($p,$classify);
            setcaches($key,$info,10);
        }

        $rs['info'] =$info;
        return $rs;
    }
    /**
     * 长视频点赞接口
     * @desc 用于视频点赞数累计和点赞操作
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].islike 是否点赞
     * @return string info[0].likes 点赞数量
     * @return string msg 提示信息
     */
    public function addLike() {
        $rs = array('code' => 0, 'msg' => '点赞成功', 'info' => array());
        $uid=$this->uid;
        $token=checkNull($this->token);
        $videoid=$this->videoid;
        $game_tenant_id=$this->game_tenant_id;

        $isBan=isBan($uid);
        if($isBan=='0'){
            $rs['code'] = 700;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }

        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $userInfo  = getUserInfo($uid);
        if (!in_array($userInfo['user_type'],[2,5,6,7])){
            $rs['code'] = 1002;
            $rs['msg'] = "当前您是游客登录，请注册账号后再试";
            return $rs;
        }
        $domain = new Domain_Longvideo();
        $result = $domain->addLike($uid,$videoid,$game_tenant_id);
        if($result==1001){
            $rs['code'] = 1001;
            $rs['msg'] = "视频已删除";
            return $rs;
        }/*else if($result==1002){
            $rs['code'] = 1002;
            $rs['msg'] = "不能给自己点赞";
            return $rs;
        }*/
        if ($result['islike'] == 0){
            $rs['msg'] = "取消点赞成功";
        }
        $rs['info'][0]=$result;
        //点赞收益发放
        return $rs;
    }

    /**
     * 评论/回复 点赞
     * @desc 用于评论/回复 点赞数累计
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].islike 是否点赞
     * @return string info[0].likes 点赞数量
     * @return string msg 提示信息
     */
    public function addCommentLike() {
        $rs = array('code' => 0, 'msg' => '点赞成功', 'info' => array());
        $uid=$this->uid;
        $token=checkNull($this->token);
        $commentid=$this->commentid;
        $game_tenant_id=$this->game_tenant_id;
        $isBan=isBan($uid);
        if($isBan=='0'){
            $rs['code'] = 700;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }

        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $userInfo  = getUserInfo($uid);
        if (!in_array($userInfo['user_type'],[2,5,6,7])){
            $rs['code'] = 1002;
            $rs['msg'] = "当前您是游客登录，请注册账号后再试";
            return $rs;
        }
        $domain = new Domain_Longvideo();
        $res= $domain->addCommentLike($uid,$commentid,$game_tenant_id);
        if($res==1001){
            $rs['code']=1001;
            $rs['msg']='评论信息不存在';
            return $rs;
        }
        $rs['info'][0]=$res;

        return $rs;
    }

    /**
     * 长视频收藏接口
     * @desc 用于视频收藏累计和收藏操作
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].iscollection 会员是否点赞
     * @return string info[0].collection   该视频的点赞数量
     * @return string msg 提示信息
     */
    public function addCollection() {
        $rs = array('code' => 0, 'msg' => '收藏成功', 'info' => array());
        $uid=$this->uid;
        $token=checkNull($this->token);
        $videoid=$this->videoid;
        $game_tenant_id=$this->game_tenant_id;
        $isBan=isBan($uid);
        if($isBan=='0'){
            $rs['code'] = 700;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }

        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }

        $domain = new Domain_Longvideo();
        $result = $domain->addCollection($uid,$videoid,$game_tenant_id);
        if($result==1001){
            $rs['code'] = 1001;
            $rs['msg'] = "视频已删除";
            return $rs;
        }else if($result==1002){
            $rs['code'] = 1002;
            $rs['msg'] = "不能给自己收藏";
            return $rs;
        }
        if ($result['iscollection']  == 0){
            $rs['msg'] = '取消收藏';
        }
        $rs['info'][0]=$result;
        return $rs;
    }
    /**
     * 长视频我的收藏信息
     * @desc 根据uid获取收藏视频信息
     * @return int code 操作码，0表示成功
     * @return array info
     * @return array info.videoinfo 收藏的视频列表
     * @return object info.videoinfo[].thumb 图片
     * @return string info.videoinfo[].label 标签
     * @return string info.videoinfo[].title 封面
     * @return string msg 提示信息
     */
    public function getCollection() {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $isBan=isBan($this->uid);
        $game_tenant_id=$this->game_tenant_id;
        if($isBan=='0'){
            $rs['code'] = 700;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }

        $domain = new Domain_Longvideo();

        $rs['info'] = $domain->getCollection($this->uid,$this->p,$game_tenant_id);

        return $rs;
    }
    /**
     * 观看历史 （用于长视频 个人中心）
     * @desc 根据uid获取我的观看记录视频信息
     * @return int code 操作码，0表示成功
     * @return array info
     * @return array info[0].counts 观看的视频总数量
     * @return array info[0].videoinfo 观看的视频列表
     * @return object info[0].videoinfo[].thumb 图片
     * @return string info[0].videoinfo[].label 标签
     * @return string info[0].videoinfo[].title 封面
     * @return string msg 提示信息
     */
    public function getWatchrecord() {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $isBan=isBan($this->uid);
        $game_tenant_id=$this->game_tenant_id;
        if($isBan=='0'){
            $rs['code'] = 700;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }
        $label  =  $this->label;
        $domain = new Domain_Longvideo();

        $rs['info'][0] = $domain->getWatchrecord($this->uid,$game_tenant_id,$label,$this->p);

        return $rs;
    }

        /**
         * 长视频观看次数
         * @desc 根据videoid获取长视频更新信息
         * @return int code 操作码，0表示成功
         * @return array info
         * @return string info[0].watchtimes   观看册数
         * @return string msg 提示信息
         */
        public function setConversion() {
            $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array(),'is_record'=>0);
            $isBan=isBan($this->uid);
            $game_tenant_id=$this->game_tenant_id;
            if($isBan=='0'){
                $rs['code'] = 700;
                $rs['msg'] = '该账号已被禁用';
                return $rs;
            }

            $domain = new Domain_Longvideo();
            $is_search= $this->is_search;
            $is_record= $this->is_record;
            if (!empty($is_record)){
                $rs['is_record'] = $is_record;
            }
            $result = $domain->setConversion($this->videoid,$this->uid,$game_tenant_id,$is_search,$is_record);
            $count = DI()->notorm->video_watch_record->where("uid=? and video_type=?",$this->uid,2)->group("videoid")->count();
            $result[0]['video_num'] = $rs['info'][0]["video_num"] = $count;
            if($result['code']==1001){
                $rs['code'] = 1001;
                $rs['msg'] = "视频已删除";
                return $rs;
            }
            if($result['code']==800){
                $rs['code'] = 801;
                $rs['msg'] = "游客观看数量达到最大值,请注册";
                return $rs;
            }
            if($result['code']==900){
                $rs['code'] = 900;
                $rs['msg'] = $result['msg'];

            }
            return $rs;
        }
    /**
     * 长视频观看次数排行榜
     * @desc 长视频观看次数排行榜
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].dayrank   日观看册数
     * @return string info[0].weekrank   周观看册数
     * @return string info[0].monthrank   月观看册数
     * @return string msg 提示信息
     */
    public function getRanklist() {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $isBan=isBan($this->uid);
        $game_tenant_id=$this->game_tenant_id;
        if($isBan=='0'){
            $rs['code'] = 700;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }
        $day = date('Ymd',time());
        $week = strftime('%U',time());
        $month = date('Ym',time());
        $redis =  connectionRedis();
        $rankday=$redis -> zRevRange('rank_day_list:'.$day,0,9,true);
        $rankweek=$redis -> zRevRange('rank_week_list:'.$week,0,9,true);
        $rankmonth=$redis -> zRevRange('rank_month_list:'.$month,0,9,true);
        $dayinfo = array();
        $weekinfo = array();
        $monthinfo = array();
        $download_url =  play_or_download_url(1);
        foreach ($rankday as $k=>$v){
            $video=DI()->notorm->video_long
                ->select("id,thumb,create_date")
                ->where("title='{$k}' and status =2 ")
                ->fetchOne();
           if ($video){
               $dayinfo[$k]['title'] = $k;
               $dayinfo[$k]['count'] = $v;
               $dayinfo[$k]['thumb'] = $download_url['url'].$video['thumb'];
               $dayinfo[$k]['id'] = $video['id'];
               $dayinfo[$k]['create_date'] = $video['create_date'];
           }


        }
        foreach ($rankweek as $k=>$v){
            $video=DI()->notorm->video_long
                ->select("id,thumb,create_date")
                ->where("title='{$k}' and status =2 ")
                ->fetchOne();
            if ($video){
                $weekinfo[$k]['title'] = $k;
                $weekinfo[$k]['count'] = $v;
                $weekinfo[$k]['thumb'] = $download_url['url'].$video['thumb'];
                $weekinfo[$k]['id'] = $video['id'];
                $weekinfo[$k]['create_date'] = $video['create_date'];
            }

        }
        foreach ($rankmonth as $k=>$v){
            $video=DI()->notorm->video_long
                ->select("id,thumb,create_date")
                ->where("title='{$k}' and status =2")
                ->fetchOne();
            if ($video){
                $monthinfo[$k]['title'] = $k;
                $monthinfo[$k]['count'] = $v;
                $monthinfo[$k]['thumb'] = $download_url['url'].$video['thumb'];
                $monthinfo[$k]['id'] = $video['id'];
                $monthinfo[$k]['create_date'] = $video['create_date'];
            }
        }
        $result['dayrank'] = array_values($dayinfo);
        $result['weekrank'] = array_values($weekinfo);
        $result['monthrank'] = array_values($monthinfo);
        $rs['info'] = $result;

        return $rs;
    }
    /**
     * 长视频热搜排行榜
     * @desc 长视频热搜排行榜
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].dayrank   日观看册数
     * @return string msg 提示信息
     */
    public function getRankhot()
    {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());

        $game_tenant_id = $this->game_tenant_id;
        $url = $this->url;
        if (!empty($this->uid)){
            $isBan = isBan($this->uid);
            if ($isBan == '0') {
                $rs['code'] = 700;
                $rs['msg'] = '该账号已被禁用';
                return $rs;
            }
        }


        $ranklist = DI()->redis->zRevRange('rank_longhotsearch_list', 0, 9, true);
        $download_url =  play_or_download_url(1);
        $rankinfo = array();
        if ($ranklist){
            foreach ($ranklist as $k => $v) {
                $video=DI()->notorm->video_long
                    ->select("id,thumb,title,create_date")
                    ->where("id='{$k}' and status =2 ")
                    ->fetchOne();
                if ($video){
                    $rankinfo[$k]['title'] = $video['title'];
                    $rankinfo[$k]['hot_searches'] = $v;
                    if ($url){
                        $rankinfo[$k]['thumb'] =$url.$video['thumb'];
                    }else{
                        $rankinfo[$k]['thumb'] = $download_url['url'].$video['thumb'];
                    }

                    $rankinfo[$k]['id'] = $video['id'];
                    $rankinfo[$k]['create_date'] = $video['create_date'];
                }
            }
        }else{
            $rankinfo=DI()->notorm->video_long
                ->select("id,title,hot_searches,thumb,create_date")
                ->order('hot_searches desc')
                ->limit(0,10)
                ->fetchAll();
            foreach ($rankinfo as $key =>$value){
                if ($url){
                    $rankinfo[$key]['thumb'] =$url.$value['thumb'];
                }else{
                    $rankinfo[$key]['thumb'] = $download_url['url'].$value['thumb'];
                }
            }
        }


        $result['rankinfo'] = array_values($rankinfo);
        $rs['info'] = $result;

        return $rs;

    }

    /**
     * 删除标签
     * @desc 用于删除视频观看记录
     * @return int code 操作码，0表示成功
     * @return array info[conut] 删除条数

     */
    /* 删除观看记录 */
    public function deleteWatchrecord(){
        $rs = array('code' => 0, 'msg' => '删除成功', 'info' => array());
        $uid=checkNull($this->uid);
        $id=checkNull($this->id);
        $isdelete_all=checkNull($this->isdelete_all);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);

        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }

        $domain = new Domain_Longvideo();
        $rs['info']['count'] = $domain->deleteWatchrecord($uid,$id,$isdelete_all);


        return $rs;
    }

    /**
     * 个人中心下面的观看历史，包括短视频和长视频
     * @desc 个人中心下面的观看历史
     * @return int code 操作码，0表示成功

     */

    public  function watchHistory(){
        $rs = array('code' => 0, 'msg' => '历史观看列表', 'info' => array());
        $game_tenant_id=$this->game_tenant_id;
        $p = $this->p;
        $videoDomain = new Domain_Longvideo();
        $rs['info'] = $videoDomain->watchHistory($this->uid,$p,$game_tenant_id);
      return $rs;
    }
    /**
     * 长视频分类
     * @desc 长视频分类
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息

     */
    public  function getVideoClassify(){
        $rs = array('code' => 0, 'msg' => '视频分类', 'info' => array());
        $game_tenant_id=$this->game_tenant_id;
        $label = $this->label;
        $videoDomain = new Domain_Longvideo();
        $rs['info'] = $videoDomain->getVideoClassify($label);
        return $rs;
    }


    /**
     * 我的长视频
     * @desc 我的长视频
     * @return int code 操作码，0表示成功
     * @return array info
     * @return array info[0].counts 观看的视频总数量
     * @return array info[0].videoinfo 观看的视频列表
     * @return object info[0].videoinfo[].thumb 图片
     * @return string info[0].videoinfo[].label 标签
     * @return string info[0].videoinfo[].title 封面
     * @return string info[0].videoinfo[].status  1 审核中  2 已上架3，未通过
     * @return string msg 提示信息
     */
    public  function getMyVideo(){
        $rs = array('code' => 0, 'msg' => '我的长视频列表', 'info' => array());
        $language_id = $_REQUEST['language_id'];

        if (empty($language_id)) {
            $language_id = 101;
        }
        $game_tenant_id=$this->game_tenant_id;
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $p=$this->p;
        $status=$this->status;
        $domain = new Domain_Longvideo();
        $result = $domain->getMyLongVideo($this->uid,$game_tenant_id, $p,$status);
        $rs['info']  = $result;
        return $rs;
    }

    /**
     * 长视频下载
     * @desc 用于用户下载记录
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public function downloadVideo() {
        $rs = array('code' => 0, 'msg' => '下载成功', 'info' => array());
        $uid=$this->uid;
        $token=checkNull($this->token);
        $videoid=$this->videoid;
        $game_tenant_id=$this->game_tenant_id;
        $isBan=isBan($uid);
        if($isBan=='0'){
            $rs['code'] = 700;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }

        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }

        $domain = new Domain_Longvideo();
        $result = $domain->downloadVideo($uid,$videoid,$game_tenant_id);
        if($result==1001){
            $rs['code'] = 1001;
            $rs['msg'] = "视频已删除";
            return $rs;
        }else if($result==1002){
            $rs['code'] = 1002;
            $rs['msg'] = "不用下载自己的视频";
            return $rs;
        }
        return $rs;
    }

    /**
     * 长视频下载列表
     * @desc 我的下载（长视频）
     * @return int code 操作码，0表示成功
     * @return array info
     * @return array info[0].counts 观看的视频总数量
     * @return array info[0].videoinfo 观看的视频列表
     * @return object info[0].videoinfo[].thumb 图片
     * @return string info[0].videoinfo[].label 标签
     * @return string info[0].videoinfo[].title 封面
     * @return string msg 提示信息
     */


    public  function getMydownload(){
        $rs = array('code' => 0, 'msg' => '我的下载', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $game_tenant_id=$this->game_tenant_id;
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $p=$this->p;
        $domain = new Model_Longvideo();
        $result = $domain->getMydownload($this->uid,$game_tenant_id, $p);
        $rs['info']  = $result;
        return $rs;
    }
    /**
     * 删除视频我的下载
     * @desc 用于删除我的下载（长短视频通用）
     * @return int code 操作码，0表示成功
     * @return array info[conut] 删除条数

     */
    public  function delMydownload(){
        $rs = array('code' => 0, 'msg' => '删除成功', 'info' => array());
        $uid=checkNull($this->uid);
        $id=checkNull($this->id);
        $isdelete_all=checkNull($this->isdelete_all);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);

        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }

        $domain = new Domain_Longvideo();
        $rs['info']['count'] = $domain->delMydownload($uid,$id,$isdelete_all);
        return $rs;
    }

    /**
     * 猜你喜欢
     * @desc 猜你喜欢（长视频）
     * @return int code 操作码，0表示成功
     * @return array info
     * @return array info[0].counts 观看的视频总数量
     * @return array info[0].videoinfo 观看的视频列表
     * @return object info[0].videoinfo[].thumb 图片
     * @return string info[0].videoinfo[].label 标签
     * @return string info[0].videoinfo[].title 封面
     * @return string msg 提示信息
     */

    public  function guessLikeLongVide(){
        $rs = array('code' => 0, 'msg' => '猜你喜欢', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $game_tenant_id=$this->game_tenant_id;
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $p=$this->p;

        $domain = new Domain_Longvideo();
        $result = $domain->guessLikeLongVide($this->uid, $p);
        $rs['info']  = $result;
        return $rs;

    }
    /**
     * banner图
     * @desc banner图（长视频）
     * @return int code 操作码，0表示成功
     * @return array info

     */
    public  function getBanner(){
        $rs = array('code' => 0, 'msg' => 'banner图', 'info' => array());
        $game_tenant_id=$this->game_tenant_id;
        $label =$this->label;
        $domain = new Domain_Longvideo();
        $result = $domain->getBanner($label );
        $rs['info']  = $result;
        return $rs;
    }

    /**
     * 热门演员
     * @desc 热门演员
     * @return int code 操作码，0表示成功
     * @return array info

     */
    public  function getHotPerformer(){
        $rs = array('code' => 0, 'msg' => '热门演员', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $game_tenant_id=$this->game_tenant_id;
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $p=$this->p;
        $domain = new Domain_Longvideo();
        $result = $domain->getHotPerformer($this->uid,$game_tenant_id, $p);
        $rs['info']  = $result;
        return $rs;
    }

    /**
     * 长视频热门视频
     * @desc 长视频热门视频
     * @return int code 操作码，0表示成功
     * @return array info

     */
    public  function getHotVideo(){
        $rs = array('code' => 0, 'msg' => '热门视频', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }

        $url = $this->url;
        $game_tenant_id=$this->game_tenant_id;
        if (!empty($this->uid)){
            $checkToken = checkToken($this->uid, $this->token);
            if ($checkToken == 700) {
                $rs['code'] = $checkToken;
                $language = DI()->config->get('language.tokenerror');
                $rs['msg'] = $language[$language_id];
                return $rs;
            }
        }

        $p=$this->p;
        $domain = new Domain_Longvideo();
        $result = $domain->getHotVideo($this->uid,$game_tenant_id, $p,$url);
        $rs['info']  = $result;
        return $rs;
    }

    /**
     * 长视频上传
     * @desc 长视频上传
     * @return int code 操作码，0表示成功
     * @return array info

     */
    public function  uploadVideo(){
        $rs = array('code' => 0, 'msg' => '上传成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $game_tenant_id=$this->game_tenant_id;
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $img_url = date('YmdHis', time()) . random(5, 10000000);


        $domain = new Domain_Longvideo();
        $label= $this->label;
        $title = $this->title;
        $desc = $this->desc;
        $region = $this->region;
        $years = $this->years;
        $uid = $this->uid;
        $classify = $this->classify;
        $performer =  $this->performer;
        $result = $domain->uploadVideo($uid,$label,$title,$classify,$performer,$desc,$region,$years);
        if ($result){
            return $result;
        }

    }
    /**
     * 演员列表
     * @desc 演员列表
     * @return int code 操作码，0表示成功
     * @return array info

     */
    public  function getperformer(){
        $rs = array('code' => 0, 'msg' => '演员列表', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $game_tenant_id=$this->game_tenant_id;
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $p=$this->p;
        $domain = new Domain_Longvideo();
        $result = $domain->getperformer();
        $rs['info']  = $result;
        return $rs;
    }

    public  function  getRandomVideo(){
        $rs = array('code' => 0, 'msg' => '11长视频', 'info' => array());
        $url = $this->url;
        $domain = new Domain_Longvideo();
        $result = $domain->getRandomVideo($url);
        if ($result){
            $rs['info']  = $result;
        }
        return $rs;
    }
    /**
     * 购买长视频
     * @desc 购买长视频
     * @return int code 操作码，0表示成功
     * @return array info.isbuy  短视频购买成功

     */
    public  function buyLongvideo(){
        $rs = array('code' => 0, 'msg' => '购买成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $uid = $this->uid;
        $checkToken = checkToken($this->uid, $this->token);

        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $videoid = $this->videoid;
        $domain = new Domain_Longvideo();
        $result = $domain->buyLongvideo($uid,$videoid);
        if($result==1001){
            $rs['code'] = 1001;
            $rs['msg'] = "视频已删除";
            return $rs;
        }else if($result==1002){
            $rs['code'] = 1002;
            $rs['msg'] = "不能给自己购买";
            return $rs;
        }else if($result==1003){
            $rs['code'] = 1003;
            $rs['msg'] = "该视频无需购买";
            return $rs;
        } else if($result==1004){
            $rs['code'] = 1004;
            $rs['msg'] = "已经购买了";
            return $rs;
        } else if($result==1005){
            $rs['code'] = 1004;
            $rs['msg'] = "余额不足";
            return $rs;
        } else if($result==1006){
            $rs['code'] = 1006;
            $rs['msg'] = "您是会员,该视频会员需求购买";
            return $rs;
        }

        $rs['info'][0]=$result;

        $rs['code'] = isset($result['code']) ? $result['code'] : $rs['code'];
        $rs['msg'] = isset($result['msg']) ? $result['msg'] : $rs['msg'];
        $rs['info'] = isset($result['info']) ? $result['info'] : $rs['info'];

        return $rs;
    }
    /**
     * 购买长视频vip
     * @desc 购买长视频vip
     * @return int code 操作码，0表示成功
     * @return array info.isbuy  长视频购买成功

     */
    public  function buyLongvideovip(){
        $rs = array('code' => 0, 'msg' => '购买成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $uid = $this->uid;
        $checkToken = checkToken($this->uid, $this->token);

        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $vip_grade = $this->vip_grade;
        $domain = new Domain_Longvideo();
        $result = $domain->buyLongvideovip($uid,$vip_grade);
        if($result==1001){
            $rs['code'] = 1001;
            $rs['msg'] = "无该会员等级";
            return $rs;
        }else if($result==1005){
            $rs['code'] = 1004;
            $rs['msg'] = "余额不足";
            return $rs;
        }

        $rs['info'][0]=$result;

        $rs['code'] = isset($result['code']) ? $result['code'] : $rs['code'];
        $rs['msg'] = isset($result['msg']) ? $result['msg'] : $rs['msg'];
        $rs['info'] = isset($result['info']) ? $result['info'] : $rs['info'];

        return $rs;
    }
    /**
     * 购买长视频vip记录
     * @desc 购买长视频vip记录
     * @return int code 操作码，0表示成功
     * @return array info.isbuy  长视频购买成功纪律

     */
    public  function buyLongvideovipList(){
        $rs = array('code' => 0, 'msg' => '查询成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $uid = $this->uid;
        $checkToken = checkToken($this->uid, $this->token);

        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $domain = new Domain_Longvideo();
        $result = $domain->buyLongvideovipList($uid);
        if($result==1001){
            $rs['code'] = 1001;
            $rs['msg'] = "无该会员等级";
            return $rs;
        }else if($result==1005){
            $rs['code'] = 1004;
            $rs['msg'] = "余额不足";
            return $rs;
        }

        $rs['info'][0]=$result;

        $rs['code'] = isset($result['code']) ? $result['code'] : $rs['code'];
        $rs['msg'] = isset($result['msg']) ? $result['msg'] : $rs['msg'];
        $rs['info'] = isset($result['info']) ? $result['info'] : $rs['info'];

        return $rs;
    }



    /**
     * 约会观看用户次数
     * @desc 约会观看用户次数
     * @return int code 操作码，0表示成功
     * @return array info.isbuy  短视频购买成功

     */
    public function watchVodeo(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $uid = $this->uid;
        $checkToken = checkToken($this->uid, $this->token);

        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $videoid = $this->videoid;
        $domain = new Domain_Longvideo();
        $game_tenant_id=$this->game_tenant_id;
        $result = $domain->watchVodeo($uid,$videoid,$game_tenant_id);


        $rs['code'] = isset($result['code']) ? $result['code'] : $rs['code'];
        $rs['msg'] = isset($result['msg']) ? $result['msg'] : $rs['msg'];
        $rs['info'] = isset($result['info']) ? $result['info'] : $rs['info'];
        return  $rs;
    }
    /**
     * 获取长视频vip列表
     * @desc 获取长视频vip列表
     * @return int code 操作码，0表示成功
     * @return array info.isbuy  获取长视频vip列表

     */
    public function getLongvideovip(){
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $domain = new Domain_Longvideo();
        $game_tenant_id=$this->game_tenant_id;
        $result = $domain->getLongvideovip($game_tenant_id);
        $rs['info'] =$result;
        return  $rs;
    }
    /**
     * 获取长视频热门搜索
     * @desc 获取长视频热门搜索
     * @return int code 操作码，0表示成功
     * @return array info.isbuy  获取长视频热门搜索

     */
    public function getLongvideosearch(){
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $domain = new Domain_Longvideo();
        $game_tenant_id=$this->game_tenant_id;
        $result = $domain->getLongvideosearch($game_tenant_id);
        $rs['info'] =$result;
        return  $rs;
    }
    /**
     * 获取长视频历史搜索【根据用户】
     * @desc 获取长视频历史搜索
     * @return int code 操作码，0表示成功
     * @return array info.isbuy  获取长视频历史搜索

     */
    public function getSearchbyuser(){
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $uid = $this->uid;
        $checkToken = checkToken($this->uid, $this->token);

        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_Longvideo();
        $result = $domain->getSearchbyuser($uid);
        $rs['info'] =$result;
        return  $rs;
    }
    /**
     * 删除长视频历史搜索【根据用户】
     * @desc 删除长视频历史搜索
     * @return int code 操作码，0表示成功
     * @return array info.isbuy  删除长视频历史搜索

     */
    public function delSearchbyuser(){
        $rs = array('code' => 0, 'msg' => '删除成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $uid = $this->uid;
        $search_id = $this->search_id;
        $checkToken = checkToken($this->uid, $this->token);

        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_Longvideo();
        $result = $domain->delSearchbyuser($uid,$search_id);
        $rs['info'] =$result;
        return  $rs;
    }
    /**
     * 长视频信息更新
     * @desc 短视频信息更新
     * @return int code 操作码，0表示成功
     * @return array info
     */
    public function  updateVideoInfo(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $uid = $this->uid;
        $video_id = $this->video_id;
        $file_store_key =checkNull($this->file_store_key);
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $domain = new Domain_Longvideo();
        $result = $domain->updateVideoInfo($uid, $video_id, $file_store_key);

        $rs['code'] = $result['code'] ? $result['code'] : $rs['code'];
        $rs['msg'] = $result['msg'] ? $result['msg'] : $rs['msg'];
        $rs['info'] = $result['info'] ? $result['info'] : $rs['info'];
        return $rs;
    }



}
