<?php
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;
class Api_Home extends PhalApi_Api {  

	public function getRules() {
		return array(
			'getHot' => array(
				'p' => array('name' => 'p', 'type' => 'int', 'desc' => '页数'),
                'liveclassid' => array('name' => 'liveclassid', 'type' => 'int','min'=>1, 'desc' => '直播分类id'),
                'ishot' => array('name' => 'ishot', 'type' => 'int', 'desc' => '热门主播，固定传1 （如果有该值，不需要传liveclassid）'),
                'isrecommend' => array('name' => 'isrecommend', 'type' => 'int', 'desc' => '推荐主播，固定传1 （如果有该值，不需要传liveclassid）'),
			),
			
			'getFollow' => array(
				'uid' => array('name' => 'uid', 'type' => 'int','min'=>1,'require' => true, 'desc' => '用户ID'),
				'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),

			),
			
			'getNew' => array(
                'lng' => array('name' => 'lng', 'type' => 'string', 'desc' => '经度值'),
                'lat' => array('name' => 'lat', 'type' => 'string','desc' => '纬度值'),
				'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),

            ),
			
			'search' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'min'=>1 ,'desc' => '用户ID'),
				'key' => array('name' => 'key', 'type' => 'string', 'default'=>'' ,'desc' => '用户ID'),
				'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),

			),
			
			'getNearby' => array(
                'lng' => array('name' => 'lng', 'type' => 'string', 'desc' => '经度值'),
                'lat' => array('name' => 'lat', 'type' => 'string','desc' => '纬度值'),
				'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),

            ),
			
