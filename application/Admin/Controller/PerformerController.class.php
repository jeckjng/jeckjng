<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class PerformerController extends AdminbaseController
{
    public  function index(){
        $count=M('performer')->count();
        $page = $this->page($count);
        $list = M('performer')->limit($page->firstRow . ',' . $page->listRows)->select();
        foreach ($list as $key =>$value){
            $list[$key]['label_name'] = M('video_label')->where(array('id' =>$value['label'] ))->getField('label');
        }
        $this->assign("list",$list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public  function add(){
        $label =  M('video_label')->select();
        $this->assign("label",$label);
        $performer = array();
        $id = I("get.id");
        if ($id){
            $performer =  M('performer')->where(array('id' => $id))->find();
        }
        $this->assign("id",$id);
        $this->assign("performer",$performer);
        $this->display();
    }

    public  function add_post(){
        $name   = I("post.name");
        $title  = I("post.title");
        $desc   = I("post.desc");
        $label  = I("post.label");
        $popularity  = I("post.popularity");
        $age = I("post.age");
        $region = I("post.region");

        if (!$name){
            $this->error("请填写演员名称！");
        }
        if (!$name){
            $this->error("请填写标题！");
        }
        $id = I("post.id");

        $performerModel = M("performer");
        if ($id){
            $rateInfo = $performerModel->where(['name' =>$name,'id'=>array('neq',$id) ])->find();
        }else{
            $rateInfo = $performerModel->where(['name' =>$name ])->find();
            if (!$_FILES['avatar']){
                $this->error("请上传演员头像！");
            }
        }

        if ($rateInfo){
            $this->error("此演员已添加！");
        }


        if($_FILES['avatar']) {
            $savepath = date('Ymd') . '/';
            //上传处理类
            $config = array(
                'rootPath' => './' . C("UPLOADPATH"),
                'savePath' => $savepath,
                'maxSize' => 11048576,
                'saveName' => array('uniqid', ''),
                'exts' => array('svga'),
                'autoSub' => false,
            );
            $upload = new \Think\Upload($config);//
            $info = $upload->upload();
            //开始上传
            if ($info) {
                //上传成功
                //写入附件数据库信息
                $first = array_shift($info);
                if (!empty($first['url'])) {
                    $url = $first['url'];
                } else {
                    $url = C("TMPL_PARSE_STRING.__UPLOAD__") . $savepath . $first['savename'];
                }
                $url = str_replace("http", "https", $url);

            } else {
                //上传失败，返回错误
                $this->error($upload->getError());
            }
        }else{
            $url = I("post.avatar_edit");
        }
        if ($id){
            $performerModel->where(array('id' =>$id))->save(
                array(
                    'name' => $name,
                    'title' => $title ,
                    'age' => $age,
                    'desc' => $desc,
                    'avatar' => $url,
                    'popularity' => $popularity,
                    'label' =>  $label,
                    'region' => $region,
                    'addtime' => time(),
                )
            );
            $action = '修改币种';
            setAdminLog($action);
        }else{
            $performerModel->create();
            $performerModel->add(
                array(
                    'name' => $name,
                    'title' => $title ,
                    'age' => $age,
                    'desc' => $desc,
                    'avatar' => $url,
                    'popularity' => $popularity,
                    'label' =>  $label,
                    'region' => $region,
                )
            );
            $action = '添加演员';
            setAdminLog($action);
        }

        $this->success("操作成功！", U("Performer/index"));
    }
}