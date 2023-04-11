<?php

use api\Common\CustRedis;

class Api_Live extends PhalApi_Api {

	public function getRules() {
		return array(
			'createRoom' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'user_nicename' => array('name' => 'user_nicename', 'type' => 'string', 'require' => true, 'desc' => '用户昵称 url编码'),
				'avatar' => array('name' => 'avatar', 'type' => 'string',  'require' => true, 'desc' => '用户头像 url编码'),
				'avatar_thumb' => array('name' => 'avatar_thumb', 'type' => 'string',  'require' => true, 'desc' => '用户小头像 url编码'),
				'title' => array('name' => 'title', 'type' => 'string','default'=>'', 'desc' => '直播标题 url编码'),
				'province' => array('name' => 'province', 'type' => 'string', 'default'=>'', 'desc' => '省份'),
				'city' => array('name' => 'city', 'type' => 'string', 'default'=>'', 'desc' => '城市'),
				'lng' => array('name' => 'lng', 'type' => 'string', 'default'=>'0', 'desc' => '经度值'),
				'lat' => array('name' => 'lat', 'type' => 'string', 'default'=>'0', 'desc' => '纬度值'),
				'type' => array('name' => 'type', 'type' => 'int', 'default'=>'0', 'desc' => '直播类型，0是一般直播，1是私密直播，2是收费直播，3是计时直播'),
				'type_val' => array('name' => 'type_val', 'type' => 'string', 'default'=>'', 'desc' => '类型值'),
				'anyway' => array('name' => 'anyway', 'type' => 'int', 'default'=>'0', 'desc' => '直播类型 1 PC, 0 app'),
				'liveclassid' => array('name' => 'liveclassid', 'type' => 'int', 'default'=>'0', 'desc' => '直播分类ID'),
                'tryWatchTime' => array('name' => 'tryWatchTime', 'type' => 'int',  'desc' => '试看时长(单位秒)'),
                'pushpull_id' => array('name' => 'pushpull_id', 'type' => 'int',  'desc' => '推拉流线路id'),
                'thumb' => array('name' => 'thumb', 'type' => 'string',  'desc' => '开播封面图'),
			),
			'changeLive' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
				'status' => array('name' => 'status', 'type' => 'int', 'require' => true, 'desc' => '直播状态 0关闭 1直播'),
			),
			'changeLiveType' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
				'type' => array('name' => 'type', 'type' => 'int', 'default'=>'0', 'desc' => '直播类型，0是一般直播，1是私密直播，2是收费直播，3是计时直播'),
				'type_val' => array('name' => 'type_val', 'type' => 'string', 'default'=>'', 'desc' => '类型值'),
			),
			'stopRoom' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
                'acttype' => array('name' => 'acttype', 'type' => 'string', 'default'=>'', 'desc' => '是通知的closelive，就传: amdin_stop，非通知的closelive的可以传空，或者不传'),
                'is_forbidden' => array('name' => 'is_forbidden', 'type' => 'int','default'=>0, 'desc' => '关播类型 0表示关闭当前直播 1表示超管关闭当前直播并禁用账号'),
            ),
			
			'stopInfo' => array(
				'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
			),
			
			'checkLive' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
				'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
			),
			
			'roomCharge' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
				'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
			),
			'timeCharge' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
				'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
			),
			
			'enterRoom' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
				'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
				'city' => array('name' => 'city', 'type' => 'string','default'=>'', 'desc' => '城市'),
                'version' => array('name' => 'version', 'type' => 'string','default'=>'', 'desc' => 'App版本'),
                'client' => array('name' => 'client', 'type' => 'int', 'default'=>'','desc' => '客户端：1 PC，2 H5，3 Android，4 iOS'),
			),
            'enterRoomvutar' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
                'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
                'city' => array('name' => 'city', 'type' => 'string','default'=>'', 'desc' => '城市'),
            ),
            'leaveRoom' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
                'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
                'watchtime' =>array('name' => 'watchtime', 'type' => 'int', 'min' => 0, 'require' => true, 'desc' => '观看时间，单位：秒'),
            ),
			'showVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '上麦会员ID'),
                'pull_url' => array('name' => 'pull_url', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '连麦用户播流地址'),
            ),
			
			'getZombie' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'stream' => array('name' => 'stream', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '流名'),
            ),

			'getUserLists' => array(
				'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
				'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
				'p' => array('name' => 'p', 'type' => 'int','require' => true,  'min' => 1,'desc' => '页数'),
			),
            'getUsergathers' => array(
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
                'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
                'p' => array('name' => 'p', 'type' => 'int','require' => true,  'min' => 1,'desc' => '页数'),
            ),
			
			'getPop' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
				'touid' => array('name' => 'touid', 'type' => 'int',  'require' => true, 'desc' => '对方ID'),
                'stream' => array('name' => 'stream', 'type' => 'string',  'desc' => '流名'),
			),
			
			'getGiftList' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
			),
			
			'sendGift' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
				'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
				'giftid' => array('name' => 'giftid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '礼物ID'),
				'giftcount' => array('name' => 'giftcount', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '礼物数量'),
                'send_type' => array('name' => 'send_type', 'type' => 'int', 'min' => 0,  'desc' => '送礼类型'),
			),
            'sendGiftvatul' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
                'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
                'giftid' => array('name' => 'giftid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '礼物ID'),
                'giftcount' => array('name' => 'giftcount', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '礼物数量'),
            ),

			
			'sendBarrage' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
				'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
				'content' => array('name' => 'content', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '弹幕内容'),
			),
			
			'setAdmin' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
				'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
			),
			
			'getAdminList' => array(
				'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
			),
			
			'setReport' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
				'content' => array('name' => 'content', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '举报内容'),
			),
			
			'getVotes' => array(
				'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
			),
			
			'setShutUp' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '用户token'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '禁言用户ID'),
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
                'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '直播间stream'),
            ),

            'setShutUpall' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '用户token'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1,  'desc' => '禁言用户ID'),
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
                'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '直播间stream'),

            ),
            'setShutUpcancel' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '用户token'),
                'touid' => array('name' => 'touid', 'type' => 'int',   'desc' => '解除禁言用户ID,如果该值为空，全部解除'),
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
                'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '直播间stream'),
            ),

			
			'kicking' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
				'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
                'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '直播间stream'),
            ),
			
			'superStopRoom' => array(
            	'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '会员ID'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'desc' => '会员token'),
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
				'type' => array('name' => 'type', 'type' => 'int','default'=>0, 'desc' => '关播类型 0表示关闭当前直播 1表示关闭当前直播并禁用账号'),
            ),
			'searchMusic' => array(
				'key' => array('name' => 'key', 'type' => 'string','require' => true,'desc' => '关键词'),
				'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1,'desc' => '页数'),
            ),
			
			'getDownurl' => array(
				'audio_id' => array('name' => 'audio_id', 'type' => 'int','require' => true,'desc' => '歌曲ID'),
            ),
			
			'getCoin' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '会员ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'min' => 1, 'desc' => '会员token'),
            ),
            'getLivePower' => array(
            ),
            'updateLivestatus' => array(
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
                'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
                'old_status' => array('name' => 'old_status', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '修改前的直播状态'),
                'status' => array('name' => 'status', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '修改后的直播状态'),
            ),
            'confirmLivestatus' => array(
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
                'stream' => array('name' => 'stream', 'type' => 'string',  'desc' => '流名'),
                'version' => array('name' => 'version', 'type' => 'string',  'desc' => '版本号'),
            ),
            'getBetinfo' => array(
                'tenant_id' => array('name' => 'tenant_id', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '租户ID'),

            ),
            'autoUpdatehot' => array(
                'token' => array('name' => 'token', 'type' => 'string', 'min' => 1, 'desc' => '会员token'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'desc' => '会员ID'),
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
            ),
            'autoUpdatevotes' => array(

            ),
            'keywordcheck' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '会员ID'),
                'content' => array('name' => 'content', 'type' => 'string', 'require' => true, 'desc' => '发言内容'),
            ),
            'addAnchornamecard' => array(
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'min' => 1, 'desc' => '会员token'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '会员ID'),
                'declaration' => array('name' => 'declaration', 'type' => 'string', 'require' => true, 'desc' => '宣言内容'),
                'position' => array('name' => 'position', 'type' => 'string', 'require' => true, 'desc' => '主播位置'),
                'limit_price' => array('name' => 'limit_price', 'type' => 'string', 'require' => true, 'desc' => '门槛的钻石金额'),
                'telephone' => array('name' => 'telephone', 'type' => 'string', 'require' => true, 'desc' => '联系方式'),
                'is_open' => array('name' => 'is_open', 'type' => 'int','require' => true,'default'=> 1, 'desc' => '1 开启，2 关闭'),
                'type' => array('name' => 'type', 'type' => 'int','require' => true,'default'=> 1, 'desc' => '联系方式类型'),
            ),
            'getAnchornamecard' => array(
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'min' => 1, 'desc' => '会员token'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '会员ID'),
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
            ),
            'getCarList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'getUserCarList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'BuyCar' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'carid' => array('name' => 'carid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '坐骑ID'),
            ),
            'RideCar' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'id' => array('name' => 'id', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户坐骑ID'),
            ),
            'getRoomtype' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),

            ),
            'getLiveGameInfo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
            ),
            'getEnterroomNotice' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
            ),
            'getNobleList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'buyNoble' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 0, 'require' => true, 'desc' => '主播ID（在直播间开通传主播id，在我的界面开通传0）'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'level' => array('name' => 'level', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '等级'),
                'type' => array('name' => 'type', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '类型：1.购买，2.续费'),
            ),
            'getNobleSetting' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'ExeculationLivestatus' => array(
                'language_id' => array('name' => 'language_id', 'type' => 'int', 'min' => 1, 'desc' => '语言id'),
                'game_tenant_id' => array('name' => 'game_tenant_id', 'type' => 'int', 'min' => 1, 'desc' => '游戏租户id'),
            ),
            'LiveTimeOut' => array(
                'language_id' => array('name' => 'language_id', 'type' => 'int', 'min' => 1, 'desc' => '语言id'),
                'game_tenant_id' => array('name' => 'game_tenant_id', 'type' => 'int', 'min' => 1, 'desc' => '游戏租户id'),
            ),
            'updateLivetype' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'stream' => array('name' => 'stream', 'type' => 'string', 'require' => true, 'desc' => '流名'),
                'type' => array('name' => 'type', 'type' => 'int', 'default'=>'0', 'desc' => '直播类型，0是一般直播，1是私密直播，2是收费直播，3是计时直播'),
                'type_val' => array('name' => 'type_val', 'type' => 'string', 'default'=>'', 'desc' => '类型值'),
                'tryWatchTime' => array('name' => 'tryWatchTime', 'type' => 'int',  'default'=>'0', 'desc' => '试看时长(单位秒)'),
            ),

            'getGiftall' => array(
            ),
		);
	}

     /**
	 * 创建开播
	 * @desc 用于用户开播生成记录
	 * @return int code 操作码，0表示成功
	 * @return array info
	 * @return string info[0].userlist_time 用户列表请求间隔
	 * @return string info[0].barrage_fee 弹幕价格
	 * @return string info[0].votestotal 主播映票
	 * @return string info[0].stream 流名
	 * @return string info[0].push 推流地址
	 * @return string info[0].pull 播流地址
	 * @return string info[0].chatserver socket地址
	 * @return array info[0].game_switch 游戏开关
	 * @return string info[0].game_switch[][0] 开启的游戏类型 
	 * @return string info[0].game_bankerid 庄家ID
	 * @return string info[0].game_banker_name 庄家昵称
	 * @return string info[0].game_banker_avatar 庄家头像 
	 * @return string info[0].game_banker_coin 庄家余额 
	 * @return string info[0].game_banker_limit 上庄限额 
	 * @return string info[0].shut_time 禁言时间 
	 * @return string info[0].kick_time 踢人时间 
	 * @return object info[0].liang 用户靓号信息
	 * @return string info[0].liang.name 号码，0表示无靓号
	 * @return object info[0].vip 用户VIP信息
	 * @return string info[0].vip.type VIP类型，0表示无VIP，1表示有VIP
	 * @return string info[0].guard_nums 守护数量
     * @return string info[0].show_game_entry 是否展示游戏入口: 1 展示 0 不展示
	 * @return string msg 提示信息
	 */
	public function createRoom() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		$uid = $this->uid;
		$token=checkNull($this->token);
        $pushpull_id = $this->pushpull_id;
        $tenantId = getTenantId();

        $redis = connectionRedis();

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		$configpub=getConfigPub();
		if($configpub['maintain_switch']==1){
			$rs['code']=1002;
			$rs['msg']=$configpub['maintain_tips'];
			return $rs;

		}
        $checkToken=checkToken($uid,$token);
		if($checkToken==700){
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		}
		$isban = isBan($uid);
		if(!$isban){
            $rs['code'] = 1001;
            $language = DI()->config->get('language.userlogin_forbidden');
            $rs['msg'] = $language[$language_id];
            return $rs;
		}

		$configpri=getConfigPri();
        $auth_info = isAuth($uid);
		if($configpri['auth_islimit']==1){
			if(!$auth_info){
                $rs['code'] = 1002;
                $language = DI()->config->get('language.validate_befor');
                $rs['msg'] = $language[$language_id];
                return $rs;
			}
		}
		$userinfo=getUserInfo($uid);
		if ($userinfo['user_type'] == 4){
            $rs['code']=2028;
            $rs['msg']='游客不能直播';
            return $rs;
        }
		if($configpri['level_islimit']==1){
			if( $userinfo['level'] < $configpri['level_limit'] ){
				$rs['code']=1003;
				$rs['msg']='等级小于'.$configpri['level_limit'].'级，不能直播';
				return $rs;
			}
		}

		$nowtime=time();

		$user_nicename=checkNull($this->user_nicename);
		$avatar=checkNull($this->avatar);
		$avatar_thumb=checkNull($this->avatar_thumb);
		$showid=$nowtime;
		$starttime=$nowtime;
		$title=checkNull($this->title);
		$province=checkNull($this->province);
		$city=checkNull($this->city);
		$lng=checkNull($this->lng);
		$lat=checkNull($this->lat);
		$type=checkNull($this->type);
		$type_val=checkNull($this->type_val);
		$anyway=checkNull($this->anyway);
		$liveclassid=checkNull($this->liveclassid);
        $tryWatchTime = $configpri['trywatchtime'];

		if( $type==1 && $type_val=='' ){
			$rs['code']=1002;
			$rs['msg']='密码不能为空';
			return $rs;
		}else if($type > 1 && $type_val<=0){
			$rs['code']=1002;
			$rs['msg']='价格不能小于等于0';
			return $rs;
		}

        // 门票房间限额,前端及后台设置门票房间时，门票限额
        if($type == 2 && ($type_val < $configpri['tickets_limit_min'] || $type_val > $configpri['tickets_limit_max'])){
            $rs['code']=2079;
            $rs['msg']=codemsg('2079');
            $rs['info']=[$configpri['tickets_limit_min'].' - '.$configpri['tickets_limit_max']];
            return $rs;
        }

		$stream=$uid.'_'.$nowtime;
        $wy_cid='';

        $ct_type = isset($auth_info['ct_type']) ? $auth_info['ct_type'] : 1;  // 直播线路类型：1.默认，2.黄播,3.绿播,4.赌播
        $PushpullModel = new Model_Pushpull();
        $PushpullInfo = $PushpullModel->getPushpullInfoWithId($pushpull_id);
        $PushpullInfo = empty($PushpullInfo) ? $PushpullModel->getPushpullInfo($ct_type) : $PushpullInfo;
        $push = $PushpullModel->PrivateKeyA('rtmp',$stream,1,$PushpullInfo);
        $pull = $PushpullModel->PrivateKeyA('rtmp',$stream,0,$PushpullInfo);
        $flvpull = $PushpullModel->PrivateKeyA('http',$stream.'.flv',0,$PushpullInfo);
        $m3u8pull = $PushpullModel->PrivateKeyA('http',$stream.'.m3u8',0,$PushpullInfo);
        $auth_token = '';
        if(isset($PushpullInfo['code']) && $PushpullInfo['code'] == 10){ // 10 声网推拉流服务商，获取鉴权token给前端
            $auth_token = $PushpullModel->ws_auth_token($PushpullInfo,$uid,$stream);
        }

		if(!$city){
			$city='好像在火星';
		}
		if(!$lng && $lng!=0){
			$lng='';
		}
		if(!$lat && $lat!=0){
			$lat='';
		}

        //开播封面两个都传，以file为准，单独传哪个，就以哪个为准
		$thumb=$this->thumb;
		if($_FILES){
			if ($_FILES["file"]["error"] > 0) {
				$rs['code'] = 1003;
				$rs['msg'] = T('failed to upload file with error: {error}', array('error' => $_FILES['file']['error']));
				DI()->logger->debug('failed to upload file with error: ' . $_FILES['file']['error']);
				return $rs;
			}

			if(!checkExt($_FILES["file"]['name'])){
				$rs['code']=1004;
				$rs['msg']='图片仅能上传 jpg,png,jpeg';
				return $rs;
			}

            $result = upload_file($_FILES['file']);
            $thumb = $result['url'];
		}


		if(empty($thumb) || $thumb == 'false' ){
            $thumb = $avatar_thumb;
        }

		/* 主播靓号 */
		$liang=getUserLiang($uid);
		$goodnum=0;
		if($liang['name']!=0){
			$goodnum=$liang['name'];
		}
		$info['liang']=$liang;


		/* 主播VIP */
		$vip=getUserVip($uid);
		$info['vip']=$vip;

		$dataroom=array(
			"uid"=>$uid,
			"user_nicename"=>$user_nicename,
			"avatar"=>$avatar,
			"avatar_thumb"=>$avatar_thumb,
			"showid"=>$showid,
			"starttime"=>$starttime,
			"title"=>$title,
			"province"=>$province,
			"city"=>$city,
			"stream"=>$stream,
			"thumb"=>$thumb,
            "pushpull_type" => intval($ct_type),
            "pushpull_id"=>(isset($PushpullInfo) && isset($PushpullInfo['id']) ? $PushpullInfo['id'] : 0),
            "push"=>$push,
			"pull"=>$pull,
			'flvpull'=>$flvpull,
            'm3u8pull'=>$m3u8pull,
			"lng"=>$lng,
			"lat"=>$lat,
			"type"=>$type,
			"type_val"=>$type_val,
			"goodnum"=>$goodnum,
			"isvideo"=>0,
			"islive"=>1,
            "wy_cid"=>$wy_cid,
			"anyway"=>$anyway,
			"liveclassid"=>$liveclassid,
			"hotvotes"=>0,
			"pkuid"=>0,
			"pkstream"=>'',
            "tenant_id"=>$userinfo['tenant_id'],
            "game_user_id"=>$userinfo['game_user_id'] ? $userinfo['game_user_id'] : 0,
            "isshare"=>$userinfo['isshare'],
            'tryWatchTime'=>isset($tryWatchTime) ? $tryWatchTime : 0 ,
            'isrecommend' => 1, // 主播开播以后自动上推荐
		);

		$domain = new Domain_Live();
		$result = $domain->createRoom($uid,$dataroom);
        if(isset($result['code'])){
            $rs['code'] = $result['code'];
            $rs['msg'] = $result['msg'];
            $rs['info'] = $result['info'];
            return $rs;
        }
		if($result===false){
			$rs['code'] = 1011;
			$rs['msg'] = '开播失败，请重试';
			return $rs;
		}



		$data=array('city'=>$city);
		$domain2 = new Domain_User();
		$info2 = $domain2->userUpdate($uid,$data);

		$userinfo['city']=$city;
		$userinfo['usertype']=50;
		$userinfo['sign']='0';

		DI()->redis  -> set($token,json_encode($userinfo));

		$votestotal=$domain->getVotes($uid);

        // 判断是否使用golang的socket
        $chatserver = ($configpri['socket_type'] == 3 && $configpri['go_socket_url']) ? $configpri['go_socket_url'] : $configpri['chatserver'];

		$info['userlist_time']=$configpri['userlist_time'];
		$info['barrage_fee']=$configpri['barrage_fee'];
		$info['chatserver']=$chatserver;

		$info['shut_time']=$configpri['shut_time'].'秒';
		$info['kick_time']=$configpri['kick_time'].'秒';
		$info['votestotal']=$votestotal;
		$info['stream']=$stream;
		$info['push']=$push;
		$info['pull']=$pull;
        $info['flvpull']=$flvpull;
        $info['m3u8pull']=$m3u8pull;
        $info['auth_token']=$auth_token;
        $info['appid'] = isset($PushpullInfo) && isset($PushpullInfo['appid']) ? $PushpullInfo['appid'] : '';

		/* 游戏配置信息 */
		$info['game_switch']=$configpri['game_switch'];
		$info['game_bankerid']='0';
		$info['game_banker_name']='吕布';
		$info['game_banker_avatar']='';
		$info['game_banker_coin']=NumberFormat(10000000);
		$info['game_banker_limit']=$configpri['game_banker_limit'];
		/* 游戏配置信息 */


        /* 守护数量 */
        $domain_guard = new Domain_Guard();
		$guard_nums = $domain_guard->getGuardNums($uid);
        $info['guard_nums']=$guard_nums;

        $redis->zRem('disconnect_'.$tenantId,$uid);
        $redis->hSet('live_api_heart_time',$uid,time());

        $PushpullModel->addUsenum($PushpullInfo['id']);
        $cache_key = 'pushpull_usenum_'.date('Ymd').$PushpullInfo['id'];
        $redis->incrBy($cache_key,1);
        $redis->expire($cache_key,60*60*24);

		$rs['info'][0] = $info;

		return $rs;
	}


	/**
	 * 修改直播状态
	 * @desc 用于主播修改直播状态
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].msg 成功提示信息
	 * @return string msg 提示信息
	 */
	public function changeLive() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid = $this->uid;
		$token=checkNull($this->token);
		$stream=checkNull($this->stream);
		$status=$this->status;

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		$checkToken=checkToken($uid,$token);
		if($checkToken==700){
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		}
		
		$domain = new Domain_Live();
		$info=$domain->changeLive($uid,$stream,$status);
        
        $configpri=getConfigPri();
        /* 极光推送 */
		$app_key = $configpri['jpush_key'];
		$master_secret = $configpri['jpush_secret'];

		if($app_key && $master_secret && $status==1 && $info){
			require API_ROOT.'/public/JPush/autoload.php';
			// 初始化
			$client = new \JPush\Client($app_key, $master_secret,null);
            
            $userinfo=getUserInfo($uid);
			
			$anthorinfo=array(
				"uid"=>$info['uid'],
				"avatar"=>$info['avatar'],
				"avatar_thumb"=>$info['avatar_thumb'],
				"user_nicename"=>$info['user_nicename'],
				"title"=>$info['title'],
				"city"=>$info['city'],
				"stream"=>$info['stream'],
				"pull"=>$info['pull'],
				"thumb"=>$info['thumb'],
				"isvideo"=>'0',
				"type"=>$info['type'],
				"type_val"=>$info['type_val'],
				"game_action"=>'0',
				"goodnum"=>$info['goodnum'],
				"anyway"=>$info['anyway'],
				"nums"=>0,
				"level_anchor"=>$userinfo['level_anchor'],
				"game"=>'',

			);
			$title='你的好友：'.$anthorinfo['user_nicename'].'正在直播，邀请你一起';
			$apns_production=false;
			if($configpri['jpush_sandbox']){
				$apns_production=true;
			}
            
            $pushids = $domain->getFansIds($uid); 
			$nums=count($pushids);	
			for($i=0;$i<$nums;){
                $alias=array_slice($pushids,$i,900);
                $i+=900;
				try{	
					$result = $client->push()
							->setPlatform('all')
							->addRegistrationId($alias)
							->setNotificationAlert($title)
							->iosNotification($title, array(
								'sound' => 'sound.caf',
								'category' => 'jiguang',
								'extras' => array(
									'type' => '1',
									'userinfo' => $anthorinfo
								),
							))
							->androidNotification('', array(
								'extras' => array(
									'title' => $title,
									'type' => '1',
									'userinfo' => $anthorinfo
								),
							))
							->options(array(
								'sendno' => 100,
								'time_to_live' => 0,
								'apns_production' =>  $apns_production,
							))
							->send();
				} catch (Exception $e) {   
					file_put_contents('./jpush.txt',date('y-m-d h:i:s').'提交参数信息 设备名:'.json_encode($alias)."\r\n",FILE_APPEND);
					file_put_contents('./jpush.txt',date('y-m-d h:i:s').'提交参数信息:'.$e."\r\n",FILE_APPEND);
				}					
			}			
		}
		/* 极光推送 */

		$rs['info'][0]['msg']='成功';
		return $rs;
	}	

	/**
	 * 修改直播类型
	 * @desc 用于主播修改直播类型
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].msg 成功提示信息
	 * @return string msg 提示信息
	 */
	public function changeLiveType() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid = $this->uid;
		$token=checkNull($this->token);
		$stream=checkNull($this->stream);

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		$checkToken=checkToken($uid,$token);
		if($checkToken==700){
		    $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		}
		
		$type=checkNull($this->type);
		$type_val=checkNull($this->type_val);
		
		if( $type==1 && $type_val=='' ){
			$rs['code']=1002;
			$rs['msg']='密码不能为空';
			return $rs;
		}else if($type > 1 && $type_val<=0){
			$rs['code']=1002;
			$rs['msg']='价格不能小于等于0';
			return $rs;
		}
		
		
		$data=array(
			"type"=>$type,
			"type_val"=>$type_val,
		);
		
		$domain = new Domain_Live();
		$info=$domain->changeLiveType($uid,$stream,$data);

		$rs['info'][0]['msg']='成功';
		return $rs;
	}	
	
	/**
	 * 关闭直播
	 * @desc 用于用户结束直播
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].msg 成功提示信息
	 * @return string msg 提示信息
	 */
	public function stopRoom() { 
		$rs = array('code' => 0, 'msg' => '', 'info' => array());

		$uid = $this->uid;
		$token=checkNull($this->token);
		$stream=checkNull($this->stream);
        $acttype = $this->acttype ? $this->acttype : '';
        $is_forbidden = $this->is_forbidden;
        $tenantId = getTenantId();
        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		$key='stopRoom_'.$stream;
        $info = getcaches($key);
		if(!$info){
			$checkToken=checkToken($uid,$token);
			if($checkToken==700){
			    $rs['code'] = $checkToken;
                $language = DI()->config->get('language.tokenerror');
                $rs['msg'] = $language[$language_id];
                return $rs;
			}
			$domain = new Domain_Live();
			$info=$domain->stopRoom($uid,$stream,true,$acttype,$is_forbidden);
			if(isset($info['stop_end']) && $info['stop_end'] == 1){
                setcaches($key, $info, 60*60*24);
            }
		}
        if(isset($info['code']) && isset($info['msg']) && isset($info['info'])){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
            $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
            return $rs;
        }

        delPatternCacheKeys($tenantId.'_'."getHot_"."*");
		
		$rs['info'][0]['msg']='关播成功';
        $rs['info'][0]['data'] = empty($info) ? (object)[] : $info;
		return $rs;
	}	
	
	/**
	 * 直播结束信息
	 * @desc 用于直播结束页面信息展示
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].nums 人数
	 * @return string info[0].length 时长
	 * @return string info[0].votes 映票数
	 * @return string msg 提示信息
	 */
	public function stopInfo() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$stream=checkNull($this->stream);
		
		$domain = new Domain_Live();
		$info=$domain->stopInfo($stream);

		$rs['info'][0]=$info;
		return $rs;
	}		
	
	/**
	 * 检查直播
	 * @desc 用于用户进房间时检查直播
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].type 房间类型	
	 * @return string info[0].type_val 收费房间价格，默认0	
	 * @return string info[0].type_msg 提示信息
     * @return string info[0].left_watchtime 剩余试看时间
	 * @return string msg 提示信息
	 */
	public function checkLive() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=$this->uid;
		$token=checkNull($this->token);
		$liveuid=$this->liveuid;
		$stream=checkNull($this->stream);
        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }

		$configpub=getConfigPub();
		if($configpub['maintain_switch']==1){
			$rs['code']=1002;
			$rs['msg']=$configpub['maintain_tips'];
			return $rs;

		}
        
        $checkToken=checkToken($uid,$token);
		if($checkToken==700){
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		}
        
        
		$isban = isBan($uid);
		if(!$isban){
			$rs['code']=1001;
			$rs['msg']='该账号已被禁用';
			return $rs;
		}

		

		$iskick=DI()->redis  -> hGet($stream.'kick',$uid);
		$nowtime=time();
		if($iskick>$nowtime){
			$surplus=$iskick-$nowtime;
			$rs['code']=1004;
            $language1 = DI()->config->get('language.tiren');
            $language2 = DI()->config->get('language.second');
            $rs['msg'] = $language1[$language_id].$surplus.$language2[$language_id];
			//$rs['msg']='您已被踢出房间，剩余'.$surplus.'秒';
		}else{
			DI()->redis  -> hdel($liveuid.'kick',$uid);
		}
		
		
		$domain = new Domain_Live();
		$info=$domain->checkLive($uid,$liveuid,$stream,$language_id);


		if($info==1005){
			$rs['code'] = 1005;
			$rs['msg'] = '直播已结束';
			return $rs;
		}else if($info==1007){
            $rs['code'] = 1007;
			$rs['msg'] = '超管不能进入1v1房间';
			return $rs;
        }/*else if($info==2038) {
            $rs['code'] = 2038;
            $rs['msg'] = '直播已关闭';
            return $rs;
        }*/
		$rs['info'][0]=$info;
		
		
		return $rs;
	}
	
	/**
	 * 房间扣费
	 * @desc 用于房间扣费
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].coin 用户余额
	 * @return string msg 提示信息
	 */
	public function roomCharge() { 
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=$this->uid;
		$token=checkNull($this->token);
		$liveuid=$this->liveuid;
		$stream=checkNull($this->stream);
		$game_tenant_id=$this->game_tenant_id;

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		
		$domain = new Domain_Live();
        $whichTenant= whichTenat($game_tenant_id);
       //var_dump($whichTenant);exit;
        if($whichTenant==1){
            $info=$domain->roomCharge($uid,$token,$liveuid,$stream);  //彩票租户

        }else{
            $info=$domain->roomChargealone($uid,$token,$liveuid,$stream);//独立租户
        }

		
		if($info==700){
            $rs['code'] = 700;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		}else if($info==1005){
			$rs['code'] = 1005;
			$rs['msg'] = '直播已结束';
			return $rs;
		}else if($info==1006){
			$rs['code'] = 1006;
			$rs['msg'] = '该房间非扣费房间';
			return $rs;
		}else if($info==1007){
			$rs['code'] = 1007;
			$rs['msg'] = '房间费用有误';
			return $rs;
		}else if($info==1008){
            $language = DI()->config->get('language.balance_error');
            $rs['code'] = 1008;
            $rs['msg'] = $language[$language_id];//余额不足
            return $rs;
		}
		$rs['info'][0]['coin']=$info['coin'];
        $rs['info'][0]['level']=$info['level'];

		return $rs;
	}	

	/**
	 * 房间计时扣费
	 * @desc 用于房间计时扣费
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].coin 用户余额
	 * @return string msg 提示信息
	 */
	public function timeCharge() { 
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=$this->uid;
		$token=checkNull($this->token);
		$liveuid=$this->liveuid;
		$stream=checkNull($this->stream);
		$game_tenant_id=$this->game_tenant_id;
		$language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		$domain = new Domain_Live();

		$key='timeCharge_'.$stream.'_'.$uid;
		$cache=getcaches($key);
		if($cache){
			$coin=$domain->getUserCoin($uid);
			$rs['info'][0]['coin']=$coin['coin'];
			return $rs;
		}

        $whichTenant= whichTenat($game_tenant_id);
        if($whichTenant==1){
            $info=$domain->roomCharge($uid,$token,$liveuid,$stream);  //彩票租户

        }else{
            $info=$domain->roomChargealone($uid,$token,$liveuid,$stream);//独立租户
        }
		if($info==700){
		    $rs['code'] = 700;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		}else if($info==1005){
			$rs['code'] = 1005;
			$rs['msg'] = '直播已结束';
			return $rs;
		}else if($info==1006){
			$rs['code'] = 1006;
			$rs['msg'] = '该房间非扣费房间';
			return $rs;
		}else if($info==1007){
			$rs['code'] = 1007;
			$rs['msg'] = '房间费用有误';
			return $rs;
		}else if($info==1008){
			$rs['code'] = 1008;
			$rs['msg'] = '余额不足';
			return $rs;
		}
		$rs['info'][0]['coin']=$info['coin'];
		
		setcaches($key,1,50); 
	
		return $rs;
	}		
	

	/**
	 * 进入直播间
	 * @desc 用于用户进入直播
	 * @return int code 操作码，0表示成功
     * @return string msg 提示信息
	 * @return array info 
	 * @return string info[0].votestotal 直播映票
	 * @return string info[0].barrage_fee 弹幕价格
	 * @return string info[0].userlist_time 用户列表获取间隔
	 * @return string info[0].chatserver socket地址
	 * @return string info[0].isattention 是否关注主播，0表示未关注，1表示已关注
	 * @return string info[0].nums 房间人数
	 * @return string info[0].push_url 推流地址
	 * @return string info[0].pull_url 播流地址
	 * @return string info[0].linkmic_uid 连麦用户ID，0表示未连麦
	 * @return string info[0].linkmic_pull 连麦播流地址
	 * @return array info[0].userlists 用户列表
	 * @return array info[0].game 押注信息
	 * @return array info[0].gamebet 当前用户押注信息
	 * @return string info[0].gametime 游戏剩余时间
	 * @return string info[0].gameid 游戏记录ID
	 * @return string info[0].gameaction 游戏类型，1表示炸金花，2表示牛牛，3表示转盘
	 * @return string info[0].game_bankerid 庄家ID
	 * @return string info[0].game_banker_name 庄家昵称
	 * @return string info[0].game_banker_avatar 庄家头像 
	 * @return string info[0].game_banker_coin 庄家余额 
	 * @return string info[0].game_banker_limit 上庄限额 
	 * @return object info[0].vip 用户VIP信息
	 * @return string info[0].vip.type VIP类型，0表示无VIP，1表示普通VIP，2表示至尊VIP
	 * @return object info[0].liang 用户靓号信息
	 * @return string info[0].liang.name 号码，0表示无靓号
	 * @return string info[0].shut_time 禁言时间 
	 * @return string info[0].kick_time 踢人时间
     * @return object info[0].guard 守护信息
	 * @return string info[0].guard.type 守护类型，0表示非守护，1表示月守护，2表示年守护
	 * @return string info[0].guard.endtime 到期时间
	 * @return string info[0].guard_nums 主播守护数量
     * @return object info[0].pkinfo 主播连麦/PK信息
	 * @return string info[0].pkinfo.pkuid 连麦用户ID
	 * @return string info[0].pkinfo.pkpull 连麦用户播流地址
	 * @return string info[0].pkinfo.ifpk 是否PK
	 * @return string info[0].pkinfo.pk_time 剩余PK时间（秒）
	 * @return string info[0].pkinfo.pk_gift_liveuid 主播PK总额
	 * @return string info[0].pkinfo.pk_gift_pkuid 连麦主播PK总额
	 * @return string info[0].isred 是否显示红包
     * @return string info[0].car 用户坐骑
     * @return string info[0].car.id 坐骑ID
     * @return string info[0].car.swf 动画链接
     * @return string info[0].car."swftime 动画时长
     * @return string info[0].car.words 进场话术
     * @return string info[0].left_watchtime 还剩多少观看时间
     * @return string info[0].football_live_match_id 	足球视频直播比赛ID
     * @return string info[0].pull rtmp播流地址
     * @return string info[0].flvpull flvpull播流地址
     * @return string info[0].m3u8pull m3u8pull播流地址
     *
	 */
	public function enterRoom() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
        $redis = connectionRedis();

		$uid=$this->uid;
		$token=checkNull($this->token);
		$liveuid=$this->liveuid;
		$city=checkNull($this->city);
		$stream=checkNull($this->stream);
        $version = checkNull($this->version);
        $client = $this->client;
		$tenant_id = getTenantId();

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
        $checkToken=checkToken($uid,$token);
		if($checkToken==700){
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		}

		// 更新用户客户端版本
        $domain = new Domain_Home();
        $result = $domain->updateVersion($version, $client, $uid);

        $configpri=getConfigPri();

		$isban = isBan($uid);
		if(!$isban){
			$rs['code']=1001;
			$rs['msg']='该账号已被禁用';
			return $rs;
		}

		$user_live_info = getUserLiveInfo($tenant_id, $liveuid);
		
		$domain = new Domain_Live();
		$userinfo=getUserInfo($uid);
		
		$carinfo=getUserCar($uid);
		$userinfo['car']=$carinfo;
		$issuper='0';
		if (isset($userinfo['issuper'])){
            if($userinfo['issuper']==1){
                $issuper='1';
                DI()->redis  -> hset('super',$userinfo['id'],'1');
            }else{
                DI()->redis  -> hDel('super',$userinfo['id']);
            }
        }
		if(!$city){
			$city='好像在火星';
		}
		
		$data=array('city'=>$city);
		$domain2 = new Domain_User();
		$info = $domain2->userUpdate($uid,$data);
		$userinfo['city']=$city;

		$usertype = isAdmin($uid,$liveuid);
		$userinfo['usertype'] = $usertype;
        
        $stream2=explode('_',$stream);
		$showid=$stream2[1];
        
        $contribution='0';
        if($showid){
            $contribution=$domain->getContribut($uid,$liveuid,$showid);
            $resinfo = $domain->confirmLive($liveuid);
            if ($resinfo==2038 || $resinfo['islive'] == 0){
                $rs['code']=2038;
                $rs['msg']=codemsg('2038');
                return $rs;
            }
            $livestatus = isset($resinfo['islive'])?$resinfo['islive']:'0';
            // 若该直播间配置10秒试看，用户已看5秒，退出，则下次还可以再看5秒。在其他直播间则根据其他直播间的试看设置来限制。
            if($resinfo['type'] != '0'){
                $live_watchtime_key = 'live_watchtime_'.$stream.$uid;
                $watchtime = $redis->get($live_watchtime_key);
                if($watchtime === false){
                    $watchtime = $resinfo['tryWatchTime'];
                    $redis->set($live_watchtime_key,$resinfo['tryWatchTime'],60*60*24*7);
                }
                $left_watchtime = $watchtime > 0 ? $watchtime : 0;
            }
        }
      /*  $liveInfo =  $domain ->liveInfo($liveuid);*/
		$userinfo['contribution'] = $contribution;

		
		unset($userinfo['issuper']);
        
        /* 守护 */
        $domain_guard = new Domain_Guard();
        //守护机器人
        $guard_robot =$domain_guard->getGuardrobot($liveuid);
		$guard_info=$domain_guard->getUserGuard($uid,$liveuid);
		if(empty($guard_info)){
            $guard_info = (object)[];
        }
		$guard_nums=$domain_guard->getGuardNums($liveuid);
        /* 等级+100 保证等级位置位数相同，最后拼接1 防止末尾出现0 */
        $userinfo['sign']=$userinfo['contribution'].'.'.($userinfo['level']+100).'1';

		DI()->redis  -> set($token,json_encode($userinfo));
		
        /* 用户列表 */
        $userlists=$this->getUserList($liveuid,$stream);
        
        /* 用户连麦 */
		$linkmic_uid='0';
		$linkmic_pull='';
		$showVideo=DI()->redis  -> hGet('ShowVideo',$liveuid);
		
		if($showVideo){
            $showVideo_a=json_decode($showVideo,true);
			$linkmic_uid=$showVideo_a['uid'];
			$linkmic_pull=$showVideo_a['pull_url'];
		}
        
        /* 主播连麦 */
        $pkinfo=array(
            'pkuid'=>'0',
            'pkpull'=>'0',
            'ifpk'=>'0',
            'pk_time'=>'0',
            'pk_gift_liveuid'=>'0',
            'pk_gift_pkuid'=>'0',
        );
        $pkuid=DI()->redis  -> hGet('LiveConnect',$liveuid);
        if($pkuid){
            $pkinfo['pkuid']=$pkuid;
            /* 在连麦 */
            $pkpull=DI()->redis  -> hGet('LiveConnect_pull',$pkuid);
            $pkinfo['pkpull']=$pkpull;
            $ifpk=DI()->redis  -> hGet('LivePK',$liveuid);
            if($ifpk){
                $pkinfo['ifpk']='1';
                $pk_time=DI()->redis  -> hGet('LivePK_timer',$liveuid);
                if(!$pk_time){
                    $pk_time=DI()->redis  -> hGet('LivePK_timer',$pkuid);
                }
                $nowtime=time();
                if($pk_time && $pk_time >0 && $pk_time< $nowtime){
                    $cha=5*60 - ($nowtime - $pk_time);
                    $pkinfo['pk_time']=(string)$cha;
                    
                    $pk_gift_liveuid=DI()->redis  -> hGet('LivePK_gift',$liveuid);
                    if($pk_gift_liveuid){
                        $pkinfo['pk_gift_liveuid']=(string)$pk_gift_liveuid;
                    }
                    $pk_gift_pkuid=DI()->redis  -> hGet('LivePK_gift',$pkuid);
                    if($pk_gift_pkuid){
                        $pkinfo['pk_gift_pkuid']=(string)$pk_gift_pkuid;
                    }
                    
                }else{
                    $pkinfo['ifpk']='0';
                }
            }
        }

		$domain3 = new Domain_Game();
		$game = $domain3->checkGame($liveuid,$stream,$uid);

		// 判断是否使用golang的socket
        $chatserver = ($configpri['socket_type'] == 3 && $configpri['go_socket_url']) ? $configpri['go_socket_url'] : $configpri['chatserver'];

        // 针对足球视频直播处理
        $pull = $user_live_info['pull'];
        $flvpull = $user_live_info['flvpull'];
        $m3u8pull = $user_live_info['m3u8pull'];
        $football_live_match_id = $user_live_info['football_live_match_id'];

        $language = DI()->config->get('language.second');
        $secondinfo = $language[$language_id];
	    $info=array(
			'votestotal'=>$userlists['votestotal'],
			'barrage_fee'=>$configpri['barrage_fee'],
			'userlist_time'=>$configpri['userlist_time'],
			'chatserver'=>$chatserver,
			'linkmic_uid'=>$linkmic_uid,
			'linkmic_pull'=>$linkmic_pull,
			'nums'=>$userlists['nums']+getLiveNumsDefault($stream),
			'game'=>$game['brand'],
			'gamebet'=>$game['bet'],
			'gametime'=>$game['time'],
			'gameid'=>$game['id'],
			'gameaction'=>$game['action'],
			'game_bankerid'=>$game['bankerid'],
			'game_banker_name'=>$game['banker_name'],
			'game_banker_avatar'=>$game['banker_avatar'],
			'game_banker_coin'=>$game['banker_coin'],
			'game_banker_limit'=>$configpri['game_banker_limit'],
			'shut_time'=>$configpri['shut_time'].$secondinfo,
			'kick_time'=>$configpri['kick_time'].$secondinfo,
			'coin'=>$userinfo['coin'],
			'vip'=>isset($userinfo['vip'])?$userinfo['vip']:'1',
			'liang'=>$userinfo['liang'],
			'issuper'=>(string)$issuper,
			'usertype'=>(string)$usertype,
            'islive'=>$livestatus,
            'left_watchtime'=>isset($left_watchtime) ? $left_watchtime : 0, // 剩余观看时间
            'football_live_match_id' => $football_live_match_id, // rtmp播流地址
            'pull' => trim($pull), // rtmp播流地址
            'flvpull' => trim($flvpull), // rtmp播流地址
            'm3u8pull' => trim($m3u8pull), // rtmp播流地址
            /*  'show_offers' => $liveInfo['show_offers'],
           'show_dragon_assistant' => $liveInfo['show_dragon_assistant'],
           'show_reward_reporting' => $liveInfo['show_reward_reporting'],
           'enable_follow' => $liveInfo['enable_follow'],
           'lottery_array' => $liveInfo['lottery_array'],
           'recommend_lottery_array' => $liveInfo['recommend_lottery_array'],*/
		);
        //进入房间更新redis

       $enteruid = DI()->redis->zrank('user_'.$stream,$uid);
       if($enteruid===false){
            DI()->redis -> zAdd('user_'.$stream,0,$uid);
       }
        $redis->zAdd('watching_num'.$tenant_id,0,$uid);  // 当前租户的在线人数处理
        $redis->hSet('live_api_heart_time',$liveuid,time());
        $info['reward_set']=$domain->getReward($liveuid);
		$info['isattention']=(string)isAttention($uid,$liveuid);
		$info['userlists']=$userlists['userlist'];

        $redis->hSet('user_watchtime',$uid,time());
        /* 守护 */
        $info['guard']=$guard_info;
        $info['guard_nums']=$guard_nums;
        
        /* 主播连麦/PK */
        $info['pkinfo']=$pkinfo;
        
        /* 红包 */
        $key='red_list_'.$stream;
        $nums=$redis->lLen($key);
        $isred='0';
        if($nums>0){
            $isred='1';
        }
		$info['isred']=$isred;
        $info['car']=$userinfo['car'];
        
		$rs['info'][0]=$info;
		return $rs;
	}

    /**
     * 连麦信息
     * @desc 用于主播同意连麦 写入redis
     * @return int code 操作码，0表示成功
     * @return array info 
     * @return string msg 提示信息
     */
		 
    public function showVideo() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=$this->uid;
		$token=checkNull($this->token);
		$touid=checkNull($this->touid);
		$pull_url=checkNull($this->pull_url);

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		$checkToken=checkToken($uid,$token);
		if($checkToken==700){
		    $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		}
        
        $data=array(
            'uid'=>$touid,
            'pull_url'=>$pull_url,
        );
		
		DI()->redis  -> hset('ShowVideo',$uid,json_encode($data));
					
        return $rs;
    }		

	
    /**
     * 获取僵尸粉
     * @desc 用于获取僵尸粉
     * @return int code 操作码，0表示成功
     * @return array info 僵尸粉信息
     * @return string msg 提示信息
     */
		 
    public function getZombie() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=$this->uid;
		$stream=checkNull($this->stream);
		
		$stream2=explode('_',$stream);
		$liveuid=$stream2[0];
			
	
		$domain = new Domain_Live();
		
		$iszombie=$domain->isZombie($liveuid);
		
		if($iszombie==0){
			$rs['code']=1001;
			$rs['info']='未开启僵尸粉';
			$rs['msg']='未开启僵尸粉';
			return $rs;
			
		}

		/* 判断用户是否进入过 */
		$isvisit=DI()->redis ->sIsMember($liveuid.'_zombie_uid',$uid);

		if($isvisit){
			$rs['code']=1003;
			$rs['info']='用户已访问';
			$rs['msg']='用户已访问';
			return $rs;
			
		}
	
		$times=DI()->redis  -> get($liveuid.'_zombie');
		
		if($times && $times>10){
			$rs['code']=1002;
			$rs['info']='次数已满';
			$rs['msg']='次数已满';
			return $rs;
		}else if($times){
			$times=$times+1;
			
		}else{
			$times=0;
		}
	
		DI()->redis  -> set($liveuid.'_zombie',$times);
		DI()->redis  -> sAdd($liveuid.'_zombie_uid',$uid);
		
		/* 用户列表 */ 

        $uidlist=DI()->redis -> zRevRange('user_'.$stream,0,-1);
	
		$uid=implode(",",$uidlist);

		$where='0';
		if($uid){
			$where.=','.$uid;
		} 
        
		$where=str_replace(",,",',',$where);
		$where=trim($where, ",");
		//僵尸粉只添加主播所属租户的
        $liveuserinfo=getUserInfo($liveuid);



        $where="(".$where.") and (tenant_id='{$liveuserinfo['tenant_id']}')";
		$rs['info'][0]['list'] = $domain->getZombie($stream,$where);

		$nums=DI()->redis->zCard('user_'.$stream);
        if(!$nums){
            $nums=0;
        }

		$rs['info'][0]['nums']=(string)$nums;
		
        return $rs;
    }	
	/**
	 * 用户列表 
	 * @desc 用于直播间获取用户列表
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].userlist 用户列表
	 * @return string info[0].nums 房间人数
	 * @return string info[0].votestotal 主播映票
	 * @return string info[0].guard_type 守护类型
	 * @return string msg 提示信息
	 */
	public function getUserLists() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

		$liveuid=$this->liveuid;
		$stream=checkNull($this->stream);
		$p=$this->p;

		/* 用户列表 */ 
		$info=$this->getUserList($liveuid,$stream,$p);

        $info['nums'] += getLiveNumsDefault($stream);

		$rs['info'][0]=$info;

        return $rs;
	}			

    protected function getUserList($liveuid,$stream,$p=1) {
		/* 用户列表 */ 
		$n=1;

		$pnum= $p * 20 -1;
		$start=($p-1)*20;
        
        $domain_guard = new Domain_Guard();

        $sizenums=DI()->redis->zCard('user_'.$stream);
        //人数大于100，写缓存
        if($sizenums>100){
                $key="getUserLists_".$stream.'_'.$p;
                $list=getcaches($key);
                if(!$list){
                    $list=array();

                    $uidlist=DI()->redis -> zRevRange('user_'.$stream,$start,$pnum,true);
                    $nowtime=time();
                    foreach($uidlist as $k=>$v){
                        $userinfo=getUserInfo($k);

                        $result=DI()->redis -> hGet($stream.'shutup',$k);
                        $userinfo['is_shut_up'] = 0;
                        $userinfo['shut_up_time'] = 0;
                        if($result){
                            if($nowtime<=$result) {
                                $userinfo['is_shut_up'] = 1;
                                $userinfo['shut_up_time'] = $result;
                            }
                        }
                        $touidtype = isAdmin($k,$liveuid);

                        if($touidtype==60){
                            $userinfo["is_admin"] =  1;

                        }else{
                            $userinfo["is_admin"] =  0;
                        }
                        $info=explode(".",$v);
                        $userinfo['contribution']=(string)$info[0];

                        /* 守护 */
                        $guard_info=$domain_guard->getUserGuard($k,$liveuid);
                        $userinfo['guard_type']=$guard_info['type'];
                        $userinfo['noble'] = getUserNoble($userinfo['id']);

                        $list[]=$userinfo;
                    }

                  if($list){
                      setcaches($key,$list,60);
                  }
               }
        }else{
            //直播间人数 少于100，及时更新数据
            $list=array();

            $uidlist=DI()->redis -> zRevRange('user_'.$stream,$start,$pnum,true);
            $nowtime=time();
            foreach($uidlist as $k=>$v){
                $userinfo=getUserInfo($k);

                $result=DI()->redis -> hGet($stream.'shutup',$k);
                $userinfo['is_shut_up'] = 0;
                $userinfo['shut_up_time'] = 0;
                if($result){
                    if($nowtime<=$result) {
                        $userinfo['is_shut_up'] = 1;
                        $userinfo['shut_up_time'] = $result;
                    }
                }
                $touidtype = isAdmin($k,$liveuid);

                if($touidtype==60){
                    $userinfo["is_admin"] =  1;

                }else{
                    $userinfo["is_admin"] =  0;
                }
                $info=explode(".",$v);
                $userinfo['contribution']=(string)$info[0];

                /* 守护 */
                $guard_info=$domain_guard->getUserGuard($k,$liveuid);
                $userinfo['guard_type']=$guard_info['type'];
                $userinfo['noble'] = getUserNoble($userinfo['id']);

                $list[]=$userinfo;
            }

        }
        
        if(!$list){
            $list=array();
        }
        
		$nums=DI()->redis->zCard('user_'.$stream);
        if(!$nums){
            $nums=0;
        }

		$rs['userlist']=$list;
		$rs['nums']=(string)$nums;

		/* 主播信息 */
		$domain = new Domain_Live();
		$rs['votestotal']=$domain->getVotes($liveuid);


        return $rs; 
    }
    /**
     * 踢人后用户列表
     * @desc 用于直播间获取踢人后用户列表
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].userlist 用户列表
     * @return string info[0].nums 房间人数
     * @return string info[0].votestotal 主播映票
     * @return string info[0].guard_type 守护类型
     * @return string msg 提示信息
     */
    public function getUsergathers() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $liveuid=$this->liveuid;
        $stream=checkNull($this->stream);
        $p=$this->p;

        /* 用户列表 */
        $info=$this->getUsergather($liveuid,$stream,$p);

        $rs['info'][0]=$info;

        return $rs;
    }
    protected function getUsergather($liveuid,$stream,$p=1) {
        /* 用户列表 */
        $n=1;

        $pnum= $p * 20 -1;
        $start=($p-1)*20;

        $domain_guard = new Domain_Guard();

        $list=array();

        $uidlist=DI()->redis -> zRevRange('user_'.$stream,$start,$pnum,true);
        $nowtime=time();
        foreach($uidlist as $k=>$v) {
            $userinfo = getUserInfo($k);

            $result = DI()->redis->hGet($stream . 'shutup', $k);
            $userinfo['is_shut_up'] = 0;
            $userinfo['shut_up_time'] = 0;
            if ($result) {
                if ($nowtime <= $result) {
                    $userinfo['is_shut_up'] = 1;
                    $userinfo['shut_up_time'] = $result;
                }
            }
            $touidtype = isAdmin($k, $liveuid);

            if ($touidtype == 60 || $touidtype == 40) {
                $userinfo["is_admin"] = 1;

            } else {
                $userinfo["is_admin"] = 0;
            }
            $info = explode(".", $v);
            $userinfo['contribution'] = (string)$info[0];

            /* 守护 */
            $guard_info = $domain_guard->getUserGuard($k, $liveuid);
            $userinfo['guard_type'] = $guard_info['type'];
            $userinfo['noble'] = getUserNoble($userinfo['id']);

            $list[] = $userinfo;
        }

        if(!$list){
            $list=array();
        }

        $nums=DI()->redis->zCard('user_'.$stream);
        if(!$nums){
            $nums=0;
        }

        $rs['userlist']=$list;
        $rs['nums']=(string)$nums;

        /* 主播信息 */
        $domain = new Domain_Live();
        $rs['votestotal']=$domain->getVotes($liveuid);


        return $rs;
    }



    /**
	 * 弹窗 
	 * @desc 用于直播间弹窗信息
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].consumption 消费总数
	 * @return string info[0].votestotal 票总数
	 * @return string info[0].follows 关注数
	 * @return string info[0].fans 粉丝数
	 * @return string info[0].isattention 是否关注，0未关注，1已关注
	 * @return string info[0].action 操作显示，0表示自己，30表示普通用户，40表示管理员，501表示主播设置管理员，502表示主播取消管理员，60表示超管管理主播 
	 * @return object info[0].vip 用户VIP信息
	 * @return string info[0].vip.type VIP类型，0表示无VIP，1表示普通VIP，2表示至尊VIP
	 * @return object info[0].liang 用户靓号信息
	 * @return string info[0].liang.name 号码，0表示无靓号
	 * @return array info[0].label 印象标签
	 * @return string msg 提示信息
	 */
	public function getPop() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=$this->uid;
		$liveuid=$this->liveuid;
		$touid=$this->touid;
        $stream=$this->stream;
		if ($touid == 0){
            $rs['code']=2050;
            $rs['msg']='touid应该大于或等于1, 但现在touid=0';
            return $rs;
        }
        $info=getUserInfo($touid);

        $nowtime=time();
        $result=DI()->redis->hGet($stream.'shutup',$touid);
        if($result){
            if($nowtime<=$result){
                $info['isshutup']  = 1;
            }
        }

        //判断是否对该会员，所有直播间禁言
        $all_shut = DI()->redis -> hGet('user_shutup',$touid);
        if($all_shut){
            $info['isshutup']  = 1;
        }

		if(!$info){
			$rs['code']=1002;
			$rs['msg']='用户信息不存在';
			return $rs;
		}
		if($info['user_type'] != 3){
            $info['follows']=getFollows($touid);
            $info['fans']=getFans($touid) ;
            if($liveuid == $touid){ // 如果获取对象是主播那就加上虚拟值
                $info['fans'] += getLiveFansDefault($touid);
                $info['votestotal'] += getLiveVotestotalDefault($touid);
            }
        }
        if($info['user_type'] == 3){  //生产的虚拟会员数据，收入钻石数据改成0
            $info['votestotal'] = 0 ;
        }
		$info['isattention']=(string)isAttention($uid,$touid);
		if($uid==$touid){
			$info['action']='0';
		}else{
			$uid_admin=isAdmin($uid,$liveuid);
			$touid_admin=isAdmin($touid,$liveuid);

			if($uid_admin==40 && $touid_admin==30){
				$info['action']='40';
			}else if($uid_admin==50 && $touid_admin==30){
				$info['action']='501';
			}else if($uid_admin==50 && $touid_admin==40){
				$info['action']='502';
			}else if($uid_admin==60 && $touid_admin<50){
				$info['action']='40';
			}else if($uid_admin==60 && $touid_admin==50){
				$info['action']='60';
			}else{
				$info['action']='30';
			}
			
		}
        
        /* 标签 */
        $labels=array();
        if($touid==$liveuid){
            $key="getMyLabel_".$touid;
            $label=getcaches($key);
            if(!$label){
                $domain2 = new Domain_User();
                $label = $domain2->getMyLabel($touid);

                setcaches($key,$label, 60*60*24*7);
            }
            
            $labels=array_slice($label,0,2);
        }
        $info['label']=$labels;
        if($info['level'] == '0'){
            $info['level'] = '1';
        }
        $domain_guard = new Domain_Guard();
        $guard_info=$domain_guard->getUserGuard($touid,$liveuid);
        if(empty($guard_info)){
            $guard_info = (object)[];
        }
        $info['guard'] = $guard_info;
        $info['noble'] = getUserNoble($info['id']);

		$rs['info'][0]=$info;
		return $rs;
	}				

	/**
	 * 礼物列表 
	 * @desc 用于获取礼物列表
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].coin 余额
	 * @return array info[0].giftlist 礼物列表
	 * @return string info[0].giftlist[].id 礼物ID
	 * @return string info[0].giftlist[].type 礼物类型
	 * @return string info[0].giftlist[].mark 礼物标识
	 * @return string info[0].giftlist[].giftname 礼物名称
	 * @return string info[0].giftlist[].needcoin 礼物价格
	 * @return string info[0].giftlist[].gifticon 礼物图片
	 * @return string msg 提示信息
	 */
	public function getGiftList() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=$this->uid;
		$token=checkNull($this->token);

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		$checkToken=checkToken($uid,$token);
		if($checkToken==700){
		    $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		}
		
		$key='getGiftList_'.getTenantId();
		$giftlist=getcaches($key);

		if(!$giftlist){
			$domain = new Domain_Live();
			$giftlist=$domain->getGiftList();
			setcaches($key,$giftlist, 60*60*24*7);
		}
		
		$domain2 = new Domain_User();
		$coin=$domain2->getBalance($uid);
		
		$rs['info'][0]['giftlist']=$giftlist;
		$rs['info'][0]['withdrawable_coin']=$coin['withdrawable_coin'];
        $rs['info'][0]['coin']=$coin['coin'];
        $rs['info'][0]['nowithdrawable_coin']=$coin['nowithdrawable_coin'];
		return $rs;
	}		

	/**
	 * 赠送礼物 
	 * @desc 用于赠送礼物
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].gifttoken 礼物token
	 * @return string info[0].level 用户等级
	 * @return string info[0].coin 用户余额
	 * @return string msg 提示信息
	 */
	public function sendGift() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		$uid=$this->uid;
		$token=$this->token;
		$liveuid=$this->liveuid;
		$stream=checkNull($this->stream);
		$giftid=$this->giftid;
		$giftcount=$this->giftcount;
        $game_tenant_id=$this->game_tenant_id;
        $send_type = $this->send_type;
        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		$checkToken=checkToken($uid,$token);
		if($checkToken==700){
		    $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		}
        
        $key='getGiftList_'.getTenantId();
		$giftlist=getcaches($key);
		if(!$giftlist){
			$domain = new Domain_Live();
			$giftlist=$domain->getGiftList();
			setcaches($key,$giftlist, 60*60*24*7);
		}
        $gift_info=array();
        foreach($giftlist as $k=>$v){
            if($giftid == $v['id']){
               $gift_info=$v; 
            }
        }
        if(!$gift_info){
            $rs['code']=1002;
			$rs['msg']='礼物信息不存在';
			return $rs;
        }

        if($gift_info['type']==2){
            /* 守护 */
            $domain_guard = new Domain_Guard();
            $guard_info=$domain_guard->getUserGuard($uid,$liveuid);
            if(!$guard_info){
               $rs['code']=1002;
                $rs['msg']='该礼物是守护专属礼物奥~';
                return $rs; 
            }
            if($guard_info['is_gift'] == 0 ){
                $rs['code']=1003;
                $rs['msg']='当前守护等级，没有送守护专属礼物权限哦~';
                return $rs;
            }

            if(isset($guard_info['giftarr']) && !empty($guard_info['giftarr'])){
                $giftarr  = explode(',',$guard_info['giftarr']);
                if(!in_array($giftid,$giftarr)){
                    $rs['code']=1004;
                    $rs['msg']='当前守护等级不包括该礼物~';
                    return $rs;
                }

            }
        }

		$domain = new Domain_Live();
        $whichTenant= whichTenat($game_tenant_id);
        if($whichTenant==1){
//            file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/data/Log/balance_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' ceshi11:'."\r\n",FILE_APPEND);

            $result=$domain->sendGift($uid,$liveuid,$stream,$giftid,$giftcount,$send_type);  //彩票租户

        }else{
//            file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/data/Log/balance_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' ceshi22:'."\r\n",FILE_APPEND);

            $result=$domain->sendGiftalone($uid,$liveuid,$stream,$giftid,$giftcount);//独立租户
        }

		if(isset($result['code']) && $result['code']==1001){
            $rs['code'] = $result['code'];
            $rs['msg'] = $result['msg'] ? $result['msg'] : '余额不足';
            $rs['info'] = $result['info'] ? $result['info'] : $rs['info'];
		}else if($result==1002){
			$rs['code']=1002;
			$rs['msg']='礼物信息不存在';
			return $rs;
		}
        else if($result==1009){
            $rs['code'] = 1002;
            $language = DI()->config->get('language.newwork_error');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }else if($result==1005){
            $rs['code']=1005;
            $rs['msg']='该会员没有该背包礼物';
            return $rs;
        }
		
		$rs['info'][0]['gifttoken']=$result['gifttoken'];
        $rs['info'][0]['level']=$result['level'];
        $rs['info'][0]['coin']=$result['coin'];
		
		unset($result['gifttoken']);

		DI()->redis  -> set($rs['info'][0]['gifttoken'],json_encode($result));
		
		
		return $rs;
	}		
	
	/**
	 * 发送弹幕 
	 * @desc 用于发送弹幕
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].barragetoken 礼物token
	 * @return string info[0].level 用户等级
	 * @return string info[0].coin 用户余额
	 * @return string msg 提示信息
	 */
	public function sendBarrage() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		$uid=$this->uid;
		$token=$this->token;
		$liveuid=$this->liveuid;
		$stream=checkNull($this->stream);
        $game_tenant_id=$this->game_tenant_id;
		$giftid=0;
		$giftcount=1;

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		$content=checkNull($this->content);
		if($content==''){
            $rs['code'] = 1003;
            $language = DI()->config->get('language.barrage_empty');
            $rs['msg'] = $language[$language_id];
            return $rs;  //弹幕内容不能为空
		}
		
		$checkToken=checkToken($uid,$token);
		if($checkToken==700){
		    $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		} 
		
		$domain = new Domain_Live();
        $whichTenant= whichTenat($game_tenant_id);

        if($whichTenant==1){
            $result=$domain->sendBarrage($uid,$liveuid,$stream,$giftid,$giftcount,$content);  //彩票租户

        }else{
            $result=$domain->sendBarragealone($uid,$liveuid,$stream,$giftid,$giftcount,$content);//独立租户
        }

		
		if($result==1001){
            $rs['code'] = 1001;
            $language = DI()->config->get('language.balance_error');
            $rs['msg'] = $language[$language_id];
            return $rs;       //余额不足
		}else if($result==1002){
            $rs['code'] = 1002;
            $language = DI()->config->get('language.gift_empty');
            $rs['msg'] = $language[$language_id];
            return $rs;    //礼物信息不存在
		}else if(isset($result['code']) && $result['code']== 2034){
            $rs['code'] = $result['code'];
            $rs['msg'] = $result['msg'] ? $result['msg'] : $rs['msg'];
            $rs['info'] = $result['info'] ? $result['info'] : $rs['info'];
            return $rs;
        }
		
		$rs['info'][0]['barragetoken']=$result['barragetoken'];
        $rs['info'][0]['level']=$result['level'];
        $rs['info'][0]['coin']=$result['coin'];
		
		unset($result['barragetoken']);

		DI()->redis -> set($rs['info'][0]['barragetoken'],json_encode($result));

		return $rs;
	}			

	/**
	 * 设置/取消管理员 
	 * @desc 用于获取礼物列表
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].isadmin 是否是管理员，0表示不是管理员，1表示是管理员
	 * @return string msg 提示信息
	 */
	public function setAdmin() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=$this->uid;
		$token=$this->token;
		$liveuid=$this->liveuid;
		$touid=$this->touid;

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		$checkToken=checkToken($uid,$token);
		if($checkToken==700){
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		} 
		
		if($uid!=$liveuid){
			$rs['code'] = 1001;
			$rs['msg'] = '你不是该房间主播，无权操作';
			return $rs;
		}
		
		$domain = new Domain_Live();
		$info=$domain->setAdmin($liveuid,$touid);
		
		if($info==1004){
			$rs['code'] = 1004;
			$rs['msg'] = '最多设置5个管理员';
			return $rs;
		}else if($info==1003){
			$rs['code'] = 1003;
			$rs['msg'] = '操作失败，请重试';
			return $rs;
		}
		
		$rs['info'][0]['isadmin']=$info;
		return $rs;
	}		
	
	/**
	 * 管理员列表 
	 * @desc 用于获取管理员列表
	 * @return int code 操作码，0表示成功
	 * @return array info 管理员列表
	 * @return array info[0]['list'] 管理员列表
	 * @return array info[0]['list'][].userinfo 用户信息
	 * @return string info[0]['nums'] 当前人数
	 * @return string info[0]['total'] 总数
	 * @return string msg 提示信息
	 */
	public function getAdminList() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$domain = new Domain_Live();
		$info=$domain->getAdminList($this->liveuid);

		$rs['info'][0]=$info;
		return $rs;
	}			

	/**
	 * 举报类型 
	 * @desc 用于获取举报类型
	 * @return int code 操作码，0表示成功
	 * @return array info 列表
	 * @return string info[].name 类型名称
	 * @return string msg 提示信息
	 */
	public function getReportClass() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());

		$domain = new Domain_Live();
		$info=$domain->getReportClass();

		
		$rs['info']=$info;
		return $rs;
	}	

	
	/**
	 * 用户举报 
	 * @desc 用于用户举报
	 * @return int code 操作码，0表示成功
	 * @return array info 礼物列表
	 * @return string info[0].msg 举报成功
	 * @return string msg 提示信息
	 */
	public function setReport() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=$this->uid;
		$token=checkNull($this->token);
		$touid=$this->touid;
		$content=checkNull($this->content);

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		$checkToken=checkToken($uid,$token);
		if($checkToken==700){
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		} 
		
		if(!$content){
			$rs['code'] = 1001;
			$rs['msg'] = '举报内容不能为空';
			return $rs;
		}
		
		$domain = new Domain_Live();
		$info=$domain->setReport($uid,$touid,$content);
		if($info===false){
			$rs['code'] = 1002;
			$rs['msg'] = '举报失败，请重试';
			return $rs;
		}
		
		$rs['info'][0]['msg']="举报成功";
		return $rs;
	}			
	
	/**
	 * 主播映票 
	 * @desc 用于获取主播映票
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].votestotal 用户总数
	 * @return string msg 提示信息
	 */
	public function getVotes() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$domain = new Domain_Live();
		$info=$domain->getVotes($this->liveuid);
		
		$rs['info'][0]=$info;
		return $rs;
	}		
	
    /**
     * 禁言
     * @desc 用于 禁言操作
     * @return int code 操作码，0表示成功
     * @return array info 
     * @return string msg 提示信息
     */
		 
    public function setShutUp() { 
        $rs = array('code' => 0, 'msg' => '禁言成功', 'info' => array());

		$uid=$this->uid;
		$token=$this->token;
		$liveuid=$this->liveuid;
		$touid=$this->touid;
        $stream = $this->stream;
		$checkToken = checkToken($uid,$token);
		if($checkToken==700){
			$rs['code']=700;
			$rs['msg']='token已过期，请重新登陆';
			return $rs;
		}
						
        $uidtype = isAdmin($uid,$liveuid);


		if($uidtype==30 ){
			$rs["code"]=1001;
			$rs["msg"]='无权操作';
			return $rs;									
		}

        // 不是超管，则判断对方是否是贵族防禁言
        if($uidtype != 60) {
            $user_noble = getUserNoble($touid);
            if (is_array($user_noble) && isset($user_noble['prevent_mute']) && $user_noble['prevent_mute'] == 1) { // 防禁言: 0.否，1.是
                $rs["code"] = 2097;
                $rs["msg"] = codemsg('2097');
                return $rs;
            }
            /* 守护 */
            $domain_guard = new Domain_Guard();
            $guard_info=$domain_guard->getUserGuard($touid,$liveuid);
            if( $guard_info && $guard_info['is_shutup']==1){
                $rs["code"]=1005;
                $rs["msg"]='对方是尊贵守护，不能禁言';
                return $rs;
            }
        }

        $touidtype = isAdmin($touid,$liveuid);
		
		if($touidtype==60){
			$rs["code"]=1002;
			$rs["msg"]='对方是超管，不能禁言';
			return $rs;	
		}

		if($uidtype==40 || $uidtype==50){
			if( $touidtype==50){
				$rs["code"]=1003;
				$rs["msg"]='不要禁用主播';
				return $rs;
			}
			if($touidtype==40 ){
				$rs["code"]=1004;
				$rs["msg"]='对方是管理员，不能禁言';
				return $rs;		
			}

		}

		$nowtime=time();	
        $result=DI()->redis -> hGet($stream.'shutup',$touid);
		if($result){
			if($nowtime<=$result){
				$rs["code"]=1006;
				$rs["msg"]='对方已被禁言';
				return $rs;	
			}
		}		
		
		$configpri=getConfigPri();
		$shut_time=$configpri['shut_time'];
		$time=$nowtime + $shut_time;
        DI()->redis -> hSet($stream . 'shutup',$touid,$time);
        CustRedis::getInstance()->expire($stream.'shutup', 60*60*24*7);
				
        return $rs;
    }
    /**
     * 全体禁言
     * @desc 用于 全体禁言操作
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */

    public function setShutUpall() {
        $rs = array('code' => 0, 'msg' => '全体禁言成功', 'info' => array());

        $uid=$this->uid;
        $token=$this->token;
        $liveuid=$this->liveuid;
        $touid=$this->touid;
        $stream = $this->stream;

        $checkToken = checkToken($uid,$token);
        if($checkToken==700){
            $rs['code']=700;
            $rs['msg']='token已过期，请重新登陆';
            return $rs;
        }

        $uidtype = isAdmin($uid,$liveuid);
        if($uidtype==30 ){
            $rs["code"]=1001;
            $rs["msg"]='无权操作';
            return $rs;
        }
        $touidinfo = DI()->redis -> zRange('user_'.$stream,0,-1,true);
        foreach($touidinfo as $value => $values){
            // 不是超管，则判断对方是否是贵族防禁言 --
            if($uidtype != 60){
                $user_noble = getUserNoble($touid);
                if(is_array($user_noble) && isset($user_noble['prevent_mute']) && $user_noble['prevent_mute'] == 1){ // 防禁言: 0.否，1.是--
                    $rs["code"] = 2097;
                    $rs["msg"] = codemsg('2097');
                    continue;
                }
            }

            $touidtype = isAdmin($value,$liveuid);

            if($touidtype==60){
                $rs["code"]=1001;
                $rs["msg"]='对方是超管，不能禁言';
                continue;
            }

            if($uidtype==40 || $uidtype==50){
                if( $touidtype==50){
                    $rs["code"]=1002;
                    $rs["msg"]='对方是主播，不能禁言';
                    continue;
                }
                if($touidtype==40 ){
                    $rs["code"]=1002;
                    $rs["msg"]='对方是管理员，不能禁言';
                    continue;
                }

                    /* 守护 */
                $domain_guard = new Domain_Guard();
                $guard_info=$domain_guard->getUserGuard($value,$liveuid);

                if($uid != $liveuid && $guard_info && $guard_info['type']==2){
                    $rs["code"]=1004;
                    $rs["msg"]='对方是尊贵守护，不能禁言';
                    continue;
                }

            }

            $nowtime=time();
            $result=DI()->redis -> hGet($stream . 'shutup',$value);
            if($result){
                if($nowtime<=$result){
                    $rs["code"]=1003;
                    $rs["msg"]='对方已被禁言';
                    continue;
                }
            }

            $configpri=getConfigPri();
            $shut_time=$configpri['shut_time'];
            $time=$nowtime + $shut_time;
            DI()->redis -> hSet($stream . 'shutup',$value,$time);
            CustRedis::getInstance()->expire($stream.'shutup', 60*60*24*7);
        }
      /*  $nowtime=time();
        $configpri=getConfigPri();
        $shut_time=$configpri['shut_time'];
        $time=$nowtime + $shut_time;
        DI()->redis -> hSet($stream . 'shutup',1,$time);*/

        return $rs;
    }
    /**
     * 全体解除禁言
     * @desc 用于 全体解除禁言操作
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */

    public function setShutUpcancel() {
        $rs = array('code' => 0, 'msg' => '全体解除禁言成功', 'info' => array());

        $uid=$this->uid;
        $token=$this->token;
        $liveuid=$this->liveuid;
        $touid=$this->touid;
        $stream =  $this->stream;

        $checkToken = checkToken($uid,$token);
        if($checkToken==700){
            $rs['code']=700;
            $rs['msg']='token已过期，请重新登陆';
            return $rs;
        }

        $uidtype = isAdmin($uid,$liveuid);

        if($uidtype==30 ){
            $rs["code"]=1001;
            $rs["msg"]='无权操作';
            return $rs;
        }
        if(!empty($touid)){
            DI()->redis -> hDel($stream . 'shutup',$touid);
        }else{

            DI()->redis -> del($stream . 'shutup');
        }
        return $rs;
    }

    /**
	 * 踢人 
	 * @desc 用于直播间踢人
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].msg 踢出成功
	 * @return string msg 提示信息
	 */
	public function kicking() {
		$rs = array('code' => 0, 'msg' => '踢人成功', 'info' => array());
        $redis = connectionRedis();

		$uid=$this->uid;
		$token=$this->token;
		$liveuid=$this->liveuid;
		$touid=$this->touid;
        $stream=$this->stream;

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		$checkToken=checkToken($uid,$token);
		if($checkToken==700){
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		} 

		$admin_uid=isAdmin($uid,$liveuid);
		if($admin_uid==30){
			$rs['code']=1001;
			$rs['msg']='无权操作';
			return $rs;
		}
		$admin_touid=isAdmin($touid,$liveuid);
		
		if($admin_touid==60){
            $rs['code'] = 1002;
            $language = DI()->config->get('language.nokicking_super');
            $rs['msg'] = $language[$language_id];
            return $rs; //对方是超管，不能被踢出
		}
		//超管不能踢出主播
        if($admin_uid==60){
            if($admin_touid==50){
                $rs['code'] = 1003;
                $language = DI()->config->get('language.nokicking_zhubo');
                $rs['msg'] = $language[$language_id];
                return $rs; //对方是主播，不能被踢出
            }
        }
		if($admin_uid!=60){
			if($admin_touid==50){
                $rs['code'] = 1003;
                $language = DI()->config->get('language.nokicking_zhubo');
                $rs['msg'] = $language[$language_id];
                return $rs; //对方是主播，不能被踢出
			} 
            
            if($admin_touid==40){
                $rs['code'] = 2020;
                $rs['msg'] = DI()->config->get('code.2020');
                return $rs; //对方是管理员，不能被踢出
            }
            /* 守护 */
            $domain_guard = new Domain_Guard();
            $guard_info=$domain_guard->getUserGuard($touid,$liveuid);
            if( $guard_info && $guard_info['is_shutup'] == 1){
                $rs["code"]=1005;
                $rs["msg"]='对方是尊贵守护，不能被踢出';
                return $rs;
            }

		}

        if($admin_uid==60){
            if($admin_touid==40){
                //踢出管理员
                $domain = new Domain_Live();
                $domain->setAdmin($liveuid,$touid);
            }
        }
			
		$nowtime=time();
		
		$configpri=getConfigPri();
		$kick_time=$configpri['kick_time'];
		
		$time=$nowtime + $kick_time;
		DI()->redis->zRem('user_'.$stream,$touid);
		$result=DI()->redis->hset($stream.'kick',$touid,$time);

        // 当前租户的在线人数处理
		$tou_info = getUserInfo($touid);
        $redis->zRem('watching_num'.$tou_info['tenant_id'],$touid);

		$rs['info'][0]['msg']='踢出成功';
		return $rs;
	}		
	
	/**
     * 超管关播
     * @desc 用于超管关播
     * @return int code 操作码，0表示成功
     * @return array info 
     * @return string info[0].msg 提示信息 
     * @return string msg 提示信息
     */
		
	public function superStopRoom(){

		$rs = array('code' => 0, 'msg' => '关闭成功', 'info' =>array());
		
		$domain = new Domain_Live();
		
		$result = $domain->superStopRoom($this->uid,$this->token,$this->liveuid,$this->type);
		if($result==700){
			$rs['code'] = 700;
            $rs['msg'] = 'token已过期，请重新登陆';
            return $rs;
		}else if($result==1001){
			$rs['code'] = 1001;
            $rs['msg'] = '你不是超管，无权操作';
            return $rs;
		}
		$rs['info'][0]['msg']='关闭成功';
 
    	return $rs;
	}	

	/**
	 * 用户余额 
	 * @desc 用于获取用户余额
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].coin 余额
     * @return string info[0].nowithdrawable_coin 不可提现余额
	 * @return string msg 提示信息
	 */
	public function getCoin() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=$this->uid;
		$token=checkNull($this->token);

        $language_id=$_GET['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		$checkToken=checkToken($uid,$token);
		if($checkToken==700){
		    $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		}

		
		$domain2 = new Domain_User();
		$coin=$domain2->getBalance($uid);

		$rs['info'][0]['coin']=$coin['coin'];
		return $rs;
	}
    /**
     * 虚拟用户进入直播间
     * @desc 用于用户进入直播
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].votestotal 直播映票
     * @return string info[0].barrage_fee 弹幕价格
     * @return string info[0].userlist_time 用户列表获取间隔
     * @return string info[0].chatserver socket地址
     * @return string info[0].isattention 是否关注主播，0表示未关注，1表示已关注
     * @return string info[0].nums 房间人数
     * @return string info[0].push_url 推流地址
     * @return string info[0].pull_url 播流地址
     * @return string info[0].linkmic_uid 连麦用户ID，0表示未连麦
     * @return string info[0].linkmic_pull 连麦播流地址
     * @return array info[0].userlists 用户列表
     * @return array info[0].game 押注信息
     * @return array info[0].gamebet 当前用户押注信息
     * @return string info[0].gametime 游戏剩余时间
     * @return string info[0].gameid 游戏记录ID
     * @return string info[0].gameaction 游戏类型，1表示炸金花，2表示牛牛，3表示转盘
     * @return string info[0].game_bankerid 庄家ID
     * @return string info[0].game_banker_name 庄家昵称
     * @return string info[0].game_banker_avatar 庄家头像
     * @return string info[0].game_banker_coin 庄家余额
     * @return string info[0].game_banker_limit 上庄限额
     * @return object info[0].vip 用户VIP信息
     * @return string info[0].vip.type VIP类型，0表示无VIP，1表示普通VIP，2表示至尊VIP
     * @return object info[0].liang 用户靓号信息
     * @return string info[0].liang.name 号码，0表示无靓号
     * @return string info[0].shut_time 禁言时间
     * @return string info[0].kick_time 踢人时间
     * @return object info[0].guard 守护信息
     * @return string info[0].guard.type 守护类型，0表示非守护，1表示月守护，2表示年守护
     * @return string info[0].guard.endtime 到期时间
     * @return string info[0].guard_nums 主播守护数量
     * @return object info[0].pkinfo 主播连麦/PK信息
     * @return string info[0].pkinfo.pkuid 连麦用户ID
     * @return string info[0].pkinfo.pkpull 连麦用户播流地址
     * @return string info[0].pkinfo.ifpk 是否PK
     * @return string info[0].pkinfo.pk_time 剩余PK时间（秒）
     * @return string info[0].pkinfo.pk_gift_liveuid 主播PK总额
     * @return string info[0].pkinfo.pk_gift_pkuid 连麦主播PK总额
     * @return string info[0].isred 是否显示红包
     * @return string msg 提示信息
     */
    public function enterRoomvutar() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $redis = connectionRedis();

        $uid=$this->uid;
        $token=checkNull($this->token);
        $liveuid=$this->liveuid;
        $city=checkNull($this->city);
        $stream=checkNull($this->stream);
        $tenant_id = getTenantId();

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
       /* $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }*/


        $isban = isBan($uid);
        if(!$isban){
            $rs['code']=1001;
            $rs['msg']='该账号已被禁用';
            return $rs;
        }


        $domain = new Domain_Live();
        $userinfo=getUserInfo($uid);

        $carinfo=getUserCar($uid);
        $userinfo['car']=$carinfo;
        $issuper='0';
        if($userinfo['issuper']==1){
            $issuper='1';
            DI()->redis  -> hset('super',$userinfo['id'],'1');
        }else{
            DI()->redis  -> hDel('super',$userinfo['id']);
        }
        if(!$city){
            $city='好像在火星';
        }

        $data=array('city'=>$city);
        $domain2 = new Domain_User();
        $info = $domain2->userUpdate($uid,$data);
        $userinfo['city']=$city;

        $usertype = isAdmin($uid,$liveuid);
        $userinfo['usertype'] = $usertype;

        $stream2=explode('_',$stream);
        $showid=$stream2[1];

        $contribution='0';
        if($showid){
            $contribution=$domain->getContribut($uid,$liveuid,$showid);
        }

        $userinfo['contribution'] = $contribution;


        unset($userinfo['issuper']);

        /* 守护 */
        $domain_guard = new Domain_Guard();
        $guard_info=$domain_guard->getUserGuard($uid,$liveuid);

        $guard_nums=$domain_guard->getGuardNums($liveuid);
        $userinfo['guard_type']=$guard_info['type'];
        /* 等级+100 保证等级位置位数相同，最后拼接1 防止末尾出现0 */
        $userinfo['sign']=$userinfo['contribution'].'.'.($userinfo['level']+100).'1';

        DI()->redis  -> set($token,json_encode($userinfo));

        /* 用户列表 */
        $userlists=$this->getUserList($liveuid,$stream);

        /* 用户连麦 */
        $linkmic_uid='0';
        $linkmic_pull='';
        $showVideo=DI()->redis  -> hGet('ShowVideo',$liveuid);

        if($showVideo){
            $showVideo_a=json_decode($showVideo,true);
            $linkmic_uid=$showVideo_a['uid'];
            $linkmic_pull=$showVideo_a['pull_url'];
        }

        /* 主播连麦 */
        $pkinfo=array(
            'pkuid'=>'0',
            'pkpull'=>'0',
            'ifpk'=>'0',
            'pk_time'=>'0',
            'pk_gift_liveuid'=>'0',
            'pk_gift_pkuid'=>'0',
        );
        $pkuid=DI()->redis  -> hGet('LiveConnect',$liveuid);
        if($pkuid){
            $pkinfo['pkuid']=$pkuid;
            /* 在连麦 */
            $pkpull=DI()->redis  -> hGet('LiveConnect_pull',$pkuid);
            $pkinfo['pkpull']=$pkpull;
            $ifpk=DI()->redis  -> hGet('LivePK',$liveuid);
            if($ifpk){
                $pkinfo['ifpk']='1';
                $pk_time=DI()->redis  -> hGet('LivePK_timer',$liveuid);
                if(!$pk_time){
                    $pk_time=DI()->redis  -> hGet('LivePK_timer',$pkuid);
                }
                $nowtime=time();
                if($pk_time && $pk_time >0 && $pk_time< $nowtime){
                    $cha=5*60 - ($nowtime - $pk_time);
                    $pkinfo['pk_time']=(string)$cha;

                    $pk_gift_liveuid=DI()->redis  -> hGet('LivePK_gift',$liveuid);
                    if($pk_gift_liveuid){
                        $pkinfo['pk_gift_liveuid']=(string)$pk_gift_liveuid;
                    }
                    $pk_gift_pkuid=DI()->redis  -> hGet('LivePK_gift',$pkuid);
                    if($pk_gift_pkuid){
                        $pkinfo['pk_gift_pkuid']=(string)$pk_gift_pkuid;
                    }

                }else{
                    $pkinfo['ifpk']='0';
                }
            }
        }

        $configpri=getConfigPri();
        $domain3 = new Domain_Game();
        $game = $domain3->checkGame($liveuid,$stream,$uid);

        $info=array(
            'votestotal'=>$userlists['votestotal'],
            'barrage_fee'=>$configpri['barrage_fee'],
            'userlist_time'=>$configpri['userlist_time'],
            'chatserver'=>$configpri['chatserver'],
            'linkmic_uid'=>$linkmic_uid,
            'linkmic_pull'=>$linkmic_pull,
            'nums'=>$userlists['nums'],
            'game'=>$game['brand'],
            'gamebet'=>$game['bet'],
            'gametime'=>$game['time'],
            'gameid'=>$game['id'],
            'gameaction'=>$game['action'],
            'game_bankerid'=>$game['bankerid'],
            'game_banker_name'=>$game['banker_name'],
            'game_banker_avatar'=>$game['banker_avatar'],
            'game_banker_coin'=>$game['banker_coin'],
            'game_banker_limit'=>$configpri['game_banker_limit'],
            'shut_time'=>$configpri['shut_time'].'秒',
            'kick_time'=>$configpri['kick_time'].'秒',
            'coin'=>$userinfo['coin'],
            'vip'=>$userinfo['vip'],
            'liang'=>$userinfo['liang'],
            'issuper'=>(string)$issuper,
            'usertype'=>(string)$usertype,
        );
        //进入房间更新redis

        $enteruid = DI()->redis->zrank('user_'.$stream,$uid);
        if($enteruid===false){
            DI()->redis -> zAdd('user_'.$stream,0,$uid);
        }



        $info['isattention']=(string)isAttention($uid,$liveuid);
        $info['userlists']=$userlists['userlist'];

        /* 守护 */
        $info['guard']=$guard_info;
        $info['guard_nums']=$guard_nums;

        /* 主播连麦/PK */
        $info['pkinfo']=$pkinfo;

        /* 红包 */
        $key='red_list_'.$stream;
        $nums=$redis->lLen($key);
        $isred='0';
        if($nums>0){
            $isred='1';
        }
        $info['isred']=$isred;

        $rs['info'][0]=$info;
        return $rs;
    }
    /**
     * 虚拟用户赠送礼物
     * @desc 用于虚拟用户赠送礼物
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].gifttoken 礼物token
     * @return string info[0].level 用户等级
     * @return string info[0].coin 用户余额
     * @return string msg 提示信息
     */
    public function sendGiftvatul() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $uid=$this->uid;
        $token=$this->token;
        $liveuid=$this->liveuid;
        $stream=checkNull($this->stream);
        $giftid=$this->giftid;
        $giftcount=$this->giftcount;
        $game_tenant_id=$this->game_tenant_id;

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }

        $key='getGiftList_'.getTenantId();
        $giftlist=getcaches($key);

        if(!$giftlist){
            $domain = new Domain_Live();
            $giftlist=$domain->getGiftList();
            setcaches($key,$giftlist, 60*60*24*7);
        }
        $gift_info=array();
        foreach($giftlist as $k=>$v){
            if($giftid == $v['id']){
                $gift_info=$v;
            }
        }
        if(!$gift_info){
            $rs['code']=1002;
            $rs['msg']='礼物信息不存在';
            return $rs;
        }

        if($gift_info['type']==2){
            /* 守护 */
            $domain_guard = new Domain_Guard();
            $guard_info=$domain_guard->getUserGuard($uid,$liveuid);
            if($guard_info['type']==0){
                $rs['code']=1002;
                $rs['msg']='该礼物是守护专属礼物奥~';
                return $rs;
            }
        }

        $domain = new Domain_Live();
        $whichTenant= whichTenat($game_tenant_id);
       $result=$domain->sendGiftalonevutar($uid,$liveuid,$stream,$giftid,$giftcount);//虚拟用户



        if($result==1001){
            $rs['code']=1001;
            $rs['msg']='余额不足';
            return $rs;
        }else if($result==1002){
            $rs['code']=1002;
            $rs['msg']='礼物信息不存在';
            return $rs;
        }
        else if($result==1009){
            $rs['code'] = 1002;
            $language = DI()->config->get('language.newwork_error');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $rs['info'][0]['gifttoken']=$result['gifttoken'];
        $rs['info'][0]['level']=$result['level'];
        $rs['info'][0]['coin']=$result['coin'];

        unset($result['gifttoken']);

        DI()->redis  -> set($rs['info'][0]['gifttoken'],json_encode($result));


        return $rs;
    }

    /**
     * 是否有直播权限
     * @desc 是否有直播权限
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].coin 余额
     * @return string msg 提示信息
     */
    public function getLivePower() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $tenantId=getTenantId();
        $info = getTenantInfo($tenantId);
        $rs['info']['live_jurisdiction'] = $info['live_jurisdiction'];
        return  $rs;
    }
    /**
     * 修改值播状态
     * @desc 修改值播状态
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].type 房间类型
     * @return string info[0].type_msg 提示信息
     * @return string msg 提示信息
     */
    public function updateLivestatus() {
        $rs = array('code' => 0, 'msg' => '修改成功', 'info' => array());

        $liveuid=$this->liveuid;
        $old_status=$this->old_status;
        $status=$this->status;
        $stream=checkNull($this->stream);

        $domain = new Domain_Live();
        $info=$domain->updateLivestatus($liveuid,$stream,$old_status,$status);

        $stream_a=explode("_",$stream);
        $liveuid=$stream_a[0];
        $showid=$stream_a[1];
        if($info==1005){
            $rs['code'] = 1005;
            $rs['msg'] = '不存在满足条件的直播间，无法修改';
            return $rs;
        }
        $rs['info'][0]=$info;
        return $rs;
    }
    /**
     * 查询直播间是否存在
     * @desc 查询直播间是否存在
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].type 房间类型
     * @return string info[0].type_msg 提示信息
     * @return string msg 提示信息
     */
    public function confirmLivestatus() {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());

        $liveuid=$this->liveuid;
        $version=$this->version;
        $domain = new Domain_Live();
        $info=$domain->confirmLivestatus($liveuid,$version);
        $redis = connectionRedis();
        $last_thumb = $redis->hGet('author_thumb',$liveuid);
        $last_title = $redis->hGet('author_title',$liveuid);
        if($last_title == false){
            $last_title = '';
        }
        $rs['thumb']=$last_thumb;
        $rs['title']=$last_title;
        $stream= $info['stream'];
        if($info==2058){
            $rs['code'] = 2058;
            $rs['msg'] = codemsg('2058');
            return $rs;
        }
        if($info==2038){
            $rs['code'] = 2038;
            $rs['msg'] = codemsg('2038');
            return $rs;
        }

        $tenant_id = getTenantId();

        $configpri=getConfigPri();

        $userinfo=getUserInfo($liveuid);

        if($configpri['level_islimit']==1){
            if( $userinfo['level'] < $configpri['level_limit'] ){
                $rs['code']=1003;
                $rs['msg']='等级小于'.$configpri['level_limit'].'级，不能直播';
                return $rs;
            }
        }

        $user_live_info = $info;

        $PushpullModel = new Model_Pushpull();
        $PushpullInfo = $PushpullModel->getPushpullInfoWithId($info['pushpull_id']);
        $auth_token = '';
        if(isset($PushpullInfo['code']) && $PushpullInfo['code'] == 10){ // 10 声网推拉流服务商，获取鉴权token给前端
            $auth_token = $PushpullModel->ws_auth_token($PushpullInfo,$liveuid,$info['stream']);
        }
        if(isset($info['isvideo']) && $info['isvideo'] == 0){
            $info['push'] = $PushpullModel->PrivateKeyA('rtmp',$stream,1,$PushpullInfo);
            $info['pull'] = $PushpullModel->PrivateKeyA('rtmp',$stream,0,$PushpullInfo);
            $info['flvpull'] = $PushpullModel->PrivateKeyA('http',$stream.'.flv',0,$PushpullInfo);
            $info['m3u8pull'] = $PushpullModel->PrivateKeyA('http',$stream['stream'].'.m3u8',0,$PushpullInfo);
        }

        $info['auth_token'] = $auth_token;
        $info['appid'] = isset($PushpullInfo) && isset($PushpullInfo['appid']) ? $PushpullInfo['appid'] : '';

        $configpri=getConfigPri();
        $info['userlist_time']=$configpri['userlist_time'];
        $info['barrage_fee']=$configpri['barrage_fee'];
        $info['chatserver']=$configpri['chatserver'];

        $info['shut_time']=$configpri['shut_time'].'秒';
        $info['kick_time']=$configpri['kick_time'].'秒';
        $domain = new Domain_Live();
        $votestotal=$domain->getVotes($liveuid);
        $info['votestotal']=$votestotal;
        /* 游戏配置信息 */
        $info['game_switch']=$configpri['game_switch'];
        $info['game_bankerid']='0';
        $info['game_banker_name']='吕布';
        $info['game_banker_avatar']='';
        $info['game_banker_coin']=NumberFormat(10000000);
        $info['game_banker_limit']=$configpri['game_banker_limit'];
        /* 游戏配置信息 */


        /* 守护数量 */
        $domain_guard = new Domain_Guard();
        $guard_nums = $domain_guard->getGuardNums($liveuid);
        $info['guard_nums']=$guard_nums;

        $rs['info'][0]=$info;
        if($info['islive']==1){
            $rs['code'] = 0;
            $rs['msg'] = '该直播直播中';
            return $rs;
        }
        if($info['islive']==2){
            $rs['code'] = 0;
            $rs['msg'] = '该直播正在暂停中';
            return $rs;
        }



        return $rs;
    }
    /**
     * 获取配置的跟头信息
     * @desc 获取配置的跟头信息
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].type 房间类型
     * @return string info[0].type_msg 提示信息
     * @return string msg 提示信息
     */
    public function getBetinfo() {
        $rs = array('code' => 0, 'msg' => '修改成功', 'info' => array());


        $tenant_id=checkNull($this->tenant_id);

        $domain = new Domain_Live();
        $info=$domain->getBetinfo($tenant_id);


        $rs['info'][0]=$info;
        return $rs;
    }
    /**
     * @desc 根据主播id 返回主播端魅力值和，会员端魅力值
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].anchor_total 主播端魅力值
     * @return string info[0].member_total 会员端魅力值
     * @return string msg 提示信息
     */
    public function autoUpdatehot() { // 前端10s请求一次
        $uid = $this->uid;
        $liveuid = checkNull($this->liveuid);
        $rs = array('code' => 0, 'msg' => '更新成功', 'info' => array());

        $redis = connectionRedis();
        if($uid == $liveuid){ // 如果是主播
            $redis->hSet('live_api_heart_time',$liveuid,time());
            $user_live = DI()->notorm->users_live->where('uid=?',$liveuid)->fetchOne();
            if(!$user_live || !in_array($user_live['islive'], [1,2])){
                logapi(['liveuid'=>$liveuid, 'user_live'=>$user_live], '【用户已下播, 需要重新开播】');
                // 通知前端关播
                $redis->zAdd('user_closelive',1, json_encode(['uid'=>$liveuid]));
                $rs = array('code' => 800, 'msg' => codemsg(800), 'info' => array());
            }
        }

        $total = getLiveVotestotalDefault($liveuid);
        $domain = new Domain_Live();
        //$total= $redis->hGet("autovotes",$liveuid);
        $info['anchor_total']= intval($domain->getVotes($liveuid));
        $info['member_total']= $info['anchor_total']+$total;
        $rs['info'][0]=$info;
        return $rs;

    }
    /**
     * @desc 自动更新会员端魅力值
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */

    public function autoUpdatevotes() {
        $rs = array('code' => 0, 'msg' => '更新成功', 'info' => array());
        $domain = new Domain_Live();
        $domain->autoUpdatevotes();
        return $rs;

    }
    /**
     * @desc 关键字检测接口
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */

    public function keywordcheck() {

        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid=checkNull($this->uid);
        $content=checkNull($this->content);
        $domain = new Domain_Live();
        $result = $domain->keywordcheck($uid,$content);
        if($result == 1001){
            $rs['code'] = 1001;
            $rs['msg'] = '触发敏感信息';
            return $rs;
        }
        if($result == 1002){
            $rs['code'] = 1002;
            $rs['msg'] = '您重复发送敏感信息，已经被禁言';
            return $rs;
        }
        if($result == 1003){
            $rs['code'] = 1003;
            $rs['msg'] = '系统检测你重复发送敏感信息，你已经被踢出房间';
            return $rs;
        }

        return $rs;


    }
    /**
     * @desc 保存主播名片
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */

    public function addAnchornamecard() {

        $rs = array('code' => 0, 'msg' => '更新成功', 'info' => array());
        $uid=checkNull($this->uid);
        $declaration=checkNull($this->declaration);
        $position=checkNull($this->position);
        $limit_price=checkNull($this->limit_price);
        $telephone=checkNull($this->telephone);
        $is_open=checkNull($this->is_open);
        $token=checkNull($this->token);
        $type=checkNull($this->type);

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $domain = new Domain_Live();
        $result = $domain->addAnchornamecard($uid,$declaration,$position,$limit_price,$telephone,$is_open,$type);


        return $rs;


    }
    /**
     * @desc 获取主播名片
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */

    public function getAnchornamecard() {

        $rs = array('code' => 0, 'msg' => '更新成功', 'info' => array());
        $uid=checkNull($this->uid);
        $liveuid=checkNull($this->liveuid);
        $token=checkNull($this->token);

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_Live();

        $info = $domain->getAnchornamecards($uid,$liveuid);
       /* if($info == 1001){
            $rs['code'] = 1001;
            $rs['msg'] = '您的消费未达标，暂时无法查看主播名片';
            return $rs;
        }*/
        if($info == 1002){
            $rs['code'] = 1002;
            $rs['msg'] = '该主播未设置主播名片';
            return $rs;
        }
        $rs['info'][0]=$info;

        return $rs;


    }

    /**
     * 获取坐骑列表
     * @desc 用于获取坐骑列表
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 坐骑列表
     * @return string info.0.id 坐骑ID
     * @return string info.0.name 名称
     * @return string info.0.thumb 图片链接
     * @return string info.0.swf 动画链接
     * @return string info.0.swftime 动画时长
     * @return string info.0.needcoin 价格
     * @return string info.0.words 进场话术
     * @return string info.0.type 贵族专属: 0.否，1.是
     */
    public function getCarList(){
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $game_tenant_id = $this->game_tenant_id;
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700 && whichTenat($game_tenant_id)!=1) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }
        $domain = new Domain_Live();
        $info = $domain->getCarList($uid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 获取用户坐骑列表
     * @desc 用于获取用户坐骑列表
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 用户坐骑列表
     * @return string info.0.id 用户坐骑ID
     * @return string info.0.carid 坐骑ID
     * @return string info.0.endtime 到期时间
     * @return string info.0.endtime_format 到期时间（已格式化，如：2023-02-03 19:07:36）
     * @return int info.0.lefttime 剩余时间（单位：秒，小于等于0，则表示已经过期）
     * @return string info.0.status 是否启用
     * @return string info.0.name 名称
     * @return string info.0.thumb 图片链接
     * @return string info.0.swf 动画链接
     * @return string info.0.swftime 动画时长
     * @return string info.0.needcoin 价格
     * @return string info.0.words 进场话术
     */
    public function getUserCarList(){
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $game_tenant_id = $this->game_tenant_id;
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }
        $domain = new Domain_Live();
        $info = $domain->getUserCarList($uid);
       
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];

        return $rs;
    }

    /**
     * 购买坐骑
     * @desc 用于购买坐骑
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info
     */
    public function buyCar(){
        $rs = array('code' => 0, 'msg' => '购买成功', 'info' => array());

        $uid = $this->uid;
        $carid = $this->carid;
        $game_tenant_id = $this->game_tenant_id;
        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_Live();
        $whichTenant= whichTenat($game_tenant_id);
        if($whichTenant==1){
            $info=$domain->buyCar($uid,$carid);  //彩票租户
        }else{
            $info=$domain->buyCaralone($uid,$carid);//独立租户
        }

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 乘坐坐骑
     * @desc 用于乘坐坐骑
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info
     */
    public function rideCar(){
        $rs = array('code' => 0, 'msg' => '乘坐成功', 'info' => array());

        $uid = $this->uid;
        $id = $this->id;
        $token = checkNull($this->token);
        $game_tenant_id = $this->game_tenant_id;
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }
        $domain = new Domain_Live();
        $info = $domain->rideCar($uid,$id);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 获取用户房间类型
     * @desc 用于获取用户房间类型
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info
     */
    public  function getRoomtype(){
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid = $this->uid;

        $domain = new Domain_Live();
        $info = $domain->getroomtype($uid);

        $rs['info'][0] = $info;
        return $rs;
    }

    /**
     * 离开直播间
     * @desc 用于会员离开直播间调用
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     */
    public function leaveRoom(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid=$this->uid;
        $token=checkNull($this->token);
        $liveuid=$this->liveuid;
        $stream=checkNull($this->stream);
        $watchtime=checkNull($this->watchtime);

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_Live();
        $info = $domain->leaveRoom($uid,$liveuid,$stream,$watchtime);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }



    /**
     * 获取直播间游戏列表
     * @desc 用于获取直播间游戏列表
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info.0 列表信息
     */
    public function getLiveGameInfo(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid=$this->uid;
        $token=checkNull($this->token);
        $liveuid=$this->liveuid;

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_Live();
        $info = $domain->getLiveGameInfo($uid,$liveuid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 获取进入直播间公告
     * @desc 用于获取进入直播间公告
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 列表信息
     * @return string info.zh 中文
     * @return string info.en 英文
     * @return string info.vn 越南
     * @return string info.th 泰文
     * @return string info.my 马来西亚
     * @return string info.ind 印度尼西亚
     */
    public function getEnterroomNotice(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid=$this->uid;
        $language_id = $this->language_id;

        $domain = new Domain_Live();
        $info = $domain->getEnterroomNotice($uid,$language_id);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 获取贵族列表
     * @desc 用于获取贵族列表
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 列表信息
     * @return string info[0].level 等级
     * @return string info[0].name 名称
     * @return string info[0].medal 贵族勋章
     * @return string info[0].knighthoodcard 爵位牌
     * @return string info[0].special_effect 开通特效: 0.否，1.是
     * @return string info[0].golden_light 进房金光: 0.否，1.是
     * @return string info[0].exclu_custsevice 专属客服: 0.否，1.是
     * @return string info[0].avatar_frame 头像框
     * @return string info[0].upgrade_speed 升级加速
     * @return string info[0].broadcast 开通广播: 0.否，1.是
     * @return string info[0].pubchat_bgskin 公聊背景皮肤
     * @return string info[0].enter_stealth 进场隐身: 0.否，1.是
     * @return string info[0].exclu_car 专属座驾: 0.否，1.是
     * @return string info[0].exclu_car_nobleicon 贵族内图标
     * @return string info[0].exclu_car_bagicon 背包座驾图标
     * @return string info[0].exclu_car_swf SVG动画
     * @return string info[0].exclu_car_swftime 动画时长
     * @return string info[0].exclu_car_words 进场话术
     * @return string info[0].ranking_stealth 榜单隐身: 0.否，1.是
     * @return string info[0].prevent_mute 防禁言: 0.否，1.是
     * @return string info[0].price 开通价格
     * @return string info[0].renewal_price 续费价格
     * @return string info[0].handsel 开通赠送
     * @return string info[0].renewal_handsel 续费赠送
     * @return string info[0].exclu_allnum 专属特权（所有）
     * @return string info[0].exclu_currnum 专属特权（已开通）
     */
    public function getNobleList(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid=$this->uid;
        $token=checkNull($this->token);
        $language_id = $this->language_id;

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_Live();
        $info = $domain->getNobleList($uid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 开通贵族或续费
     * @desc 用于开通贵族或续费
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info
     */
    public function buyNoble(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid = $this->uid; delUserNoble($uid);
        $liveuid = $this->liveuid;
        $level = $this->level;
        $type = $this->type;
        $game_tenant_id = $this->game_tenant_id;
        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_Live();
        $whichTenant= whichTenat($game_tenant_id);
        if($whichTenant==1){
            $info=$domain->buyNoble($uid,$liveuid,$level,$type);  //彩票租户
        }else{
            $info=$domain->buyNoblealone($uid,$liveuid,$level,$type);//独立租户
        }

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 获取贵族配置
     * @desc 用于获取贵族配置
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info
     * @return string info[0].status 贵族开关：0.关闭，1.开启
     * @return string info[0].details 贵族说明（FAQ）
     */
    public function getNobleSetting(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid = $this->uid; delUserNoble($uid);
        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_Live();
        $info=$domain->getNobleSetting($uid);  //彩票租户

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 定时处理主播在线状态
     * @desc 用于定时处理主播在线状态
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info
     */
    public function ExeculationLivestatus(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $domain = new Domain_Live();
        $info = $domain->ExeculationLivestatus();  //彩票租户

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 直播暂停超时后自动关播
     * @desc 用于直播暂停超时后自动关播
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info
     */
    public function LiveTimeOut(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $domain = new Domain_Live();
        $info = $domain->LiveTimeOut();

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }


    /**
     * 直播间可更改房间类型
     * @desc 直播间可更改房间类型
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info
     */
    public function updateLivetype(){
        $rs = array('code' => 0, 'msg' => '更改成功', 'info' => array());

        $uid = $this->uid;
        $stream = $this->stream;
        $type = $this->type;
        $type_val = $this->type_val;
        $tryWatchTime = $this->tryWatchTime;

        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }
        if( $type==1 && $type_val=='' ){
            $rs['code']=1001;
            $rs['msg']='密码不能为空';
            return $rs;
        }else if($type > 1 && $type_val<=0){
            $rs['code']=1002;
            $rs['msg']='价格不能小于等于0';
            return $rs;
        }
        $configpri=getConfigPri();$configpri=getConfigPri();
        // 门票房间限额,前端及后台设置门票房间时，门票限额
        if($type == 2 && ($type_val < $configpri['tickets_limit_min'] || $type_val > $configpri['tickets_limit_max'])){
            $rs['code']=2079;
            $rs['msg']=codemsg('2079');
            $rs['info']=[$configpri['tickets_limit_min'].' - '.$configpri['tickets_limit_max']];
            return $rs;
        }
        $domain = new Domain_Live();

        $info=$domain->updateLivetype($uid,$stream,$type,$type_val,$tryWatchTime);
        if($info==1003){
            $rs['code']=1003;
            $rs['msg']='不存在满足条件的直播间，无法修改!';
            return $rs;
        }
        if($info==0){
            $rs['msg'] = '更新成功，没有改变房间类型';
        }
        return $rs;
    }

    /**
     * 礼物所有动画返回
     * @desc 用于获取礼物列表
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].coin 余额
     * @return array info[0].giftlist 礼物列表
     * @return string info[0].giftlist[].id 礼物ID
     * @return string info[0].giftlist[].type 礼物类型
     * @return string info[0].giftlist[].mark 礼物标识
     * @return string info[0].giftlist[].giftname 礼物名称
     * @return string info[0].giftlist[].needcoin 礼物价格
     * @return string info[0].giftlist[].gifticon 礼物图片
     * @return string msg 提示信息
     */
    public function getGiftall() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $key='getGiftall_'.getTenantId();
        $giftlist=getcaches($key);

        if(!$giftlist){
            $domain = new Domain_Live();
            $giftlist=$domain->getGiftList();
            setcaches($key, $giftlist, 60*60*24*7);
        }
        $domain = new Domain_Live();
        $carlist = $domain->getCarList(1);


        $rs['info'][0]['giftlist']=$giftlist;
        $rs['info'][0]['carlist']=$carlist['info'] ? $carlist['info'] : $rs['info'];

        return $rs;
    }


}
