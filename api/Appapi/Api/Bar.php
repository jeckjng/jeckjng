<?php
/**
 * Created by PhpStorm.
 * User: 哎哟哎哟
 * Date: 2021/6/25
 * Time: 18:00
 */
class Api_Bar extends PhalApi_Api
{
    public function getRules()
    {
        return array(
            'barList' => array(
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'type' => array('name' => 'type', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '1普通贴子，2求片,3我的普通贴子,4我的2求片,5审核中'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),


            ),
            'barInfo' => array(
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'bar_id' => array('name' => 'bar_id', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '数据id'),
            ),
            'commentList' => array(
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'bar_id' => array('name' => 'bar_id', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '贴子id'),
                'type' => array('name' => 'type', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '1 普通贴子，2求片贴子'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),

            ),
            'commentDesc' => array(
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'comment_id' => array('name' => 'comment_id', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '评论id'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),


            ),
            'postBar' => array(
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'desc'=> array('name' => 'desc', 'type' => 'string', 'require' => true, 'desc' => '内容'),
                'img'=> array('name' => 'img', 'type' => 'string',  'desc' => '图片(多图用英文逗号分隔(,)'),
                'fileStoreKey'=> array('name' => 'fileStoreKey', 'type' => 'string',  'desc' => '视频返回的加密串'),
                'video_img'=> array('name' => 'video_img', 'type' => 'string',  'desc' => '视频图片'),
                'type'=> array('name' => 'type', 'type' => 'string', 'require' => true, 'desc' => '1普通贴子,2求片'),
                'reward_money'=> array('name' => 'reward_money', 'type' => 'string',  'desc' => '求片必传'),

            ),
            'postComment'=> array(
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'id' => array('name' => 'comment_id', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '被评论id'),
                'type'=> array('name' => 'type', 'type' => 'int', 'require' => true, 'desc' => '1贴子评论,2评论回复,3推片'),
                'desc'=> array('name' => 'desc', 'type' => 'string',  'desc' =>'评论内容'),
                'video_id'=> array('name' => 'video_id', 'type' => 'string', 'desc' =>'视频id'),
                'video_type'=> array('name' => 'video_type', 'type' => 'string', 'desc' =>'视频类型 1 短视频 2 长视频 '),

            ),
            'setOptimumComment'=> array(
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'bar_id' => array('name' => 'bar_id', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '贴子id'),
                'comment_id'=> array('name' => 'comment_id', 'type' => 'int', 'require' => true, 'desc' => '推片id(评论id)'),


            ),
            'barLikes'=> array(
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'bar_id' => array('name' => 'bar_id', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '贴子id'),


            ),
            'myData'=> array(
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),



            ),
        );
    }
    /**
     * 贴吧列表
     * @desc 贴吧列表
     * @return string msg 提示信息
     * @return array info.comments_number  评论数量
     * @return array info.like_number  点赞数量
     * @return array info.validtime   有效时间（validtime + endtime 结束时间错）
     * @return array info.href 视频
     * @return array info.img 图片(需要异或解密 ，参考视频的封面图)
     * @return array info.video_img 视频封面(需要异或解密 ，参考视频的封面图)
     */
    public  function barList(){
        $rs = array('code' => 0, 'msg' => '贴吧列表', 'info' => array());
        $domain = new Domain_Bar();
        $uid = checkNull($this->uid);
        $token =checkNull($this->token);
        $type = checkNull($this->type);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        $p = $this->p;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $barList = $domain->barList($game_tenant_id,$type,$uid,$p);


        if ($barList ===1000){
            $rs['code'] = 1000;
            $rs['msg'] = '贴吧已关闭';
            return $rs;
        }
        $rs['info'] = $barList;
        return $rs;
    }
    /**
     * 贴吧详情
     * @desc 贴吧详情
     * @return string msg 提示信息
     * @return
     */
    public  function barInfo(){
        $rs = array('code' => 0, 'msg' => '贴吧详情', 'info' => array());
        $domain = new Domain_Bar();
        $uid = checkNull($this->uid);
        $bar_id = checkNull($this->bar_id);
        $token =checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $barList = $domain->barInfo($game_tenant_id,$uid,$bar_id);
        $rs['info'] = $barList;
        return $rs;
    }
    /**
     * 贴吧评论
     * @desc 贴吧评论（一级评论）
     * @return string msg 提示信息
     * @return array info.publish_user_nicename 评论者用户昵称
     * @return array info.publish_uid  ==  array info.bar_uid 为作者
     * @return array info.comment_desc.publish_user_nicename 评论者用户昵称
     * @return array info.comment_desc.parent_reply_user_nicename 被评论者用户昵称
     * @return array info.status = 2 ( 最佳评论 求片才有)
     */
    public  function commentList(){
        $rs = array('code' => 0, 'msg' => '贴吧列表', 'info' => array());
        $domain = new Domain_Bar();
        $uid = checkNull($this->uid);
        $token =checkNull($this->token);
        $bar_id = checkNull($this->bar_id);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        $p = $this->p;
        $type = $this->type;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $barList = $domain->commentList($game_tenant_id,$bar_id,$uid,$type,$p);
        $rs['info'] = $barList;
        return $rs;
    }
    /**
     * 评论回复列表
     * @desc 评论回复列表
     * @return string msg 提示信息
     *  *@return array info.publish_user_nicename 评论者用户昵称
     * @return array info.parent_reply_user_nicename 被评论者用户昵称
     */
    public  function commentDesc(){
        $rs = array('code' => 0, 'msg' => '评论回复', 'info' => array());
        $domain = new Domain_Bar();
        $uid = checkNull($this->uid);
        $token =checkNull($this->token);
        $comment_id = checkNull($this->comment_id);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        $p = $this->p;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $barList = $domain->commentDesc($game_tenant_id,$comment_id,$uid,$p);
        $rs['info'] = $barList;
        return $rs;
    }
    /**
     * 发帖
     * @desc 发帖
     * @return string msg 提示信息
     * @return array info
     */
    public  function  postBar(){
        $rs = array('code' => 0, 'msg' => '发贴', 'info' => array());
        $domain = new Domain_Bar();
        $uid = checkNull($this->uid);
        $token =checkNull($this->token);
        $desc = checkNull($this->desc);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        $img = $_REQUEST['img']?$_REQUEST['img']:'';
        $imgArray =  explode(',',$img);
        if (count($imgArray) > 9){
            $rs['code'] = 1006;
            $rs['msg'] = '图片最多上传9张';
            return $rs;
        }

        $fileStoreKey = $_REQUEST['fileStoreKey']?$_REQUEST['fileStoreKey']:'';
        $video_img = $_REQUEST['video_img']?$_REQUEST['video_img']:'';
        $type = $this->type;
        $reward_money = isset($_REQUEST['reward_money'])? (int)$_REQUEST['reward_money']:0;;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        if ($img && $fileStoreKey){

            $rs['code'] = 1007;
            $rs['msg'] = '视频和图片不能同时发布';
            return $rs;
        }
        $barinfo = $domain->postBar($game_tenant_id,$desc,$uid,$img,$fileStoreKey,$video_img,$type,$reward_money);

        if ($barinfo['code'] === 1000){
            $rs['code'] = 1000;
            $rs['msg'] = '功能维护中';
        }
        if ($barinfo['code'] === 1001){
            $rs['code'] = 1001;
            $rs['msg'] = '余额不足';
        }
        if ($barinfo['code'] === 1002){
            $rs['code'] = 1002;
            $rs['msg'] = '暂无该权限，请联系客服';
        }
        if ($barinfo['code'] === 1003){
            $rs['code'] = 1003;
            $rs['msg'] = '您当前VIP等级无权限';
        }
        if ($barinfo['code'] === 1004){
            $rs['code'] = 1004;
            $rs['msg'] = '发帖数量到达最大值';
        }


        if ($barinfo['code'] === 1005){
            $rs['code'] = 1005;
            $rs['msg'] = $barinfo['info'];
        }

        return $rs;
    }
    /**
     * 发表评论
     * @desc 发表评论
     * @return string msg 提示信息
     * @return array info
     */
    public  function  postComment(){
        $rs = array('code' => 0, 'msg' => '发表评论', 'info' => array());
        $domain = new Domain_Bar();
        $uid = checkNull($this->uid);
        $token =checkNull($this->token);
        $desc = checkNull($this->desc);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        $id = $this->id;
        $type = $this->type;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $video_id = $_REQUEST['video_id']?$_REQUEST['video_id']:'';
        $video_type = $_REQUEST['video_type']?$_REQUEST['video_type']:'';
        $barinfo = $domain->postComment($game_tenant_id,$desc,$uid,$type,$id,$video_id,$video_type);
        if ($barinfo ===1000){
            $rs['code'] = 1000;
            $rs['msg'] = '功能维护中';
        }
        if ($barinfo === 1002){
            $rs['code'] = 1002;
            $rs['msg'] = '暂无该权限，请联系客服';
        }
        if ($barinfo === 1003){
            $rs['code'] = 1003;
            $rs['msg'] = '您当前VIP等级无权限';
        }
        if ($barinfo === 1004){
            $rs['code'] = 1004;
            $rs['msg'] = '此片已推送';
        }
        if ($barinfo === 1005){
            $rs['code'] = 1005;
            $rs['msg'] = '该求片属于自己';
        }

        return $rs;
    }
    /**
     * 设为最佳评论
     * @desc 设为最佳评论
     * @return string msg 提示信息
     * @return array info
     */
    public  function setOptimumComment(){
        $rs = array('code' => 0, 'msg' => '设为最佳推片', 'info' => array());
        $domain = new Domain_Bar();
        $uid = checkNull($this->uid);
        $token =checkNull($this->token);
        $bar_id = checkNull($this->bar_id);
        $comment_id = checkNull($this->comment_id);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $barList = $domain->setOptimumComment($game_tenant_id,$bar_id,$comment_id,$uid);
        if ($barList === 1000){
            $rs['code'] = 1000;
            $rs['msg'] = '请操作自己的求片贴';
        }
        if ($barList === 1001){
            $rs['code'] = 1001;
            $rs['msg'] = '已有最佳评论';
        }
        $rs['info'] = $barList;
        return $rs;
    }
    /**
     * 点赞
     * @desc 点赞
     * @return string msg 提示信息
     * @return array info
     */
    public  function  barLikes(){
        $rs = array('code' => 0, 'msg' => '设为最佳推片', 'info' => array());
        $domain = new Domain_Bar();
        $uid = checkNull($this->uid);
        $token =checkNull($this->token);
        $bar_id = checkNull($this->bar_id);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $barList = $domain->barLikes($game_tenant_id,$bar_id,$uid);
        $rs['info'] = $barList;
        return $rs;
    }

    /**
     * 贴吧头部数据统计
     * @desc 我的数据
     * @return string msg 提示信息
     * @return array info
     */
    public  function  myData(){
        $rs = array('code' => 0, 'msg' => '我的数据', 'info' => array());
        $domain = new Domain_Bar();
        $uid = checkNull($this->uid);
        $token =checkNull($this->token);

        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $barList = $domain->myData($game_tenant_id,$uid);
        $rs['info'] = $barList;
        return $rs;
    }



}