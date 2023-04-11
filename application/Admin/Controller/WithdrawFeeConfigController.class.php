<?php

/**
 * VIP管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Admin\Cache\WithdrawFeeConfigCache;

class WithdrawFeeConfigController extends AdminbaseController {

    public function index(){
        $tenant_list = getTenantList();
        if(IS_POST){
            $param = I('post.');

            $tenant_id = intval($param['tenant_id']);
            $list = M('withdraw_fee_config')->where(['tenant_id'=>getTenantIds()])->order('amount asc, id asc')->select();
            $ids_arr = array_column($list,null,'id');
            foreach ($param['data'] as $key=>$val){
                $num = $key+1;
                if($val['amount']<0 || $val['amount']>999999999){
                    $this->error('【提现手续费'.$num.'】金额输入值错误，输入范围：0 - 999999999');
                }
                if($val['fee']<0 || $val['fee']>999999){
                    $this->error('【提现手续费'.$num.'】手续费/手续费比例输入值错误，输入范围：0 - 999999');
                }

                $val['tenant_id'] = $tenant_id;
                $val['operated_by'] = get_current_admin_user_login();
                $val['amount'] = floatval($val['amount']);
                $val['type'] = intval($val['type']);
                $val['fee'] = floatval($val['fee']);
                if(isset($val['id']) && $val['id']){
                    $val['update_time'] = time();
                    M('withdraw_fee_config')->where(['id'=>intval($val['id'])])->save($val);
                    unset($ids_arr[$val['id']]);
                }else{
                    $val['create_time'] = time();
                    M('withdraw_fee_config')->add($val);
                }
            }

            $ids = array_keys($ids_arr);
            if(count($ids) > 0){
                M('withdraw_fee_config')->where(['id'=>['in',$ids], 'tenant_id'=>$tenant_id])->delete();
            }
            setAdminLog('【更新提现手续费设置】'.json_encode($param), 7, $tenant_id);
            WithdrawFeeConfigCache::getInstance()->delCache($tenant_id);
            $this->success('操作成功');
        }

        $param = $_REQUEST;
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $list = M('withdraw_fee_config')->where(['tenant_id'=>$tenant_id ])->order('amount asc, id asc')->select();
        foreach ($list as $key=>$val){
            $list[$key]['amount'] = floatval($val['amount']);
            $list[$key]['fee'] = floatval($val['fee']);
        }

        $this->assign('list', $list);
        $this->assign('tenant_list', $tenant_list);
        $this->assign('tenant_id', $tenant_id);
        $this->display();
    }

}
