<?php
/**
 * Created by PhpStorm.
 * User: jonem
 * Date: 2021/11/15
 * Time: 23:44
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Composer\Package\Loader\ValidatingArrayLoader;
use QCloud\Cos\Api;
use QCloud\Cos\Auth;
use PHPExcel\PHPExcel\APC;
use Admin\Cache\LiveingSet;

class LotterysController extends AdminbaseController {


    /*待审核视频列表*/
    public function index(){
        $param = I('param.');
        $map = array();
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map['tenant_id'] = $tenant_id;

        if(isset($param['status']) && $param['status'] != ''){
            $map['status'] = $param['status'];
        }

        $model = M("lottery_config");
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);
        $list = $model->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach ($list as $key=>$val){
            $tenantInfo = getTenantInfo($val['tenant_id']);
            $list[$key]['tenant_name'] = $tenantInfo ? $tenantInfo['name'] : $val['tenant_id'];
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign('tenant_list', getTenantList());
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public  function  add(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $this->assign('tenant_id', $tenant_id);
        $this->assign('tenant', getTenantList());
        $this->display();
    }

    public  function  add_post(){

        $tenantinfo =  explode('_', $_POST['tenantinfo']);
        $tenant_id = $tenantinfo[0];
        $game_tenant_id = $tenantinfo[1];


        $play_cname = $_POST['play_cname'];
        if (empty($play_cname)){
            $this->error('请填写彩种名称');
        }
        $play_name = $_POST['play_name'];
        if (empty($play_name)){
            $this->error('请填写彩种简称名称');
        }
        $upper_id = $_POST['upper_id'];
        if ($upper_id === ''){
            $this->error('请填写upper_id');
        }
        $play_id = $_POST['play_id'];
        if (empty($play_id)){
            $this->error('请填写play_id名称');
        }
        $status = $_POST['status'];
        $data['play_name'] = $play_name;
        $data['upper_id'] = $upper_id;
        $data['play_id'] = $play_id;
        $data['status'] = $status;
        $data['play_cname'] = $play_cname;
        $data['tenant_id'] = $tenant_id;
        $data['game_tenant_id'] = $game_tenant_id;
        $success = M('lottery_config')->add($data);
        $id= M('lottery_config')->getLastInsID();
        if ($success){
            $action="添加彩种配置,修改id为".$id;
            $this->success('添加成功', U('index', array('tenant_id'=>$tenant_id)));
        }else{
            $this->success('添加失败');
        }
    }

    public function edit(){
        $param = I('param.');
        $id = $_GET['id'];
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $info = M('lottery_config')->where(array('id' => $id))->find();

        $this->assign('tenant_id', $tenant_id);
        $this->assign('tenant', getTenantList());
        $this->assign('info', $info);
        $this->display();
    }

    public  function edit_post(){
        $id = $_POST['id'];
        $tenantinfo =  explode('_', $_POST['tenantinfo']);
        $tenant_id = $tenantinfo[0];
        $game_tenant_id = $tenantinfo[1];
        $play_cname = $_POST['play_cname'];
        if (empty($play_cname)){
            $this->error('请填写彩种名称');
        }
        $play_name = $_POST['play_name'];
        if (empty($play_name)){
            $this->error('请填写彩种简称名称');
        }
        $upper_id = $_POST['upper_id'];
        if ($upper_id === ''){
            $this->error('请填写upper_id');
        }
        $play_id = $_POST['play_id'];
        if (empty($play_id)){
            $this->error('请填写play_id名称');
        }
        $status = $_POST['status'];
        $data['play_name'] = $play_name;
        $data['upper_id'] = $upper_id;
        $data['play_id'] = $play_id;
        $data['status'] = $status;
        $data['play_cname'] = $play_cname;
        $data['tenant_id'] = $tenant_id;
        $data['game_tenant_id'] = $game_tenant_id;
        M('lottery_config')->where(array('id'=>$id))->save($data);

        $this->success('修改成功', U('index', array('tenant_id'=>$tenant_id)));

    }

    /*待审核视频列表*/
    public function livesetting(){
        $param = I('param.');
        $map = array();
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map['tenant_id'] = $tenant_id;

        if(isset($param['uid']) && $param['uid'] != ''){
            $map['uid'] = $param['uid'];
        }

        $model = M("liveing_set");
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);
        $list = $model->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();


        $lottery_config = M("lottery_config")->where(['tenant_id'=>$tenant_id])->order("id DESC")->select();
        $playlist = array();
        foreach ($lottery_config as $key=>$val){
            $play_id_t = $val['play_id'].$val['tenant_id'];
            $playlist[$play_id_t] = $val;
        }


        foreach ($list as $key=>$val){
            $play_id_t = $val['game_recommend'].$val['tenant_id'];
            $list[$key]['game_recommend'] = isset($playlist[$play_id_t]) ? $playlist[$play_id_t]['play_cname'] : $val['game_recommend'];
            $game_list = explode(',',$val['game_list']);
            $str = '';
            foreach ($game_list as $k=>$v){
                $play_id_t = $v.$val['tenant_id'];
                if($v && isset($playlist[$play_id_t])){
                    $str .= $playlist[$play_id_t]['play_cname'].'，';
                }
            }
            $list[$key]['game_list'] = trim($str,'，');
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign('tenant_list', getTenantList());
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public  function  livesetting_add(){
        $param = I('param.');

        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $lottery_config =M("lottery_config")->where(['tenant_id'=>$tenant_id])->order("id DESC")->select();

        $nowtime = date("Y-m-d", time()).'T'.date("H:i", time());
     //   $nowtime = "2021-12-04T15:54";

        $this->assign('tenant_id', $tenant_id);
        $this->assign('nowtime', $nowtime);
        $this->assign('tenant', getTenantList());
        $this->assign('lottery_config', $lottery_config);
        $this->display();
    }

    public  function  livesetting_addpost(){
        $param = I('param.');
        $uid = $_POST['uid'];
        if (empty($uid)){
            $this->error('会员ID不能为空');
        }

        $data['uid'] = $uid;
        $tenantinfo =  explode('_', $_POST['tenamtinfo']);
        $tenant_id = $tenantinfo[0];
        $game_tenant_id = $tenantinfo[1];
        $data['tenant_id'] = $tenant_id;
        $data['game_tenant_id'] = $game_tenant_id;
        $data['tenant_name'] = $tenantinfo[2];

        $map['tenant_id'] = $tenant_id;
        $map['game_tenant_id'] = $game_tenant_id;
        $map['uid'] = $uid;
        $lottery_set =M("liveing_set")->where($map)->order("id DESC")->select();
        if (!empty($lottery_set)){
            $this->error('该会员ID已经添加，请勿重复添加！');
        }

        if ($_POST['is_template'] == 1){
            $maps['tenant_id'] = $tenant_id;
            $maps['game_tenant_id'] = $game_tenant_id;
            $maps['is_template'] = $_POST['is_template'];
            $lottery_sets =M("liveing_set")->where($maps)->order("id DESC")->find();
            if (!empty($lottery_sets)){
                    $this->error('该租户已经添加模板数据，请勿重复添加！');
            }
        }



        /* $game_recommend ='';
         foreach ($_POST['game_recommend'] as $key=>$value){
             $recommend =  explode('_', $value);
             $game_recommend .= $recommend[2].',';
         }
         $game_recommend = rtrim($game_recommend, ',');
         $data['game_recommend'] = $game_recommend;*/

        $game_recommend =  explode('_', $_POST['game_recommend']);
        $data['game_recommend'] = $game_recommend[0];
        $game_list ='';
        foreach ($_POST['game_list'] as $key=>$value){
            $list =  explode('_', $value);
            $game_list .= $list[0].',';
        }
        $game_list = rtrim($game_list, ',');
        $data['game_list'] = $game_list;

        $data['game_open'] = $_POST['game_open'];
        $data['game_discount'] = $_POST['game_discount'];
        $data['game_longbet'] = $_POST['game_longbet'];
        $data['game_rewards'] = $_POST['game_rewards'];
        $data['game_follow'] = $_POST['game_follow'];
        $data['game_sharebet'] = $_POST['game_sharebet'];
        $data['game_iswork'] = $_POST['game_iswork'];
        $data['game_halfscreen'] = $_POST['game_halfscreen'];
        $data['is_template'] = $_POST['is_template'];
        $data['time_start'] =strtotime($_POST['time_start']);
        $data['time_end'] =strtotime($_POST['time_end']);

        $success = M('liveing_set')->add($data);
        if ($success){
            LiveingSet::getInstance()->delLiveingSetListCache($tenant_id); // 清理直播间设置列表信息缓存
            $this->success('添加成功', U('livesetting', array('tenant_id'=>$tenant_id)));
        }else{
            $this->success('添加失败');
        }
    }
    public function livesetting_edit(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $lottery_config = M("lottery_config")->where(['tenant_id'=>$tenant_id])->order("id DESC")->select();
        $id = $_REQUEST['id'];
        $info = M("liveing_set")->where(array('id' => $id))->order("id DESC")->find();


        $info['time_start'] = date("Y-m-d",  $info['time_start']).'T'.date("H:i",  $info['time_start']);
        $info['time_end'] = date("Y-m-d", $info['time_end']).'T'.date("H:i", $info['time_end']);
        $game_list =  explode(',',  $info['game_list']);
      /*  $game_recommend =  explode(',',  $info['game_recommend']);*/
        foreach ($lottery_config as $key=>$value) {
            foreach ($game_list as $vauel1){
                   if($value['play_id'] == $vauel1){
                       $lottery_config[$key]['list_use'] = 1;
                       break;
                   }else{
                       $lottery_config[$key]['list_use'] = 0;
                   }
            }
            if($value['play_id'] ==$info['game_recommend']){
                $lottery_config[$key]['recommend_use'] = 1;
            }else{
                $lottery_config[$key]['recommend_use'] = 0;
            }
          /*  foreach ($game_recommend as $vauel2){
                if($value['play_cname'] == $vauel2){
                    $lottery_config[$key]['recommend_use'] = 1;
                    break;
                }else{
                    $lottery_config[$key]['recommend_use'] = 0;
                }
            }*/


        }
     /*   print_r('<pre>');
        print_r($lottery_config);
        print_r('<pre>');
        print_r('<pre>');
        print_r($game_list);
        print_r('<pre>');
        exit;*/

        $this->assign('lottery_config', $lottery_config);
        $this->assign('tenant', getTenantList());
        $this->assign('info', $info);
        $this->display();
    }

    public  function  livesetting_editpost(){
        $param = I('param.');
        $id = $_POST['id'];

        $uid = $_POST['uid'];
        if (empty($uid)){
            $this->error('会员ID不能为空');
        }
        $data['uid'] = $uid;
        $tenantinfo =  explode('_', $_POST['tenamtinfo']);
        $tenant_id = $tenantinfo[0];
        $game_tenant_id = $tenantinfo[1];
        $data['tenant_id'] = $tenant_id;
        $data['game_tenant_id'] = $game_tenant_id;
        $data['tenant_name'] = $tenantinfo[2];



        if ($_POST['is_template'] == 1){
            $maps['tenant_id'] = $tenant_id;
            $maps['game_tenant_id'] = $game_tenant_id;
            $maps['is_template'] = $_POST['is_template'];
            $lottery_sets =M("liveing_set")->where($maps)->order("id DESC")->select();

            /*print_r('<pre>');
            print_r($lottery_sets);
            print_r('<pre>');exit;*/
            if (!empty($lottery_sets)){
                foreach ($lottery_sets as $key => $value)
               if ( $value['uid'] != $uid){
                   $this->error('该租户已经添加模板数据，请勿重复添加！');
               }

            }
        }




        /*  $game_recommend ='';
          foreach ($_POST['game_recommend'] as $key=>$value){
              $recommend =  explode('_', $value);
              $game_recommend .= $recommend[2].',';
          }
          $game_recommend = rtrim($game_recommend, ',');
          $data['game_recommend'] = $game_recommend;*/
        $game_recommend =  explode('_', $_POST['game_recommend']);
        $data['game_recommend'] = $game_recommend[0];

        $game_list ='';
        foreach ($_POST['game_list'] as $key=>$value){
            $list =  explode('_', $value);
            $game_list .= $list[0].',';
        }
        $game_list = rtrim($game_list, ',');
        $data['game_list'] = $game_list;

        $data['game_open'] = $_POST['game_open'];
        $data['game_discount'] = $_POST['game_discount'];
        $data['game_longbet'] = $_POST['game_longbet'];
        $data['game_rewards'] = $_POST['game_rewards'];
        $data['game_follow'] = $_POST['game_follow'];
        $data['game_sharebet'] = $_POST['game_sharebet'];
        $data['game_iswork'] = $_POST['game_iswork'];
        $data['game_halfscreen'] = $_POST['game_halfscreen'];
        $data['is_template'] = $_POST['is_template'];
        $data['time_start'] =strtotime($_POST['time_start']);
        $data['time_end'] =strtotime($_POST['time_end']);
        //$success = M('liveing_set')->add($data);
        M('liveing_set')->where(array('id'=>$id))->save($data);

        LiveingSet::getInstance()->delLiveingSetListCache($tenant_id); // 清理直播间设置列表信息缓存

        $this->success('修改成功', U('livesetting', array('tenant_id'=>$tenant_id)));

    }

}

