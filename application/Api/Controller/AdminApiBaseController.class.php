<?php
namespace Api\Controller;
use Admin\Model\LogApiModel;
use Think\Controller;
use Common\Controller\CustRedis;

class AdminApiBaseController extends Controller {

    private $sign_key = '230eb23718974713afa2eb12002d70b6-c11301ff38b407c4510a3cc42abf4419-ADCAFD57C5A9A808DB3DC2B90CB3B80F-66ACC0D685695CD8C9520D19F0E2BECC';

    public function __construct(){
        parent::__construct();

        $this->initial();
    }

    protected function initial(){
        if(CustRedis::getInstance()->get('logapi_reqeuest_status') == 1){
            $res = LogApiModel::getInstance()->add(array_merge($_GET,$_POST),'【TP接口请求日志】');
        }

    }

    protected function check($param = array()){
        if(!isset($param['sign']) || empty($param['sign'])){
            return false;
        }
        ksort($param);
        $sign_str = '';
        foreach ($param as $key=>$val){
            if($key == 'sign'){
                continue;
            }
            $sign_str .= $key.'='.$val.'&';
        }
        $sign_str .= 'd='.date('Y-m-d').'&';
        $sign_str .= '&key='.$this->sign_key;
        $sign = md5($sign_str);
        if($param['sign'] !== $sign){
            return '签名错误 | '.$sign_str.' | '.$sign;
        }
        return true;
    }

    protected function out_put($data, $code = 20000, $msg = ''){
        $out_out = array(
            'code' => intval($code),
            'msg' => trim($msg),
            'data' => $data,
        );
        header('Content-type:text/json');
        echo json_encode($out_out);
        exit;
    }

}
