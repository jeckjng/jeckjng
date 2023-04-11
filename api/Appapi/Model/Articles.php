<?php

class Model_Articles extends PhalApi_Model_NotORM
{
    /* 发布文章 */
    public function createArticle($data)
    {
        $ret = DI()->notorm->articles->insert($data);
        return $ret;
    }

    public function getList($page, $pagesize,$order,$userid,$mine){
        $prefix = DI()->config->get('dbs.tables.__default__.prefix');
        if($order == 0){
            $order = "created_at DESC";
        }else{
            $order = "comments DESC";
        }
        $offset = ($page-1)*$pagesize;
        $where = "status=1";
        if(!empty($mine)){
            $where.=" AND `uid`={$userid}";
        }
        $count = DI()->notorm->articles->where($where)->count();
        if(empty($count)){
            return array();
        }
        
        $articles =  DI()->notorm->articles->queryAll("SELECT * FROM {$prefix}articles WHERE ".$where ." ORDER BY {$order} LIMIT {$pagesize} OFFSET {$offset}");
        
        if(empty($articles)){
            return $articles;
        }

        $uid = "";
        $art_id = "";
        foreach($articles as &$article){
            $article['is_attention'] = 0;
            $uid .= ",".$article['uid'];
            $art_id .= ",".$article['id'];
            $article['userinfo'] = new stdClass;
            $article['created_at'] = date("Y-m-d H:i:s",$article['created_at']);
        }


        $uid = ltrim($uid,",");
        $uid = rtrim($uid,",");
        $art_id = ltrim($art_id,",");
        $art_id = rtrim($art_id,",");
        //get like
        $likes = DI()->notorm->users_articles_like->queryAll("SELECT * FROM {$prefix}users_articles_like WHERE uid={$userid} AND art_id IN ({$art_id})");
        $collects = DI()->notorm->users_articles_collection->queryAll("SELECT * FROM {$prefix}users_articles_collection WHERE uid={$userid} AND article_id IN ({$art_id})");
        $attention = DI()->notorm->users_articles_like->queryAll("SELECT * FROM {$prefix}users_attention WHERE uid={$userid} AND touid IN ({$uid})");
        $now = time();
        $users =  DI()->notorm->users->select("id,avatar,avatar_thumb,sex,tenant_id,user_type,user_nicename,is_certification")->where("id in (".$uid.")")->fetchAll();
        $vips = DI()->notorm->articles->queryAll(
            "SELECT a.`uid`,b.`vip_grade`,b.`name` FROM {$prefix}users_buy_longvip as a LEFT JOIN {$prefix}vip_longgrade as b ON a.vip_level = b.vip_grade
             WHERE a.uid IN ({$uid}) AND a.endtime>{$now}"
        );
        $area = DI()->notorm->region->fetchAll();
        $paly_url = play_or_download_url(1);

        foreach($articles as &$article){  
            $article['thumb']= "";
            if(!empty($attention)){
                foreach($attention as $at){
                    if($at['touid'] == $article['uid']){
                        $article['is_attention'] = 1;     
                    }
                }
            }         
            $url = DI()->notorm->video->select($paly_url['viode_table_field'].",thumb")->where('id=?',$article['images'])->fetchOne();
            if(!empty($url)){
                $article['images'] = get_protocal().'://' . $_SERVER['HTTP_HOST'].$url[$paly_url['viode_table_field']];
                if($paly_url['name'] == 'minio' && strrpos($url['thumb'], '/liveprod-store-1039') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                    $article['thumb'] = $paly_url['url'] .'/liveprod-store-1039'. $url['thumb'];
                }else{
                    $article['thumb'] = $paly_url['url'] . $url['thumb'];
                }
            }
            if($article['province'] ==0 && $article['city']==0){
                $article['province'] = "";
                $article['city'] = '';
            }
            if($article['province']==0){
                $article['province'] = '';
            }

            if(!empty($article['province']) || !empty($article['city'])){
                foreach($area as $a){
                    if($a['id']==$article['province']){
                        $article['province'] = $a['name'];
                    }
    
                    if($a['id']==$article['city']){
                        $article['city'] = $a['name'];
                    }
                }
            }

            $article['is_like'] = 0;
            $article['is_collect'] = 0;
            foreach($users as $user){
                if($user['id'] == $article['uid']){
                    $user['vip_grade'] = '';
                    $user['vip_name'] = '';
                    foreach($vips as $vip){
                        if($vip['uid'] == $user['id']){
                            $user['vip_grade'] = $vip['vip_grade'];
                            $user['vip_name'] = $vip['name'];
                            if(stripos($user['avatar'],"/")==0){
                                $user['avatar'] =  get_protocal().'://' . $_SERVER['HTTP_HOST'].$user['avatar'];
                            }
                        }
                    }
                    $article['userinfo']=$user;
                }
                
            }

            foreach($likes as $like){
                if($like['art_id']==$article['id']){
                    $article['is_like'] = 1;
                }
            }

            foreach($collects as $collect){
                if($collect['article_id']==$article['id']){
                    $article['is_collect'] = 1;
                }
            }
        }
        return $articles;
    }

