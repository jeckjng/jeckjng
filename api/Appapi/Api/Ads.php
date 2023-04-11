<?php
/**
 * Created by PhpStorm.
 * User: 哎哟哎哟
 * Date: 2021/6/25
 * Time: 18:00
 */
class Api_Ads extends PhalApi_Api
{
    public function getRules()
    {
        return array(
            'adsList' => array(
            ),
            'getCarousel' => array(
                'cat_name' => array('name' => 'cat_name', 'type' => 'string', 'require' => true, 'desc' => '分类名称：recom_carousel:推荐页面轮播图'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>1, 'desc' => '分页，默认 1'),
            ),

            'getCreationAds' => array(
                'sid' => array('name' => 'sid', 'type' => 'int', 'require' => true, 'desc' => '13(创作说明)14,剪辑创作技巧,15剪辑工具推荐'),
            ),
            'getAdsbyname' => array(
                'adname' => array('name' => 'adname', 'type' => 'string', 'require' => true, 'desc' => '根据name查询'),
            ),
            'AdsById' => array(
                'id' => array('name' => 'id', 'type' => 'int', 'require' => true, 'desc' => '根据id查询，16.首页底部轮播文案，17.我的页面 保证金，18.创作中心保证金，24.活动横幅'),
                'long_video_class_id' => array('name' => 'long_video_class_id', 'type' => 'int',  'desc' => '视频分类下的广告'),

            ),
        );

    }

    /**
     * 广告图列表
     * @desc 广告图列表
     * @return string msg 提示信息
     * @return array info
     */
    public function adsList()
    {
        $rs = array('code' => 0, 'msg' => '广告图列表', 'info' => array());
        $domain = new Domain_Ads();
        $game_tenant_id = $this->game_tenant_id;
        $adsList= $domain->adsList($game_tenant_id);
        $rs['info'] = $adsList;
        return $rs;
    }
    /**
     * 创作攻略
     * @desc 创作攻略
     * @return string msg 提示信息
     * @return array info.grade 难度
     * @return array info.thumb 视频封面图
     *  @return array info.url （看前台具体情况而定，可以是视频播放地址，也可以是外部链接，也可是下载地址）
     * @return array info.des （描述）
     * @return array info.type_name （剪辑工具(软件类型) 只有 工具推荐才有用到
     */
    public function getCreationAds()
    {
        $rs = array('code' => 0, 'msg' => '广告图列表', 'info' => array());
        $domain = new Domain_Ads();
        $game_tenant_id = $this->game_tenant_id;
        $sid = $this->sid;

        $adsList= $domain->getCreationAds($game_tenant_id,$sid);
        $rs['info'] = $adsList;
        return $rs;
    }

    /**
     * 获取轮播
     * @desc 获取轮播
     * @return string msg 提示信息
     * @return array info 列表
     * @return string info[0].slide_id 广告id
     * @return string info[0].slide_name 名称
     * @return string info[0].slide_pic 图片
     * @return string info[0].slide_url 链接
     * @return string info[0].slide_des 描述
     * @return string info[0].slide_content 内容
     * @return string info[0].listorder 排序
     */
    public function getCarousel()
    {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());

        $cat_name = checkNull($this->cat_name);
        $p = $this->p;

        $domain = new Domain_Ads();
        $info = $domain->getCarousel($cat_name,$p);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }
    /**
     * 根据name查询广告
     * @desc 查询广告
     * @return string msg 提示信息
     * @return array info.grade 难度
     * @return array info.thumb 视频封面图

     */
    public function getAdsbyname()
    {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $domain = new Domain_Ads();
        $game_tenant_id = $this->game_tenant_id;
        $adname = $this->adname;

        $adsList= $domain->getAdsbyname($game_tenant_id,$adname);
        $rs['info'] = $adsList;
        return $rs;
    }

    /**
     * 根据name查询广告
     * @desc 查询广告
     * @return string msg 提示信息
     * @return array info.grade 难度
     * @return array info.thumb 视频封面图

     */
    public  function AdsById(){
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $domain = new Domain_Ads();
        $game_tenant_id = $this->game_tenant_id;
        $adname = $this->id;
        $long_video_class_id = $this->long_video_class_id;
        $adsList= $domain->AdsById($game_tenant_id,$adname,$long_video_class_id);
        $rs['info'] = $adsList;
        return $rs;
    }

}