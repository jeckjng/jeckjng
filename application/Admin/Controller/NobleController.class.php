<?php

/**
 * 经验等级
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

class NObleController extends AdminbaseController {

    private $goGroup = '/live_noble';

    protected $status_list = array(
        '0' => '否',
        '1' => '是',
    );

    protected $type_list = array(
        '1' => '正常开通',
        '2' => '续费',
        '3' => '升级',
    );

    public function _initialize() {
        parent::_initialize();
    }

    public function index(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;
        $http_post_map = [
            'tenant_id' => intval($tenant_id),
            'page' => intval($p),
            'page_size' => intval($page_size),
            'level' => 0,
        ];
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $map['tenant_id'] = $tenant_id;
        if(isset($param['level']) && $param['level'] != ''){
            $map['level'] = $param['level'];
            $http_post_map['level'] = intval($param['level']);
        }

        if(enableGolangReplacePhp() === true){
            // golang替换
            $url = goAdminUrl().goAdminRouter().$this->goGroup.'/get_noble_level_list';
            $http_post_res = http_post($url, $http_post_map);
            $Data = $http_post_res['Data'];
            $page = $this->page($Data['count'], $page_size);
            $lists = $Data['list'];
        }else{
            $count = M('noble')->where($map)->count();
            $page = $this->page($count, $page_size);
            $lists = M('noble')
                ->where($map)
                ->order("level asc")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
            foreach($lists as $key=>$val){
                $lists[$key]['medal'] = get_upload_path($val['medal']);
                $lists[$key]['knighthoodcard'] = get_upload_path($val['knighthoodcard']);
                $lists[$key]['avatar_frame'] = get_upload_path($val['avatar_frame']);
                $lists[$key]['pubchat_bgskin'] = get_upload_path($val['pubchat_bgskin']);
                $lists[$key]['exclu_card'] = get_upload_path($val['exclu_card']);
                $lists[$key]['operated_by'] = getUserInfo($val['act_uid'])['user_login'];
                $lists[$key]['created_at'] = date('Y-m-d H:i:s',$val['ctime']);
                $lists[$key]['updated_at'] = $val['mtime'] ? date('Y-m-d H:i:s',$val['mtime']) : '-';
            }
        }

        foreach($lists as $key=>$val){
            if(($key+1) == count($lists) && (ceil($count/$page_size) == $p || $count <= $page_size)){
                $lists[$key]['del'] = 1;
            }else{
                $lists[$key]['del'] = 0;
            }
        }

    	$this->assign('lists', $lists);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('status_list',$this->status_list);
        $this->assign('name_coin',getConfigPri($tenant_id)['name_coin']);
    	$this->display();
    }

    public function add(){
		if(IS_POST){
            $param = I('param.');
            if($param['exclu_car'] == 1 && !$param['car_id']){
               $this->error('请选择坐骑');
            }
            if($param['special_effect'] == 1  && ($param['special_effect_swftime'] <= 0 || $param['special_effect_swftime'] > 9999)){
                $this->error('动画时长不合法，请输入该范围：[1 - 9999]');
            }
            if($param['upgrade_speed'] < 0 || $param['upgrade_speed'] > 100){
                $this->error('升级加速不合法，请输入该范围：[0 - 100]');
            }

            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

            if(enableGolangReplacePhp() === true){
                // golang替换
                $data = array(
                    'tenant_id' => intval($param['tenant_id']),
                    'name' => $param['name'],
                    'name_color' => $param['name_color'],
                    'price' => floatval($param['price']),
                    'renewal_price' => floatval($param['renewal_price']),
                    'avatar_frame' => $param['avatar_frame'],
                    'medal' => $param['medal'],
                    'knighthood_card' => $param['knighthoodcard'],
                    'background_skin_of_public_chat' => $param['pubchat_bgskin'],
                    'upgrade_speed' => intval($param['upgrade_speed']),
                    'enable_special_effect' => intval($param['special_effect'])==1 ? true : false,
                    'special_effect_swf' => $param['special_effect_swf'],
                    'special_effect_swf_time' => floatval($param['special_effect_swftime']),
                    'enable_golden_light_of_entry_room' => intval($param['golden_light'])==1 ? true : false,
                    'enable_stealth_of_entry_room' => intval($param['enter_stealth'])==1 ? true : false,
                    'enable_exclusive_custom_services' => intval($param['exclu_custsevice'])==1 ? true : false,
                    'enable_exclusive_car' => intval($param['exclu_car'])==1 ? true : false,
                    'exclusive_car_id' => intval($param['car_id']),
                    'exclusive_car_nobleicon' => $param['exclu_car_nobleicon'],
                    'enable_stealth_of_ranking' => intval($param['ranking_stealth'])==1 ? true : false,
                    'enable_prevent_mute' => intval($param['prevent_mute'])==1 ? true : false,
                    'enable_broadcast' => intval($param['broadcast'])==1 ? true : false,
                    'rebate_of_first_active' => floatval($param['handsel']),
                    'rebate_of_renewal' => floatval($param['renewal_handsel']),
                    'operated_by' => get_current_admin_user_login(),
                );
                $url = goAdminUrl().goAdminRouter().$this->goGroup.'/add_noble_level';
                $http_post_res = http_post($url, $data);
                if($http_post_res['Code'] != 0){
                    $this->error('操作失败: '.$http_post_res['Desc']);
                }
            }else{
                $info = M('noble')->where(['tenant_id'=>$tenant_id])->field("MAX(level) as level")->find();

                $param['level'] = isset($info['level'])&&$info['level']>=0 ? ($info['level']+1) : 1;
                if($param['level'] > 500){ // 等级不能大于500
                    $this->error('等级已经超出上限500');
                }
                if(M('noble')->where(['tenant_id'=>$tenant_id,'name'=>$param['name']])->find()){
                    $this->error('已存在该名称，请重新输入');
                }
                $data = $param;
                foreach ($data as $key=>$val){
                    $data[$key] = trim($val);
                }

                $data['tenant_id'] = $tenant_id;
                $data['act_uid'] = get_current_admin_id();
                $data['ctime'] = time();

                try{
                    M('noble')->add($data);
                }catch (\Exception $e){
                    setAdminLog('添加贵族等级失败：'.$e->getMessage());
                    $this->error('操作失败');
                }
            }
            delNobleList($tenant_id); // 清除贵族等级列表缓存

            $this->success('操作成功', U('index',array('tenant_id'=>$tenant_id)));
		}

        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $skin_list = getNobleSkinList($tenant_id);
        $skin_list_json = count($skin_list) > 0 ? json_encode($skin_list) : '';

        $this->assign('tenant_list',getTenantList());
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_id',$tenant_id);
        $this->assign('car_list',get_carlist($tenant_id));
        $this->assign('skin_list_json',htmlentities($skin_list_json));
        $this->display();
    }

    public function edit(){
        $param = I('param.');
        if(IS_POST){
            if(!isset($param['id']) || !$param['id']){
                $this->error('参数错误');
            }
            if($param['special_effect'] == 1  && ($param['special_effect_swftime'] <= 0 || $param['special_effect_swftime'] > 9999)){
                $this->error('动画时长不合法，请输入该范围：[1 - 9999]');
            }
            if($param['upgrade_speed'] < 0 || $param['upgrade_speed'] > 100){
                $this->error('升级加速不合法，请输入该范围：[0 - 100]');
            }
            if($param['exclu_car'] == 1 && !$param['car_id']){
                $this->error('请选择坐骑');
            }

            if(enableGolangReplacePhp() === true){
                // golang替换
                $data = array(
                    'id' => intval($param['id']),
                    'tenant_id' => intval($param['tenant_id']),
                    'level' => intval($param['level']),
                    'name' => $param['name'],
                    'name_color' => $param['name_color'],
                    'price' => floatval($param['price']),
                    'renewal_price' => floatval($param['renewal_price']),
                    'avatar_frame' => $param['avatar_frame'],
                    'medal' => $param['medal'],
                    'knighthood_card' => $param['knighthoodcard'],
                    'background_skin_of_public_chat' => $param['pubchat_bgskin'],
                    'upgrade_speed' => intval($param['upgrade_speed']),
                    'enable_special_effect' => intval($param['special_effect'])==1 ? true : false,
                    'special_effect_swf' => $param['special_effect_swf'],
                    'special_effect_swf_time' => floatval($param['special_effect_swftime']),
                    'enable_golden_light_of_entry_room' => intval($param['golden_light'])==1 ? true : false,
                    'enable_stealth_of_entry_room' => intval($param['enter_stealth'])==1 ? true : false,
                    'enable_exclusive_custom_services' => intval($param['exclu_custsevice'])==1 ? true : false,
                    'enable_exclusive_car' => intval($param['exclu_car'])==1 ? true : false,
                    'exclusive_car_id' => intval($param['car_id']),
                    'exclusive_car_nobleicon' => $param['exclu_car_nobleicon'],
                    'enable_stealth_of_ranking' => intval($param['ranking_stealth'])==1 ? true : false,
                    'enable_prevent_mute' => intval($param['prevent_mute'])==1 ? true : false,
                    'enable_broadcast' => intval($param['broadcast'])==1 ? true : false,
                    'rebate_of_first_active' => floatval($param['handsel']),
                    'rebate_of_renewal' => floatval($param['renewal_handsel']),
                    'operated_by' => get_current_admin_user_login(),
                );
                $url = goAdminUrl().goAdminRouter().$this->goGroup.'/update_noble_level';
                $http_post_res = http_post($url, $data);
                if($http_post_res['Code'] != 0){
                    $this->error('操作失败: '.$http_post_res['Desc']);
                }
            }else{
                $info = M('noble')->where(['id'=>intval($param['id'])])->find();

                if(M('noble')->where(['tenant_id'=>$info['tenant_id'],'id'=>['neq',intval($param['id'])],'name'=>$param['name']])->find()){
                    $this->error('已存在该名称，请重新输入');
                }
                $data = $param;
                foreach ($data as $key=>$val){
                    $data[$key] = trim($val);
                }

                $data['act_uid'] = get_current_admin_id();
                $data['mtime'] = time();

                try{
                    M('noble')->where(['id'=>intval($param['id'])])->save($data);
                }catch (\Exception $e){
                    setAdminLog('添加贵族等级失败：'.$e->getMessage());
                    $this->error('操作失败');
                }
            }
            delNobleList($param['tenant_id']); // 清除贵族等级列表缓存

            $this->success('操作成功', U('index',array('tenant_id'=>$param['tenant_id'])));
        }

        if(enableGolangReplacePhp() === true){
            // golang替换
            $info = $this->getGolangNobleInfo($param['tenant_id'], $param['level']);
        }else{
            $id = I('id');
            if(!$id){
                $this->error('参数错误');
            }
            $info = M('noble')->where(['id'=>intval($id)])->find();
        }

        $skin_list = getNobleSkinList($info['tenant_id']);
        $skin_list_json = count($skin_list) > 0 ? json_encode($skin_list) : '';

        $this->assign('tenant_list',getTenantList());
        $this->assign('role_id',getRoleId());
        $this->assign('info',$info);
        $this->assign('car_list',get_carlist($info['tenant_id']));
        $this->assign('skin_list_json',htmlentities($skin_list_json));
        $this->display();
	}

	public function getGolangNobleInfo($tenant_id, $level){
        $url = goAdminUrl().goAdminRouter().$this->goGroup.'/get_noble_level_info';
        $http_post_res = http_post($url, ['tenant_id'=>intval($tenant_id),'level'=>intval($level)]);
        return $http_post_res['Data'];
    }

    public function del(){
        $param = I('param.');
        if(enableGolangReplacePhp() === true){
            // golang替换
            $data = array(
                'tenant_id' => intval($param['tenant_id']),
                'level' => intval($param['level']),
            );
            $http_post_res = http_post(goAdminUrl().goAdminRouter().$this->goGroup.'/del_noble_level', $data);
            if($http_post_res['Code'] != 0){
                $this->error('操作失败: '.$http_post_res['Desc']);
            }
            delNobleList($param['tenant_id']); // 清除贵族等级列表缓存
            $this->success('操作成功',U('index',array('tenant_id'=>$param['tenant_id'])));
        }else{
            $id=intval($_GET['id']);
            if($id){
                $info = M('noble')->where(['id'=>intval($id)])->find();
                $count = M("users_noble")->where(['tenant_id'=>$info['tenant_id'], 'level'=>intval($info['level']),['etime'=>['gt',time()]]])->count();
                if($count > 0){
                    $this->error('用户开通的贵族在使用中，不能删除');
                }
                try{
                    $res = M('noble')->where(['id'=>intval($id)])->delete();
                    if($res){
                        M("users_noble")->where(['tenant_id'=>$info['tenant_id'], 'level'=>intval($info['level'])])->delete();
                    }
                }catch (\Exception $e){
                    setAdminLog('删除贵族等级失败：'.$e->getMessage());
                    $this->error('操作失败');
                }
                delNobleList($info['tenant_id']); // 清除贵族等级列表缓存
                setAdminLog('删除贵族等级成功【'.$id.'】');

                $this->success('操作成功',U('index',array('tenant_id'=>$info['tenant_id'])));
            }else{
                $this->error('参数错误');
            }
        }
    }

    /*
     * 用户贵族
     * */
    public function usernoble(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;
        $http_post_map = [
            'tenant_id' => intval($tenant_id),
            'page' => intval($p),
            'page_size' => intval($page_size),
            'user_id' => 0,
            'username' => '',
            'third_user_id' => '',
        ];
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $map['tenant_id'] = $tenant_id;
        if(isset($param['user_login']) && $param['user_login'] != ''){
            $map['user_login'] = $param['user_login'];
            $http_post_map['username'] = $param['user_login'];
        }
        if(isset($param['uid']) && $param['uid'] != ''){
            $map['uid'] = $param['uid'];
            $http_post_map['user_id'] = intval($param['uid']);
        }
        if($_REQUEST['game_user_id'] != ''){
            $map['game_user_id'] = $_REQUEST['game_user_id'];
            $http_post_map['third_user_id'] = $param['game_user_id'];
        }

        if(enableGolangReplacePhp() === true){
            // golang替换
            $url = goAdminUrl().goAdminRouter().$this->goGroup.'/get_noble_users_list';
            $http_post_res = http_post($url, $http_post_map);
            $Data = $http_post_res['Data'];
            $page = $this->page($Data['count'], $page_size);
            $lists = $Data['list'];
        }else {
            $count = M('users_noble')->where($map)->count();
            $page = $this->page($count, $page_size);
            $lists = M('users_noble')
                ->where($map)
                ->order("id desc")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
            $nobleids = count($lists) > 0 ? array_keys(array_column($lists,'noble_id','noble_id')) : [];
            $noble_list = count($nobleids) > 0 ? M('noble')->field('id,name')->where(['id'=>['in',$nobleids]])->select() : [];
            $noble_list = count($noble_list) > 0 ? array_column($noble_list,null,'id') : [];

            foreach($lists as $key=>$val){
                $lists[$key]['noble_name'] = isset($noble_list[$val['noble_id']]) ? $noble_list[$val['noble_id']]['name'] : $val['noble_id'];
                $lists[$key]['status'] = $val['etime'] < time() ? '已过期' : '已开通';
                $lists[$key]['expire_time'] = date('Y-m-d H:i:s',$val['etime']);
            }
        }

        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('status_list',$this->status_list);
        $this->display();
    }

    /*
    * 开通记录
    * */
    public function openrecord(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;
        $http_post_map = [
            'tenant_id' => intval($tenant_id),
            'page' => intval($p),
            'page_size' => intval($page_size),
            'created_at_start' => 0,
            'created_at_end' => 0,
            'username' => '',
            'user_id' => 0,
            'third_user_id' => '',
        ];
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $map['tenant_id'] = $tenant_id;
        if(isset($param['stime']) && $param['stime']!=''){
            $map['ctime']=array("gt",strtotime($param['stime']));
            $http_post_map['created_at_start'] = intval(strtotime($param['stime']));
        }
        if(isset($param['etime']) && $param['etime']!=''){
            $map['ctime']=array("lt",strtotime($param['etime'].' 23:59:59'));
            $http_post_map['created_at_end'] = intval(strtotime($param['etime'].' 23:59:59'));
        }
        if(isset($param['stime']) && isset($param['etime']) && $param['stime']!='' && $param['etime']!='' ){
            $map['ctime']=array("between",array(strtotime($param['stime']),strtotime($param['etime'])));
        }
        if(isset($param['user_login']) && $param['user_login'] != ''){
            $map['user_login'] = $param['user_login'];
            $http_post_map['username'] = $param['user_login'];
        }
        if(isset($param['uid']) && $param['uid'] != ''){
            $map['uid'] = $param['uid'];
            $http_post_map['user_id'] = intval($param['uid']);
        }
        if($_REQUEST['game_user_id'] != ''){
            $map['game_user_id'] = $_REQUEST['game_user_id'];
            $http_post_map['third_user_id'] = $param['game_user_id'];
        }

        if(enableGolangReplacePhp() === true){
            // golang替换
            $url = goAdminUrl() . goAdminRouter() . $this->goGroup . '/get_noble_buy_record_list';
            $http_post_res = http_post($url, $http_post_map);
            $Data = $http_post_res['Data'];
            $page = $this->page($Data['count'], $page_size);
            $lists = $Data['list'];
        }else{
            $count = M('users_noble_log')->where($map)->count();
            $page = $this->page($count, $page_size);
            $lists = M('users_noble_log')
                ->where($map)
                ->order("id desc")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
            $nobleids = count($lists) > 0 ? array_keys(array_column($lists,'noble_id','noble_id')) : [];
            $noble_list = count($nobleids) > 0 ? M('noble')->field('id,name')->where(['id'=>['in',$nobleids]])->select() : [];
            $noble_list = count($noble_list) > 0 ? array_column($noble_list,null,'id') : [];

            foreach($lists as $key=>$val){
                $lists[$key]['noble_name'] = isset($noble_list[$val['noble_id']]) ? $noble_list[$val['noble_id']]['name'] : $val['noble_id'];
                $lists[$key]['created_at'] = date('Y-m-d H:i:s',$val['ctime']);
                $lists[$key]['third_user_id'] = $val['game_user_id'];
                $lists[$key]['username'] = $val['user_login'];
                $lists[$key]['user_id'] = $val['uid'];
                $lists[$key]['rebate'] = $val['handsel'];
            }
        }

        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('type_list',$this->type_list);
        $this->assign('name_coin',getConfigPri($tenant_id)['name_coin']);
        $this->display();
    }

    /*
     * 贵族配置
     * */
    public function setting(){
        if(IS_POST){
            $param = I('post.');
            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

            if(enableGolangReplacePhp() === true){
                // golang替换
                $data = array(
                    'tenant_id' => intval($tenant_id),
                    'enable_noble' => intval($param['status'])==1 ? true : false,
                    'details' => $param['details'],
                    'operated_by' => get_current_admin_user_login(),
                );
                $url = goAdminUrl().goAdminRouter().$this->goGroup.'/update_noble_tenant_config';
                $http_post_res = http_post($url, $data);
                if($http_post_res['Code'] != 0){
                    $this->error('操作失败: '.$http_post_res['Desc']);
                }
            }else{
                $info = M('noble_setting')->where(['id'=>intval($param['id'])])->find();

                $data['status'] = intval($param['status']);
                $data['details'] = trim($param['details']);
                $data['act_uid'] = get_current_admin_id();
                $data['mtime'] = time();

                try{
                    M('noble_setting')->where(['id'=>intval($param['id'])])->save($data);
                }catch (\Exception $e){
                    setAdminLog('贵族配置失败：'.$e->getMessage());
                    $this->error('操作失败');
                }
            }
            delNobleSetting($info['tenant_id']); // 清除贵族等级列表缓存
            $this->success('操作成功', U('setting',array('tenant_id'=>$tenant_id)));
        }
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        if(enableGolangReplacePhp() === true){
            // golang替换
            $url = goAdminUrl().goAdminRouter().$this->goGroup.'/get_tenant_noble_config';
            $http_post_res = http_post($url, ['tenant_id'=>intval($tenant_id)]);
            $info = $http_post_res['Data'];
        }else{
            $info = M("noble_setting")->where(['tenant_id'=>$tenant_id])->find();
            if(!$info){
                M("noble_setting")->add(['details'=>'','tenant_id'=>$tenant_id,'act_uid'=>get_current_admin_id(),'ctime'=>time()]);
                $info = M("noble_setting")->where(['tenant_id'=>$tenant_id])->find();
            }
        }
        $info['details_htmls'] = htmlspecialchars_decode($info['details']);

        $this->assign('info',$info);
        $this->assign('tenant_list',getTenantList());
        $this->assign('role_id',getRoleId());
        $this->display();
    }

    public function skin(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;
        $http_post_map = [
            'tenant_id' => intval($tenant_id),
            'page' => intval($p),
            'page_size' => intval($page_size),
        ];
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $map['tenant_id'] = $tenant_id;

        if(enableGolangReplacePhp() === true){
            // golang替换
            $url = goAdminUrl().goAdminRouter().$this->goGroup.'/get_noble_skin_list';
            $http_post_res = http_post($url, $http_post_map);
            $Data = $http_post_res['Data'];
            $page = $this->page($Data['count'], $page_size);
            $lists = $Data['list'];
        }else{
            $count = M('noble_skin')->where($map)->count();
            $page = $this->page($count, $page_size);

            $lists = M('noble_skin')
                ->where($map)
                ->order("id asc")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
            foreach($lists as $key=>$val){
                $lists[$key]['operated_by'] = getUserInfo($val['act_uid'])['user_login'];
                $lists[$key]['created_at'] = date('Y-m-d H:i:s',$val['ctime']);
                $lists[$key]['updated_at'] = $val['mtime'] ? date('Y-m-d H:i:s',$val['mtime']) : '-';
            }
        }

        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->display();
    }

    public function skinadd(){
        if(IS_POST){
            $param = I('post.');
            if(!isset($param['val']) || !$param['val']){
                $this->error('皮肤颜色不能为空');
            }

            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

            if(enableGolangReplacePhp() === true){
                // golang替换
                $data = array(
                    'tenant_id' => intval($param['tenant_id']),
                    'val' => $param['val'],
                    'operated_by' => get_current_admin_user_login(),
                );
                $url = goAdminUrl().goAdminRouter().$this->goGroup.'/add_noble_skin';
                $http_post_res = http_post($url, $data);
                if($http_post_res['Code'] != 0){
                    $this->error('操作失败: '.$http_post_res['Desc']);
                }
            }else{
                $data['val'] = $param['val'];
                $data['tenant_id'] = intval($tenant_id);
                $data['act_uid'] = intval(get_current_admin_id());
                $data['ctime'] = time();

                try{
                    M('noble_skin')->add($data);
                }catch (\Exception $e){
                    setAdminLog('添加公聊皮肤失败：'.$e->getMessage());
                    $this->error('操作失败');
                }
            }

            delNobleSkinList($tenant_id); // 清除公聊皮肤列表缓存
            $this->success('操作成功',U('skin',array('tenant_id'=>$tenant_id)));
        }

        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $this->assign('tenant_list',getTenantList());
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_id',$tenant_id);
        $this->display();
    }

    public function skinedit(){
        $param = I('param.');
        $config = getConfigPub($param['tenant_id']);
        if(IS_POST){
            if(!isset($param['id']) || !$param['id']){
                $this->error('参数错误');
            }
            if(!isset($param['val']) || !$param['val']){
                $this->error('皮肤颜色不能为空');
            }

            if(enableGolangReplacePhp() === true){
                // golang替换
                $data = array(
                    'id' => intval($param['id']),
                    'tenant_id' => intval($param['tenant_id']),
                    'val' => $param['val'],
                    'operated_by' => get_current_admin_user_login(),
                );
                $url = goAdminUrl().goAdminRouter().$this->goGroup.'/update_noble_skin';
                $http_post_res = http_post($url, $data);
                if($http_post_res['Code'] != 0){
                    $this->error('操作失败: '.$http_post_res['Desc']);
                }
            }else {
                $info = M('noble_skin')->where(['id' => intval($param['id'])])->find();

                $data['val'] = $param['val'];
                $data['act_uid'] = intval(get_current_admin_id());
                $data['mtime'] = time();

                try {
                    M('noble_skin')->where(['id' => intval($param['id'])])->save($data);
                } catch (\Exception $e) {
                    setAdminLog('添加公聊皮肤失败：' . $e->getMessage());
                    $this->error('操作失败');
                }
            }
            delNobleSkinList($info['tenant_id']); // 清除公聊皮肤缓存
            $this->success('操作成功',U('skin',array('tenant_id'=>$info['tenant_id'])));
        }

        if(enableGolangReplacePhp() === true){
            // golang替换
            $url = goAdminUrl().goAdminRouter().$this->goGroup.'/get_noble_skin_info';
            $http_post_res = http_post($url, ['tenant_id'=>intval($param['tenant_id']), 'id'=>intval($param['id'])]);
            $info = $http_post_res['Data'];
        }else {
            $id = I('id');
            if (!$id) {
                $this->error('参数错误');
            }
            $info = M('noble_skin')->where(['id' => intval($id)])->find();
        }

        $this->assign('tenant_list',getTenantList());
        $this->assign('role_id',getRoleId());
        $this->assign('info',$info);
        $this->display();
    }

    public function skindel(){
        $param = I('param.');
        if(enableGolangReplacePhp() === true){
            // golang替换
            $data = array(
                'tenant_id' => intval($param['tenant_id']),
                'id' => intval($param['id']),
            );
            $http_post_res = http_post(goAdminUrl().goAdminRouter().$this->goGroup.'/del_noble_skin', $data);
            if($http_post_res['Code'] != 0){
                $this->error('操作失败: '.$http_post_res['Desc']);
            }
            delNobleSkinList($param['tenant_id']); // 清除公聊皮肤表缓存
            $this->success('操作成功',U('skin',array('tenant_id'=>$param['tenant_id'])));
        }else {
            $id = intval($_GET['id']);
            if ($id) {
                $info = M('noble_skin')->where(['id' => intval($id)])->find();
                try {
                    M('noble_skin')->where(['id' => intval($id)])->delete();
                } catch (\Exception $e) {
                    setAdminLog('删除公聊皮肤失败：' . $e->getMessage());
                    $this->error('操作失败');
                }
                delNobleSkinList($info['tenant_id']); // 清除公聊皮肤表缓存
                setAdminLog('删除公聊皮肤成功【' . $id . '】');
                $this->success('操作成功', U('skin', array('tenant_id' => $info['tenant_id'])));
            } else {
                $this->error('参数错误');
            }
        }
        $this->display();
    }

    public function get_noble_skin_list_json(){
        $param = I('param.');
        if (!isset($param['tenant_id']) || !$param['tenant_id']) {
            $this->error('参数错误');
        }
        $skin_list = getNobleSkinList($param['tenant_id']);
        $skin_list_json = count($skin_list) > 0 ? json_encode($skin_list) : '';

        $this->success($skin_list_json);
    }

}