    public function deleteArticle($id,$uid)
    {
        return DI()->notorm->articles->where("id={$id} and uid={$uid}")->delete();
    }


    public function addComment($id, $uid, $content, $parentid){
        //判断是否有发布
        $art = DI()->notorm->articles->where("status=1 and id=".$id)->fetchOne();
        if(empty($art)){
            return 0;
        }
        DI()->notorm->articles->where("id=".$id)->update(array("comments"=>$art["comments"]+1));
        return DI()->notorm->users_articles_comments
                ->insert(array("uid"=>$uid,"article_id"=>$id,"content"=>$content,"parentid"=>$parentid,'addtime'=>time()));
    }


    public function commentsList($id, $page, $pagesize)
    {   
        //$count = DI()->notorm->users_articles_comments->where("article_id=".$id)->count();
        $prefix = DI()->config->get('dbs.tables.__default__.prefix');
        $num = ($page-1)*$pagesize;
        $ret = DI()->notorm->users_articles_comments->select("id,uid,article_id,content,addtime")->where("article_id=".$id)->order("id desc")->limit($num,$pagesize)->fetchAll();
        $uid = "";
        foreach($ret as &$item){
            $uid .= ",".$item['uid'];
            $item['addtime'] = date("Y-m-d H:i:s",$item['addtime']);
            $item['userinfo'] = new stdClass;
        }
        $uid = ltrim($uid,",");
        $uid = rtrim($uid,",");
        if(!empty($uid)){
            $users =  DI()->notorm->users->select("id,avatar,avatar_thumb,sex,tenant_id,user_type,user_nicename,is_certification")->where("id in (".$uid.")")->fetchAll();

            foreach($ret as &$item){
                foreach($users as $user){
                    if(stripos($user['avatar'],"/")==0){
                       
                        $user['avatar'] =  get_protocal().'://' . $_SERVER['HTTP_HOST'].$user['avatar'];
                    }
                    if($user['id'] == $item['uid']){
                        $item['userinfo']=$user;
                    }
                }
            }
        }
        return $ret;
    }

    public function deleteComment($id, $uid)
    {
        $condition = "article_id=".$id." and uid=".$uid;
        return DI()->notorm->users_articles_comments->where($condition)->delete();
    }

    public function addCollection($id, $uid){
        //判断是否有发布
        $art = DI()->notorm->articles->where("status=1 and id=".$id)->fetchOne();
        if(empty($art)){
            return false;
        }
        $collect = DI()->notorm->users_articles_collection->where("article_id={$id} and uid=$uid")->count();
        if($collect>0){
            DI()->notorm->articles->where("id=".$id)->update(array("collect"=>$art["collect"]-1));
            return DI()->notorm->users_articles_collection->where("article_id={$id} and uid=$uid")->delete();
        }
        DI()->notorm->articles->where("id=".$id)->update(array("collect"=>$art["collect"]+1));
        return DI()->notorm->users_articles_collection->insert(array("article_id"=>$id,"uid"=>$uid,'addtime'=>time()));
    }


