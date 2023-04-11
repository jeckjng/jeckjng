<?php

class Api_Articles extends PhalApi_Api{
    public function getRules()
    {
        return array(
            "getList"=>array(
                "page"=>array("name"=>"page","type"=>"int","min"=>1,"require"=> true,"desc"=>"页数"),
                "uid"=>array("name"=>"uid","type"=>"int",'require'=>false,"desc"=>"用户id"),
                "mine"=>array("name"=>"mine","type"=>"int",'require'=>false,"desc"=>"是否只筛选当前用户所发的,0是所有，1是只筛选个人的"),
                "pagesize"=>array("name"=>"pagesize","type"=>"int","min"=>1,"require"=> true,"desc"=>"页码"),
                "order"=>array("name"=>"order","type"=>"int","min"=>0,"require"=> false,"desc"=>"排序， 0 为最新, 1 为最热门")
            ),
            "addArticle"=>array(
                "city"=>array("name"=>"city","type"=>"int","require"=>false,"min"=>0,"desc"=>"城市id"),
                "province"=>array("name"=>"province","type"=>"int","require"=>false,"min"=>0,"desc"=>"省份id"),
                "images"=>array("name"=>"images","type"=>"string","require"=>true,"desc"=>"图片地址或者视频id"),
                "type"=>array("name"=>"type","type"=>"int","require"=>true,"desc"=>"视频类型,1 图文, 2 视频"),
                "context"=>array("name"=>"context","type"=>"string","require"=>true,"desc"=>"文章内容"),
                "uid"=>array("name"=>"uid","type"=>"int",'require'=>true,"desc"=>"用户id"),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token')
            ),
            "deleteArticle" => array(
                "id"=>array("name"=>"id","type"=>"int","min"=>1,'require'=>true,"desc"=>"文章id"),
                "uid"=>array("name"=>"uid","type"=>"int",'require'=>true,"desc"=>"用户id"),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token')
            ),
            "addComment"=>array(
                "id"=>array("name"=>"id","type"=>"int","min"=>1,'require'=>true,"desc"=>"文章id"),
                "uid"=>array("name"=>"uid","type"=>"int",'require'=>true,"desc"=>"用户id"),
                //"parent_id"=>array("name"=>"parent_id","type"=>"int",'require'=>false,"desc"=>"评论id"),
                "content"=>array("name"=>"content","type"=>"string","require"=>true,"desc"=>"评论内容"),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token')
            ),
            "commentsList"=>array(
                "id"=>array("name"=>"id","type"=>"int","min"=>1,'require'=>true,"desc"=>"文章id"),
                "uid"=>array("name"=>"uid","type"=>"int",'require'=>true,"desc"=>"用户id"),
                "page"=>array("name"=>"page","type"=>"int","min"=>1,"require"=> true,"desc"=>"页数"),
                "pagesize"=>array("name"=>"pagesize","type"=>"int","min"=>1,"require"=> true,"desc"=>"页码"),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token')
            ),
            "deleteComment"=>array(
                "id"=>array("name"=>"id","type"=>"int","min"=>1,'require'=>true,"desc"=>"文章id"),
                "uid"=>array("name"=>"uid","type"=>"int",'require'=>true,"desc"=>"用户id"),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token')
            ),

            "addCollection"=>array(
                "id"=>array("name"=>"id","type"=>"int","min"=>1,'require'=>true,"desc"=>"文章id"),
                "uid"=>array("name"=>"uid","type"=>"int",'require'=>true,"desc"=>"用户id"),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token')
            ),

            "addLike"=>array(
                "id"=>array("name"=>"id","type"=>"int","min"=>1,'require'=>true,"desc"=>"文章id"),
                "uid"=>array("name"=>"uid","type"=>"int",'require'=>true,"desc"=>"用户id"),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token')
            ),

            "getCollection"=>array(
                "uid"=>array("name"=>"uid","type"=>"int",'require'=>true,"desc"=>"用户id"),
                "page"=>array("name"=>"page","type"=>"int","min"=>1,"require"=> true,"desc"=>"页数"),
                "pagesize"=>array("name"=>"pagesize","type"=>"int","min"=>1,"require"=> true,"desc"=>"页码"),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token')
            ),

            "getDetail" => array(
                "id"=>array("name"=>"id","type"=>"int",'require'=>true,"desc"=>"文章id"),
                "uid"=>array("name"=>"uid","type"=>"int",'require'=>false,"desc"=>"用户id"),
            ),

            "regionList"=>array(
                "id"=>array("name"=>"id","type"=>"string",'require'=>false,"desc"=>"地区id"),
            ),
            "hotregion" => array(),
            "cityList" => array(),
            "provinceList"=> array(),
        );
    }


    public function getList(){
        if(empty($this->order)){
            $this->order = 0;
        }
        if(empty($this->uid)){
            $this->uid = 0;
        }


        if(empty($this->mine)){ 
            //是否自己发的
            $this->mine = 0;
        }
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $domain = new Domain_Articles();
        $res = $domain->artList($this->page, $this->pagesize, $this->order,$this->uid,$this->mine);
        $rs['info'] = $res;
        return $rs;
    }

