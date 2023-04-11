<?php
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;
class Api_Message extends PhalApi_Api {

	public function getRules() {
		return array(
			'getList' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string',  'require' => true, 'desc' => '用户Token'),
				'p' => array('name' => 'p', 'type' => 'int','default'=>1, 'desc' => '页码'),
			),
            'sendGameMsg'=>array(
                'toliverooms' => array('name' => 'toliverooms', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '推送目标直播间ID，支持多个,逗号分隔'),
                'msgtype' => array('name' => 'msgtype', 'type' => 'int',  'require' => true, 'desc' => '1、下注消息；2、中奖消息；3、开奖消息(预留)'),
                'msgid' => array('name' => 'msgid', 'type' => 'string','require'=>true, 'desc' => '消息id'),
                'msgcontent' => array('name' => 'msgcontent', 'type' => 'string','require'=>true, 'desc' => '消息内容文案'),
                'msgactiontype' => array('name' => 'msgactiontype', 'type' => 'int','require'=>false, 'desc' => '消息后续动作  1、button；2、link'),
                'buttoncontent' => array('name' => 'buttoncontent', 'type' => 'string','require'=>false, 'desc' => 'button或link显示文案'),
                'buttonlinkurl' => array('name' => 'buttonlinkurl', 'type' => 'string','require'=>false, 'desc' => 'button或link链接地址或回调参数   消息的ID通过此参数进行传递   类似这样格式   http://h5.com/showmsg?msgid=1847173838'),
                'token' => array('name' => 'token', 'type' => 'string',  'require' => true, 'desc' => '鉴权使用'),
                'nickname' => array('name' => 'nickname', 'type' => 'string','require'=>false, 'desc' => '用户'),
                'playname' => array('name' => 'playname', 'type' => 'string','require'=>false, 'desc' => '彩种'),
                'money' => array('name' => 'money', 'type' => 'string','require'=>false, 'desc' => '金额'),
            ),

		);
	}
	
	/**
	 * 系统消息
	 * @desc 用于 获取系统消息
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0] 支付信息
	 * @return string msg 提示信息
	 */
	public function getList() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=$this->uid;
		$token=checkNull($this->token);
		$p=checkNull($this->p);
        
        if($p<1){
			$p=1;
		}
        
        
        $checkToken=checkToken($uid,$token);
		if($checkToken==700){
			$rs['code'] = $checkToken;
			$rs['msg'] = '您的登陆状态失效，请重新登陆！';
			return $rs;
		}
		
		$domain = new Domain_Message();
		$list = $domain->getList($uid,$p);
		
        foreach($list as $k=>$v){
            $v['addtime']=date('Y-m-d H:i',$v['addtime']);
            $list[$k]=$v;
        }

		
		$rs['info']=$list;
		return $rs;			
	}

    /**
     * 发送游戏消息
     * @desc 推送游戏的开奖,跟投,中奖等消息
     * @return int code 操作码，0表示成功
     * @return array info 返回不在直播中的房间号数组
     * @return string msg 提示信息
     */
    public function sendGameMsg(){
        $rs = array('code' => 0, 'msg' => '发送成功', 'info' => array());

        $config=getConfigPri();
        $msgtype=checkNull($this->msgtype);
        $msgid=checkNull($this->msgid);
        $msgcontent=checkNull($this->msgcontent);
        $msgactiontype=checkNull($this->msgactiontype);
        $buttoncontent=checkNull($this->buttoncontent);
        $buttonlinkurl=checkNull($this->buttonlinkurl);
        $playname=checkNull($this->playname);
        $nickname=checkNull($this->nickname);
        $money=checkNull($this->money);
        $tenantId=getTenantId();
        //TODO 校验双方定义令牌
        $token=checkNull($this->token);
        $toliverooms=checkNull($this->toliverooms);

        $content['playname'] = isset($playname)?$playname:'';
        $content['nickname'] = isset($nickname)?$nickname:'';
        $content['money'] = isset($money)?$money:'';
        $data=array(
            'msgtype'=>$msgtype,
            'msgid'=>$msgid,
            'msgcontent'=>$msgcontent,
            'msgactiontype'=>$msgactiontype,
            'buttoncontent'=>$buttoncontent,
            'buttonlinkurl'=>$buttonlinkurl,
            'newmsgcontent'=>$content,
        );

        //此处token需与nodejs服务器配置的一致
        $nodeJsToken = '1234567';
        $roomnums=$toliverooms;

     /*   $data = [
            'roomnums'=>$roomnums,
            'tenantId'=>$tenantId,
            'gamedata'=> $data,
            'token' => $nodeJsToken,
        ];*/

        $bet_message = array(
            "retcode" => "000000",
            "retmsg" => "OK",
            "msg" => array(
                [
                    "_method_" => "GameNews",
                    "action" => "GameNews",
                    "msgtype" => "4",
                    "data" => $data,
                ]
            ),
            'roomnums'=>  $roomnums,
            'token' =>   $nodeJsToken,
        );
        $domain = new Domain_Message();
        $rs = $domain->pushgamedata($bet_message);

       /* try {
            $client = new Client(new Version1X($config['chatserver']));
            //TODO 定义报文格式

            $data = [
                'roomnums'=>$roomnums,
                'tenantId'=>$tenantId,
                'gamedata'=> $data,
                'token' => $nodeJsToken,
            ];

            $client->initialize();
            $client->emit('gamenews', $data);
            $client->close();

        }catch (Exception $e){

            $rs['code']=1001;
            $rs['msg']='发送失败,连接推送服务器异常';
        }

        //查询哪些直播间不在线
        $roomArray=explode(',',$roomnums);
        $existRoomArray= DI()->notorm->users_live->select('uid')->where("FIND_IN_SET(uid,?)",$roomnums)->fetchAll();

        $notExistRoomArray=array_diff($roomArray,$existRoomArray);
        $rs['info']=$notExistRoomArray;*/

        return $rs;
    }
	
}