    public function addLike($id, $uid){
        //判断是否有发布
        $art = DI()->notorm->articles->where("status=1 and id=".$id)->fetchOne();
        if(empty($art)){
            return false;
        }
        $collect = DI()->notorm->users_articles_like->where("art_id={$id} and uid=$uid")->count();
        if($collect>0){
            DI()->notorm->articles->where("id=".$id)->update(array("likes"=>$art["likes"]-1));

            return DI()->notorm->users_articles_like->where("art_id={$id} and uid=$uid")->delete();
        }
        DI()->notorm->articles->where("id=".$id)->update(array("likes"=>$art["likes"]+1));
        return DI()->notorm->users_articles_like->insert(array("art_id"=>$id,"uid"=>$uid,'addtime'=>time()));
    }


    public function getCollection($uid, $page, $pagesize){
        $prefix = DI()->config->get('dbs.tables.__default__.prefix');
        // $count = DI()->notorm->articles->queryAll(
        //     "select count(a.id) from {$prefix}articles as a left join 
        //     {$prefix}users_articles_collection b on a.id=b.article_id
        //     where b.uid={$uid} and a.status=1"
        // )[0]["count(a.id)"];
        $offset = ($page-1) * $pagesize;
        $data = DI()->notorm->articles->queryAll(
            "select a.* from {$prefix}articles as a left join 
            {$prefix}users_articles_collection b on a.id=b.article_id
            where b.uid={$uid} and a.status=1 limit {$pagesize} offset {$offset}"
        );

        if(empty($data)){
            return [];
        }
        //$uid = "";
        $art_id = ""; 
        $area = DI()->notorm->region->fetchAll();

        $paly_url = play_or_download_url(1);        
        foreach($data as &$item){
           // $uid .= ",".$item['uid'];
           $item['thumb'] = "";
            $art_id .= ",".$item['id'];
            $item['userinfo'] = new stdClass;
            $item['is_like'] = 0;
            $url = DI()->notorm->video->select($paly_url['viode_table_field'])->where('id=?',$item['images'])->fetchOne();
            if(!empty($url)){
                $item['images'] = get_protocal().'://' . $_SERVER['HTTP_HOST'].$url[$paly_url['viode_table_field']];
                if($paly_url['name'] == 'minio' && strrpos($url['thumb'], '/liveprod-store-1039') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                    $item['thumb'] = $paly_url['url'] .'/liveprod-store-1039'. $url['thumb'];
                }else{
                    $item['thumb'] = $paly_url['url'] . $url['thumb'];
                }
            }
            if($item['province']==0&&$item['city']==0){
                $item['province'] = "";
                $item['city'] = '';
            }
            if($item['province']==0){
                $item['province'] = '';
            }

            if(!empty($item['province']) || !empty($item['city'])){
                foreach($area as $a){
                    if($a['id']==$item['province']){
                        $item['province'] = $a['name'];
                    }
    
                    if($a['id']==$item['city']){
                        $item['city'] = $a['name'];
                    }
                }
            }
        }


        //$uid = ltrim($uid,",");
        //$uid = rtrim($uid,",");
        $art_id = ltrim($art_id,",");
        $art_id = rtrim($art_id,",");

        $likes = DI()->notorm->users_articles_like->queryAll("SELECT * FROM {$prefix}users_articles_like WHERE uid={$uid} AND art_id IN ({$art_id})");

        foreach($data as &$item){
            foreach($likes as $like){
                if($like['art_id']==$item['id']){
                    $item['is_like'] = 1;
                }
            }
            $item['is_collect'] = 1;
        }

        $uid = "";
        foreach($data as &$item){
            $uid .= ",".$item['uid'];
        }


        $uid = ltrim($uid,",");
        $uid = rtrim($uid,",");
        
        if(!empty($uid)){
            $users =  DI()->notorm->users->select("id,avatar,avatar_thumb,sex,tenant_id,user_type,user_nicename,is_certification")->where("id in (".$uid.")")->fetchAll();
            $now = time();
            $vips = DI()->notorm->articles->queryAll(
                "SELECT a.`uid`,b.`vip_grade`,b.`name` FROM {$prefix}users_buy_longvip as a LEFT JOIN {$prefix}vip_longgrade as b ON a.vip_level = b.vip_grade
                 WHERE a.uid IN ({$uid}) AND a.endtime>{$now}"
            );
            foreach($data as &$item){
                foreach($users as $user){
                    if($user['id'] == $item['uid']){
                        if(stripos($user['avatar'],"/")==0){
                            $user['avatar'] = get_protocal().'://' . $_SERVER['HTTP_HOST'].$user['avatar'];
                        }
                        $user['vip_grade'] = '';
                        $user['vip_name'] = '';
                        foreach($vips as $vip){
                            if($vip['uid'] == $user['id']){
                                $user['vip_grade'] = $vip['vip_grade'];
                                $user['vip_name'] = $vip['name'];
                            }
                        }

                        $item['userinfo']=$user;
                    }
                }
            }
        }
        
        return $data;
    }

