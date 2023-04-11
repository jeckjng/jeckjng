<?php
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Driver\FFProbeDriver;
use Qiniu\Storage\UploadManager;
use Qiniu\Auth;

use api\Common\CustRedis;

class Api_Shotvideo extends PhalApi_Api {

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
            'addCollection' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
            ),
            'getCollection' => array(
                'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
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
            	'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'url'=>array('name' => 'url', 'desc' => '前台域名'),
                ),
            'getVideobyclassify' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' =>true,'min' => 1, 'desc' => '用户ID'),
                'classify' => array('name' => 'classify', 'type' => 'string', 'desc' => '视频分类.如果为空,按时间倒序返回所有视频分类'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'type' => array('name' => 'type', 'type' => 'string', 'desc' => '1 :最高人气 2:最多下载 3:最多观看'),
                'lists_params' => array('name' => 'lists_params', 'type' => 'string', 'desc' => '偏移量参数，返回的数据保存一下在本地，每次请求的时候获取保存的参数上传'),
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
                'url'=>array('name' => 'url', 'desc' => '前台域名'),
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


            'getNearby'=>array(
            	'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
                'lng' => array('name' => 'lng', 'type' => 'string', 'desc' => '经度值'),
                'lat' => array('name' => 'lat', 'type' => 'string','desc' => '纬度值'),
				'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
            ),

            'setConversion'=>array(
            	'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
            	//'token' => array('name' => 'token', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => 'token'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
                //'random_str'=>array('name' => 'random_str', 'type' => 'string', 'require' => true, 'desc' => '加密串'),
                'is_search'=>array('name' => 'is_search', 'type' => 'string',  'desc' => '是否是搜索 1:是不传或传其他为不是 '),
                'is_record'=>array('name' => 'is_record', 'type' => 'string',  'desc' => ' 1: 返回操作记录次数据 '),
                'type'=> array('name' => 'type', 'type' => 'int', 'min' => 0,  'desc' => '1短视频 2 长视频'),
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
            'getVideoclassify'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'min' => 1, 'desc' => 'token'),
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
            'guessLikeShotVide'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string',  'min' => 1, 'require' => true, 'desc' => 'token'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
            ),
       /*     'getShotVideoLabel'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string',  'min' => 1, 'require' => true, 'desc' => 'token'),

            ),*/
            'getShotVideoRecommend'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int',  'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string',  'min' => 1, 'desc' => 'token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'url'=>array('name' => 'url', 'desc' => '前台域名'),
            ),
            'getShotVideoByLikes'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int',  'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string',  'min' => 1,  'desc' => 'token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'data_type' => array('name' => 'data_type', 'type' => 'string',  'desc' => '数据类型 1：点赞 2 ：最新上传 '),
                'cycle'=> array('name' => 'cycle', 'type' => 'string',  'desc' => '数据类型 1：周榜 2 ：月榜  ;传空字符传表示不筛选'),
                'is_surge'=> array('name' => 'is_surge', 'type' => 'string',  'desc' => '数据类型 1：人气飙升; 传空字符传表示不筛选：'),
            ),
            'getHotLabelData'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int',  'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string',  'min' => 1, 'require' => true, 'desc' => 'token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'data_type' => array('name' => 'data_type', 'type' => 'string','require' => true,  'desc' => '数据类型 1：最高人气 2 ：最多下载 3：最多观看 '),

            ),
            'getSearchContent' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'desc' => '用户ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'searchcontent' => array('name' => 'searchcontent', 'type' => 'string', 'desc' => '搜索类容，当type=search 时，必传'),
                'url'=>array('name' => 'url', 'desc' => '前台域名'),
                ),
            'getRankhot'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'desc' => '用户ID'),
                'url'=>array('name' => 'url', 'desc' => '前台域名'),
            ),
            'getHotPerformer' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => false, 'desc' => '用户Token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
            ),
            'getHotVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户Token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'url'=>array('name' => 'url', 'desc' => '前台域名'),
            ),
            'uploadVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'file' => array('name' => 'file', 'type' => 'file', 'require' => true, 'desc' => '视频文件'),
                'title' => array('name' => 'title', 'type' => 'string', 'require' => true, 'desc' => '标题'),
                'label' => array('name' => 'label', 'type' => 'string', 'require' => true, 'desc' => '标签 字符串英文逗号拼接 =》 改成分类了'),
                'desc' => array('name' => 'desc', 'type' => 'string', 'desc' => '剧情简介'),
                'region' => array('name' => 'region', 'type' => 'string', 'desc' => '地区'),
                'years' => array('name' => 'years', 'type' => 'string',  'desc' => '年份'),
            ),
            'addNewVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'title' => array('name' => 'title', 'type' => 'string', 'require' => true, 'desc' => '标题'),
                'classify' => array('name' => 'classify', 'type' => 'string', 'require' => true, 'desc' => '多分类个使用英文逗号","拼接'),
                'desc' => array('name' => 'desc', 'type' => 'string', 'desc' => '剧情简介'),
                'years' => array('name' => 'years', 'type' => 'string',  'desc' => '年份'),
            ),
            'uploadVideoToUrl' => array(
                'game_tenant_id' => array('name' => 'game_tenant_id', 'type' => 'int', 'min' => 1, 'require' => false, 'desc' => '用户ID'),
                'language_id' => array('name' => 'language_id', 'type' => 'int', 'require' => false, 'desc' => '用户Token'),
                'file' => array('name' => 'file', 'type' => 'file', 'require' => true, 'desc' => '视频文件'),
            ),
            'updateVideoInfo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'video_id' => array('name' => 'video_id', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
                'file_store_key' => array('name' => 'file_store_key', 'type' => 'string', 'require' => true, 'desc' => '视频key'),
            ),
            'delMyVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => 'token'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '视频ID'),
                'type'=> array('name' => 'type', 'type' => 'int', 'min' => 1,  'desc' => '1短视频 2 长视频'),
            ),

            'getconcentrationVideo'=> array(
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'url'=>array('name' => 'url', 'desc' => '前台域名'),
            ),

            'getAuthurVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
                'url'=>array('name' => 'url', 'desc' => '前台域名'),
            ),
            'getMyVideoStatistics' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
            ),
            'buyShotvideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' =>true,'min' => 1, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'require' => true,'desc' => '视频id'),

            ),
            'buyHistory' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' =>true,'min' => 1, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),

            ),
            'bindShop' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' =>true,'min' => 1, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'videoid' => array('name' => 'videoid', 'type' => 'string', 'require' => true, 'desc' => '视频id'),
                'shoptype' => array('name' => 'shoptype', 'type' => 'string', 'require' => true, 'desc' => '1 绑定商品  2  绑定店铺'),
                'shop_value' => array('name' => 'shop_value', 'type' => 'string', 'require' => true, 'desc' => '绑定值'),
                'shop_url' => array('name' => 'shop_url', 'type' => 'string', 'desc' => '商城地址'),
            ),

            // "getWatchVideoNum"=>array(
            //     'uid' => array('name' => 'uid', 'type' => 'int', 'require' =>true,'min' => 1, 'desc' => '用户ID'),
            //     'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
            //     'type'=> array('name' => 'type', 'type' => 'int', 'min' => 0,  'desc' => '1短视频 2 长视频'),
            // ),
            "autoPass" => array(

            ),
    );

	}
	
	/**
	 * 发布短视频
	 * @desc 用于发布短视频
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].id 视频记录ID
	 * @return string msg 提示信息
	 */
	public function setVideo() {
		$rs = array('code' => 0, 'msg' => '发布成功', 'info' => array());
		
		$uid=$this->uid;
        $label=$this->label;
		$token=checkNull($this->token);
		$title=checkNull($this->title);
		$thumb=checkNull($this->thumb);
        $hrefcontent=checkNull($this->hrefcontent);
        $hrefcontent = base64_decode($hrefcontent);
		$gifurl=checkNull($this->gifurl);
       // $videoid=checkNull($this->videoid);
		$checkToken=checkToken($uid,$token);
		if($checkToken==700){
			$rs['code'] = $checkToken;
			$rs['msg'] = '您的登陆状态失效，请重新登陆！';
			return $rs;
		}

		//文件名称
        $file_name = date('YmdHis',time()).random(5,10000000);
        //写入m3u8
        file_put_contents($_SERVER['DOCUMENT_ROOT']."/test/".$file_name.".m3u8",$hrefcontent);
        $video_href = 'http://'.$_SERVER['SERVER_NAME'].'/test/'.$file_name.".m3u8";

		/* $qiniu_space_host=DI()->config->get('app.Qiniu.space_host');
		
		$thumb=$qiniu_space_host.'/'.$thumb;
		$thumb_s=$thumb.'?imageView2/2/w/200/h/200';
		$href=$qiniu_space_host.'/'.$href; */
        $thumb_s='';
        if($thumb){
            $thumb_s=$thumb.'?imageView2/2/w/200/h/200';
        }
		

		$data=array(
			"uid"=>$uid,
            "label"=>$label,
			"title"=>$title,
			"thumb"=>$thumb,
			"href"=>$video_href,
			"gifurl"=>$gifurl,
			"create_date"=>date('Y-m-d h:m:s'),
           // "video_id"=>$videoid,
        );
		
		$domain = new Domain_Shotvideo();
		$info = $domain->setVideo($data);
		if(!$info){
			$rs['code']=1001;
			$rs['msg']='发布失败';
		}
        $rs['info'][0]=$info;
		return $rs;
	}		
	
   	/**
     * 评论/回复
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
		
		$uid=$this->uid;
		$token=checkNull($this->token);
		$touid=$this->touid;
		$videoid=$this->videoid;
		$commentid=$this->commentid;
		$parentid=$this->parentid;
		$content=checkNull($this->content);
		$at_info=$this->at_info;
        $game_tenant_id=$this->game_tenant_id;

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
        $userInfo  = getUserInfo($uid);
        if (!in_array($userInfo['user_type'],[2,5,6,7])){
            $rs['code'] = 1002;
            $rs['msg'] = "当前您是游客登录，请注册账号后再试";
            return $rs;
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
            'video_type'=>1
		);

		/*var_dump($data);
		die;*/

        $domain = new Domain_Shotvideo();
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
  /*  /**
     * 回复列表
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
 /*   public function getReplys() {
        $rs = array('code' => 0, 'msg' => '查询成功', 'info' => array());
        $isBan=isBan($this->uid);
        if($isBan=='0'){
            $rs['code'] = 700;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }

        $domain = new Domain_Shotvideo();
        $rs['info'] = $domain->getReplys($this->uid,$this->commentid,$this->p);

        return $rs;
    }*/
    /**
     * 视频评论列表
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

        $domain = new Domain_Shotvideo();
        $rs['info'][0] = $domain->getComments($this->uid,$this->videoid,$this->p,$game_tenant_id);

        return $rs;
    }
    /**
     * 视频详情
     * @desc 用于获取视频详情
     * @return int code 操作码，0表示成功，1000表示视频不存在
     * @return array info[] 视频详情
     * @return object info.uid   会员id
     * @return string info.video_id 视频id
     * @return string info.href m3u8地址
     * @return string info.label 视频标签
     * @return string info.comments 评论数
     * @return string info.is_download 是否下载 1 已下载 0未下载
     * @return string info.collection 收藏数
     * @return string info.watchtimes 观看数
     * @return string info.is_collection  是否收藏
     * @return string info.is_like 是否喜欢
     * @return string info.is_follow 是否关注
     * @return string msg 提示信息
     */
    public function getVideo() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        if (!empty($this->uid)){
            $uid=checkNull($this->uid);
        }else{
            $uid = '';
        }

        $url = $this->url;
        $videoid=checkNull($this->videoid);
        $is_search=$this->is_search;
        $domain = new Domain_Shotvideo();
        $result = $domain->getVideo($uid,$videoid,$is_search,$url);
        if($result==1000){
            $rs['code'] = 1000;
            $rs['msg'] = "视频已删除";
            return $rs;

        }
        $rs['info'][0]=$result;

        return $rs;
    }

    /**
     * 新增标签
     * @desc 用于新增视频标签
     * @return int code 操作码，0表示成功，1000表示视频不存在
     * @return array info[0] 视频详情

     */
    /* 视频新增标签 */
    public function setVideoLabel(){
        $rs = array('code' => 0, 'msg' => '查询成功', 'info' => array());
        $uid=checkNull($this->uid);
		$token=checkNull($this->token);
		$label=checkNull($this->label);

        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $isexist=DI()->notorm->video_label
            ->select("*")
            ->where('uid=? and label=? and is_delete=?',$uid,$label,1)
            ->fetchOne();
        if($isexist){
            $rs['code']=1001;
            $rs['msg']='已经存在该标签';
            return $rs;
        }
        $nowtime=time();
        $data=array(
                    'uid'=>$uid,
                    'label'=>$label,
                    'addtime'=>$nowtime,
                    'is_delete'=>1,
        );
        $rs=DI()->notorm->video_label->insert($data);
        return $rs;
    }

    /**
     * 删除标签
     * @desc 用于删除视频标签
     * @return int code 操作码，0表示成功，1000表示视频不存在
     * @return array info[0] 视频详情

     */
    /* 删除视频标签 */
    public function deleteVideoLabel(){
        $rs = array('code' => 0, 'msg' => '删除成功', 'info' => array());
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $label=checkNull($this->label);


        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }

        $isexist=DI()->notorm->video_label
            ->select("*")
            ->where('uid=? and label=? and is_delete=?',$uid,$label,1)
            ->fetchOne();
        if(!$isexist){
            $rs['code']=1001;
            $rs['msg']='不存在该标签';
            return $rs;
        }
        $data = array(
            'uid'=>$uid,
            'label'=>$label
        );
        $domain = new Domain_Shotvideo();
        $rs['info'][0] = $domain->delVideolabel($data);


        return $rs;
    }
    /**
     * 视频分类列表
     * @desc 用于获取短视频分类列表

     * @return string msg 提示信息
     */
    public function getVideoclassify() {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid=$this->uid;


        if(!empty($uid)){
            $token=checkNull($this->token);
            $checkToken=checkToken($uid,$token);

            if($checkToken==700){
                $rs['code'] = $checkToken;
                $rs['msg'] = '您的登陆状态失效，请重新登陆！';
                return $rs;
            }

        }

        $domain = new Domain_Shotvideo();
        $result = $domain->getVideoclassify($uid);

        $rs['info'][0]=$result;

        return $rs;
    }

    /**
     *   获取多个视频详情
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
     * @return string count 返回标签下面的视频数量信息
     * @return string label 对应的标签名称
     * @return string labelnums 标签下视频数量
     */
    public function getVideoList() {

        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid=$this->uid;
        $p=$this->p;
        $isBan=isBan($this->uid);
        $url = $this->url;
        if($isBan=='0'){
            $rs['code'] = 700;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }

        $key='shotvideoHot_'.$p;

      /*  $info=getcaches($key);

        if(!$info){*/
            $domain = new Domain_Shotvideo();
            $info= $domain->getVideoList($uid,$p,$url);

            if($info==10010){
                $rs['code'] = 0;
                $rs['msg'] = "暂无视频列表";
                return $rs;
            }

           /* if (isset($info['code'] ) && $info['code'] ==800){
                $rs['code'] = 800;
                $rs['msg'] = "游客只能观看".$info['num']."条视频";
                return $rs;
            }*/
           // setcaches($key,$info,2);
     /*   }*/

        $rs['count'] = $info['count'];
        unset($info['count']);
        $rs['info'] = array_values($info);
        return $rs;
    }

    /**
     * 短视频点赞接口
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

        /*$checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }*/
        $userInfo  = getUserInfo($uid);
        if (!in_array($userInfo['user_type'],[2,5,6,7])){
            $rs['code'] = 1002;
            $rs['msg'] = "当前您是游客登录，请注册账号后再试";
            return $rs;
        }
        $domain = new Domain_Shotvideo();
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
        $rs['info'][0]=$result;
        //点赞收益发放
        return $rs;
    }

    /**
     * 根据分类获取视频
     * @desc  短视频分类视频
     * @return int code 操作码，0表示成功，1000表示视频不存在
     *  @return string msg 提示信息
     * @return array userinfo  会员信息（头像，昵称等等等）
     * @return array info[0] 视频详情
     * @return object info[0].uid   会员id
     * @return object info[0].title  视频标题
     * @return string info[0].video_id 视频id
     * @return string info[0].href   m3u8地址
     * @return string info[0].label 视频标签
     * @return string info[0].comments 评论数
     * @return string lists_params 偏移量参数
     */
    public function getVideobyclassify() {

        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());

        $p=$this->p;
        $classify=$this->classify;
        $uid=$this->uid;

        $domain = new Domain_Shotvideo();
        $info= $domain->getVideobyclassify($p,$classify,$uid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        $rs['lists_params'] = $info['lists_params'] ? $info['lists_params'] : $rs['lists_params'];
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
        $domain = new Domain_Shotvideo();
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
     * 短视频收藏接口
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

        $domain = new Domain_Shotvideo();
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
        $rs['info'][0]=$result;
        return $rs;
    }
    /**
     * 短视频我的收藏信息
     * @desc 根据uid获取收藏视频信息
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info.counts   收藏数量
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

        $domain = new Domain_Shotvideo();

        $rs['info'] = $domain->getCollection($this->uid,$this->p,$game_tenant_id);

        return $rs;
    }
    /**
     * 更新短视频观看次数
     * @desc 根
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].watchtimes 返回该视频观看次数
     * @return string info[0].ad_link 广告链接（观看数量达到上限返回）
     * @return string info[0].ad_time 广告持续时间（秒，观看数量达到上限返回）
     * @return string info[0].ad_link_type 广告链接类型：1.图片，2.视频（观看数量达到上限返回）
     * @return string msg 提示信息
     */
    public function setConversion() {
        $rs = array('code' => 0, 'msg' => '更新观看次数成功', 'info' => array(),'is_record'=> 0);
        $isBan=isBan($this->uid);
        $game_tenant_id=$this->game_tenant_id;
        if($isBan=='0'){
            $rs['code'] = 700;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }

        $domain = new Domain_Shotvideo();

        $is_search =$this->is_search;
        $is_record =$this->is_record;
        $result = $domain->setConversion($this->videoid,$this->uid,$game_tenant_id,$is_search,$is_record);
        $count = DI()->notorm->video_watch_record->where("uid=?",$this->uid)->group("videoid")->count();
        $result[0]['video_num'] = $rs['info'][0]["video_num"] = $count;
        
        if (!empty($is_record)){
            $rs['is_record'] = $is_record;
        }
        if($result['code']==1001){
            $rs['code'] = 1001;
            $rs['msg'] = "视频已删除";
            return $rs;
        }
        if($result['code'] == 2059 || $result['code'] == 2061){
            $rs['code'] = $result['code'];
            $rs['msg'] = $result['msg'] ? $result['msg'] : $rs['msg'];
            $rs['info'] = $result['info'] ? $result['info'] : $rs['info'];
            return $rs;
        }
        if($result['code']==800){
            $rs['code'] = 801;
            $rs['msg'] = "游客观看数量达到最大值,请注册";
            return $rs;
        }

        if($result['code']==1002){
            $rs['code'] = 900;
            $rs['msg'] = $result['msg'];

        }
        $rs['info'] = $result;

        return $rs;
    }
    /**
     * 我的短视频
     * @desc 我的短视频
     * @return int code 操作码，0表示成功
     * @return array info
     *  @return string info[0].videoinfo[].status  1 审核中  2 已上架3，未通过

     */
    public  function getMyVideo(){
        $rs = array('code' => 0, 'msg' => '我的短视频列表', 'info' => array());
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
        $domain = new Domain_Shotvideo();
        $result = $domain->getMyShotVideo($this->uid,$game_tenant_id, $p,$status);
        $rs['info']  = $result;
        return $rs;
    }
    /**
     * 短视频下载
     * @desc 用于用户下载记录
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].iscollection 会员是否点赞
     * @return string info[0].collection   该视频的点赞数量
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

        $domain = new Domain_Shotvideo();
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
     * 短视频下载列表
     * @desc 我的下载（短视频）
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
        $rs = array('code' => 0, 'msg' => '短视频下载列表', 'info' => array());
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
        $domain = new Domain_Shotvideo();
        $result = $domain->getMydownload($this->uid,$game_tenant_id, $p);
        $rs['info']  = $result;
        return $rs;
    }
    /**
     * 猜你喜欢
     * @desc 猜你喜欢（短视频）
     * @return int code 操作码，0表示成功
     * @return array info
     * @return array info[0].counts 观看的视频总数量
     * @return array info[0].videoinfo 观看的视频列表
     * @return object info[0].videoinfo[].thumb 图片
     * @return string info[0].videoinfo[].label 标签
     * @return string info[0].videoinfo[].title 封面
     * @return string msg 提示信息
     */

    public  function guessLikeShotVide(){
        $rs = array('code' => 0, 'msg' => '猜你喜欢', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $p=$this->p;
        $domain = new Domain_Shotvideo();
        $result = $domain->guessLikeShotVide($this->uid, $p);

        $rs['info']  = $result;
        return $rs;

    }

    /**
     * 获取短视频标签
     * @desc 获取短视频标签（短视频）
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
/*    public  function getShotVideoLabel(){
        $rs = array('code' => 0, 'msg' => '标签列表', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_Shotvideo();
        $result = $domain->getShotVideoLabel();
        $rs['info']  = $result;
        return $rs;
    }*/
    /**
     * 获取短视频推荐
     * @desc 获取短视频推荐（短视频）
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public  function getShotVideoRecommend(){
        $rs = array('code' => 0, 'msg' => '获取短视频推荐', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $url = $this->url;
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
        $domain = new Domain_Shotvideo();
        $result = $domain->getShotVideoRecommend($this->uid,$p,$url);
        $rs['info']  = $result;
        return $rs;
    }

    /**
     * 根据点赞数排序（精选列表上部分数据公用）
     * @desc 根据点赞数排序（短视频）
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public function getShotVideoByLikes(){
        $rs = array('code' => 0, 'msg' => '根据点赞数排序', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        if (!empty($this->uid)){
            $checkToken = checkToken($this->uid, $this->token);
            if ($checkToken == 700) {
                $rs['code'] = $checkToken;
                $language = DI()->config->get('language.tokenerror');
                $rs['msg'] = $language[$language_id];
                return $rs;
            }
        }


        $p  = $this->p;
        $dataType = $this->data_type;
        $cycle = $this->cycle;
        $isSurge = $this->is_surge;
        $domain = new Domain_Shotvideo();
        $result = $domain->getShotVideoByLikes($this->uid,$dataType,$cycle,$isSurge,$p);
        $rs['info']  = $result;
        return $rs;
    }


    /**
     * 获取火热标签数据列表
     * @desc 获取火热标签数据列表（首页下半部分）
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public  function getHotLabelData(){
        $rs = array('code' => 0, 'msg' => '获取火热标签数据', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $p  = $this->p;
        $dataType = $this->data_type;
        $domain = new Domain_Shotvideo();
        $result = $domain->getHotLabelData($this->uid,$dataType,$p);
        $rs['info']  = $result;
        return $rs;
    }

    /**
     *   视频搜索
     * @desc 视频热搜
     *   短视频视频搜索
     * @desc 短视频视频热搜
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
        $url = $this->url;
        if (!empty($uid)){
            $isBan=isBan($this->uid);
            if($isBan=='0'){
                $rs['code'] = 700;
                $rs['msg'] = '该账号已被禁用';
                return $rs;
            }

        }
        $p=$this->p;

        $searchcontent = $this->searchcontent;

       /* $key='shotvideosearch_'.$p;

        $info=getcaches($key);

        if(!$info){*/
            $domain = new Domain_Shotvideo();
            $info= $domain->getSearchContent($uid,$p,$searchcontent,$url);

            if($info==10010){
                $rs['code'] = 0;
                $rs['msg'] = "暂无视频列表";
                return $rs;
            }

       /*     setcaches($key,$info,2);
        }*/
        //观影排行榜 数据更新
        /*if(!empty($info)){

            $scorehot = DI()->redis->zScore('rank_shothotsearch_list',$searchcontent);
            if(!$scorehot){
                $rankhot=DI()->redis -> zAdd('rank_shothotsearch_list',1,$searchcontent);
            }else{
                $rankhot=DI()->redis -> zAdd('rank_shothotsearch_list',$scorehot+1,$searchcontent);
            }
        }*/

        $rs['info'] = array_values($info);
        return $rs;
    }
    /**
     * 短视频热搜排行榜
     * @desc 短视频热搜排行榜
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].dayrank   日观看册数
     * @return string msg 提示信息
     */
    public function getRankhot()
    {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());

        if (!empty($this->uid)){
            $isBan = isBan($this->uid);
            if ($isBan == '0') {
                $rs['code'] = 700;
                $rs['msg'] = '该账号已被禁用';
                return $rs;
            }
        }
        $game_tenant_id = $this->game_tenant_id;

        $url = $this->url;
        $ranklist = DI()->redis->zRevRange('rank_shorhotsearch_list', 0, 9, true);
        $download_url =  play_or_download_url(1);
        $rankinfo = array();
        if ($ranklist){
            foreach ($ranklist as $k => $v) {
                $video = DI()->notorm->video
                    ->select("id,thumb,title,create_date")
                    ->where("id='{$k}' and status =2 ")
                    ->fetchOne();
                if ($video) {
                    $rankinfo[$k]['title'] = $video['title'];
                    $rankinfo[$k]['hot_searches'] = $v;
                    if ($url){
                        $rankinfo[$k]['thumb'] = $url.$video['thumb'];
                    }else{
                        $rankinfo[$k]['thumb'] = $download_url['url'].$video['thumb'];
                    }

                    $rankinfo[$k]['id'] = $video['id'];
                    $rankinfo[$k]['create_date'] = $video['create_date'];
                }
            }
        }else{
            $rankinfo= DI()->notorm->video

                ->select("id,title,hot_searches,thumb,create_date")
                ->order('hot_searches desc')
                ->limit(0,10)
                ->fetchAll();
            foreach ($rankinfo as $key =>$value){
                if ($url){
                    $rankinfo[$key]['thumb'] = $url.$value['thumb'];
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
     * 短视频热门演员
     * @desc 短视频热门演员
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
        $domain = new Domain_Shotvideo();
        $result = $domain->getHotPerformer($this->uid,$game_tenant_id, $p);
        $rs['info']  = $result;
        return $rs;
    }

    /**
     * 短视频热门视频
     * @desc 短视频热门视频
     * @return int code 操作码，0表示成功
     * @return array info

     */
    public  function getHotVideo(){
        $rs = array('code' => 0, 'msg' => '热门演员', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
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
        $url = $this->url;
        $domain = new Domain_Shotvideo();
        $result = $domain->getHotVideo($this->uid,$game_tenant_id, $p,$url);
        $rs['info']  = $result;
        return $rs;
    }
    /**
     * 短视频上传
     * @desc 短视频上传
     * @return int code 操作码，0表示成功
     * @return array info
     * @return int info.0.video_id 视频ID
     * @return string info.0.title 视频标题
    */
    public function  uploadNewVideo(){
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

        $domain = new Domain_Shotvideo();
        $label= $this->label;
        $title = $this->title;

        $uid = $this->uid;
        $desc = $this->desc;
        $region = $this->region;
        $years = $this->years;
        $result = $domain->uploadVideo($uid,$label,$title,$desc,$region,$years);

        $rs['code'] = $result['code'] ? $result['code'] : $rs['code'];
        $rs['msg'] = $result['msg'] ? $result['msg'] : $rs['msg'];
        $rs['info'] = $result['info'] ? $result['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 新增短视频
     * @desc 新增短视频
     * @return int code 操作码，0表示成功
     * @return array info
     * @return int info.0.video_id 视频ID
     * @return string info.0.title 视频标题
     * @return array info.0.upload_video_url 视频上传url
     */
    public function  addNewVideo(){
        $rs = array('code' => 0, 'msg' => '新增成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $uid = $this->uid;
        $title = trim($this->title);
        $classify = trim($this->classify);
        $desc = trim($this->desc);
        $years = trim($this->years);

        $domain = new Domain_Shotvideo();
        $result = $domain->addNewVideo($uid,$classify,$title,$desc,$years);

        $rs['code'] = $result['code'] ? $result['code'] : $rs['code'];
        $rs['msg'] = $result['msg'] ? $result['msg'] : $rs['msg'];
        $rs['info'] = $result['info'] ? $result['info'] : $rs['info'];
        return $rs;
    }




    /**
     * 新增短视频
     * @desc 新增短视频
     * @return int code 操作码，0表示成功
     * @return array info
     * @return int info.0.video_id 视频ID
     * @return string info.0.title 视频标题
     * @return array info.0.upload_video_url 视频上传url
     */
    public function  uploadVideo(){
        $rs = array('code' => 0, 'msg' => '新增成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $uid = $this->uid;
        $title = trim($this->title);
        $classify = trim($this->classify);
        $desc = trim($this->desc);
        $years = trim($this->years);

        $domain = new Domain_Shotvideo();
        $result = $domain->addNewVideo($uid,$classify,$title,$desc,$years);

        $rs['code'] = $result['code'] ? $result['code'] : $rs['code'];
        $rs['msg'] = $result['msg'] ? $result['msg'] : $rs['msg'];
        $rs['info'] = $result['info'] ? $result['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 上传视频到指定url
     * @desc 上传视频到指定url
     * @return int code 操作码，200表示成功
     * @return string msg 结果信息，success表示成功
     * @return object data
     * @return string data.fileStoreKey 视频key
     */
    public function  uploadVideoToUrl(){
        $rs = array('code' => 200, 'msg' => 'success', 'info' => array('fileStoreKey'=>''));
        return $rs;
    }

    /**
     * 短视频信息更新
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

        $domain = new Domain_Shotvideo();
        $result = $domain->updateVideoInfo($uid, $video_id, $file_store_key);

        $rs['code'] = $result['code'] ? $result['code'] : $rs['code'];
        $rs['msg'] = $result['msg'] ? $result['msg'] : $rs['msg'];
        $rs['info'] = $result['info'] ? $result['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 删除我的视频
     * @desc 删除我的视频
     * @return int code 操作码，0表示成功
     * @return array info

     */
    public  function delMyVideo(){
        $rs = array('code' => 0, 'msg' => '删除成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $game_tenant_id=$this->game_tenant_id;
        $type =  $this->type;
        if (empty($type)){
            $type = 1;
        }
        $checkToken = checkToken($this->uid, $this->token);

        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $videoid=$this->videoid;
        $domain = new Domain_Shotvideo();
        $result = $domain->delMyVideo($this->uid, $videoid,$type);
        if ($result == 102){
            $rs['code'] = 102;
            $rs['msg'] ='请选择自己的视频';
        }

        return $rs;
    }

    /**
     * 视频精选
     * @desc 视频精选 （bob独立app）
     * @return int code 操作码，0表示成功
     * @return array info

     */
    public  function  getconcentrationVideo(){
        $rs = array('code' => 0, 'msg' => '短视频精选', 'info' => array());
        $url = $this->url;
        $domain = new Domain_Shotvideo();
        $p=$this->p;
        $result = $domain->getconcentrationVideo($p,$url);
        $rs['info']  = $result;
        return $rs;
    }

    /**
     * 作者作品列表
     * @desc 作者作品列表
     * @return int code 操作码，0表示成功
     * @return array info

     */
    public  function getAuthurVideo(){
        $rs = array('code' => 0, 'msg' => '作者作品列表', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $uid = $this->uid;
        $liveuid = $this->liveuid;
        $url =  $this->url;

        $checkToken = checkToken($this->uid, $this->token);

        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $p=$this->p;
        $domain = new Domain_Shotvideo();
        $result = $domain->getAuthurVideo($uid,$liveuid, $p,$url);
        $rs['info'][0]  = $result;
        return $rs;
    }

    /**
     * 我的视频统计
     * @desc 我的视频统计（）
     * @return int code 操作码，0表示成功
     * @return array info.shotVideoRevieweCount  短视频待审核
     * @return array info.shotVideoPassCount  短视频上架
     * @return array info.shotVideoRejectCount  短视频不通过
     *@return array info.longVideoRevieweCount  长视频待审核
     * @return array info.longVideoPassCount  长视频上架
     * @return array info.longVideoRejectCount  长视频不通过
     */
    public  function getMyVideoStatistics(){
        $rs = array('code' => 0, 'msg' => '我的视频统计', 'info' => array());
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

        $domain = new Domain_Shotvideo();
        $result = $domain->getMyVideoStatistics($uid);
        $rs['info'][0]  = $result;
        return $rs;
    }
    /**
     * 购买短视频
     * @desc 购买短视频
     * @return int code 操作码，0表示成功
     * @return array info.isbuy  短视频购买成功

     */
    public  function buyShotvideo(){
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
        $domain = new Domain_Shotvideo();
        $result = $domain->buyShotvideo($uid,$videoid);
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
            $rs['code'] = 1005;
            $rs['msg'] = "金额不足";
            return $rs;
        }

        $rs['info'][0]=$result;

        $rs['code'] = isset($result['code']) ? $result['code'] : $rs['code'];
        $rs['msg'] = isset($result['msg']) ? $result['msg'] : $rs['msg'];
        $rs['info'] = isset($result['info']) ? $result['info'] : $rs['info'];

        return $rs;
    }

    /**
     * 我的购买短视频，长视频记录
     * @desc 我的购买短视频，长视频记录
     * @return int code 操作码，0表示成功
     * @return array info.isbuy  短视频购买成功

     */
    public  function buyHistory(){
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $p=$this->p;
        
        $uid = $this->uid;
        $checkToken = checkToken($this->uid, $this->token);

        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_Shotvideo();
        $result = $domain->buyHistory($uid,$p);
     
        $rs['info'][0]=$result;
        return $rs;
    }

    /**
     * 短视频绑定 商城或者店铺
     * @desc 我的购买短视频，长视频记录
     * @return int code 操作码，0表示成功
     * @return array info.isbuy  短视频购买成功

     */
    public  function bindShop(){
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $shoptype=$this->shoptype;
        $shop_value=$this->shop_value;
        $shop_url=$this->shop_url;
        $videoid=$this->videoid;
        $uid = $this->uid;
        $checkToken = checkToken($this->uid, $this->token);

        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_Shotvideo();
        $result = $domain->bindShop($shoptype,$shop_value,$videoid,$shop_url);

        $rs['code'] = isset($result['code']) ? $result['code'] : $rs['code'];
        $rs['msg'] = isset($result['msg']) ? $result['msg'] : $rs['msg'];
        $rs['info'] = isset($result['info']) ? $result['info'] : $rs['info'];
        return $rs;
    }

    // public  function getWatchVideoNum()
    // {
    //     $uid = $this->uid;
    //     $type = intval($this->type);
    //     if($type != 1 && $type != 2 && $type=0){
    //         $type = 0;
    //     }

    //     $checkToken = checkToken($this->uid, $this->token);
    //     $language_id = $_REQUEST['language_id'];
    //     if (empty($language_id)) {
    //         $language_id = 101;
    //     }
    //     if ($checkToken == 700) {
    //         $rs['code'] = $checkToken;
    //         $language = DI()->config->get('language.tokenerror');
    //         $rs['msg'] = $language[$language_id];
    //         return $rs;
    //     }
    //     $domain = new Domain_Shotvideo();
    //     $result = $domain->getWatchVideoNum($uid,$type);
    //     $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
    //     $rs['info'][]['count']= $result;
    //     return $rs;
    // }

    //定时任务，30秒执行一次，自动审核通过用户发布的短视频
    public function autoPass(){
        $domain = new Domain_Shotvideo();
        return $domain->autoPass();
    }

}
