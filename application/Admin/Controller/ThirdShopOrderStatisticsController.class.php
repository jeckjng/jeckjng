<?php

/**
 * 提现
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Api\Controller\ProfitShareController as ApiProfitShare;

class ThirdShopOrderStatisticsController extends AdminbaseController {

    private $status_list = array(
        '1' => array(
            'name' => '待审核',
            'color' => 'red',
        ),
        '2' => array(
            'name' => '已审核',
            'color' => 'green',
        ),
        '3' => array(
            'name' => '已拒绝',
            'color' => 'red',
        ),
        '4' => array(
            'name' => '已关闭',
            'color' => 'red',
        ),
    );

    /*
     * 外接商城单量报表
     * */
    public function index(){
        $param = I('param.');
        $timeselect = get_timeselect(); // 获取时间格式
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 100;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $data_param = array(
            'page_size'     => $page_size,
            'p'             => $p,
            'start_time'    => strtotime(date('Y-m-d 00:00:00')),
            'end_time'      => strtotime(date('Y-m-d 23:59:59')),
            'search_time_field' => 'add_time',
            'shop_id'       => 0,
            'status'        => 0,
        );
        $data_param['page_size'] = $page_size;
        $data_param['p'] = $p;

        $data_param['search_time_field'] = isset($param['search_time_type']) && $param['search_time_type'] == 1 ?  'add_time' : 'pay_time';

        $param['time_type'] = isset($param['time_type']) ? $param['time_type'] : 'today';
        if(!isset($param['start_time']) || $param['start_time'] == ''){
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];
        }
        if(!isset($param['end_time']) || $param['end_time'] == ''){
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }
        if((!isset($param['start_time']) && !isset($param['end_time'])) || ($param['start_time'] == '' && $param['end_time'] == '')){
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }

        $data_param['start_time'] = strtotime($param['start_time']);
        $data_param['end_time'] = strtotime($param['end_time'].' 23:59:59');

        if(($data_param['end_time'] - $data_param['start_time']) > 60*60*24*31){
            $this->error('时间间隔不能大于31天');
        }

        if(isset($param['shop_id']) && $param['shop_id'] != ''){
            $data_param['shop_id'] = $param['shop_id'];
        }
        if(isset($param['status']) && $param['status'] !=''){
            $data_param['status'] = $param['status'];
        }

        $tenant_info = getTenantInfo($tenant_id);
        $url = trim(trim($tenant_info['shop_url']),'/').'/api.php?s=V2order/Orderstatistics';
        $resullt_data = http_post($url, $data_param);

        $data['order_total_count'] = isset($resullt_data['data']['order_total_count']) ? floatval($resullt_data['data']['order_total_count']) : 0;
        $data['order_total_amount'] = isset($resullt_data['data']['order_total_amount']) ? floatval($resullt_data['data']['order_total_amount']) : 0;
        $data['list'] = isset($resullt_data['data']['list']) && is_array($resullt_data['data']['list']) ? $resullt_data['data']['list'] : array();

        $count = isset($resullt_data['data']['total']) ? intval($resullt_data['data']['total']) : 0;
        $page = $this->page($count, $page_size);

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('data',$data);
        $this->assign("page", $page->show('Admin'));
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('status_list', $this->status_list);
        $this->display();
    }


}
