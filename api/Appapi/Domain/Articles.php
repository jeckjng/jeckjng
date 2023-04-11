<?php

class Domain_Articles {
    public function artList($page, $pagesize, $order,$uid,$mine){
        $rs = array();
        $model = new Model_Articles();
        $rs = $model->getList($page, $pagesize, $order,$uid,$mine);
        return $rs;
    }

    public function deleteArticle($id,$uid)
    {
        $model = new Model_Articles();
        $rs = $model->deleteArticle($id,$uid);
        return $rs;
    }


    public function createArticle($data){
        $model = new Model_Articles();
        $rs = $model->createArticle($data);
        return $rs;
    }

    public function addComment($id,$uid,$content, $parentid)
    {
        $model = new Model_Articles();
        $rs = $model->addComment($id, $uid, $content, $parentid);
        return $rs;
    }

    public function commentsList($id,$page, $pagesize){
        if($page <1){
            $page = 1;
        }
        if($pagesize<1){
            $pagesize = 20;
        }
        $model = new Model_Articles();
        $rs = $model->commentsList($id, $page, $pagesize);
        return $rs;
    }


    public function deleteComment($id, $uid)
    {
        $model = new Model_Articles();
        $rs = $model->deleteComment($id, $uid);
        return $rs;
    }

    public function addCollection($id, $uid)
    {
        $model = new Model_Articles();
        $rs = $model->addCollection($id, $uid);
        return $rs;
    }

    public function getCollection($uid, $page, $pagesize){
        if($page <1){
            $page = 1;
        }
        if($pagesize<1){
            $pagesize = 20;
        }
        $model = new Model_Articles();
        $rs = $model->getCollection($uid, $page, $pagesize);
        return $rs;
    }

    public function getDetail($id,$uid){
        $model = new Model_Articles();
        $rs = $model->getDetail($id,$uid);
        return $rs;
    }

    
    public function addLike($id, $uid)
    {
        $model = new Model_Articles();
        $rs = $model->addLike($id, $uid);
        return $rs;
    }
}

?>