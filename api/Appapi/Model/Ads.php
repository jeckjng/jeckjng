<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */
class Model_Ads extends PhalApi_Model_NotORM {


    public function adsList($game_tenant_id) {
        $list=DI()->notorm->ads
            ->fetchAll();
        foreach ($list as $key => $value){
                $ads = DI()->notorm->ads_sort
                    ->where("tenant_id = {$game_tenant_id}" )
                    ->select('sortname')
                ->fetchOne();
            $list[$key]['class_name']  = $ads['sortname'];
        }
        return $list;
    }
    public function getCreationAds($game_tenant_id,$sid) {

        $_ads = DI()->notorm->ads
            ->where("sid = {$sid} and tenant_id = {$game_tenant_id} " )
            ->select("*")
            ->order('orderno ')
            ->fetchAll();
        $ads=[];
        if(!empty($_ads)){
            foreach ($_ads as $k=>$v)
            {
                $v['des']= html_entity_decode($v['des']);
                $ads[$k]=$v;
            }
        }

        return $ads;
    }

    public function getCarousel($cat_name,$p) {
        $tenant_id = getTenantId();

        $p = $p >= 1 ? $p : 1;
        $limit = 20;
        $start = ($p-1)*$limit;

        $fields = 'slide_id,slide_name,slide_pic,slide_url,slide_des,slide_content,listorder';
        $list = DI()->notorm->slide->select($fields)
            ->where(["tenant_id"=>$tenant_id,'cat_name'=>$cat_name,'slide_status'=>1])
            ->order('listorder asc, slide_id desc')
            ->limit($start,$limit)
            ->fetchAll();

        return array('code' => 0, 'msg' => '', 'info' => $list);
    }

    public function getAdsbyname($game_tenant_id,$adname) {

        $_ads = DI()->notorm->ads
            ->where("name = '{$adname}' and tenant_id = {$game_tenant_id} " )
            ->select("*")
            ->order('orderno ')
            ->fetchAll();
        $ads=[];
        if(!empty($_ads)){
            foreach ($_ads as $k=>$v)
            {
                $v['des']= html_entity_decode($v['des']);
                $ads[$k]=$v;
            }
        }

        return $ads;
    }

    public function AdsById($game_tenant_id,$id,$long_video_class_id){

        $where  = "sid = '{$id}' and tenant_id = {$game_tenant_id} ";
        if ($long_video_class_id){
            $where .= " and  long_video_class_id = {$long_video_class_id} ";
        }
        $_ads = DI()->notorm->ads
            ->where($where)
            ->select("*")
            ->order('orderno ')
            ->fetchAll();

            foreach ($_ads as $k=>$v)
            {
                $_ads[$k]['des']= html_entity_decode($v['des']);
            }


        return $_ads;
    }

}