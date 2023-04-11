<?php

/**
 * 短视频
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Composer\Package\Loader\ValidatingArrayLoader;
use QCloud\Cos\Api;
//use QCloud\Cos\Auth;
use PHPExcel\PHPExcel\APC;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Driver\FFProbeDriver;

use Qiniu\Storage\UploadManager;
use Qiniu\Auth;




class AwardLogController extends AdminbaseController {
    private $type_name = array(
        '1' => '任务奖励',
        '2' => '签到奖励',
        '3' => '邀请好友注册奖励',
        '4' => '好友消费奖励',
        '5' => '转盘奖励',
    );
    private $data_type = array(
        '1' => '钻石',
        '2' => '碎片',
        '3' => '转盘次数',
    );
    private $status_type = array(
        '1' => '已发放',
        '2' => '未完成',
        '3' => '已操作',
    );
    public  function index(){
        $param = I('param.');
        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['addtime'] = array("egt", strtotime($param['start_time']));
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['addtime'] = array("elt", strtotime($param['end_time'])+86399);
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map['addtime'] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
        }
        if(isset($param['type']) && $param['type'] !=''){
            $map['type']=$param['type'];
        }
        if(isset($param['uid']) && $param['uid'] !='') {
            $map['uid'] = $param['uid'];
        }
        if(isset($param['user_login']) && $param['user_login'] !='') {
            $map['user_login'] = $param['user_login'];
        }
        $awardLogModel =M("award_log aw");
        $count=$awardLogModel
            ->field('aw.*,u.user_login')
            ->join('cmf_users u on u.id=aw.uid','left')
            ->where($map)
            ->count();
        $page = $this->page($count);
        $lists = $awardLogModel
            ->field('aw.*,u.user_login')
            ->join('cmf_users u on u.id=aw.uid','left')
            ->where($map)
            ->order('id desc')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $this->assign('type_name', $this->type_name);
        $this->assign('data_type', $this->data_type);
        $this->assign('status_type', $this->status_type);
        $this->assign('lists', $lists);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->assign('param',$param);


        $this->display();
    }
}