    public function regionList(){
        $rs['code'] = 0;
        $rs['msg'] = '操作成功';
        $rs['info'] = array();
        if(!empty($this->id)){
            $rs['info'] = DI()->notorm->region->where("id in({$this->id})")->fetchAll();
        }else{
            $rs['info'] = DI()->notorm->region->fetchAll();
        }
        return $rs;
    }

    public function cityList(){
        $rs['code'] = 0;
        $rs['msg'] = '操作成功';
        $rs['info'] = DI()->notorm->region->where('level=2')->fetchAll();
        return $rs;
    }

    public function provinceList(){
        $rs['code'] = 0;
        $rs['msg'] = '操作成功';
        $rs['info'] = DI()->notorm->region->where('level=1')->fetchAll();
        return $rs;
    }
    public function hotregion(){
        $rs['code'] = 0;
        $rs['msg'] = '操作成功';
        $rs['info'] =  DI()->notorm->region->where("id in (1,108,231,233,122)")->fetchAll();
        return $rs;
    }
    // 删除文章
    public function deleteArticle(){
        $rs['code'] = 0;
        $rs['msg'] = '删除成功！';
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Articles();
        $res = $domain->deleteArticle($this->id, $this->uid);
        if($res<1){
            $rs['code'] = 1001;
            $rs['msg'] = '删除失败！';
            return $rs;
        }
        return $rs;
    }


    //修改文章
    public function updateArticle(){
        return 1;
    }

    // 关注文章
    public function addCollection(){
        $rs['code'] = 0;
        $rs['msg'] = '操作成功';
        $rs['info'] = array();
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Articles();
        $res =  $domain->addCollection($this->id, $this->uid);
        if($res<1){
            $rs['code'] = 1001;
            $rs['msg'] = '操作失败！';
            return $rs;
        }
        return $rs;
    }

        // 点赞文章
        public function addLike(){
            $rs['code'] = 0;
            $rs['msg'] = '操作成功';
            $rs['info'] = array();
            $uid=checkNull($this->uid);
            $token=checkNull($this->token);
            $checkToken=checkToken($uid,$token);
            if($checkToken==700){
                $rs['code'] = $checkToken;
                $rs['msg'] = '您的登陆状态失效，请重新登陆！';
                //return $rs;
            }
            $domain = new Domain_Articles();
            $res =  $domain->addLike($this->id, $this->uid);
            if($res<1){
                $rs['code'] = 1001;
                $rs['msg'] = '操作失败！';
                return $rs;
            }
            return $rs;
        }

    //评论文章
    public function addComment(){
        $rs['code'] = 0;
        $rs['msg'] = '评论成功！';
        $rs['info'] = array();
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Articles();
        if(empty($this->parent_id)){
            $this->parent_id= 0;
        }
        $res = $domain->addComment($this->id, $this->uid, $this->content, $this->parent_id);
        if($res<1){
            $rs['code'] = 1001;
            $rs['msg'] = '评论失败！';
            return $rs;
        }
        return $rs;
    }

    // 文章详情
    public function getDetail(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $id = checkNull($this->id);
        $domain = new Domain_Articles();
        if(empty($uid)){
            $this->uid=0;
        }
        $rs['info'] = $domain->getDetail($id,$this->uid);
        return $rs;
    }

    // 关注列表
    public function getCollection(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $page = $this->page;
        $pagesize = $this->pagesize;
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        // if($checkToken==700){
        //     $rs['code'] = $checkToken;
        //     $rs['msg'] = '您的登陆状态失效，请重新登陆！';
        //     return $rs;
        // }
        $domain = new Domain_Articles();
        $data = $domain->getCollection($this->uid, $page, $pagesize);
        $rs["info"] = $data;
        return $rs;
    }

    //评论列表
    public function commentsList(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $page = $this->page;
        $pagesize = $this->pagesize;
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Articles();
        $res = $domain->commentsList($this->id, $page, $pagesize);
        $rs["info"] = $res;
        return $rs;
    }

    //删除评论
    public function deleteComment(){
        $rs['code'] = 0;
        $rs['msg'] = '删除成功！';
        $rs['info'] = array();
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Articles();
        $res = $domain->deleteComment($this->id, $this->uid);
        if($res<1){
            $rs['code'] = 1001;
            $rs['msg'] = '删除失败！';
            return $rs;
        }
        return $rs;
    }

    public function addArticle(){
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        if($this->type==1){
            $arr = explode(",",$this->images);
            if(count($arr)>9){
                $rs['code'] = $checkToken;
                $rs['msg'] = '发布图片不能大于9张!';
                return $rs;
            }
        }
        $rs = array('code' => 0, 'msg' => '发布成功', 'info' => array());
        $city = empty($this->city) ? 0 : $this->city;
        $province = empty($this->province) ? 0 : $this->province;
        $data = array(
            "city"=>$city,
            "province"=>$province,
            "comments"=>0,
            "likes"=>0,
            "images"=>$this->images,
            "type" => $this->type,
            "context"=> $this->context,
            "created_at"=>time(),
            "status"=>1,
            "uid"=>$this->uid
        );
        $domain = new Domain_Articles();
        $res= $domain->createArticle($data);
        if(!empty($res['id'])){
            return $rs;
        }else{
            $rs['code'] =1001;
            $rs['msg'] = "发布失败!";
        }
        return $rs;
    }



}



?>