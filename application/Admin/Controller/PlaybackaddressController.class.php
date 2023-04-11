<?php
/*
   播放线路配置，每条线路对应视频表的某一条播放地址
 */

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class PlaybackaddressController extends AdminbaseController{
    public  function index(){
        $tenant_id = getTenantIds();
        // 如果没有，则添加网站设置
        add_playback_address($tenant_id);

        $count= M("playback_address")->where(['tenant_id'=> $tenant_id])->count();
        $page = $this->page($count);
        $playback_address_list =M("playback_address")->where(['tenant_id'=> intval($tenant_id)])->order('is_enable desc')->limit($page->firstRow . ',' . $page->listRows)->select();

        $this->assign('playback_address_list',$playback_address_list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public  function edit(){
        $id = $_REQUEST['id'];
        $playback_address = M("playback_address")->where(array('id' =>$id ))->find();
        if ($_POST['id']){
            $param = I('param.');
            $id = $_POST['id'];
            $name = $_POST['name'];
            if (empty($name)){
                $this->error('请输入名称');
            }
            $java_field = $_POST['java_field'];
            $url =trim($_POST['url']);
            if (empty($name)){
                $this->error('请输入播放域名');
            }
            $data = array(
                'name'=>$name,
                'url' => $url,
                'java_field' => $java_field,
            );

            $replace_domain_arr = array();
            if(isset($param['replace_domain']) && $param['replace_domain']){
                $arr_cut_video_url = explode("\n", $param['replace_domain']);
                foreach ($arr_cut_video_url as $key=>$val){
                    $val = trim($val);
                    $val = urldecode($val);
                    if($val){
                        array_push($replace_domain_arr,$val);
                    }
                }
            }
            $data['replace_domain'] = implode("\n",$replace_domain_arr);

            if ($playback_address['type'] == 1){
                $shot_video = M("video")->where(array('origin' => array('neq',3)))->where(['tenant_id'=>getTenantIds()])->field("id,{$playback_address['viode_table_field']}")->order('id desc')->select();
                foreach ($shot_video as $shot_key => $shot_value){
                    if(file_exists($_SERVER['DOCUMENT_ROOT'].$shot_value[$playback_address['viode_table_field']])){
                        $fil_contents = file_get_contents($_SERVER['DOCUMENT_ROOT'].$shot_value[$playback_address['viode_table_field']]);
                        if($playback_address['name'] == 'minio' && strrpos($fil_contents,'/liveprod-store-1039') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                            $contents = str_replace($playback_address['url'],trim($url,'/').'/liveprod-store-1039',$fil_contents);
                        }else{
                            $contents = str_replace($playback_address['url'],$url,$fil_contents);
                        }
                        $contents = str_replace('/liveprod-store-1039/liveprod-store-1039','/liveprod-store-1039',$contents);
                        foreach ($replace_domain_arr as $replace_domain_k=>$replace_domain_v){
                            $contents = str_replace($replace_domain_v, $url, $contents);
                        }
                        file_put_contents($_SERVER['DOCUMENT_ROOT']  . $shot_value[$playback_address['viode_table_field']], $contents);
                    }
                }
                $long_video = M("video_long")->where(array('origin' => array('neq',3)))->where(['tenant_id'=>getTenantIds()])->field("{$playback_address['viode_table_field']}")->order('id desc')->select();
                foreach ($long_video as $long_key => $long_value){
                    if(file_exists($_SERVER['DOCUMENT_ROOT'].$long_value[$playback_address['viode_table_field']])){
                        $fil_contents = file_get_contents($_SERVER['DOCUMENT_ROOT'].$long_value[$playback_address['viode_table_field']]);
                        $contents = str_replace($playback_address['url'],$url,$fil_contents);
                        foreach ($replace_domain_arr as $replace_domain_k=>$replace_domain_v){
                            $contents = str_replace($replace_domain_v, $url, $contents);
                        }
                        file_put_contents($_SERVER['DOCUMENT_ROOT']  . $long_value[$playback_address['viode_table_field']], $contents);
                    }
                }
                $bar = M("bar")->where(['tenant_id'=>getTenantIds()])->field("{$playback_address['viode_table_field']}")->order('id desc')->select();
                foreach ($bar as $bar_key => $bar_value){
                    if(file_exists($_SERVER['DOCUMENT_ROOT'].$bar_value[$playback_address['viode_table_field']])){
                        $fil_contents = file_get_contents($_SERVER['DOCUMENT_ROOT'].$bar_value[$playback_address['viode_table_field']]);
                        $contents = str_replace($playback_address['url'],$url,$fil_contents);
                        foreach ($replace_domain_arr as $replace_domain_k=>$replace_domain_v){
                            $contents = str_replace($replace_domain_v, $url, $contents);
                        }
                        file_put_contents($_SERVER['DOCUMENT_ROOT']  . $bar_value[$playback_address['viode_table_field']], $contents);
                    }
                }
            }


            M("playback_address")->where(array('id' =>$id ))->save($data);
            $this->success('修改成功!');
        }

        $playback_address['replace_domain_rows'] = count(explode("\n",$playback_address['replace_domain'])) + 1;

        $this->assign('playback_address',$playback_address);
        $this->display();
    }

    public function upstatus(){
        $id     =   intval(I('get.id'));
        $type =  intval(I('get.type'));
        $tenantId  =  getTenantIds();
        M("playback_address")->where(array('id' =>array('neq',$id),'type'=>$type  ))->where(['tenant_id'=> $tenantId])->save(array('is_enable' =>0));
        M("playback_address")->where(array('id' =>$id ))->save(array('is_enable' =>1));

        $action = '将id'.$id .'设为主用线路';
        setAdminLog($action);
        $this->success('设置成功!');

    }

}