    public function getDetail($id,$uid)
    {
        $prefix = DI()->config->get('dbs.tables.__default__.prefix');
        $ret = DI()->notorm->articles->select("*")->where("status=1 and id={$id}")->fetchOne();
        if(empty($ret)){
            return $ret;
        }
        $like = DI()->notorm->users_articles_like->queryAll("SELECT * FROM {$prefix}users_articles_like WHERE uid={$uid} AND art_id IN ({$ret['id']})");

        $collect = DI()->notorm->users_articles_collection->queryAll("SELECT * FROM {$prefix}users_articles_collection WHERE uid={$uid} AND article_id IN ({$ret['id']})");
        $ret['is_like'] = 0;
        $ret['is_collect'] = 0;
        if(!empty($like)){
            $ret['is_like'] = 1;
        }

        if(!empty($collect)){
            $ret['is_collect'] = 1;
        }
        $uid = $ret['uid'];
        $userinfo = DI()->notorm->users->select("avatar,avatar_thumb,sex,tenant_id,user_type,user_nicename,is_certification")->where("id=".$uid)->fetchOne();
        $now = time();

        $vips = DI()->notorm->articles->queryAll(
            "SELECT a.`uid`,b.`vip_grade`,b.`name` FROM {$prefix}users_buy_longvip as a LEFT JOIN {$prefix}vip_longgrade as b ON a.vip_level = b.vip_grade
             WHERE a.uid IN ({$uid}) AND a.endtime>{$now}"
        );
        if(!empty($vips)){
            $userinfo['vip_grade'] = $vips[0]['vip_grade'];
            $userinfo['vip_name'] = $vips[0]['name'];
        }else{
            $userinfo['vip_grade'] = '';
            $userinfo['vip_name'] = '';
        }
        $area = DI()->notorm->region->fetchAll();
        if($ret['province']==0 && $ret['city']==0){
            $ret['province'] = '';
            $ret['city'] = '';
        }
        if($ret['province']==0){
            $ret['province'] = '';
        }
        if(!empty($ret['province']) || !empty($ret['city'])){
            foreach($area as $a){
                if($a['id']==$ret['province']){
                    $ret['province'] = $a['name'];
                }

                if($a['id']==$ret['city']){
                    $ret['city'] = $a['name'];
                }
            }
        }

        $ret['userinfo'] = $userinfo;
        if($ret['type']==2 && !empty($ret['images'])){
            $paly_url = play_or_download_url(1);
            $url = DI()->notorm->video->select($paly_url['viode_table_field'])->where('id=?',$ret['images'])->fetchOne();
            if(!empty($url)){
                $ret['images'] =  get_protocal().'://' . $_SERVER['HTTP_HOST'].$url[$paly_url['viode_table_field']];
                if($paly_url['name'] == 'minio' && strrpos($url['thumb'], '/liveprod-store-1039') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                    $ret['thumb'] = $paly_url['url'] .'/liveprod-store-1039'. $url['thumb'];
                }else{
                    $ret['thumb'] = $paly_url['url'] . $url['thumb'];
                }
            }
        }
        $read = $ret['reads'] +1;
        DI()->notorm->articles->queryAll("UPDATE cmf_articles SET `reads` = {$read} WHERE id ={$ret['id']}");
        return $ret;
    }
}


?>