			'getRecommend' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'min'=>1 ,'desc' => '用户ID'),

			),
			
			'attentRecommend' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'min'=>1 ,'desc' => '用户ID'),
				'touid' => array('name' => 'touid', 'type' => 'string', 'require' => true, 'min'=>1 ,'desc' => '关注用户ID，多个用,分隔'),
			),
            'profitList'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int','min'=>1,'require' => true, 'desc' => '用户ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
                'type' => array('name' => 'type', 'type' => 'string', 'default'=>'day' ,'desc' => '参数类型，day表示日榜，week表示周榜，month代表月榜，total代表总榜'),
            ),

            
            'consumeList'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int','min'=>1,'require' => true, 'desc' => '用户ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
                'type' => array('name' => 'type', 'type' => 'string', 'default'=>'day' ,'desc' => '参数类型，day表示日榜，week表示周榜，month代表月榜，total代表总榜'),
                'touid' => array('name' => 'touid', 'type' => 'int' ,'desc' => '主播id'),
            ),
            'consumeListall'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int','min'=>1,'require' => true, 'desc' => '用户ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
                'type' => array('name' => 'type', 'type' => 'string', 'default'=>'day' ,'desc' => '参数类型，day表示日榜，week表示周榜，month代表月榜，total代表总榜'),
            ),
            
            'getClassLive'=>array(
                'liveclassid' => array('name' => 'liveclassid', 'type' => 'int', 'default'=>'0' ,'desc' => '直播分类ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),

            ),
            'getLive'=>array(
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
            ),
            'getBetinfo' => array(
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
            ),
            'recommendRoom' => array(
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
            ),
            'getAutotask' => array(
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
            ),
            'shareCollcet' => array(
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
            ),
            'basicsalaryCollcet' => array(
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
            ),
             'consumptionCollcet' => array(
                 'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
             ),
            'appHeartbeat' => array(
                'version' => array('name' => 'version', 'type' => 'string', 'require' => true, 'desc' => 'App版本号'),
                'client' => array('name' => 'client', 'type' => 'int', 'require' => true, 'desc' => '客户端：1 PC，2 H5，3 Android，4 iOS'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'min'=>1 ,'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'hometest' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'min'=>1 ,'desc' => '用户ID'),
            ),
            "getGroupInfo" => array(
                "page"=>array("name"=>"page","type"=>"int","min"=>1,"require"=> false,"desc"=>"页数"),
                "pagesize"=>array("name"=>"pagesize","type"=>"int","min"=>1,"require"=> false,"desc"=>"页码"),
            )
		);
	}
	
    /**
     * 配置信息
     * @desc 用于获取配置信息
     * @return int code 操作码，0表示成功
     * @return array info 
     * @return array info[0] 配置信息

     * @return  array  vip_model Vip模式 （1 为购买模型，老的不用管 ，2 为保证金模式）
     * @return array info[0].cash_account_type 提现账号类型：1.银行卡，2.USDT
     * @return string info[0].cash_network_type 提现网络类型：TRC20，ERC20
     * @return string msg 提示信息
     */
    public function getConfig() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $info=getFrontConfig();
        $info_pri = getConfigPri();

        
        $list = getLiveClass();

        
        $level= getLevelList();
        
        foreach($level as $k=>$v){
            unset($v['experience']);
            unset($v['level_up']);
            unset($v['addtime']);
            unset($v['id']);
            unset($v['levelname']);
            $level[$k]=$v;
        }
        
        $levelanchor= getLevelAnchorList();
        
        foreach($levelanchor as $k=>$v){
            unset($v['experience']);
            unset($v['addtime']);
            unset($v['id']);
            unset($v['levelname']);
            $levelanchor[$k]=$v;
        }
        
        $info['liveclass']=$list;

        
        $info['level']=$level;
        
        $info['levelanchor']=$levelanchor;
        
        $info['tximgfolder']=isset($info_pri['tximgfolder'])?$info_pri['tximgfolder']:'';//腾讯云图片存储目录
        $info['txvideofolder']=isset($info_pri['txvideofolder'])?$info_pri['txvideofolder']:'';//腾讯云视频存储目录
        $info['txcloud_appid']=isset($info_pri['txcloud_appid'])?$info_pri['txcloud_appid']:'';//腾讯云视频APPID
        $info['txcloud_region']=isset($info_pri['txcloud_region'])?$info_pri['txcloud_region']:'';//腾讯云视频地区
        $info['txcloud_bucket']=isset($info_pri['txcloud_bucket'])?$info_pri['txcloud_bucket']:'';;//腾讯云视频存储桶
        $info['cloudtype']=isset($info_pri['cloudtype'])?$info_pri['cloudtype']:'';;//视频云存储类型
		$info['qiniu_domain']=isset($info_pri['qiniu_domain'])?$info_pri['qiniu_domain']:'';;//七牛云存储空间地址（后台配置）
        $info['video_audit_switch']=isset($info_pri['video_audit_switch'])?$info_pri['video_audit_switch']:'';; //视频审核是否开启
        $info['cust_service_addr']=isset($info_pri['cust_service_addr'])?$info_pri['cust_service_addr']:'';; //视频审核是否开启
        $info['turntable_desc']  = addslashes(htmlspecialchars_decode($info_pri['turntable_desc']));
        $rs['info'][0] = $info;

        return $rs;
    }	

    /**
     * 登录方式开关信息
     * @desc 用于获取登录方式开关信息
     * @return int code 操作码，0表示成功
     * @return array info 
     * @return array info[0].login_type 开启的登录方式
     * @return string info[0].login_type[][0] 登录方式标识

     * @return string msg 提示信息
     */
    public function getLogin() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $info = getConfigPub();
        $rs['info'][0]['login_type'] = $info['login_type'];

        return $rs;
    }		
	
    /**
     * 获取热门主播
     * @desc 用于获取首页热门主播
     * @return int code 操作码，0表示成功
     * @return array info 
     * @return array info[0]['slide'] 
     * @return string info[0]['slide'][].slide_pic 图片
     * @return string info[0]['slide'][].slide_url 链接
     * @return array info[0]['list'] 热门直播列表
     * @return string info[0]['list'][].uid 主播id
     * @return string info[0]['list'][].avatar 主播头像
     * @return string info[0]['list'][].avatar_thumb 头像缩略图
     * @return string info[0]['list'][].user_nicename 直播昵称
     * @return string info[0]['list'][].title 直播标题
     * @return string info[0]['list'][].city 主播位置
     * @return string info[0]['list'][].stream 流名
     * @return string info[0]['list'][].pull rtmp播流地址
     * @return string info[0]['list'][].flvpull flv播流地址
     * @return string info[0]['list'][].m3u8pull hls播流地址
     * @return string info[0]['list'][].nums 人数
     * @return string info[0]['list'][].thumb 直播封面
     * @return string info[0]['list'][].level_anchor 主播等级
     * @return string info[0]['list'][].game 游戏名称
     * @return string info[0]['list'][].type 直播类型
     * @return string info[0]['list'][].goodnum 靓号
     * @return string msg 提示信息
     */
    public function getHot() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $liveclassid = $this->liveclassid;
        $ishot = $this->ishot;
        $isrecommend = $this->isrecommend;
        $tenantId= getTenantId();

        $domain = new Domain_Home();
		$key1=$tenantId.'_'.'getSlide';
		$slide=getcaches($key1);
		if(!$slide){
			$slide = $domain->getSlide($tenantId,'recom_carousel');
			setcaches($key1,$slide,60*60*24*7);
		}

        $list = $domain->getHot($this->p,$tenantId,$liveclassid,$ishot,$isrecommend);

        $rs['info'][0]['slide'] = $slide;
        $rs['info'][0]['list'] = $list;

        return $rs;
    }

    /**
     * 获取热门主播列表(不分页)
     * @desc 用于获取首页热门主播(不分页)
     * @return int code 操作码，0表示成功
     * @return array info
     * @return array info[0]['slide']
     * @return string info[0]['slide'][].slide_pic 图片
     * @return string info[0]['slide'][].slide_url 链接
     * @return array info[0]['list'] 热门直播列表
     * @return string info[0]['list'][].uid 主播id
     * @return string info[0]['list'][].avatar 主播头像
     * @return string info[0]['list'][].avatar_thumb 头像缩略图
     * @return string info[0]['list'][].user_nicename 直播昵称
     * @return string info[0]['list'][].title 直播标题
     * @return string info[0]['list'][].city 主播位置
     * @return string info[0]['list'][].stream 流名
     * @return string info[0]['list'][].pull rtmp播流地址
     * @return string info[0]['list'][].flvpull flv播流地址
     * @return string info[0]['list'][].m3u8pull hls播流地址
     * @return string info[0]['list'][].nums 人数
     * @return string info[0]['list'][].thumb 直播封面
     * @return string info[0]['list'][].level_anchor 主播等级
     * @return string info[0]['list'][].game 游戏名称
     * @return string info[0]['list'][].type 直播类型
     * @return string info[0]['list'][].goodnum 靓号
     * @return string msg 提示信息
     */
    public function getHotList() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $domain = new Domain_Home();
        $tenantId= getTenantId();
        $key1='getSlide';
        $slide=getcaches($key1);
        if(!$slide){
            $slide = $domain->getSlide($tenantId,'recom_carousel');
            setcaches($key1,$slide);
        }

        $key2="getHotList";
        $list=getcaches($key2);
        if(!$list){
            $list = $domain->getHotList($tenantId);
            setCaches($key2,$list,2);
        }

        $rs['info'][0]['slide'] = $slide;
        $rs['info'][0]['list'] = $list;

        return $rs;
    }

    /**
     * 获取关注主播列表
     * @desc 用于获取用户关注的主播的直播列表
     * @return int code 操作码，0表示成功
     * @return string info[0]['title'] 提示标题
     * @return string info[0]['des'] 提示描述
     * @return array info[0]['list'] 直播列表
     * @return string info[0]['list'][].uid 主播id
     * @return string info[0]['list'][].avatar 主播头像
     * @return string info[0]['list'][].avatar_thumb 头像缩略图
     * @return string info[0]['list'][].user_nicename 直播昵称
     * @return string info[0]['list'][].title 直播标题
     * @return string info[0]['list'][].city 主播位置
     * @return string info[0]['list'][].stream 流名
     * @return string info[0]['list'][].pull 播流地址
     * @return string info[0]['list'][].nums 人数
     * @return string info[0]['list'][].thumb 直播封面
     * @return string info[0]['list'][].level_anchor 主播等级
     * @return string info[0]['list'][].game 游戏名称
     * @return string info[0]['list'][].type 直播类型
     * @return string info[0]['list'][].goodnum 靓号
     * @return string info[0]['list'][].islive 是否在直播 0-没有直播 1-正在直播
     * @return string msg 提示信息
     */
    public function getFollow() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $domain = new Domain_Home();
        $info = $domain->getFollow($this->uid,$this->p, getTenantId());


        $rs['info'][0] = $info;

        return $rs;
    }

    /**
     * 获取最新主播
     * @desc 用于获取首页最新开播的主播列表
     * @return int code 操作码，0表示成功
     * @return array info 主播列表
     * @return string info[].uid 主播id
     * @return string info[].avatar 主播头像
     * @return string info[].avatar_thumb 头像缩略图
     * @return string info[].user_nicename 直播昵称
     * @return string info[].title 直播标题
     * @return string info[].city 主播位置
     * @return string info[].stream 流名
     * @return string info[].pull 播流地址
     * @return string info[].nums 人数
     * @return string info[].distance 距离
     * @return string info[].thumb 直播封面
     * @return string info[].level_anchor 主播等级
     * @return string info[].game 游戏名称
     * @return string info[].type 直播类型
     * @return string info[].goodnum 靓号
     * @return string msg 提示信息
     */
    public function getNew() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$lng=checkNull($this->lng);
		$lat=checkNull($this->lat);
		$p=checkNull($this->p);
		$tenantId=checkNull( getTenantId());
		if(!$p){
			$p=1;
		}

		$key=$tenantId.'_'.'getNew_'.$p;
		$info=$this->getcaches($key);
		if(!$info){
			$domain = new Domain_Home();
			$info = $domain->getNew($lng,$lat,$p);

			$this->setCaches($key,$info,2);
		}
		
        $rs['info'] = $info;

        return $rs;
    }		
		
	/**
     * 搜索
     * @desc 用于首页搜索会员
     * @return int code 操作码，0表示成功
     * @return array info 会员列表
     * @return string info[].id 用户ID
     * @return string info[].user_nicename 用户昵称
     * @return string info[].avatar 头像
     * @return string info[].sex 性别
     * @return string info[].signature 签名
     * @return string info[].level 等级
     * @return string info[].isattention 是否关注，0未关注，1已关注
     * @return string msg 提示信息
     */
    public function search() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=checkNull($this->uid);
		$key=checkNull($this->key);
		$p=checkNull($this->p);
		$tenantId=checkNull( getTenantId());
		if($key==''){
			$rs['code'] = 1001;
			$rs['msg'] = "请填写关键词";
			return $rs;
		}
		
		if(!$p){
			$p=1;
		}
		
        $domain = new Domain_Home();
        $info = $domain->search($uid,$key,$p,$tenantId);

        $rs['info'] = $info;

        return $rs;
    }	
	
    /**
     * 获取附近主播
     * @desc 用于获取附近开播的主播列表
     * @return int code 操作码，0表示成功
     * @return array info 主播列表
     * @return string info[].uid 主播id
     * @return string info[].avatar 主播头像
     * @return string info[].avatar_thumb 头像缩略图
     * @return string info[].user_nicename 直播昵称
     * @return string info[].title 直播标题
     * @return string info[].province 省份
     * @return string info[].city 主播位置
     * @return string info[].stream 流名
     * @return string info[].pull 播流地址
     * @return string info[].nums 人数
     * @return string info[].distance 距离
     * @return string info[].thumb 直播封面
     * @return string info[].level_anchor 主播等级
     * @return string info[].game 游戏名称
     * @return string info[].type 直播类型
     * @return string info[].goodnum 靓号
     * @return string msg 提示信息
     */
    public function getNearby() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$lng=checkNull($this->lng);
		$lat=checkNull($this->lat);
		$p=checkNull($this->p);
		$tenantId=checkNull( getTenantId());
		
		if($lng==''){
			return $rs;
		}
		
		if($lat==''){
			return $rs;
		}
		
		if(!$p){
			$p=1;
		}
		
		$key=$tenantId.'_'.'getNearby_'.$lng.'_'.$lat.'_'.$p;
		$info=getcaches($key);
		if(!$info){
			$domain = new Domain_Home();
			$info = $domain->getNearby($lng,$lat,$p,$tenantId);

			setcaches($key,$info,2);
		}
		
        $rs['info'] = $info;

        return $rs;
    }	
	
	/**
     * 推荐主播
     * @desc 用于显示推荐主播
     * @return int code 操作码，0表示成功
     * @return array info 会员列表
     * @return string info[].id 用户ID
     * @return string info[].user_nicename 用户昵称
     * @return string info[].avatar 头像
     * @return string info[].fans 粉丝数
     * @return string info[].isattention 是否关注，0未关注，1已关注
     * @return string msg 提示信息
     */
    public function getRecommend() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=checkNull($this->uid);
		$tenantId=checkNull( getTenantId());
		
		$key=$tenantId.'_'.'getRecommend';
		$info=getcaches($key);
		if(!$info){
			$domain = new Domain_Home();
			$info = $domain->getRecommend($tenantId);

			setcaches($key,$info,60*10);
		}
		
		foreach($info as $k=>$v){
			$info[$k]['isattention']=(string)isAttention($uid,$v['id']);
		}

        $rs['info'] = $info;

        return $rs;
    }	
	
	/**
     * 关注推荐主播
     * @desc 用于关注推荐主播
     * @return int code 操作码，0表示成功
     * @return array info 
     * @return string msg 提示信息
     */
    public function attentRecommend() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=checkNull($this->uid);
		$touid=checkNull($this->touid);


		$domain = new Domain_Home();
		$info = $domain->attentRecommend($uid,$touid);

        //$rs['info'] = $info;

        return $rs;
    }	
	
	
	/* IOS上架单用 */
	public function iosShelves(){
		return '1';
	}

    /**
     * 收益榜单
     * @desc 获取收益榜单
     * @return int code 操作码 0表示成功
     * @return string msg 提示信息 
     * @return array info
     * @return string info[0]['user_nicename'] 主播昵称
     * @return string info[0]['avatar_thumb'] 主播头像
     * @return string info[0]['totalcoin'] 主播钻石数
     * @return string info[0]['uid'] 主播id
     * @return string info[0]['levelAnchor'] 主播等级
     * @return string info[0]['isAttention'] 是否关注主播 0 否 1 是
     **/
    
    public function profitList(){
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $uid=checkNull($this->uid);
        $p=checkNull($this->p);
        $type=checkNull($this->type);
        $domain=new Domain_Home();
        $res=$domain->profitList($uid,$type,$p);

        $rs['info']=$res;
        return $rs;
    }

    /**
     * 消费榜单
     * @desc 获取消费榜单
     * @return int code 操作码 0表示成功
     * @return string msg 提示信息 
     * @return array info
     * @return string info[0]['user_nicename'] 用户昵称
     * @return string info[0]['avatar_thumb'] 用户头像
     * @return string info[0]['totalcoin'] 用户钻石数
     * @return string info[0]['uid'] 用户id
     * @return string info[0]['levelAnchor'] 用户等级
     * @return string info[0]['isAttention'] 是否关注用户 0 否 1 是
     **/
    
    public function consumeList(){
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $uid=checkNull($this->uid);
        $p=checkNull($this->p);
        $type=checkNull($this->type);
        $touid=checkNull($this->touid);
        $domain=new Domain_Home();
        $res=$domain->consumeList($uid,$type,$p,$touid);

        $rs['info']=$res;
        return $rs;
    }
    /**
     * 土豪榜  总消费榜单
     * @desc 土豪榜 获取总消费榜单
     * @return int code 操作码 0表示成功
     * @return string msg 提示信息
     * @return array info
     * @return string info[0]['user_nicename'] 用户昵称
     * @return string info[0]['avatar_thumb'] 用户头像
     * @return string info[0]['totalcoin'] 用户钻石数
     * @return string info[0]['uid'] 用户id
     * @return string info[0]['levelAnchor'] 用户等级
     * @return string info[0]['isAttention'] 是否关注用户 0 否 1 是
     **/

    public function consumeListall(){
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $uid=checkNull($this->uid);
        $p=checkNull($this->p);
        $type=checkNull($this->type);
        $domain=new Domain_Home();
        $res=$domain->consumeListall($uid,$type,$p);

        $rs['info']=$res;
        return $rs;
    }


    /**
     * 获取分类下的直播
     * @desc 获取分类下的直播
     * @return int code 操作码 0表示成功
     * @return string msg 提示信息 
     * @return array info
     * @return string info[].uid 主播id
     * @return string info[].avatar 主播头像
     * @return string info[].avatar_thumb 头像缩略图
     * @return string info[].user_nicename 直播昵称
     * @return string info[].title 直播标题
     * @return string info[].city 主播位置
     * @return string info[].stream 流名
     * @return string info[].pull 播流地址
     * @return string info[].nums 人数
     * @return string info[].distance 距离
     * @return string info[].thumb 直播封面
     * @return string info[].level_anchor 主播等级
     * @return string info[].game 游戏名称
     * @return string info[].type 直播类型
     * @return string info[].goodnum 靓号
     **/
    
    public function getClassLive(){
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $liveclassid=checkNull($this->liveclassid);
        $p=checkNull($this->p);
        $tenantId=checkNull( getTenantId());
        
        if(!$liveclassid){
            return $rs;
        }
        $domain=new Domain_Home();
        $res=$domain->getClassLive($liveclassid,$p,$tenantId);

        $rs['info']=$res;
        return $rs;
    }
    /**
     * 根据租户id获取正在直播的数据
     * @desc 获取该租户下面的直播
     * @return int code 操作码 0表示成功
     * @return string msg 提示信息
     * @return array info
     * @return string info[].uid 主播id
     * @return string info[].avatar 主播头像
     * @return string info[].avatar_thumb 头像缩略图
     * @return string info[].user_nicename 直播昵称
     * @return string info[].title 直播标题
     * @return string info[].city 主播位置
     * @return string info[].stream 流名
     * @return string info[].pull 播流地址
     * @return string info[].nums 人数
     * @return string info[].distance 距离
     * @return string info[].thumb 直播封面
     * @return string info[].level_anchor 主播等级
     * @return string info[].game 游戏名称
     * @return string info[].type 直播类型
     * @return string info[].goodnum 靓号
     **/



    public function getLive(){
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $tenantId=getTenantId();
        $domain=new Domain_Home();
        $res=$domain->getLive($tenantId);

        $rs['info']=$res;
        return $rs;
    }
    /**
     * 获取推荐直播间
     * @desc 获取推荐直播间
     * @return int code 操作码 0表示成功
     * @return string msg 提示信息
     * @return array info
     * @return string info[].liveuid 主播id
     * @return string info[].stream 直播流
     **/



    public function recommendRoom(){
        $rs = array('code' => 0, 'msg' => '获取推荐直播间成功', 'info' => array());
        $tenantId=getTenantId();

        $domain=new Domain_Home();
        $res=$domain->recommendRoom($tenantId);

        $rs['info']=$res;
        return $rs;
    }
    /**
     * 获取配置的跟投信息
     * @desc 获取配置的跟投信息
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].type 房间类型
     * @return string info[0].type_msg 提示信息
     * @return string msg 提示信息
     */
    public function getBetinfo() {
        $rs = array('code' => 0, 'msg' => '跟投信息获取成功', 'info' => array());
        $tenantId=getTenantId();

        $domain = new Domain_Home();
        $info=$domain->getBetinfo($tenantId);


        $rs['info'][0]=$info;
        return $rs;
    }

    /**
     * 获取正在推送的定时任务信息
     * @desc 获取正在推送的定时任务信息
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info 正在推送的任务类型
     * @return string msg 提示信息
     */
    public function getAutotask() {
        $rs = array('code' => 0, 'msg' => '定时任务ID获取成功');
        $tenantId=getTenantId();
        $info_pri = getConfigPri();

        $domain = new Domain_Home();
        $info=$domain->getAutotask($tenantId);
        $rs['taskid']=$info;
        $rs['sockettype']=$info_pri['socket_type'];

        $ismore = strpos($rs['taskid'], ',');
        try {
            $client = new Client(new Version1X('http://47.243.178.149:2021'));
            //TODO 定义报文格式
            if($ismore){
                $taskid = explode(',',$rs['taskid']);

                foreach ($taskid as $key=>$value){
                    $task = array(
                        'id'  =>$value,
                        'status'=>1,
                        'sockettype'=>$info_pri['socket_type']
                    );

                    $client->initialize();
                    $client->emit('autosend', json_encode($task));
                    $client->close();
                }

            }else{
                $task = array(
                    'id'  =>$info['id'],
                    'status'=>1,
                    'sockettype'=>$info_pri['socket_type']
                );
                $client->initialize();
                $client->emit('autosend', json_encode($task));
                $client->close();
            }


        }catch (Exception $e){

            $rs['code']=1001;
            $rs['msg']='发送失败,连接推送服务器异常';
        }
        return $rs;
    }

    /**
     * 分成报表统计
     * @desc 获取主播，消费租户，平台租户 分成统计
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info
     * @return string msg 提示信息
     */
    public  function  shareCollcet(){
        $rs = array('code' => 0, 'msg' => '数据写入成功', 'info' => array());
        $domain=new Domain_Home();
        $res=$domain->shareCollcet();

        $rs['info']=$res;
        return $rs;
    }
    /**
     * 底薪统计
     * @desc 获取主播底薪统计
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info
     * @return string msg 提示信息
     */
    public  function  basicsalaryCollcet(){
        $rs = array('code' => 0, 'msg' => '数据写入成功', 'info' => array());
        $domain=new Domain_Home();
        $res=$domain->basicsalaryCollcet();

        $rs['info']=$res;
        return $rs;
    }


    /**
     * 会员消费统计
     * @desc 会员消费统计
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info
     * @return string msg 提示信息
     */
    public  function  consumptionCollcet(){
        $rs = array('code' => 0, 'msg' => '数据写入成功', 'info' => array());
        $domain=new Domain_Home();
        $res=$domain->consumptionCollcet();

        $rs['info']=$res;
        return $rs;
    }

    /**
     * app心跳
     * @desc app心跳
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].type 消息类型：1.整站公告，2.个人通知
     * @return object info[0].data 消息数据
     * @return string info[0].data.ct 消息内容
     * @return string msg 提示信息
     */
    public function appHeartbeat(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $version = $this->version;
        $client = $this->client;
        $uid = $this->uid;
        $token = checkNull($this->token);
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_Home();
        $result = $domain->appHeartbeat($version, $client, $uid);
        $rs['code'] = $result['code'] ? $result['code'] : $rs['code'];
        $rs['msg'] = $result['msg'] ? $result['msg'] : $rs['msg'];
        $rs['info'] = $result['info'] ? $result['info'] : $rs['info'];

        return $rs;
    }
    /**
     * hometest 测试
     * @desc app测试
     * @return int code 操作码，0表示成功

     */
    public function hometest(){
        $uid = $this->uid;
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        delUserInfoCache($uid);

        return $rs;
    }

    public function getGroupInfo(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $page = intval($this->page);
        $pageSize = intval($this->pagesize);
        if($page<1){
            $page = 1;
        }
        if($pageSize<1){
            $pageSize = 20;
        }
        $offset = ($page-1)*$pageSize;
        $count = DI()->notorm->groups_config->where("status=1 and is_deleted=0")->count();
        $data = DI()->notorm->groups_config->select("id,url,icon,status,created_at,updated_at,`desc`")->where("status=1 and is_deleted=0")->limit($offset,$pageSize)->order("id desc")->fetchAll();
        $rs['info'] = $data;
        return $rs;
    }

    //获取轮播域名
    public function getDomain(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $data = DI()->notorm->domain_config->where("is_view=1 and is_reachable=1")->select("title")->fetchAll();
        if(!empty($data)){
            foreach($data as $v){
                $rs['info'][] = $v['title'];
            }
        }
        return $rs;
    }

} 
