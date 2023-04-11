<?php
/**
 * Created by PhpStorm.
 * User: 哎哟哎哟
 * Date: 2021/6/25
 * Time: 18:00
 */
class Api_Appointment extends PhalApi_Api
{
    public function getRules(){
        return array(
            'appointmentList' => array(
                'class' => array('name' => 'class', 'type' => 'int', 'min' => 1,  'desc' => '分类 1最新发布，2红榜推荐，3认证女神，4，金主推荐'),
                'type' => array('name' => 'type', 'type' => 'int', 'min' => 1,  'desc' => '分类 1外围，2会所,3楼风'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1,'desc' => '页数'),
                'limit' => array('name' => 'limit', 'type' => 'int', 'min' => 1, 'default'=>10,'desc' => '条数'),
                'shop_id'=> array('name' => 'shop_id', 'type' => 'int', 'desc' => '店铺id'),
                'title'=> array('name' => 'title', 'type' => '', 'desc' => ' 标题搜索'),
                'uid' =>  array('name' =>'uid', 'type' => 'int', 'min' => 1,  'desc' => ''),
                'province_id' =>  array('name' =>'province_id', 'type' => 'int', 'min' => 1,  'desc' => ''),
                'city_id' =>  array('name' =>'city_id', 'type' => 'int', 'min' => 1,  'desc' => ''),
                'area_id' =>  array('name' =>'area_id', 'type' => 'int', 'min' => 1,  'desc' => ''),
            ),
            'appointmentInfo' => array(
                'id' => array('name' =>'id', 'type' => 'int', 'min' => 1,  'desc' => ''),
                'uid' =>  array('name' =>'uid', 'type' => 'int', 'min' => 1,  'desc' => ''),
                'token' =>  array('name' =>'token', 'type' => 'string',  'desc' => ''),
            ),
            'appointmentTotal' => array(),
            'getAddress' => array(),
            'getShopByType' =>array(
                'type' => array('name' => 'type', 'type' => 'int', 'min' => 1,  'desc' => ' 1 外围下数据'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1,'desc' => '页数'),
                'limit' => array('name' => 'limit', 'type' => 'int', 'min' => 1, 'default'=>10,'desc' => '条数'),

            ),
            'addCollect' =>array(
                'appointment_id' => array('name' => 'appointment_id', 'type' => 'int', 'min' => 1,  'desc' => ' 约会id'),
                'uid' =>  array('name' =>'uid', 'type' => 'int', 'min' => 1,  'desc' => ''),
                'token' =>  array('name' =>'token', 'type' => 'int', 'min' => 1,  'desc' => ''),

            ),
            'collectList' =>array(

                'uid' =>  array('name' =>'uid', 'type' => 'int', 'min' => 1,  'desc' => ''),
                'token' =>  array('name' =>'token', 'type' => 'string', 'min' => 1,  'desc' => ''),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1,'desc' => '页数'),
                'limit' => array('name' => 'limit', 'type' => 'int', 'min' => 1, 'default'=>10,'desc' => '条数'),

            ),
            'commentList' =>array(
                'appointment_id' => array('name' => 'appointment_id', 'type' => 'int', 'min' => 1,  'desc' => ' 约会id'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1,'desc' => '页数'),
                'limit' => array('name' => 'limit', 'type' => 'int', 'min' => 1, 'default'=>10,'desc' => '条数'),
            ),
            'placeorder'=>array(
                'appointment_id' => array('name' => 'appointment_id','require' => true, 'type' =>  'int', 'min' => 1,  'desc' => '约会id'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'min' => 1, 'default'=>1,'desc' => '用户id'),

            ),
            'browseLog' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'min' => 1, 'default'=>1,'desc' => '用户id'),
                'token' =>  array('name' =>'token', 'type' => 'string',  'desc' => ''),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1,'desc' => '页数'),
                'limit' => array('name' => 'limit', 'type' => 'int', 'min' => 1, 'default'=>10,'desc' => '条数'),

                ),
            'inviteSet' => array(),
            'consumptionSet' => array(),
            'stationList'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'min' => 1, 'default'=>1,'desc' => '用户id'),
                'token' =>  array('name' =>'token', 'type' => 'int', 'string' => 1,  'desc' => ''),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1,'desc' => '页数'),
                'limit' => array('name' => 'limit', 'type' => 'int', 'min' => 1, 'default'=>10,'desc' => '条数'),
            ),
            'stationInfo'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'min' => 1, 'default'=>1,'desc' => '用户id'),
                'token' =>  array('name' =>'token', 'type' => 'string',   'desc' => ''),
                'id' => array('name' => 'id', 'type' => 'int', 'min' => 1, 'default'=>1,'desc' => '消息id'),

            ),
            'popStation'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'min' => 1, 'default'=>1,'desc' => '用户id'),
                'token' =>  array('name' =>'token', 'type' => 'string',   'desc' => ''),

            ),

            'newStation'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'min' => 1, 'default'=>1,'desc' => '用户id'),
                'token' =>  array('name' =>'token', 'type' => 'string',   'desc' => ''),

            ),
            'shopInfo'=> array(

                'id' => array('name' => 'id', 'type' => 'int', 'min' => 1, 'default'=>1,'desc' => '商铺id'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1,'desc' => '页数 用于获取商户列的约会'),
                'limit' => array('name' => 'limit', 'type' => 'int', 'min' => 1, 'default'=>10,'desc' => '条数 用于获取商户列的约会'),

            ),

            'turntableConfig' =>array(

            ),
            'turntableaward' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'min' => 1, 'default'=>1,'desc' => '用户id'),
                'token' =>  array('name' =>'token', 'type' => 'string',   'desc' => ''),

            ),
            'getaddre'=> array()

        /*    'myOrder'=>array(
                'uid' => array('name' => 'p', 'type' => 'int', 'require' => true, 'min' => 1, 'default'=>1,'desc' => '用户id'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1,'desc' => '页数'),
                'limit' => array('name' => 'limit', 'type' => 'int', 'min' => 1, 'default'=>10,'desc' => '条数'),
            ),*/
        );



    }

    /**
     *
     * 约会列表
     * @desc 约会列表
     * @return string msg 提示信息
     * @return array info.0.title 标题,
     * @return array info.0.age 年龄,
     * @return array info.0.province_name 省,
     * @return array info.0.city_name 市,
     * @return array info.0.area_name 区,
     * @return array info.0.price 价格,
     * @return array info.0.score 综合评分,
     * @return array info.0.shop_id 店铺,
     * @return array info.0.type 类型 1外围 2，会所，3楼风,
     * @return array info.0.service_items 服务项目,
     * @return array info.0.phone 联系方式,
     * @return array info.0.address 地址信息,
     * @return array info.0.img 图片 多个视频用,隔开：30,
     * @return array info.0.video 视频 多个视频用|隔开：30,
     * @return array info.0.viewing_times 浏览量,
     * @return array info.0.unlock_times 解锁量,
     * @return array info.0.is_top 是否置顶 1是 2 否,
     * @return array info.0.is_authentication  是否认证 1是 2 否,
     *
     *
     */

    public  function appointmentList(){
        $rs = array('code' => 0, 'msg' => '约会列表', 'info' => array());
        $domain = new Domain_Appointment();
        $class = $this->class;
        $type = $this->type;
        $game_tenant_id = $this->game_tenant_id;
        $p = $this->p;
        $limit = $this->limit ;
        $title = $this->title;
        $shop_id = $this->shop_id;
        $uid= $this->uid;
        $province_id =  $this->province_id;
        $city_id  = $this->city_id;
        $area_id  = $this->area_id;
        $appointmentList = $domain->appointmentList($class,$type,$title,$shop_id,$uid,$province_id,$city_id,$area_id,$game_tenant_id,$p,$limit);
        $rs['info'] = $appointmentList;
        return $rs;
    }
    /**
     * 约会头部统计
     * @desc 约会头部统计
     **/
    public  function appointmentTotal(){
        $rs = array('code' => 0, 'msg' => '类型统计', 'info' => array());
        $domain = new Domain_Appointment();
        $game_tenant_id = $this->game_tenant_id;

        $total = $domain->appointmentTotal($game_tenant_id);
        $rs['info'] = $total;
        return $rs;
    }
    /**
     * 城市列表
     * @desc 城市列表
     **/
    public  function getAddress(){
        $rs = array('code' => 0, 'msg' => '类型统计', 'info' => array());
        $domain = new Domain_Appointment();
        $game_tenant_id = $this->game_tenant_id;

        $list = $domain->getAddress($game_tenant_id);
        $rs['info'] = $list;
        return $rs;
    }
     /**
      *
      * 约会详情
      * * @desc 约会详情
      * * @return array info.title 标题,
      * @return array info.age 年龄,
      * @return array info.province_id 省,
      * @return array info.city_id 市,
      * @return array info.area_id 区,
      * @return array info.price 价格,
      * @return array info.score 综合评分,
      * @return array info.shop_id 店铺,
      * @return array info.type 类型 1外围 2，会所，3楼风,
      * @return array info.service_items 服务项目,
      * @return array info.phone 联系方式,
      * @return array info.address 地址信息,
      * @return array info.img 图片 多个视频用,隔开：30,
      * @return array info.video 视频 多个视频用|隔开：30,
      * @return array info.viewing_times 浏览量,
      * @return array info.unlock_times 解锁量,
      * @return array info.is_top 是否置顶 1是 2 否,
      * @return array info.is_authentication  是否认证 1是 2 否,
      * */
    public function appointmentInfo(){
        $rs = array('code' => 0, 'msg' => '类型统计', 'info' => array());
        $domain = new Domain_Appointment();
        $game_tenant_id = $this->game_tenant_id;
        $id  = $this->id;
        $uid = $this->uid;
        $info = $domain->appointmentInfo($game_tenant_id,$id,$uid);
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 店铺
     * @desc 获取个分类下的店铺
     */
    public function getShopByType()
    {
        $rs = array('code' => 0, 'msg' => '商铺列表', 'info' => array());
        $domain = new Domain_Appointment();
        $type = $this->type;
        $game_tenant_id = $this->game_tenant_id;
        $p = $this->p;
        $limit = $this->limit ;
        $list = $domain->getShopByType($type,$game_tenant_id,$p,$limit);

        $rs['info'] = $list;
        return $rs;
    }

    /**
     * 下单
     * @desc 下单
     **/
    public function placeorder()
    {
        $rs = array('code' => 0, 'msg' => '商铺列表', 'info' => array());
        $domain = new Domain_Appointment();
        $uid = $this->uid;
        $game_tenant_id = $this->game_tenant_id;
        $appointment_id = $this->appointment_id ;
        $info = $domain->placeorder($uid,$appointment_id,$game_tenant_id);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    public function myOrder()
    {
        $rs = array('code' => 0, 'msg' => '商铺列表', 'info' => array());
        $domain = new Domain_Appointment();
        $uid = $this->uid;
        $game_tenant_id = $this->game_tenant_id;
        $p  = $this->p;
        $limit = $this->limit;
        $list = $domain->myOrder($uid,$p,$limit,$game_tenant_id);

        $rs['info'] = $list;
        return $rs;
    }

    /**
     * 添加移除收藏
     * @desc 约会头部统计
     **/
    public function addCollect(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $domain = new Domain_Appointment();
        $uid = $this->uid;
        $game_tenant_id = $this->game_tenant_id;
        $appointment_id = $this->appointment_id ;
        $list = $domain->addCollect($uid,$appointment_id,$game_tenant_id);

        $rs['info'] = $list;
        return $rs;
    }
    /**
     * 收藏列表
     * @desc 收藏列表
     **/
    public function collectList(){
        $rs = array('code' => 0, 'msg' => '收藏列表', 'info' => array());
        $domain = new Domain_Appointment();
        $uid = $this->uid;
        $token = $this->token;
        $checkToken=checkToken($uid,$token);
        $language_id = $this->language_id;
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $game_tenant_id = $this->game_tenant_id;
        $p  = $this->p;
        $limit = $this->limit;
        $list = $domain->collectList($uid,$game_tenant_id,$p,$limit);

        $rs['info'] = $list;
        return $rs;
    }
    /**
     * 约会浏览记录
     * @desc 约会浏览记录
     **/
    public function  browseLog(){
        $rs = array('code' => 0, 'msg' => '浏览记录', 'info' => array());
        $domain = new Domain_Appointment();
        $uid = $this->uid;
        $token = $this->token;
        $checkToken=checkToken($uid,$token);
        $language_id = $this->language_id;
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $game_tenant_id = $this->game_tenant_id;
        $p  = $this->p;
        $limit = $this->limit;
        $list = $domain->browseLog($uid,$game_tenant_id,$p,$limit);

        $rs['info'] = $list;
        return $rs;
    }
    /**
     * 评论列表
     * @desc 评论列表
     **/
    public  function commentList(){
        $rs = array('code' => 0, 'msg' => '评论列表', 'info' => array());
        $domain = new Domain_Appointment();
       /* $uid = $this->uid;
        $token = $this->token;
        $checkToken=checkToken($uid,$token);*/
        $appointment_id = $this->appointment_id;
        $p  = $this->p;
        $limit = $this->limit;
        $list = $domain->commentList($appointment_id,$p,$limit);

        $rs['info'] = $list;
        return $rs;
    }

    /**
     * 邀请配置
     * @desc 邀请配置
     *
     * @return array info.0.reward  奖励金额
     *  @return array info.0. per_num  人数
     **/
    public  function inviteSet(){
        $rs = array('code' => 0, 'msg' => '邀请设置', 'info' => array());
        $domain = new Domain_Appointment();
        $list = $domain->inviteSet();
        $rs['info'] = $list;
        return $rs;
    }

    /**
     * 消费配置
     * @desc 消费配置
     * @return array info.0.reward  奖励金额
     * @return array info.0. min 最小值
     * @return array info.0. max最最大值
     **/
    public function consumptionSet(){
        $rs = array('code' => 0, 'msg' => '消费设置', 'info' => array());
        $domain = new Domain_Appointment();
        $list = $domain->consumptionSet();
        $rs['info'] = $list;
        return $rs;
    }
    /**
     * 站内信消息列表
     * @desc 站内信消息
     * @return array info.0.status   1  未读 2 已读
     *  @return array info.0.type   1 后台 推送  后续扩展使用
     * @return array info.total 总消息数
     *  @return array info.no_read_total 未读消息数
     **/
    public  function stationList(){
        $rs = array('code' => 0, 'msg' => '站内信消息列表', 'info' => array());
        $domain = new Domain_Appointment();
        $uid = $this->uid;
        $token = $this->token;
        $checkToken=checkToken($uid,$token);
        $language_id = $this->language_id;
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $game_tenant_id = $this->game_tenant_id;
        $p  = $this->p;
        $limit = $this->limit;
        $list = $domain->stationList($uid,$game_tenant_id,$p,$limit);

        $rs['info'] = $list['list'];
        $rs['total'] = $list['total'];
        $rs['no_read_total'] = $list['no_read_total'];
        return $rs;
    }
    /**
     * 站内信消息详情
     * @desc 站内信消息
     * @return array info.0.status   1  未读 2 已读
     *  @return array info.0.type   1 后台 推送  后续扩展使用
     **/
    public  function stationInfo(){
        $rs = array('code' => 0, 'msg' => '站内信消息详情', 'info' => array());
        $domain = new Domain_Appointment();
        $uid = $this->uid;
        $token = $this->token;
        $checkToken=checkToken($uid,$token);
        $language_id = $this->language_id;
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $id = $this->id;
        $game_tenant_id = $this->game_tenant_id;
        $list = $domain->stationInfo($id);
        $rs['info'] = $list;
        return $rs;
    }
    /**
     * 弹窗公告
     * @desc 弹窗公告
     * @return array info.0.status   1  未读 2 已读
     *  @return array info.0.type   1 后台 推送  后续扩展使用
     **/
    public  function popStation(){
        $rs = array('code' => 0, 'msg' => '站内信消息详情', 'info' => array());
        $domain = new Domain_Appointment();
        $uid = $this->uid;
        $token = $this->token;
        $checkToken=checkToken($uid,$token);
        $language_id = $this->language_id;
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $list = $domain->popStation($uid);
        $rs['info'] = $list;
        return $rs;
    }
    /**
     * 是否有新消息
     * @desc 是否有新消息
     * @return array info.0.count   大于0 表示有新信息未读

     **/
    public function newStation(){
        $rs = array('code' => 0, 'msg' => '新消息', 'info' => array());
        $domain = new Domain_Appointment();
        $uid = $this->uid;
        $token = $this->token;
        $checkToken=checkToken($uid,$token);
        $language_id = $this->language_id;
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $info = $domain->newStation($uid);
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }
    /**
     * 删除消息
     * @desc 删除消息
     * @return array info.0.status   1  未读 2 已读
     *  @return array info.0.type   1 后台 推送  后续扩展使用
     **/
    public function delstation(){
        $rs = array('code' => 0, 'msg' => '删除成功', 'info' => array());
        $domain = new Domain_Appointment();
        $uid = $this->uid;
        $token = $this->token;
        $checkToken=checkToken($uid,$token);
        $language_id = $this->language_id;
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $id = $this->id;
        $game_tenant_id = $this->game_tenant_id;
        $list = $domain->delstation($id);
        $rs['info'] = $list;
        return $rs;
    }

    /**
     * 商铺详情
     * @desc 删除消息
     * @return array info.0.status   1  未读 2 已读
     *  @return
     **/
    public  function shopInfo(){
        $rs = array('code' => 0, 'msg' => '商铺详情', 'info' => array());
        $domain = new Domain_Appointment();
        $p = $this->p;
        $limit = $this->limit ;
        $id = $this->id;
        $game_tenant_id = $this->game_tenant_id;
        $list = $domain->shopInfo($id,$p,$limit);
        $rs['info'] = $list;
        return $rs;
    }

    /**
     * 转盘数据
     * @desc 转盘数据
     *  @return
     **/

    public  function turntableConfig(){
        $rs = array('code' => 0, 'msg' => '转盘列表', 'info' => array());
        $domain = new Domain_Appointment();
        $list = $domain->turntableConfig();
        $rs['info'] = $list;
        return $rs;
    }

    /**
     * 转盘抽奖
     * @desc 转盘抽奖
     *  @return
     **/
    public function turntableaward(){
        $rs = array('code' => 0, 'msg' => '抽奖', 'info' => array());
        $uid = $this->uid;
        $token = $this->token;
        $checkToken=checkToken($uid,$token);
        $language_id = $this->language_id;
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_Appointment();
        $game_tenant_id = $this->game_tenant_id;
        $info = $domain->turntableaward($uid,$game_tenant_id);
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;

    }

    /**
     * 根据ip 定位
     * @desc 根据ip 定位
     *  @return
     **/
    public function getaddre(){
        $rs = array('code' => 0, 'msg' => '转盘列表', 'info' => array());
        try{
            $ip = get_client_ip();
            $url = file_get_contents("http://ip.taobao.com/outGetIpInfo?ip={$ip}&accessKey=alibaba-inc");
            $data = json_decode($url,true);
            $provincewhere = "  1= 1";
            $citywhere = " 1 = 1";
            if ($data['data']['region']){

                $provincewhere  = " province like '%".$data['data']['region']."%'";
            }
            $provinceInfo = DI()->notorm->province->where($provincewhere)->fetchOne();
            if (empty($provinceInfo)){
                $provinceInfo = DI()->notorm->province->fetchOne();
            }
            if ($data['data']['city']){
                $citywhere  = " city like '%".$data['data']['city']."%'";
            }else{
                $citywhere  = " father_id = {$provinceInfo['province_id']} ";
            }
            $cityInfo = DI()->notorm->city->where($citywhere )->fetchOne();
            if (empty($cityInfo)){
                $citywhere  = " father_id = {$provinceInfo['province_id']} ";
                $cityInfo = DI()->notorm->city->where($citywhere )->fetchOne();
            }
            $areaInfo = DI()->notorm->area->where( ['father_id'=>$cityInfo['city_id'] ])->fetchOne();
            $rs['info']['province'] = $provinceInfo;
            $rs['info']['city'] = $cityInfo;
            $rs['info']['area'] = $areaInfo;
        }catch (Exception $exception){
            $provinceInfo = DI()->notorm->province->fetchOne();
            $citywhere  = " father_id = {$provinceInfo['province_id']} ";
            $cityInfo = DI()->notorm->city->where($citywhere )->fetchOne();
            $areaInfo = DI()->notorm->area->where( ['father_id'=>$cityInfo['city_id'] ])->fetchOne();
            $rs['info']['province'] = $provinceInfo;
            $rs['info']['city'] = $cityInfo;
            $rs['info']['area'] = $areaInfo;
        }

        return $rs;

    }

}