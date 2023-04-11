<?php
/**
 * 提现记录
 */
namespace Appapi\Controller;
use Common\Controller\HomebaseController;
class CashController extends HomebaseController {
    
    var $status=array(
        '0'=>
            array(
                '101'=>'申请中',
                '102'=>'Application in progress',
                '103'=>'Đang thực hiện ứng dụng',
                '104'=>'สมัคร',
                '105'=>'Aplikasi dalam proses',
                '106'=>'Aplikasi dalam proses',
            ),
        '1'=>array(
            '101'=>'成功',
            '102'=>'success',
            '103'=>'thành công',
            '104'=>'ประสบความสำเร็จ',
            '105'=>'sukses',
            '106'=>'sukses',
        ),
        '2'=>array(
            '101'=>'失败',
            '102'=>'fail',
            '103'=>'hỏng',
            '104'=>'เสียเหลี่ยม',
            '105'=>'gagal',
            '106'=>'gagal',
        ),
    );
    var $tokenerror=array(
        '101'=>'您的登陆状态失效，请重新登陆！',
        '102'=>'Login invalid. Please try again!',
        '103'=>'Trạng thái đăng nhập của bạn không thành công, vui lòng đăng nhập lại!',
        '104'=>'สถานะการเข้าสู่ระบบของคุณไม่มีประสิทธิภาพ โปรดเข้าสู่ระบบอีกครั้ง!',
        '105'=>'Status log masuk anda tidak sah, sila log masuk sekali lagi!',
        '106'=>'Status login Anda tidak valid, mohon login lagi!',
    );
    var $money=array(
        '101'=>'金额',
        '102'=>'money',
        '103'=>'số tiền',
        '104'=>'ที่ถูกต้อง',
        '105'=>'yang betul',
        '106'=>'yang benar',
    );
    var $titlename=array(
    '101'=>'提现记录',
    '102'=>'Withdrawal record',
    '103'=>'Thu hồi',
    '104'=>'การบันทึก',
    '105'=>'Rekod tarik',
    '106'=>'Rekaman penarikan',
);
	function index(){       
		$uid=I("uid");
		$token=I("token");
        $language_id=I("language_id");
        if(empty($language_id)){
            $language_id = 101;
        }
		
		if( !$uid || !$token || checkToken($uid,$token)==700 ){
			$this->assign("reason",$this->tokenerror[$language_id]);
			$this->display(':error');
			exit;
		} 
		$this->assign("uid",$uid);
		$this->assign("token",$token);
        $this->assign("titlename",$this->titlename[$language_id]);
        $this->assign("money",$this->money[$language_id]);
		$list=M("users_cashrecord")->where(" uid={$uid}")->order("addtime desc")->limit(0,50)->select();
		foreach($list as $k=>$v){

			$list[$k]['addtime']=date('Y.m.d',$v['addtime']);
			$list[$k]['status_name']=$this->status[$v['status']][$language_id];
		}
		
		$this->assign("list",$list);
		
		$this->display();
	    
	}
	
	public function getlistmore()
	{
		$uid=I('uid');
		$token=I('token');
		
		$result=array(
			'data'=>array(),
			'nums'=>0,
			'isscroll'=>0,
		);
	
		if(checkToken($uid,$token)==700){
			echo json_encode($result);
			exit;
		} 
		
		$p=I('page');
		$pnums=50;
		$start=($p-1)*$pnums;

        $list=M("users_cashrecord")->where(" uid={$uid}")->order("addtime desc")->limit($start,$pnums)->select();
		foreach($list as $k=>$v){

			$list[$k]['addtime']=date('Y.m.d',$v['addtime']);
			$list[$k]['status_name']=$this->status[$v['status']];
		}
		
		$nums=count($list);
		if($nums<$pnums){
			$isscroll=0;
		}else{
			$isscroll=1;
		}
		
		$result=array(
			'data'=>$list,
			'nums'=>$nums,
			'isscroll'=>$isscroll,
		);

		echo json_encode($result);
		exit;
	}

}