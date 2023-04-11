<?php
namespace Api\Controller;
use Admin\Model\UsersCoinrecordModel;
use Think\Controller;
use Api\Controller\AdminApiBaseController;
use Common\Controller\CustRedis;
use Admin\Model\LogComplexModel;

class LogComplexController extends AdminApiBaseController {

    public function __construct(){
        parent::__construct();
    }

    /*
    * 新增综合日志记录
    * */
    public function add_complex(){
        $param = $_POST;
        $data = array();
        $data['param'] = $param;
        if(!IS_POST){
            $this->out_put($data, 1,'请求方式错误');
        }

        $check_res = $this->check($param);
        if($check_res !== true){
//            $this->out_put($data, 1,'不合法: '.$check_res.'  method: '.$_SERVER['REQUEST_METHOD'].' '.json_encode($param));
        }
        $ct = isset($param['ct']) ? $param['ct'] : '';
        $remark = isset($param['remark']) ? $param['remark'] : '';
        $type = isset($param['type']) ? $param['type'] : 1;
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : '';
        $uid = isset($param['uid']) ? $param['uid'] : 0;
        $user_acount = isset($param['user_acount']) ? $param['user_acount'] : '';
        $admin_id = isset($param['admin_id']) ? $param['admin_id'] : 0;
        $admin_acount = isset($param['admin_acount']) ? $param['admin_acount'] : '';

        LogComplexModel::getInstance()->add($ct, $remark, $type, $tenant_id, $uid, $user_acount, $admin_id, $admin_acount);
        $data['sql'] = LogComplexModel::getInstance()->getLastSql();

        if(CustRedis::getInstance()->get('log_complex_day_delete') != '200'){
            $tmonth_start = date('Y-m-d 00:00:00',strtotime(date("Y-m",time())));
            $month3_start = date('Y-m-d 00:00:00',strtotime('-2 month',strtotime($tmonth_start)));
            LogComplexModel::getInstance()->delete('ctime < '.strtotime($month3_start));
            CustRedis::getInstance()->set('log_complex_day_delete','200',60*60*24);
        }

        $this->out_put($data, 20000,'success method: '.$_SERVER['REQUEST_METHOD'].' '.json_encode($param));
    }


}
